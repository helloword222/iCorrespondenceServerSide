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
	
	//--- open the document fully loaded (content)
	function openDocument($docId){
		$document = array('contents'=>getDocumentContent($docId, -1));
		return $document;
	}
	//--- Get the document Content data
	function writeDocumentContentToResponse($docId, $contentIndex){
		$contentMetaData = getDocumentContent($docId, $contentIndex);
		if(count($contentMetaData) > 0){
			$filepath = $contentMetaData[0]['item']['Path'];
			$mimeType = $contentMetaData[0]['item']['MimeType'];
			$filename = $contentMetaData[0]['item']['Name'];
			$filesize = filesize($filepath);
			//$filename = basename($filepath);
			header("Pragma: public");
			header("Expires: 0");
			header("Pragma: no-cache");
			header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header('Content-disposition: attachment; filename=' . $filename);
			header("Content-Type: $mimeType");
			header("Content-Transfer-Encoding: binary");
			header('Content-Length: ' . $filesize);
			@readfile($filepath);
			//echo "<br/>$filesize<br/>$filename<br/>$mimeType<br/>$filePath";
		}
	}
	
	function _getDocumentInfo($docId, $link){
		$query = "SELECT d.Subject Subject, d.ID DocumentID, d.SecurityLevel SecurityLevel, d.PriorityLevel PriorityLevel FROM Document d WHERE d.ID = $docId";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		$elements = array();
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$elements[] = $row;
			}
		}
		return $elements;
	}
	
	function getDocumentInfo($docId){
		$link = openDBConnection();
		$elements = _getDocumentInfo($docId, $link);
		closeDBConnection($link);
		return $elements;
	}
	
	//--- Get the Document Content meta data
	function getDocumentContent($docId, $contentIndex){
		$link = openDBConnection();
		$result = _getDocumentContent($docId, $contentIndex, $link);
		closeDBConnection($link);
		return $result;
	}
	
	function _getDocumentContent($docId, $contentIndex, $link){
		$limitStr = $contentIndex < 0?"":"LIMIT $contentIndex, 1";
		$query = "SELECT Name, Description, Path, Size, MimeType FROM DocumentContent WHERE DocumentID = $docId $limitStr";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		$elements = array();
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$elements[] = array('item' => $row);
				if($contentIndex >= 0) break;
			}
		}
		return $elements;
	}
	
	//--- Get the Document Logs
	function _getDocumentLogs($docId, $link){
		$query = "SELECT ID, DocumentID, Sender, Receiver, Comment, Date, Action FROM DocumentLog WHERE DocumentID = $docId";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		$elements = array();
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$elements[] = $row;
			}
		}
		return $elements;
	}
	
	function getDocumentLogs($docId){
		$link = openDBConnection();
		$elements = _getDocumentLogs($docId, $link);
		closeDBConnection($link);
		return $elements;
	}
	
	//--- Get the Document Linked Books
	function _getDocumentLinkedBooks($docId, $link){
		$query = "SELECT d.Subject Subject, d.ID DocumentID, d.SecurityLevel SecurityLevel, d.PriorityLevel PriorityLevel FROM Document d, DocumentDocumentLink ddl WHERE (d.ID = ddl.HeadDocumentID and ddl.TailDocumentID = $docId) or (d.ID = ddl.TailDocumentID and ddl.HeadDocumentID = $docId)";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		$elements = array();
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$elements[] = $row;
			}
		}
		return $elements;
	}
	
	function getDocumentLinkedBooks($docId){
		$link = openDBConnection();
		$elements = _getDocumentLinkedBooks($docId, $link);
		closeDBConnection($link);
		return $elements;
	}
?>