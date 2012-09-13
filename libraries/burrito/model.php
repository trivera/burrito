<?php 
defined('C5_EXECUTE') or die("Access Denied.");

class BurritoModel extends ADOdb_Active_Record {
	
	public function __construct($data=null) {
	 	$db = Loader::db();
	 	parent::__construct();
	
		if (is_array($data)) {
			$this->setData($data);
		}
	}
	
	/* 
		Children should override this method and follow the field convention.
	*/
	public function getFields() {
		return array();
	}
	
	/* 
		This is a function to return either all of the data for the fields on this model
		or just a single value for a field. Handy for getting data for "multi" fields.
	*/
	public function getData($key = null) {
		$idKey = $this->getIdKey();
		$fields = $this->getFields();
		
		if ($key) {
			// Return a single value
			$field = $fields[$key]; // load the field options
			
			if ($key == 'id') {
				return $this->id;
			}
			elseif (!$field['multi']) {
				return $this->{$key};
			}
			else {
				// Return an array of all of the related values
				$model = BModel::get($field['relation_model']);
				return $model->find($field['foreign_key'].' = ?', $this->{$idKey});
			}
		}
		else {
			// return everything
			$data = array(
				$idKey => $this->{$idKey}
			);
			foreach ($fields as $key => $field) {
				$data[$key] = $this->getData($key);
			}
			return $data;
		}
	}
	
	/* Supports ID columns not called "id" */
	public function getIdKey() {
		return ($this->customIdKey) ? $this->customIdKey : 'id';
	}
	
	/* 
		Skip all of the Loader::model BS and just get a class
	*/
	public static function get($handle, $id = null, $idKey = 'id') {
		$th = Loader::helper('text');

		Loader::model($handle);
		$className = $th->camelcase($handle);
		$class = new $className;
		if ($id) {
			$class->loadById($id, $idKey);	
		}
		return $class;
	}
	
	/*
	   Skip all of the Loader::model BS and just saturate a class with pre-fetched data
	*/
	static public function factory($handle, $data = null){
	    $th = Loader::helper('text');
	    $className = $th->camelcase($handle);
	    
	    Loader::model($handle);
        $object = new $className();
        if (is_array($data)) {
            $object->setData($data);
        }
        
        return $object;
	}
	
	
	/*
		Initializes an object with an existing one in the database.
	*/
	public function loadById($id, $idKey = 'id') {
		$this->load($idKey.' = ?', $id);
	}
	
	
	/*
		Returns an array of all objects in this table.
	*/
	public function all() {
		return $this->find('1=1');
	}
	
	
	/*
		Loop through an array and set each value to this object.
	*/
	public function setData($data) {
		foreach ($data as $key => $value) {
			
			// PHP thinks 0 == '', so this had to be updated
			if (is_string($value) && empty($value)) {
				$value = null;
			}
			
			$this->{$key} = $value;
		}
	}
	
	/*
		Optionally sets the data in the array,
		then calls replace() to insert/update the record.
	*/
	
	public function save($data = null) {
		if ($data != null) {
			$this->setData($data);
		}
		parent::replace();
	}
	
	
	public function getFile($field) {
		return File::getById($this->{$field});
	}
	
	public function getThumbnail($field, $width=100, $height=100, $options=array()) {
		if ($_ = $this->getFile($field)) {
			
			$bih = Loader::helper('burrito/image', 'burrito');
			return $bih->outputThumbnail($_, $width, $height, $options);
		}

		return null;
	}
	
	
	/*
		Generates & returns a key => value array suitable for using in C5's 
		form helper select input function.
	*/
	
	public function getSelectOptions($idKey = 'id', $displayKey = 'name', $blankText = null) {
		$items = array();
		if ($blankText) {
			$items['NULL'] = $blankText;
		}
		foreach ($this->find('1=1 ORDER BY '.$displayKey.' ASC') as $item) {
			$items[$item->$idKey] = $item->$displayKey;
		}
		return $items;
	}
	
	/*	
		This will travel through the sitemap to figure out what record to load given a page ID.
	*/
	static public function getObjectFromPage($objectHandle, $pageId=null, $pageColumn="page_id") {
		
		if (is_null($pageId)) {
			$page = Page::getCurrentPage();
			$pageId = $page->getCollectionID();
		}
		
		$object = BModel::get($objectHandle, $pageId, $pageColumn);
		
		// return object if found
		if ($object->id) {
			return $object;
		}
		
		// recursively check ancestor pages for a match
		else {
			$page = Page::getById($pageId);
			if ($parent=$page->getCollectionParentID()) {
				return self::getObjectFromPage($objectHandle, $parent, $pageColumn);
			}
			else {
				return false;
			}
		}
	}	
}

if (!class_exists('BModel') && function_exists('class_alias')) {
	class_alias('BurritoModel', 'BModel');
}