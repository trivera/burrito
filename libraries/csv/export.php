<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * Csv Export Wrapper
 *
 * 	Example Implementation
 *  ======================
 *  Loader::library('csv/export', 'burrito');
 *  
 *  class ProductExport extends CsvExport {
 *  
 *  	protected function filename() {
 *  		return 'Products-' . time();
 *  	}
 *  
 *  	protected function getData($db){
 *  		return $db->execute("SELECT * FROM Products")->getRows();
 *  	}
 *  
 *  	protected function before($db) {
 *  
 *  	}
 * 
 * 		protected function header($db, $first_row) {
 * 			return array_keys($first_row);
 * 		}
 *  
 *  	protected function eachRow($db, $row) {
 *  		return array_values($row);
 *  	}
 *  
 *  	protected function after($db) {
 *  
 *  	}	
 *  }
 * 
 * 
 *  Example Usage
 *  =============
 * 	try {
 * 		Loader::library('product_export');
 * 		$export = new ProductExport();
 * 		$export->run();
 * 		exit;
 * 	} catch (Exception $e) {
 * 		FlashHelper::error($e->getMessage());
 * 	}
 */
abstract class CsvExport {
	
	abstract protected function filename();
	abstract protected function getData($db);
	abstract protected function eachRow($db, $row);
	
	const FILE_EXTENSION = 'csv';
	const MIME_TYPE = 'text/csv';
	
	private $db;
	private $filename;
	private $h;
	private $data;
	
	public function __construct() {		
		$this->db = Loader::db();
	}
	
	private function init() {
		
		$this->h = $this->getHandle();
		$this->data = $this->getData($this->db);
		
		if (strlen($this->filename()) <= 0) {
			throw new Exception('CsvExport#filename must return a valid base filename');
		}
				
		if (!is_array($this->data)) {
			throw new Exception('CsvExport#getData must return an associated array');
		}
		
		if (empty($this->data)) {
			throw new Exception('There is not data to export');
		}
		
		return true;
	}
	
	private function getHandle() {
		
		if (($h=fopen('php://output', 'w')) === false) {
			throw new Exception('There was an error opening the output stream');
		}
		
		return $h;
	}
	
	
	
	private function callback() {
		
		$args = func_get_args();
		$callback = array_shift($args);
		
		if (method_exists($this, $callback) && call_user_func_array(array($this, $callback), $args) === false) {
			return false;
		}
		
		return true;
	}
	
	private function output(){
		$csv = $this->capture();
		
	    header('Content-type: ' . self::MIME_TYPE);
        header('Content-Disposition: attachment; filename="' . $this->filename() . '.' . self::FILE_EXTENSION . '"');
        header('Content-Length: ' . strlen($csv));
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		echo $csv;
	}
	
	private function capture(){
		ob_start();
		
		// header
        fputcsv($this->h, $this->header($this->db, $this->data[0]));
		
		// rows
		foreach($this->data as $row){
            fputcsv($this->h, $this->eachRow($this->db, $row));
        }

		$csv = ob_get_clean();
		
		if (fclose($this->h) === false) {
			throw new Exception('There was an error closing the output stream');
		}
		
		return $csv;
	}
	
	final public function run() {
		return $this->init()
			&& $this->callback('before', $this->db)
			&& $this->output()
			&& $this->callback('after', $this->db)
		;	
		
		
	}
	
	
}