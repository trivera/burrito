<?php 

defined('C5_EXECUTE') or die('Access Denied.');

/* 
	This helper will take a field "object" and output the proper field input HTML
*/

class FieldHelper {
	
	public function __construct() {
		$this->form = Loader::helper('burrito/form', 'burrito');
	}

	public function output($key, $field, $data = array(), $autoEcho = false) {	
		$baseKey = $key; // store the original key since it can get modified below
		$editMode = (!empty($data[$key]));
		
		if ($editMode && $field['multi'] && !$this->isFileField($field)) {
			$html = '';
			foreach ($data[$key] as $value) {
				if (is_object($value)) {
					$value = $value->$key;
				}
				$html .= '<div class="multi-clone">';
				$html .= $this->generateHtml($key.'[]', $field, $value);
				$html .= '</div>';
			}
		}
		elseif (!($field['multi'] && $this->isFileField($field) && $editMode)) {
			// Weird condition here but basically we don't want to generate HTML for multi-file type fields when in edit mode
			$value = $data[$key];
			$key = ($field['multi'] && !$this->isFileField($field)) ? $key.'[]' : $key;
			$html = $this->generateHtml($key, $field, $value, $data);
		}
		
		$this->form->reset();
		
		// autoEcho preserves backwards compatibility with older (likely moldy) burritos that do not auto-echo
		if ($autoEcho) {
			echo $html;
			if ($field['multi']) {
				$element = ($this->isFileField($field)) ? 'multi_file' : 'multi';
				Loader::packageElement($element, 'burrito', array('key' => $baseKey, 'field' => $field, 'data' => $data));
			}
		}
		
		
		
		return $html;
	}
	
	/* Is this field a special C5 Asset Library widget? */
	public function isFileField($field) {
		$fileFields = array('file', 'image');
		return in_array($field['type'], $fileFields);
	}
	
	/* Generates the actual HTML used to display a field in a model */
	public function generateHtml($key, $field, $value = null, $data = array()) {
		$dtt = Loader::helper('form/date_time');
		$al = Loader::helper('concrete/asset_library');
		
		switch ($field['type']) {
			case 'text':
				$html = $this->form->text($key, $value);
				break;
			case 'textarea':
				$html = $this->form->textarea($key, $value);
				break;
			case 'datetime':
				if ($field['required']) {
					$html = $dtt->datetime($key, $value);
				}
				else {
					$checked = (isset($value)) ? ' checked' : '';
					$html = '<input type="checkbox" class="date-toggle" value="yes" name="'.$key.'-toggle"'.$checked.'>'.$dtt->datetime($key, $value);
				}
				break;
			case 'date':
				$html = $dtt->date($key, $value);
				break;
			case 'boolean':
				$html = $this->form->select($key, array(
					'0' => 'No',
					'1' => 'Yes'
				), $value);
				break;
			case 'select':
				$html = $this->form->select($key, $field['options'], $value);
				break;
			case 'combo':
				$html = $this->form->select($key, $field['options'], $value, array('class' => 'combobox'));
				$html .= '
					<script type="text/javascript" charset="utf-8">
						$("#'.$key.'").combobox();
					</script>
				';
				break;
			case 'page':
				Loader::packageElement('page_options', 'burrito', array('key' => $key, 'data' => $data));
				break;
			case 'wysiwyg':
				Loader::element('editor_controls');
				$html = $this->form->textarea($key, $value, array('style' => 'width:100%;', 'class' => 'ccm-advanced-editor'));
				break;
			case 'checkbox_list':
				$html = $this->buildCheckboxList($field['items'], $key, $data);
				break;
			case 'image':
				// d($value);
				if (isset($value) && $value) {
					$file = File::getByID($value);
				}
				$html = $al->image($key, $key, 'Choose image...', $file);
				break;
			case 'file':
				if (isset($value) && $value) {
					$file = File::getByID($value);
				}
				$html = $al->file($key, $key, 'Choose file...', $file);
				break;
			case 'schedule':
				Loader::packageElement('schedule', 'burrito', array('key' => $key, 'field' => $field, 'data' => $data));
				break;
			case 'group':
				$db = Loader::db();
				
				$sql = 'select * from Groups where gID > 3 order by gName asc';
				$r = $db->getAll($sql);
				$opts = array(null => 'Select a group...');
				
				foreach ($r as $row) {
					$opts[$row['gID']] = $row['gName'];
				}
				
				$html = $this->form->select($key, $opts, $value);
				break;
			case 'user':
				break;
			case 'price':
				$html = $this->form->text($key, $value);
				break;
		}
		
		return $html;
	}
	
	private function buildCheckboxList($checkboxes, $groupName = 'options', $data = array()) {
		$html = '';

		foreach ($checkboxes as $handle => $name) {
			$checked = (isset($data[$groupName][$handle])) ? true : false;
			$html .= '<label class="checkbox-item">'.$this->form->checkbox($groupName.'[]', $handle, $checked).$name.'</label>';
		}
		return $html;
	}
	
}