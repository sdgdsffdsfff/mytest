<?php
// 创建一个新cURL资源
$ch = curl_init();
$posts = array('ucid'=>array('778440','786440',));
// 设置URL和相应的选项
curl_setopt($ch, CURLOPT_URL, "http://172.30.16.22:8002/im/user/getclientstatus");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($posts));
// 抓取URL并把它传递给浏览器
curl_exec($ch);

// 关闭cURL资源，并且释放系统资源
curl_close($ch);