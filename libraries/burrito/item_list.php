<?php
defined('C5_EXECUTE') or die("Access Denied.");
class BurritoItemList extends DatabaseItemList {

	var $count = 20; // how many items to load per page
	var $name = 'Params';
	
	var $sorts = array();
	
	var $startPage = 1; // default to first page
	
	public function __construct() {
		$this->name = get_class($this).'Params';
	}
	
	/* Retrieve pages or items */
	public function getAll($paged = false, $page = false) {
		
		if (isset($this->customSelect)) {
			$this->setQuery($this->customSelect);
		}
		else {
			$this->setQuery('SELECT * FROM ' . $this->table);	
		}
		
		$this->storeFields();
		
		// Apply sorts if they exist
		$sorts = $this->getSorts();
		if (!empty($sorts)) {
			$i = 0;
			foreach ($sorts as $col => $dir) {
				$this->sortByString .= $col . ' ' . $dir;
				if (($i + 1) < count($sorts)) { 
					$this->sortByString .= ', ';
				}
				$i++;
			}
		}
		elseif ($this->dateField) {
			$this->sortByString = $this->dateField.' ASC';
		}
		else {
			$this->sortByString = $this->sortBy;
		}
		
		if ($this->fieldExists('q')) {
			if (is_array($this->searchField)) {
				foreach ($this->searchField as $i => $sf) {
					$andOr = ($i == 0) ? ' AND ' : ' OR ';
					$this->addPostQuery($andOr.$sf.' LIKE "%'.$this->getField('q').'%"');
				}
			}
			else {
				$this->filter($this->searchField, '%'.$this->getField('q').'%', 'LIKE');
			}
		}
		
		if ($this->fieldExists('d1')) {
			$date = date('Y-m-d', strtotime($this->getField('d1')));
			
			if ($this->fieldExists('d2')) {
				// if there's an end date, too, this adds a "date BETWEEN x AND y" filter
				// kind of weird syntax though
				
				$this->filter($this->dateField, $date, 'BETWEEN');
				$this->filter('', '"'.date('Y-m-d', strtotime($this->getField('d2'))).'"');
			}
			else {
				// otherwise just show all posts that start after the start date
				$this->filter($this->dateField, $date, '>');
			}
		}
		else if ($this->fieldExists('d2')) {
			// no start date, just show everything up to the end date
			$date = date('Y-m-d', strtotime($this->getField('d2')));
			$this->filter($this->dateField, $date, '<');
		}

		$items = $this->get();

		if ($paged){
			$this->setItemsPerPage($this->count);
			
			// if there's no page parameter, figure out what page contains the most recent items
			if (!isset($_GET['ccm_paging_p']) && empty($sorts)) {
				$i = 0;
				foreach ($items as $item) {
					$today = strtotime('today');

					if (strtotime($item[$this->dateField]) > $today) {
						$page = $i / $this->count;
						$this->startPage = (int)$page + 1;
						break;
					}

					$i++;
				}
			}
			else {
				$this->startPage = $_GET['ccm_paging_p'];
			}
					
			return $this->getPage($this->startPage);
		}
		else {
			return $items;
		}
	}
	
	protected function fieldExists($fieldName) {
		$params = $this->getFields();
		return (isset($params[$fieldName]) && $params[$fieldName] != '' && $params[$fieldName] != null);
	}
	
	protected function getField($key) {
		$params = $this->getFields();
		return $params[$key];
	}
	
	public function getFields() {
		$params = $_GET;
		if (empty($params) || isset($params['ccm_paging_p'])) {
			$params = $_SESSION[$this->name];
		}
		return $params;
	}
	
	protected function storeFields() {
		if (!empty($_GET) && !isset($_GET['ccm_paging_p'])) {
			$_SESSION[$this->name] = $_GET;
		}
	}
	
	public function resetFields() {
		unset($_SESSION[$this->name]);
		unset($_SESSION[$this->getSortKey()]);
	}
	
	public function getUnfilteredTotal() {
		$db = Loader::db();
		$r = $db->GetOne('SELECT COUNT(*) FROM '.$this->table);
		return $r;
	}
	
	public function isFiltered() {
		// If the count of items returned does not match the total in the database,
		// there's a filter applied. Not totally accurate but acceptable for our needs.
		return ($this->getUnfilteredTotal() != $this->getTotal());
	}
	
	public function modifySort($column, $dir = 'asc') {
		// $dir can be 'asc', 'desc', 'remove' (removes the sort)
		$sortKey = $this->getSortKey();
		if (!is_array($_SESSION[$sortKey])) {
			$_SESSION[$sortKey] = array();
		}
		
		if ($dir != 'remove') {
			$_SESSION[$sortKey][$column] = $dir;
		}
		else {
			unset($_SESSION[$sortKey][$column]);
		}
	}
	
	protected function getSortKey() {
		return $this->name . 'Sorts';
	}
	
	public function getSorts() {
		return $_SESSION[$this->getSortKey()];
	}
	
	public function addPostQuery($q) {
		$this->userPostQuery .= $q;
	}
	
}