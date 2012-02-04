<?php
	require_once('db/db.php');
	
	$userCommentType = array(
		'ID' => array('name' => 'ID', 'type' => 'xsd:int'),
        'Comment' => array('name' => 'Comment', 'type' => 'xsd:string')
    );
	
	$userCommentsType = array(
		array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:userCommentType[]')
	);
	
	// Define the method as a PHP function
	//--- Get the User Comments
	function getUserComments($userId, $start, $length){
		$link = openDBConnection();
		// fetch the comments from  the db
		$query = "SELECT ID, Comment FROM UserComment WHERE UserID = $userId LIMIT $start, $length";
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
	
	//--- Save a Comment
	function saveComment($userId, $comment){
		$link = openDBConnection();
		
		$query = "INSERT INTO UserComment (UserID , Comment) VALUES ($userId, '$comment')";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		if($result){
			$result = '0';
		}else{
			$result = 'Error: ' . mysql_error();
		}
		closeDBConnection($link);
		return $result;
	}
	
	//--- Save a Comment
	function deleteComment($commentId){
		$link = openDBConnection();
		
		$query = "DELETE FROM UserComment WHERE ID = $commentId";
		$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
		if($result){
			$result = '0';
		}else{
			$result = 'Error: ' . mysql_error();
		}
		closeDBConnection($link);
		return $result;
	}
?>