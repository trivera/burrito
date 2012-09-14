<?php 
defined('C5_EXECUTE') or die("Access Denied.");

class BurritoModel extends ADOdb_Active_Record {
	
	// override constructor
	public function __construct($data=null) {
	 	$db = Loader::db(); # THIS LINE IS REQUIRED
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
	public function all($appendSql=null) {
		return $this->find("1=1 {$appendSql}");
	}
	
	
	/*
		Loop through an array and set each value to this object.
	*/
	public function setData($data) {
		foreach ($data as $key => $value) {
			
			// PHP thinks 0 == '', so this had to be updated to ===
			// empty strings are the only values to be coerced into NULL
			if ($value === '') {
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
		
		// after a record is saved, an ID is set
		// this flag is used to trigger the appropriate
		// create OR update callback
		$this->magic('action', $this->id ? 'update' : 'create');

		// pre callbacks
		$this->__callbacks('before');
		
		// set data helper
		if ($data != null) {
			$this->setData($data);
		}
		
		// magic fields
		$this->applyMagic();
		
		// parent op
		parent::replace();
		
		// post callbacks
		$this->__callbacks('after');
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
	public function getSelectOptions($idKey = 'id', $displayKey = 'name', $blankText = null, $blankKey = '') {
		$items = array();
		if ($blankText) {
			$items[$blankKey] = $blankText;
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
	
	/**
	 *  this is used to accompany some of the magic
	 *  field and callbacks
	 */
	private $__magic = array();
	
	/**
	 * magic getter/setter
	 */
	private function magic($key, $value=null) {
		
		// get
		if (is_null($value)) {
			return array_key_exists($key, $this->__magic)
				? $this->__magic[$key]
				: null
			;
		}
		
		// set
		else {
			return $this->__magic[$key] = $value;
		}
	}
	
	
	/**
	 *  this function allows you to declare callbacks that
	 *  automatically perform tasks on fields
	 * 
	 * 	example:
	 *  protected function __magic_created_at() {...}
	 *  
	 *  in this example, if your model has a `created_at` field,
	 * 	this method will be called
	 */
	private function applyMagic() {
		foreach (get_class_methods($this) as $method) {
			if (preg_match('/^__magic_(\w+)$/', $method, $_) !== false) {
				if (property_exists($this, $_[1])) {
					call_user_func(array($this, $method));
				}
			}
		}
	}
	
	// auto set datetime if created_at field exists
	protected function __magic_created_at() {
		if (empty($this->created_at)) {
			$this->created_at = date('Y-m-d h:i:s');
		}
	}
	
	// timestamp if updated_at field exists
	protected function __magic_updated_at() {
		$this->updated_at = date('Y-m-d h:i:s');
	}
	
	private function __callbacks($prefix) {
		
		$th = Loader::helper('text');
		
		$callbacks = array(
			'create' 	=> $this->magic('action')=='create',
			'update' 	=> $this->magic('action')=='update',
			'save'		=> true
		);
		
		foreach ($callbacks as $event => $trigger) {
			if (method_exists($this, $fn=$prefix.$th->camelcase($event)) && $trigger) {
				call_user_func(array($this, $fn));
			}
		}
	}
}

if (!class_exists('BModel') && function_exists('class_alias')) {
	class_alias('BurritoModel', 'BModel');
}
