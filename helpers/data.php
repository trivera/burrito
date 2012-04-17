<?php 

defined('C5_EXECUTE') or die('Access Denied.');

class DataHelper {

	/* 
		Takes an array of fields and an array of corresponding data.
		Runs them through simple validations and also cleans stuff up for 
		database use. Returns the cleaned data array.
	*/
	public function validateAndCleanup($fields, $data) {
		$val = Loader::helper('validation/form');
		$dth = Loader::helper('form/date_time');
		
		foreach ($fields as $key => $field) {			
			if (($field['type'] == 'datetime' || $field['type'] == 'date')) {
				if (($field['required'] || (!$field['required'] && $data[$key.'-toggle']))) {
					$data[$key] = $dth->translate($key);
				}
				else {
					$data[$key] = null;
				}
			}
			elseif ($field['type'] == 'image') {
				$data[$key] = ($data[$key] == '0') ? null : $data[$key];
			}
			if ($field['multi'] && $field['required']) {
				if (empty($data[$key]) || !$data[$key][0]) {
					FlashHelper::error('You must choose at least one '.$field['label']);
				}
			}
			elseif ($field['required']) {
				$val->addRequired($key, $field['label'].' is required.');
			}
		}
		
		$val->setData($data);
		
		if (!$val->test()) {
			FlashHelper::error($val->getError()->getList());
		}
		
		return $data;
	}
	
	public function saveMulti($fields, $data, $id) {
		foreach ($fields as $key => $field) {
			if ($field['multi']) {
				$model = Burrito::get($field['relation_model']);
				
				// delete any existing ones
				foreach ($model->find($field['foreign_key'].' = ?', $id) as $item) {
					$item->delete();
				}
				
				foreach ($data[$key] as $value) {
					if ($value) {
						$model->save(array(
							$field['foreign_key'] => $id,
							$key => $value
						));	
					}
				}
				
			}
		}
	}

}