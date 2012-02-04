<?php
require_once('nusoap/lib/nusoap.php');
function getCityNames($cityCode){
	$cityNames = array('1'=>'Damascus', '2'=>'Aleppo', '3'=>'Homs');
	return $cityNames['$cityCode'];
}

$server = new soap_server;
$server->register('getCityName');
$server->service($_SERVER['HTTP_RAW_POST_DATA']);
exit();












?>