<?php
function getRequestAPI($str, $key) {
    if (strpos ( $str, '[' . $key ) !== false) {
        
        // 日志中的格式：[key:value]
        $s1 = strpos ( $str, '[' . $key );
        $s = strpos ( $str, ':', $s1 ) + 1;
        $e = strpos ( $str, ']', $s );
        
        return substr ( $str, $s, $e - $s );
    }
    return null;
}
function getVersion($ua) {
    $key = 'HomeLink ';
    if (strpos ( $ua, $key ) !== false) {
        
        // 日志中的格式：[HomeLink 6.0.1;Teclast P98 3Gk8(A3HY); Android 4.4.2]
        $s1 = strpos ( $ua, $key ) + strlen ( $key );
        if (strpos ( $ua, ';' ) !== false) {
            $e = strpos ( $ua, ';', $s1 );
        } else {
            $e = strlen ( $ua );
        }
        return substr ( $ua, $s1, $e - $s1 );
    }
}
$i = 0;
$arrAPI = array ();
$file = 'C:\Users\N010D90001\Downloads\0315.log';
$handle = fopen ( $file, "r" ) or die ( "can\'t open file {$file}" );
// 循环读取每一行日志
while ( ($line = fgets ( $handle, 8192 )) !== false ) {
    if (strpos ( $line, 'MAPI_REQUEST' ) === false) {
        continue;
    }
    
    // UA
    $apiUA = getRequestAPI ( $line, 'ua' );

    if (empty ( $apiUA )) {
        continue;
    }
    $v = getVersion ( $apiUA );
    if($v != '6.0.0' && $v != '6.0.1'){
        echo $v;
    }
    $i ++;
    if ($i > 10) {
        //break;
    }
    // $arrAPI [] = $apiUA;
}
fclose ( $handle );

