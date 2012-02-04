<?php
	function openDBConnection(){
		$link = mysql_connect('mysql.holooli.com', 'saeed_user', 'Z%@DSz2a34') or die('Cannot connect to the DB server');
		mysql_select_db('saeed_db', $link) or die('Cannot select the DB');
		mysql_query("SET NAMES utf8", $link);
		mysql_query("SET CHARACTER SET utf-8", $link);
		return $link;
	}

	function closeDBConnection($link){
		// disconnect from the db
		@mysql_close($link);
	}
?>