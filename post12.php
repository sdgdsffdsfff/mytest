<?php
// 创建一个新cURL资源
$ch = curl_init ();
$setting = array (
        'receive_notification' => array (
                'price_changed' => 1,
                'deal' => 1 
        ),
       // 'first_login_time' => 1429793123 
);
var_dump( json_encode ( $setting) );
$posts = array (
        'app_id'=>'test',
        'app_secret'=>'96267050595b16514bbf3c985def2c9b',
        'access_token' => 'de86c9e9b67a0b53a175641c5759aed6',
        'setting' => json_encode ( $setting,1 ) 
);
$url = 'http://zed.dev.mapi.lianjia.com:8001/user/setting/update';
// 设置URL和相应的选项
curl_setopt ( $ch, CURLOPT_URL, $url );
curl_setopt ( $ch, CURLOPT_HEADER, 0 );
curl_setopt ( $ch, CURLOPT_POST, 1 );
curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query ( $posts ) );
// 抓取URL并把它传递给浏览器
curl_exec ( $ch );

// 关闭cURL资源，并且释放系统资源
curl_close ( $ch );