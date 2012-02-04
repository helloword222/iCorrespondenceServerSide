<?php
	require_once('db/db.php');
	
	$userType = array(
		'ID' => array('name' => 'ID', 'type' => 'xsd:int'),
        'Name' => array('name' => 'Name', 'type' => 'xsd:string'),
		'DomainName' => array('name' => 'DomainName', 'type' => 'xsd:string'),
		'Password' => array('name' => 'Password', 'type' => 'xsd:string'),
		'DepartmentID' => array('name' => 'DepartmentID', 'type' => 'xsd:int')
    );
	
	$usersType = array(
		array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:userType[]')
	);
	
	// Define the method as a PHP function
	//--- Get the User by ID
	function getUserByID($userId){
		$link = openDBConnection();
		// fetch the user from  the db
		$query = "SELECT ID, Name, DomainName, Password, DepartmentID FROM User WHERE ID = $userId";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		$elements = array();
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$elements = $row;
				break;
			}
		}
		closeDBConnection($link);
		return $elements;
	}
	
	//--- Get the User by DomainName
	function getUserByDomainName($domainName){
		$link = openDBConnection();
		// fetch the user from  the db
		$query = "SELECT ID, Name, DomainName, Password, DepartmentID FROM User WHERE DomainName = '$domainName'";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		$elements = array();
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$elements = $row;
				break;
			}
		}
		closeDBConnection($link);
		return $elements;
	}
	
	//--- Get the Users by DepartmentID
	function getUsersByDepartmentID($depId){
		$link = openDBConnection();
		// fetch the users from  the db
		$query = "SELECT ID, Name, DomainName, DepartmentID FROM User WHERE DepartmentID = $depId";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		$elements = array();
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$elements[] = $row;
			}
		}
		closeDBConnection($link);
		return $elements;
	}
?>