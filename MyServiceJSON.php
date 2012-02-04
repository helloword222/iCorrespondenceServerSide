<?php
	require_once('DAO/json/inboxDAO.php');
	require_once('DAO/json/documentDAO.php');
	require_once('DAO/json/commentDAO.php');
	require_once('DAO/json/departmentDAO.php');
	require_once('DAO/json/userDAO.php');
	require_once('util/utils.php');
	$GET_N_INBOX_ITEMS 	= 'GET_N_INBOX_ITEMS';
	$OPEN_DOCUMENT 		= 'OPEN_DOCUMENT';
	$actions = array($GET_N_INBOX_ITEMS => strtolower('getNInboxItems'), $OPEN_DOCUMENT => strtolower('openDocument'), $GET_USER_COMMENTS => strtolower('getUserComments'));
	//----------------------------------
	$contentType = 'application/json';
	$cType = getRequestParam('contentType');
	if($cType != null){
		if($cType == 'json') $contentType = 'application/json';
		if($cType == 'html') $contentType = 'text/html';
	}
	header("Content-type: $contentType; charset=utf-8");
	$action = null;
	$action = strtolower(getRequestParam('action'));
	if(!isset($action)){
		echo "<h1>no action</h1>";
		echo json_encode($actions);
		return;
	}
	//----------------------------------
	if($action == $actions[$GET_N_INBOX_ITEMS]){
		$userId = getRequestParam('userId');
		$folderId = getRequestParam('folderId');
		if(! isset($userId)){
			echo "<h1>no userId</h1>";
			return;
		}
		$items = getNInboxItems($userId, $folderId, 0, 10);
		echo json_encode(array('items' => $items));	
	}else if($action == $actions[$OPEN_DOCUMENT]){
		echo json_encode(openDocumentFromInbox(getRequestParam('documentId'), getRequestParam('inboxItemId')));
	}else if($action == $actions[$GET_USER_CONTENTS]){
		$items = getUserComments(getRequestParam('userId'), 0, 10);
		echo json_encode(array('items' => $items));
	}else echo "Not supported action : $action";
?>