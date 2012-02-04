<?php
	require_once('db/db.php');
	
	$documentContentType = array(
        'Name' => array('name' => 'Name', 'type' => 'xsd:string'),
        'Description' => array('name' => 'Description', 'type' => 'xsd:string'),
        'Path' => array('name' => 'Path', 'type' => 'xsd:string'),
        'Size' => array('name' => 'Size', 'type' => 'xsd:int'),
        'MimeType' => array('name' => 'MimeType', 'type' => 'xsd:string')
    );
	
	$documentLogType = array(
		'Subject' => array('name' => 'Subject', 'type' => 'xsd:string'),
		'Sender' => array('name' => 'Sender', 'type' => 'xsd:string'),		
		'Receiver' => array('name' => 'Receiver', 'type' => 'xsd:string'),
		'Comment' => array('name' => 'Comment', 'type' => 'xsd:string'),
		'Date' => array('name' => 'Date', 'type' => 'xsd:date'),
		'Action' => array('name' => 'Action', 'type' => 'xsd:string')
	);
	
	$documentLogsType = array(
		array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:documentLog[]')
	);
	
	// Define the method as a PHP function
	//--- Get the Document Content
	function getDocumentContent($docId, $contentIndex){
		$link = openDBConnection();
		// fetch the inbox count from  the db
		$query = "SELECT Name, Description, Path, Size, MimeType FROM DocumentContent WHERE DocumentID = $docId LIMIT $contentIndex, 1";
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
	
	//--- Get the Document Logs
	function getDocumentLogs($docId){
		$link = openDBConnection();
		
		$query = "SELECT Subject, Sender, Receiver, Comment, Date, Action FROM DocumentLog WHERE DocumentID = $docId";
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