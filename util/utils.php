<?php

function getRequestParam($param){
	if(isset($_GET[$param])){
		return $_GET[$param];
	}else if(isset($_POST[$param])){
		return $_POST[$param];
	}else{
		return null;
	}
}

?>