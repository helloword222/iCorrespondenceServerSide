<?php
	require_once('db/db.php');
	
	$departmentType = array(
		'ID' => array('name' => 'ID', 'type' => 'xsd:int'),
        'Name' => array('name' => 'Name', 'type' => 'xsd:string'),
		'DepartmentID' => array('name' => 'DepartmentID', 'type' => 'xsd:int')
    );
	
	$departmentsType = array(
		array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:departmentType[]')
	);
	
	// Define the method as a PHP function
	//--- Get the Department by ID
	function getDepartmentByID($depId){
		$link = openDBConnection();
		// fetch the department from  the db
		$query = "SELECT ID, Name, DepartmentID FROM Department WHERE ID = $depId";
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
	
	//--- Get the Department Children by ParentID
	function getDepartmentChildren($parentId){
		$link = openDBConnection();
		// fetch the department from  the db
		$query = "SELECT ID, Name, DepartmentID FROM Department WHERE DepartmentID = $parentId";
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