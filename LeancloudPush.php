<?php
echo intval('18310426754');die;
// conection:
$link = mysqli_connect ( "172.30.0.20:5000", "prod", "123456", "lianjia" ) or die ( "Error " . mysqli_error ( $link ) );
$link->query ( "SET NAMES utf8" );
// consultation:
$query = 'select user_id,feed_type,details from lianjia_user_feed limit 0,1';
$result = $link->query ( $query ) or die ( "Error in the consult.." . mysqli_error ( $link ) );

$row = mysqli_fetch_array ( $result, MYSQLI_ASSOC );
while ( $row ) {
    $user_id = $row ['user_id'];
    $feed_type = $row ['feed_type'];
    $details = json_decode ( $row ['details'], 1 );
    var_dump($details);
    $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC );
}
$link->close ();
