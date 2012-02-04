<?php
	require_once('db/db.php');
	require_once('DAO/json/documentDAO.php');
	require_once('DAO/json/attachmentDAO.php');
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
	function getNInboxItems($userId, $folderId, $start, $length){
		$link = openDBConnection();
		$whereSuffix = '';
		if($folderId != null){
			$whereSuffix = " and uf.ID = $folderId ";
		}
		// fetch the inbox items ($start -> [$start + $length]) from  the db 
		$query = "SELECT d.Subject Subject, d.ID DocumentID, ii.Sender, ii.Action, ii.Opened IsOpen, ii.Date Date, uf.ID FolderID, uf.Name Folder, cs1.Name MainSite, cs2.Name SubSite, d.SecurityLevel SecurityLevel, d.PriorityLevel PriorityLevel FROM InboxItem ii LEFT OUTER JOIN Document d on d.ID = ii.DocumentID LEFT OUTER JOIN UserFolder uf on ii.FolderID = uf.ID LEFT OUTER JOIN CorrespondenceSite cs1 on d.MainSite = cs1.ID LEFT OUTER JOIN CorrespondenceSite cs2 on d.SubSite = cs2.ID LEFT OUTER JOIN Classification c1 on d.MainClassification = c1.ID LEFT OUTER JOIN Classification c2 on d.SubClassification = c2.ID WHERE ii.UserID = $userId $whereSuffix LIMIT $start, $length";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		// create one master array of the records 
		$elements = array();
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				//--- load the document log
				$logs =_getDocumentLogs($row['DocumentID'], $link);
				$attachments = _getDocumentAttachments($row['DocumentID'], $link);
				$linkedDocs = _getDocumentLinkedBooks($row['DocumentID'], $link);
				$elements[] = array('item' => $row, 'logs' => $logs, 'attachments' => $attachments, 'docs' => $linkedDocs);
			}
		}
		//-- fetch the content info for each document.
		$count = count($elements);
		for ($i = 0; $i < $count; $i++) {
			$item = $elements[$i]['item'];
			$docId = $item['DocumentID'];
			$contentElements = _getDocumentContent($docId, -1, $link);
			$contentCount = count($contentElements);
			if($contentCount > 0){
				//$elements[$i]['item']['ContentElements'] = $contentElements;
				$elements[$i]['item']['url'] = $contentElements[0]['item']['Path'];
				//$SID = session_id(); 
				//if(empty($SID)) session_start() or exit(basename(__FILE__).'(): Could not start session'); 
				//$elements[$i]['item']['session_id'] = $SID; 
			}
		}
		closeDBConnection($link);
		return $elements;
	}
	
	function markAsRead($inboxItemId){
		$link = openDBConnection();
		$query = "Update InboxItem set OPENED = 1 WHERE ID = $inboxItemId";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		$returnVal = true;
		if(! $result){
			$returnVal = false;
		}
		closeDBConnection($link);
		return $returnVal;
	}
	
	function openDocumentFromInbox($docId, $inboxItemId){
		$document = writeDocumentContentToResponse($docId, 0);
		//--- mark the inbox item as read.
		markAsRead($inboxItemId);
		exit(0);
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