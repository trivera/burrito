<?php

defined('C5_EXECUTE') or die(_("Access Denied."));
ini_set("auto_detect_line_endings", true);

/**
 * Csv Import Wrapper
 * 
 *  Example Implementation
 *  ======================
 *  Loader::library('csv/import', 'burrito');
 *  
 *  class ProductImport extends CsvImport {
 *  	
 *  	protected $fields = array(
 *  		'product_id'		=> 'int',
 *  		'title'				=> 'string',
 *  		'description'		=> 'string',
 * 			'quantity'			=> 'int',
 *  		'created_on' 		=> 'date',
 *  		'updated_on' 		=> 'date',
 *  	);
 *  	
 *  	protected function before($db) {
 *  		Loader::model('product');
 *  		$db->execute("TRUNCATE Products");
 *  	}
 *  	
 *  	protected function eachRow($db, $data) {
 *  		$p = new Product();
 *  		$p->save($data);
 *  	}
 *  	
 *  	protected function after($db) {
 * 			// update some cache tables
 *  		$db->execute("UPDATE ProductCache ...");
 *  	}	
 *  }
 * 
 *  Example Usage
 *  =============
 *  try {
 *  	Loader::library('product_import');
 *  	$import = new ProductImport('path/to/product_data.csv');
 *  	$import->run();
 *  	FlashHelper::notice("Success! Imported " . $import->getTotal('rows') . "Products");
 *  } catch (Exception $e) {
 *  	FlashHelper::error($e->getMessage());
 *  }
 *  
 */
abstract class CsvImport {
	
	abstract protected function eachRow($db, $data);
	
	private $accepted_mime_types = array('text/csv', 'text/plain');
	
	private $db;
	private $filename;
	private $h;
	private $row_totals = array(
		'rows' => 0,
		'good' => 0,
		'bad' => 0
	);
	
	public function __construct($filename) {
		
		$this->db = Loader::db();
		$this->filename = $filename;
		
		$this->checkMimeType();
		
		$this->h = $this->getHandle();
	}
	
	private function checkMimeType() {
		
		$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
		
		if (!in_array(($_=finfo_file($finfo, $this->filename)), $this->accepted_mime_types)) {
			throw new Exception("You must upload a valid CSV file; a file of type {$_} was given");
		}
		
		fclose($finfo);
	}
	
	private function getHandle(){
		
		if (($h=fopen($this->filename, 'r')) === false) {
			throw new Exception("Could not open {$this->filename}; please ensure you select a valid CSV file");
		}
		
		return $h;
	}
	
	private function callback() {
		
		$args = func_get_args();
		$callback = array_shift($args);
		
		if (method_exists($this, $callback) && call_user_func_array(array($this, $callback), $args) === false) {
			$this->db->execute('rollback');
			return false;
		}
		
		return true;
	}
	
	private function iterate() {
		
		// skip header row
		$row = fgetcsv($this->h); 
		
		// loop throw all rows
		while (($row = fgetcsv($this->h)) !== false) {
			
			// map cells to fields
			$data = array_combine(array_keys($this->fields), array_slice($row, 0, count($this->fields)));
			
			if ($this->eachRow($this->db, $this->cleanse($data)) === false) {
				$this->row_totals['bad'] += 1;
			}
			else {
				$this->row_totals['good'] += 1;
			}
			
			$this->row_totals['rows'] += 1;
		}
		
		return true;
	}
	
	private function cleanse($data) {
		
		foreach($data as $field => $value) {
			
			if (strlen($value) && method_exists($this, $_="{$this->fields[$field]}Format")) {
				$data[$field] = $this->{$_}($value);
			}
		}
		
		return $data;
	}
	
	protected function dateFormat($value){
 		return date('Y-m-d', strtotime($value));
	}
	
	protected function datetimeFormat($value){
		return date('Y-m-d H:i:s', strtotime($value));
	}
	
	final public function run() {
		
		$this->db->execute('begin');
		
		return $this->callback('before', $this->db)
			&& $this->iterate()
			&& $this->callback('after', $this->db)
			&& $this->db->execute('commit')
			&& fclose($this->h)
		;
		
		
	}
	
	public function getTotal($key) {
		return array_key_exists($key, $this->row_totals)
			? $this->row_totals[$key]
			: false
		;
	}	
}
