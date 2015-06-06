<?php
function _send($url, array $params = array()) {
    $body = http_build_query ( $params );
    $ch = curl_init ();
    $opt = array (
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => TRUE, // return web page
            CURLOPT_HEADER => FALSE, // don't return headers
            CURLOPT_FOLLOWLOCATION => FALSE, // follow redirects
            CURLOPT_AUTOREFERER => TRUE, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 1, // timeout on connect
            CURLOPT_TIMEOUT => 1, // timeout on response
            CURLOPT_SSL_VERIFYHOST => 0, // don't verify ssl
            CURLOPT_SSL_VERIFYPEER => FALSE, //
            CURLOPT_VERBOSE => FALSE
    ); //
    
    curl_setopt_array ( $ch, $opt );
    
    $raw = curl_exec ( $ch );
    var_dump ( $raw );
    $errno = curl_errno ( $ch );
    
    if ($errno == CURLE_COULDNT_CONNECT) {
        return false;
    }
    
    if ($errno != CURLE_OK) {
        $errstr = curl_error ( $ch );
        return false;
    }
    
    $code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
    
    if ($code != 200) {
        return false;
    }
    
    return true;
}
$url = 'http://yunpian.com/v1/sms/send.json';
$apikey = '01b849d086b2e1fd4fe394cbadd73060';
$templates = array (
        '【掌上链家】掌上链家提醒您：您修改密码的手机验证码为#verify_code#，请填写验证码完成密码修改。',
        '【链家网】链家网提醒您：您的手机验证码为#mycode#，请在#myttl#分钟内填写验证码完成操作。',
        '【链家网】链家网提醒您：您注册的手机验证码为#mycode#，有效期#myttl#分钟，请在注册页面填写验证码完成注册。',
        '【链家网】链家网提醒您：您登录的手机验证码为#mycode#，有效期#myttl#分钟，请在登录页面填写验证码完成登录。',
        '【掌上链家】掌上链家提醒您：您修改的手机验证码为#mycode#，请填写验证码完成密码修改。',
        '【掌上链家】您登录的验证码为#mycode#，有效期#myttl#分钟请填写验证码，完成登录。',
        '【掌上链家】您注册的验证码为#mycode#，有效期#myttl#分钟，请填写验证码完成注册。',
        '【掌上链家】您绑定手机号的验证码为#mycode#, 有效期#myttl#分钟，请填写验证码绑定手机号',
        '【掌上链家】你找回密码的验证码为#mycode#，有效期#myttl#分钟，请填写验证码找回密码。',
        '【掌上链家】掌上链家提醒您：您登录的初始密码为#mycode#，请妥善保管，如需更改请登录掌上链家>设置>修改密码完成修改。',
        '【掌上链家】掌上链家提醒您：您的手机密码为#mycode#，请妥善保管。'
);
$mycode = '1234';
$t = 10;
header ( "Content-Type: text/html; charset=utf-8" );
$i = $_REQUEST ['num'];
if (empty ( $templates [$i] )) {
    echo 'end';
    die ();
}
$str1 = str_replace ( '#mycode#', $mycode, $templates [$i] );
$str2 = str_replace ( '#myttl#', $t, $str1 );
$mobile1 ='18600045277';
$mobile2 = '18310426754';
$param = array (
        'apikey' => $apikey,
        'mobile' => $mobile1,
        'text' => $str2
);
var_dump ( $str2 );
echo '<br />';
_send ( $url, $param );
echo '<br />';

// foreach ( $templates as $tem ) {
//     $str1 = str_replace ( '#mycode#', $mycode, $tem );
//     $str2 = str_replace ( '#myttl#', $t, $str1 );
    
//     $param = array (
//             'apikey' => $apikey,
//             'mobile' => '18310426754',
//             'text' => $str2
//     );
//     var_dump($str2);
//     echo '<br />';
// _send ( $url, $param );
// echo '<br />';

// }
