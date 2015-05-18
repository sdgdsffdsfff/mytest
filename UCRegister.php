<?php
function queryArray($query) {
    // conection:
    $link = mysqli_connect ( "172.16.6.144:3306", "root", "lianjiadb153", "lianjia" ) or die ( "Error " . mysqli_error ( $link ) );
    $link->query ( "SET NAMES utf8" );
    // consultation:
    
    $result = $link->query ( $query ) or die ( "Error in the consult.." . mysqli_error ( $link ) );
    
    $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC );
    $schools = array ();
    while ( $row ) {
        $schools [] = $row;
        $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC );
    }
    $link->close ();
    return $schools;
}

$query = <<<SQL
SELECT * FROM lianjia_web_client where mobile <>'' and ctime>0 and client_id between %d and %d
SQL;
$i = 0;
$step = 1000;
while ( $i < 1000 ) {
    echo $i;
    $s = $i;
    $e = $i + $step;
    $users = queryArray ( sprintf ( $query, $s, $e ) );
    
    foreach ( $users as $user ) {
        $posts [] = array (
                'mobile' => $user ['mobile'],
                'password' => $user ['password'],
                'clientId' => $user ['client_id'],
                'displayName' => $user ['display_name'],
                'ctime' => $user ['ctime'],
                'mtime' => $user ['mtime'],
                'appId' => $user ['appid'],
                'mobileMark' => 1
        );
    }
    foreach ( $posts as $post ) {
        // 创建一个新cURL资源
        $ch = curl_init ();
        
        $url = 'http://172.30.11.77:8080/uc/user/importUser';
        // 设置URL和相应的选项
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post );
        // 抓取URL并把它传递给浏览器
        $raw = curl_exec ( $ch );
        $result = json_decode ( $raw,1 ); 
        if (empty ( $result ) || $result ['code'] != 0) {
            //echo 'error:' . $raw;
        }
        // 关闭cURL资源，并且释放系统资源
        curl_close ( $ch );
    }
    $i+=$step;
}

// header('Content-type: application/json; charset=utf-8');
// echo json_encode($raw);
