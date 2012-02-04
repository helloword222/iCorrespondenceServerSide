<?php

/* require the item id as the parameter */
if (isset($_GET['id']) && intval($_GET['id'])) {

  /* soak in the passed variable or set our own */
  $limit = isset($_GET['num']) ? intval($_GET['num']) : 10; //10 is the default
  $format = strtolower($_GET['format']) == 'json' ? 'json' : 'xml'; //xml is the default
  $id = intval($_GET['id']); //no default

  /* connect to the db */
  $link = mysql_connect('mysql.holooli.com', 'saeed_user', 'Z%@DSz2a34') or die('Cannot connect to the DB server');

  mysql_select_db('saeed_db', $link) or die('Cannot select the DB');

  mysql_query("SET NAMES utf8", $link);
  mysql_query("SET CHARACTER SET utf-8", $link);

  // Make sure any results we retrieve or commands we send use the same charset and collation as the database:
  //$db_charset = mysql_query( "SHOW VARIABLES LIKE 'character_set_database'" );
  //$charset_row = mysql_fetch_assoc( $db_charset );
  //mysql_query( "SET NAMES '" . $charset_row['Value'] . "'" );
  //mysql_query("SET CHARACTER SET '".$charset_row['Value']."'");
  //unset( $db_charset, $charset_row );


  /* grab the items from the db */
  $query = "SELECT Subject, Department FROM Document WHERE id = $id ORDER BY ID DESC LIMIT $limit";
  $result = mysql_query($query, $link) or die('Errant query:  ' . $query);

  /* create one master array of the records */
  $items = array();
  if(mysql_num_rows($result)) {
    while ($row = mysql_fetch_assoc($result)) {
      $items[] = array('item' => $row);
    }
  }

  /* output in necessary format */
  if ($format == 'json') {
    header('Content-type: application/json; charset=utf-8');
    echo json_encode(array('items' => $items));
  }
  else {
    header('Content-type: text/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="utf-8"?><items>';
    foreach($items as $index => $item) {
      if (is_array($item)) {
        foreach ($item as $key => $value) {
          echo '<',$key,'>';
          if (is_array($value)) {
            foreach($value as $tag => $val) {
              echo '<',$tag,'>',$val,'</',$tag,'>';
            }
          }
          echo '</',$key,'>';
        }
      }
    }echo '<item><subject>سعيد تجريب</subject></item>';
    echo '</items>';
  }


  /* disconnect from the db */
  @mysql_close($link);
}