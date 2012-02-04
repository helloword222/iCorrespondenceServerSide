<?php
	require_once('db/db.php');
	
	// Define the method as a PHP function
	
	//--- open the attachment fully loaded (content)
	function openAttachment($attId){
		$attachment = array('contents'=>getAttachmentContent($attId, -1));
		return $attachment;
	}
	//--- Get the Attachment Content data
	function writeAttachmentContentToResponse($attId, $contentIndex){
		$contentMetaData = getAttachmentContent($attId, $contentIndex);
		if(count($contentMetaData) > 0){
			$filepath = $contentMetaData[0]['item']['Path'];
			$mimeType = $contentMetaData[0]['item']['MimeType'];
			$filename = $contentMetaData[0]['item']['Name'];
			$filesize = filesize($filepath);
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
		}
	}
	
	//--- Get the Attachment Content meta data
	function getAttachmentContent($attId, $contentIndex){
		$link = openDBConnection();
		$result = _getAttachmentContent($attId, $contentIndex, $link);
		closeDBConnection($link);
		return $result;
	}
	function _getAttachmentContent($attId, $contentIndex, $link){
		$limitStr = $contentIndex < 0?"":"LIMIT $contentIndex, 1";
		$query = "SELECT Name, Description, Path, Size, MimeType FROM AttachmentContent WHERE AttachmentID = $attId $limitStr";
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
	
	//--- Get the Document Attachments
	function _getDocumentAttachments($docId, $link){
		$query = "SELECT att.ID, adl.DocumentID, att.Subject, att.Description, att.Path, att.Size, att.MimeType , att.Description FROM AttachmentDocumentLink adl LEFT OUTER JOIN Attachment att on att.ID = adl.AttachmentID  WHERE adl.DocumentID = $docId";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		
		$elements = array();
		if(mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$elements[] = $row;
			}
		}
		return $elements;
	}
	
	function getDocumentAttachments($docId){
		$link = openDBConnection();
		$elements = _getDocumentAttachments($docId, $link);
		closeDBConnection($link);
		return $elements;
	}
?>