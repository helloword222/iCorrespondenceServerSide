<?php
	require_once('../DAO/db/db.php');
	header( 'Content-type: text/xml' );
	$link = openDBConnection();
	if ( $_REQUEST['past'] ) {
		$query = 'SELECT * FROM ChatItem WHERE id > '.mysql_real_escape_string( $_REQUEST['past'] ).' ORDER BY added LIMIT 50';
	} else {
		$query = 'SELECT * FROM ChatItem ORDER BY added LIMIT 50';
	}
	$result = mysql_query($query, $link) or die('Errant query:  ' . $query);
?>
<chat>
<?php
while ($row = mysql_fetch_assoc($result)) {
?>
<message added="<?php echo( $row['added'] ) ?>" id="<?php echo( $row['id'] ) ?>">
    <user><?php echo( $row['user']  ) ?></user>
    <text><?php echo( $row['message'] ) ?></text>
</message>
<?php
}
	mysql_free_result($result);
	closeDBConnection($link);
?>
</chat>