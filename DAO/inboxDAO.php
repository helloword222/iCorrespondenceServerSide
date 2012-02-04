<?php
	require_once('db/db.php');
	
	$inboxCountType = array(
        'read' => array('name' => 'read', 'type' => 'xsd:int'),
        'unread' => array('name' => 'unread', 'type' => 'xsd:int')
    );
	
	$inboxItemType = array(
		'Subject' => array('name' => 'Subject', 'type' => 'xsd:string'),
		'Sender' => array('name' => 'Sender', 'type' => 'xsd:string'),		
		'Date' => array('name' => 'Date', 'type' => 'xsd:date'),
		'Action' => array('name' => 'Action', 'type' => 'xsd:string'),
		'Opened' => array('name' => 'Opened', 'type' => 'xsd:string'),
		'Folder' => array('name' => 'Folder', 'type' => 'xsd:string'),
		'MainSite' => array('name' => 'MainSite', 'type' => 'xsd:string'),
		'SubSite' => array('name' => 'SubSite', 'type' => 'xsd:string'),
		'SecurityLevel' => array('name' => 'SecurityLevel', 'type' => 'xsd:string'),
		'PriorityLevel' => array('name' => 'PriorityLevel', 'type' => 'xsd:string'),
		'MainClassification' => array('name' => 'MainClassification', 'type' => 'xsd:string'),
		'SubClassification' => array('name' => 'SubClassification', 'type' => 'xsd:string')
	);
	
	$inboxItemsType = array(
		array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:inboxItem[]')
	);
	
	// Define the method as a PHP function
	// Define the method as a PHP function
	function getInboxCount($userId){
		$link = openDBConnection();
		// fetch the inbox count from  the db 
		$query = "SELECT COUNT(*) Count, Opened FROM InboxItem WHERE UserID = $userId GROUP BY Opened";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		/* create one master array of the records */
		$counts = array('read'=>0, 'unread'=>0);
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$name = '';
				if($row['Opened'] == 0){
					$name = 'unread';
				}else{
					$name = 'read';
				}
				$counts[$name] = $row['Count'];
			}
		}
		closeDBConnection($link);
		return $counts;
	}
	
	
	//--- Get the n inbox items
	function getNInboxItems($userId, $start, $length){
		$link = openDBConnection();
		// fetch the inbox items ($start -> [$start + $length]) from  the db 
		$query = "SELECT d.Subject Subject, ii.Sender, ii.Date Date, ii.Action, IF(ii.Opened = 0, 'NO', 'YES') Opened, uf.Name Folder, cs1.Name MainSite, cs2.Name SubSite, IF(d.SecurityLevel = 0, 'N','S') SecurityLevel, IF(d.PriorityLevel = 0, 'N', 'U') PriorityLevel, c1.Name MainClassification, c2.Name SubClassification FROM InboxItem ii LEFT OUTER JOIN Document d on d.ID = ii.DocumentID LEFT OUTER JOIN UserFolder uf on ii.FolderID = uf.ID LEFT OUTER JOIN CorrespondenceSite cs1 on d.MainSite = cs1.ID LEFT OUTER JOIN CorrespondenceSite cs2 on d.SubSite = cs2.ID LEFT OUTER JOIN Classification c1 on d.MainClassification = c1.ID LEFT OUTER JOIN Classification c2 on d.SubClassification = c2.ID WHERE ii.UserID = $userId LIMIT $start, $length";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		// create one master array of the records 
		$elements = array();
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$elements[] = $row;
			}
		}
		closeDBConnection($link);
		return $elements;
	}
	
	//--- Complete step element
	function completeInboxItem($inboxItemId, $comment){
		$link = openDBConnection();
		$result = mysql_query("SET AUTOCOMMIT=0");
		$result = mysql_query("START TRANSACTION");
		
		$query = "SELECT UserID, DocumentID from InboxItem WHERE ID = $inboxItemId";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		$documentId = null; $userId = null;
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$documentId = $row['DocumentID'];
				$userId = $row['UserID'];
				break;
			}
		}
		if($documentId == null || $userId == null){
			$result = mysql_query("ROLLBACK");
			return -1;
		}
		
		$subject = null; $sender = null; $senderDomainName = null;
		$query = "SELECT Subject FROM Document WHERE ID = $documentId";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$subject = $row['Subject'];
				break;
			}
		}
		
		if($subject == null){
			$result = mysql_query("ROLLBACK");
			return -2;
		}
		$query = "SELECT Name, DomainName FROM User WHERE ID = $userId";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$sender = $row['Name'];
				$senderDomainName = $row['DomainName'];
				break;
			}
		}
		
		if($sender == null || $senderDomainName == null){
			$result = mysql_query("ROLLBACK");
			return -3;
		}
		
		$query = "DELETE FROM InboxItem WHERE ID = $inboxItemId";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		if(! $result){
			$result = mysql_query("ROLLBACK");
			return -4;
		}
		
		$query = "INSERT INTO DocumentLog (DocumentID, Subject, Sender, SenderDomainName, Receiver, ReceiverDomainName, Comment, Date, Action) VALUES ($documentId, $subject, '$sender', '$senderDomainName', null, null, '$comment', CURTIME( ), 'Complete' )";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		if(! $result){
			$result = mysql_query("ROLLBACK");
			return -5;
		}
		$result = mysql_query("COMMIT");
		closeDBConnection($link);
		return 0;
	}
	
?>