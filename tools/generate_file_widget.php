<?php
$al = Loader::helper('concrete/asset_library');

$type = (isset($_GET['type']) && $_GET['type'] == 'image') ? 'image' : 'file';
$value = (isset($_GET['value'])) ? $_GET['value'] : null;

$file = ($value) ? File::getByID($value) : null;

echo $al->$type($_GET['key'], $_GET['key'], 'Choose '.$type.'...', $file);
exit;