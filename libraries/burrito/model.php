<?php 

defined('C5_EXECUTE') or die("Access Denied.");

class BurritoModel extends ADOdb_Active_Record {
	
	public function __construct() {
	 	$db = Loader::db();
	 	parent::__construct();
	}
	
	/* 
		Children should override this method and follow the field convention.
	*/
	public function getFields() {
		return array();
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
	static public function factory($handle, $data=null){
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
			if ($value == '') {
				$value = null;
			}
			$this->$key = $value;
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
	
	/*	*/
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

if (!class_exists('BModel')) {
	class_alias('BurritoModel', 'BModel');
}

?>