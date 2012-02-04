<?php
	require_once('../DAO/db/db.php');
	header( 'Content-type: text/xml' );
	$link = openDBConnection();
	//$query = "INSERT INTO ChatItem VALUES ( null, null, '".mysql_real_escape_string( $_REQUEST['user'] )."', '".mysql_real_escape_string( $_REQUEST['message'] )."')";
	$query = "INSERT INTO ChatItem VALUES ( null, null, '".$_REQUEST['user']."', '".$_REQUEST['message']."')";
	$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
	closeDBConnection($link);
?>
<success />