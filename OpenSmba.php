<?php

function curl_post($url, array $post = NULL, array $options = array()) {
    $defaults = array (
            //CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            //CURLOPT_TIMEOUT => 4,
            //CURLOPT_POSTFIELDS => http_build_query ( $post )
    );

    $ch = curl_init ();
    curl_setopt_array ( $ch, ($options + $defaults) );
    if (! $result = curl_exec ( $ch )) {
        trigger_error ( curl_error ( $ch ) );
    }
    curl_close ( $ch );
    return $result;
}

$filename = '\\\172.30.0.10\work\www\lianjia-web\README.md';

$result=curl_post($filename);
echo $result;
$myLine = '';

return json_decode ( $myLine, 1 );