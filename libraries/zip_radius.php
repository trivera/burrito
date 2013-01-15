<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * ZipRadius
 * Some Zip code math functions
 * Requires the Zip Code database from http://zips.sourceforge.net/
 * OR another Zip Code database containing lat/long data (and with the proper column names)
 * Based off of https://www.dougv.com/2009/03/27/getting-all-zip-codes-in-a-given-radius-from-a-known-point-zip-code-via-php-and-mysql/
 * @author David Strack <davidstrack@icloud.com>
 * @copyright 2013 Trivera Interactive
 */

class ZipRadius {

	protected $zipTable = 'ZipCodes';
	protected $earthRadius = 3959; // radius of the earth in miles

	public function getZipsInRadius($centerZip, $miles = '5', $sort = true) {
		$db = Loader::db();

		$row = $db->getAll('SELECT * FROM '.$this->getZipTable().' WHERE zip = ?', $centerZip);

		$zip = $row[0];

		$lat1 = $zip['latitude'];
		$lon1 = $zip['longitude'];

		// shorthand variables used in below equations
		$d = $miles;
		$r = $this->earthRadius;

		// compute lat/long ranges
		$latN = rad2deg(asin(sin(deg2rad($lat1)) * cos($d / $r) + cos(deg2rad($lat1)) * sin($d / $r) * cos(deg2rad(0))));
		$latS = rad2deg(asin(sin(deg2rad($lat1)) * cos($d / $r) + cos(deg2rad($lat1)) * sin($d / $r) * cos(deg2rad(180))));
		$lonE = rad2deg(deg2rad($lon1) + atan2(sin(deg2rad(90)) * sin($d / $r) * cos(deg2rad($lat1)), cos($d / $r) - sin(deg2rad($lat1)) * sin(deg2rad($latN))));
		$lonW = rad2deg(deg2rad($lon1) + atan2(sin(deg2rad(270)) * sin($d / $r) * cos(deg2rad($lat1)), cos($d / $r) - sin(deg2rad($lat1)) * sin(deg2rad($latN))));

		// find all coordinates in the lat/long range
		$table = $this->getZipTable();
		$query = "SELECT * FROM $table WHERE (latitude <= $latN AND latitude >= $latS AND longitude <= $lonE AND longitude >= $lonW) AND city != '' ORDER BY state, city, latitude, longitude";

		$results = $db->getAll($query);

		$zips = array();

		foreach ($results as $row) {
			$item = $row;

			// add the distance from the source zip
			$item['distance'] = $this->milesBetween($lat1, $lon1, $row['latitude'], $row['longitude']);

			$zips[] = $item;
		}

		// sort by distance
		if ($sort) {
			usort($zips, function($a, $b) {
				if ($a['distance'] == $b['distance']) {
					return 0;
				}
				return ($a['distance'] < $b['distance']) ? -1 : 1;
			});
		}

		return $zips;
	}

	public function milesBetween($lat1, $lon1, $lat2, $lon2) {
		$r = $this->earthRadius;
		$distance = acos(sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2) - deg2rad($lon1))) * $r;
		return number_format($distance, 1);
	}

	public function milesBetweenZips($zip1, $zip2) {
		$db = Loader::db();
		$table = $this->getZipTable();
		$sql = "SELECT * FROM {$table} WHERE zip = ?";
		$r1 = $db->getAll($sql, $zip1);
		$r2 = $db->getAll($sql, $zip2);

		$r1 = $r1[0];
		$r2 = $r2[0];

		return $this->milesBetween($r1['latitude'], $r1['longitude'], $r2['latitude'], $r2['longitude']);
	}

	public function setZipTable($table) {
		$this->zipTable = $table;
	}

	public function getZipTable() {
		return $this->zipTable;
	}

}