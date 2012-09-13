<?php 
defined('C5_EXECUTE') or die('Access Denied.');

/*
	# Access Helper
	
		Allows for simple checking of the logged in user's group memberships.
		Acts in a "is user at least" model.
	
	## Usage Example
	
		if (AccessHelper::canAccess("editor")) {
			// stuff that only editors and above can do
		}
		
		Will return true if logged in user is at least an "Editor"
			
		Define role handles ("admin", "guest", etc) in /config/burrito/access.ini
		Map each custom group handle with the corresponding Group's ID in the database.
		
		This allows you to refer to groups with names rather than IDs.
	
	## Example access.ini config:
	
		[production]
		admin = 3
		editor = 186
		
	## Using Access Levels in Models
		
		If you'd wish to limit individual field visibility on a Model, simply add a
		"minimum_level" option with the Group handle as the value.
	
*/

class AccessHelper {

	static $levels = array();
	
	public function __construct() {
		self::$levels = BurritoConfig::env('access');
	}
	
	public function setAccessLevels($levels = array()) {
		self::$levels = $levels;
	}

	public function getAccessLevels() {
		return self::$levels;
	}

	public static function canAccess($level) {
		switch ($level) {
			case 'admin':
				return self::isUserAdmin();
				break;
			case 'editor':
				return self::isUserEditor();
				break;
			case 'division':
				return self::isUserDivisionRep();
				break;
			case 'department':
				return self::isUserDepartmentRep();
				break;
		}
		return false;
	}

	private function isUserAdmin() {
		$u = new User();
		$adminGroup = Group::getByID(self::$levels['admin']);
		return ($u->inGroup($adminGroup) || $u->isSuperUser());
	}

	private function isUserEditor() {
		$u = new User();
		$editorsGroup = Group::getByID(self::$levels['editor']);
		return ($u->inGroup($editorsGroup) || self::isUserAdmin());
	}

	private function isUserDivisionRep() {
		$u = new User();
		$divisionGroup = Group::getByID(self::$levels['division']);
		return ($u->inGroup($divisionGroup) || self::isUserEditor());
	}

	private function isUserDepartmentRep() {
		$u = new User();
		$deptGroup = Group::getByID(self::$levels['department']);
		return ($u->inGroup($deptGroup) || self::isUserDivisionRep());
	}

}