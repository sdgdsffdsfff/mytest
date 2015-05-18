<?php

function curl($urls,$post) {
    $queue = curl_multi_init();
    $map = array();
    foreach ($urls as $key => $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post[$key]);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        curl_multi_add_handle($queue, $ch);
        $map[(string) $ch] = $url;
    }
    $responses = array();
    do {
        while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM) ;
        if ($code != CURLM_OK) { break; }
        while ($done = curl_multi_info_read($queue)) {
            $error = curl_error($done['handle']);
            $results = curl_multi_getcontent($done['handle']);
            $responses[$map[(string) $done['handle']]] = compact('error', 'results');
            curl_multi_remove_handle($queue, $done['handle']);
            curl_close($done['handle']);
        }
        if ($active > 0) {
            curl_multi_select($queue, 0.5);
        }
    } while ($active);
    curl_multi_close($queue);
    return $responses;
}

// 创建一个新cURL资源
$ch = curl_init ();

$posts = array (
        'mobile'=>'18310426754',
        'password'=>md5('123456'),
        'mobileMark' => 1,
        'appId' => 103
);
$url = 'http://172.30.11.77:8080/uc/user/register';
// 设置URL和相应的选项
curl_setopt ( $ch, CURLOPT_URL, $url );
curl_setopt ( $ch, CURLOPT_HEADER, 0 );
curl_setopt ( $ch, CURLOPT_POST, 1 );
curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query ( $posts ) );
// 抓取URL并把它传递给浏览器
curl_exec ( $ch );

// 关闭cURL资源，并且释放系统资源
curl_close ( $ch );