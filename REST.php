<?php
// define ( 'APPID', '61f8o88g50rrpmzeu9fep3v5z44j0pve8wqqzf82hspqcp2h' );
// define ( 'APPKEY', 'k3mmc3apquj914lmjmrxrpoaogq4u1klwhpg57dzjxomplcd' );
// define ( 'MASTERKEY', 'xwszglw01mim4z66r2q0exa35ondetsfedwz56m3d1en4k9u' );
// define ( 'PUSH', 'homelink_fangyuandongtai' );


define ( 'APPID', '4e1dh2g3tn08i6swaqtperykbrdas2r8xebmusjeucrptsfd' );
define ( 'APPKEY', 'jcd4alcajl0cp4w7vj6gd8zuib7chk5653dy4biffqgynn4n' );
define ( 'MASTERKEY', 'yv25r85j2cwfprihs2evx52kb50c36otxo2kpwa319tqamaf' );
define ( 'PUSH', 'xiaoqudongtai' );

//define ( 'PUSH', 'homelink_house' );
//define ( 'PUSH', 'homelink_community' );
//  function actionGetHeaderSign() {
//     $clientId = AccountModel::getClientId ();
//     if (empty ( $clientId )) {
//         return ApiError::error ( ApiError::INVALID_ACCESS_TOKEN_NO, ApiError::INVALID_ACCESS_TOKEN_INFO );
//     }
//     $result = array (
//             'errno' => 0
//     );
//     $masterkey = 'xwszglw01mim4z66r2q0exa35ondetsfedwz56m3d1en4k9u';
//     $timestamp = time ();
//     $result ['data'] = sprintf ( '%s,%s,%s', md5 ( $timestamp . $masterkey ), $timestamp, 'master' );
//     return $result;
// }
function sign() {
    $timestamp = time ();
    return sprintf ( '%s,%s,%s', md5 ( $timestamp . MASTERKEY ), $timestamp, 'master' );
}
function sha1sign($str) {
    return hash_hmac ( "sha1", $str, MASTERKEY, false );
}
function _queryLeanCloud($url, array $params = array()) {
    $body = json_encode ( $params );
    
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
            CURLOPT_VERBOSE => FALSE, //
            CURLOPT_HTTPHEADER => array (
                    "X-AVOSCloud-Application-Id: 61f8o88g50rrpmzeu9fep3v5z44j0pve8wqqzf82hspqcp2h",
                    "X-AVOSCloud-Application-Key: k3mmc3apquj914lmjmrxrpoaogq4u1klwhpg57dzjxomplcd",
                    "Content-Type: application/json" 
            ) 
    );
    
    curl_setopt_array ( $ch, $opt );
    
    $raw = curl_exec ( $ch );
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
function sendRequest($url, $method, $param) {
    // 创建一个新cURL资源
    $ch = curl_init ();
    // $headerArr['X-AVOSCloud-Request-Sign'] = sign();
    // $headerArr ['Content-Type'] = 'application/json';
    $headerArr = array (
            "X-AVOSCloud-Application-Id: " . APPID,
            // "X-AVOSCloud-Application-Key: k3mmc3apquj914lmjmrxrpoaogq4u1klwhpg57dzjxomplcd",
            "X-AVOSCloud-Request-Sign: " . sign (),
            "Content-Type: application/json" 
    );
    // 设置URL和相应的选项
    $opt = array (
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE, // return web page
            CURLOPT_HEADER => FALSE, // don't return headers
            CURLOPT_FOLLOWLOCATION => FALSE, // follow redirects
            CURLOPT_AUTOREFERER => TRUE, // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 10, // timeout on connect
            CURLOPT_TIMEOUT => 10, // timeout on response
            CURLOPT_SSL_VERIFYHOST => 0, // don't verify ssl
            CURLOPT_SSL_VERIFYPEER => FALSE, //
            CURLOPT_VERBOSE => FALSE, //
            CURLOPT_HTTPHEADER => $headerArr 
    );
    curl_setopt_array ( $ch, $opt );
    if ($method == 'POST') {
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode ( $param ) );
    }
    if ($method == 'GET') {
        // $buildurl = $url.'?'.http_build_query( $param );
        // $buildurl ='https://leancloud.cn/1.1/rtm/messages/logs?convid=551e3563e4b043f1c8332315&peerid=PUSH&nonce=bibce&signature_ts=1428123525&signature=a7e111140d3ddbcf424cd7910bd0fe6be00e3fd4';
        // curl_setopt ( $ch, CURLOPT_URL, $buildurl );
        curl_setopt ( $ch, CURLOPT_POST, 0 );
    }
    // 抓取URL并把它传递给浏览器
    $raw = curl_exec ( $ch );
    $errno = curl_errno ( $ch );
    if ($errno != CURLE_OK) {
        $errstr = curl_error ( $ch );
        var_dump ( $raw );
        return false;
    }
    
    $code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
    if ($code != 200 && $code != 201) {
        var_dump ( $raw );
        return false;
    }
    
    if (empty ( $raw )) {
        
        return false;
    }
    // 关闭cURL资源，并且释放系统资源
    curl_close ( $ch );
    return $raw;
}

// 获取某个应用的聊天记录
function getChatHistory() {
    $peerid = PUSH;
    $convid = '556e3bf0e4b0f7e4fddfd7b0';
    $nonce = 'bibce';
    $signature_ts = time ();
    $str = sprintf ( '%s:%s:%s:%s:%s', APPID, $peerid, $convid, $nonce, $signature_ts );
    $signature = sha1sign ( $str );
    $param = array (
            'convid' => $convid,
            'peerid' => $peerid,
            'nonce' => $nonce,
            'signature_ts' => $signature_ts,
            'signature' => $signature,
            'limit' => 10 
    );
    
    $url = 'https://leancloud.cn/1.1/rtm/messages/logs?' . http_build_query ( $param );
    // $url = 'https://leancloud.cn/1.1/rtm/messages/logs?convid=552119bbe4b043f1c84c0b7a';
    $chatHistory = sendRequest ( $url, 'GET', $param );
    header ( "Content-Type: application/json; charset=utf-8" );
    var_dump($chatHistory);die; 
    var_dump(json_decode(( json_decode ( $chatHistory, 1 )[0]['data'])));
    return json_decode ( $chatHistory, 1 );
}
function userReg() {
    $url = 'https://leancloud.cn/1.1/users';
    $headerArr = array (
            'Content-Type' => 'application/json' 
    );
    $postArr = array (
            'username' => PUSH,
            'password' => 'abcdefg',
            'phone' => '415-392-0202' 
    );
    sendRequest ( $url, 'POST', $postArr );
}
function sendMessageToUser($message, $userId) {
    $url = 'https://leancloud.cn/1.1/rtm/messages';
    $conv = queryUserConv ( $userId );
    $convId = '';
    if (empty ( $conv ['results'] )) {
        $conv = createConv ( $userId );
        $convId = $conv ['objectId'];
    } else {
        $convId = $conv ['results'] [0] ['objectId'];
    }
    $postArr = array (
            'from_peer' => PUSH,
            // 'message' => "{\"_lctype\":-1,\"_lctext\":\"这是一个纯文本消息\",\"_lcattrs\":{\"a\":\"_lcattrs 是用来存储用户自定义的一些键值对\"}}",
            'message' => json_encode ( $message ),
            'conv_id' => $convId,
            'transient' => false 
    );
    sendRequest ( $url, 'POST', $postArr );
}
function createConv($userId) {
    $url = 'https://leancloud.cn/1.1/classes/_Conversation';
    $postArr = array (
            'attr' => array (
                    'type' => 0 
            ),
            'c' => PUSH,
            // 'im' => date(),
            'm' => array (
                    PUSH,
                    $userId 
            ),
            'mu' => array () 
    );
    $conv = sendRequest ( $url, 'POST', $postArr );
    
    return json_decode ( $conv, 1 );
}
function queryUserConv($userId) {
    $url = 'https://leancloud.cn/1.1/classes/_Conversation?where=' . urlencode ( '{"c":"' . PUSH . '","m":["' . PUSH . '","' . $userId . '"]}' );
    // $getArray = 'where={"playerName":"Sean Plott","cheatMode":false}';

    $conv = sendRequest ( $url, 'GET', array () );
    return json_decode ( $conv, 1 );
}
function pushMessageToUserName($username) {
    $url = 'https://leancloud.cn/1.1/push';
    $postArr = array (
            // 'push_time'=> '2013-11-28T00:51:13Z',
            // 'expiration_interval'=> 518400,
            // 'where'=>array('$inQuery'=>array('$Installation'=>array('username'=>$username))),
            // 'where'=>array('objectId'=>'551ffcdbe4b043f1c843197c'),
            // 'where'=>array('objectId'=>array('$select'=>array('query'=>array('className'=>'_User', 'where'=>array('username'=>$username)),'key'=>'installation'))),
            'where' => array (
                    'objectId' => array (
                            '$select' => array (
                                    'query' => array (
                                            'className' => '_User',
                                            'where' => array (
                                                    'username' => $username 
                                            ) 
                                    ),
                                    'key' => 'installation.objectId' 
                            ) 
                    ) 
            ),
            
            'data' => array (
                    'title' => 'hello',
                    'alert' => 'Hello From PHP' 
            ) 
    );
    sendRequest ( $url, 'POST', $postArr );
}
function createUpdateInfo() {
    $url = 'https://leancloud.cn/1.1/classes/UpdateInfo';
    $postArr = array (
            'desc' => 'asdf',
            // 'im' => date(),
            'version' => 2.0,
            'apkUrl' => 'http://www.baidu.com/' 
    );
    $conv = sendRequest ( $url, 'POST', $postArr );
}
function createAddRequest() {
    $url = 'https://leancloud.cn/1.1/classes/AddRequest';
    $postArr = array (
            'fromUser' => 'asdf',
            // 'im' => date(),
            'toUser' => 2.0,
            'status' => 1 
    );
    $conv = sendRequest ( $url, 'POST', $postArr );
}
function getAddRequest() {
    $url = 'https://api.leancloud.cn/1.1/classes/AddRequest?where={"toUser":{"__type":"Pointer","className":"_User","objectId":"551e5ac7e4b01ae283a5bb5d"}}';
    $url = 'https://leancloud.cn/1.1/classes/AddRequest';
    $add = sendRequest ( $url, 'GET', array () );
}
function getUserId($username) {
    $url = 'https://leancloud.cn/1.1/users?where=' . urlencode ( '{"username":"' . $username . '"}' );
    // $getArray = 'where={"playerName":"Sean Plott","cheatMode":false}';
    $userStr = sendRequest ( $url, 'GET', array () );
    $userArr = json_decode ( $userStr, 1 );
    if (empty ( $userArr ['results'] [0] ['objectId'] )) {
        return null;
    }
    return $userArr ['results'] [0] ['objectId'];
}
function sendMessage() {
    //$userId = getUserId ( '2dong' );
    $userId = '2000000000940912';
    //$userId = '2000000000917606';
    if(1){
        $text ='小区动态：homelink_house';
        $type ='house';
    }else{
        $text ='房源动态：homelink_community';
        $type='community';
    }
    $msg = array (
            '_lctype' => -1,
            '_lctext' => $text,
            '_lcattrs' => array (
                    'm_type' => $type
            ) 
    );
    sendMessageToUser ( $msg, $userId );
}

//sendMessage ();

// header ( "Content-Type: text/html; charset=utf-8" );
// createUpdateInfo();
$his = getChatHistory ();

//pushMessageToUserName ( '2dong' );
//userReg();
