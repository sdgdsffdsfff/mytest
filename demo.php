<?php
// 消息推送Demo
// 增加了IOS的离线消息推送,IOS不支持IGtNotyPopLoadTemplate模板
// 更新时间为2014年01月13日 VERSION:3.0.0.1
// IOS用户，增加PushInfo的长度判断，超过256字节的长度则禁止发送，android用户请注释 setPushInfo字段
// 一个中文汉字为3个字节，一个英文与一个数字为一个字节
// 增加用户状态查询接口
// 增加任务停止功能
// 更新时间为2014年02月25日 VERSION:3.0.0.2
// 增加toList接口返回每个用户状态的功能
// 更新时间为2014年08月30日
// IOS在设置setPushInfo为{"",-1,"","","","","",""} 会抛出异常，不允许设置
header ( "Content-Type: text/html; charset=utf-8" );

require_once (dirname ( __FILE__ ) . '/' . 'IGt.Push.php');

define ( 'APPKEY', 'YTf8dPFUuk5ZfaIXgh0yN8' );
define ( 'APPID', 'OTKxCDCNVM9VbTqcAk74m9' );
define ( 'MASTERSECRET', 'bUh8KKcU5n8CztOkUMvpDA' );
// define('APPKEY','dBHTyRF5YF8xVu2tgKnc15');
// define('APPID','Z62qOoG7SN8FI0TkWmcNIA');
// define('MASTERSECRET','ejIaSe25Xv87IXajc9Z1Y1');
define ( 'HOST', 'http://sdk.open.api.igexin.com/apiex.htm' );
define ( 'CID', '9881b6b34fc18b9e288b47480193d7ac' );
// define('DEVICETOKEN','请输入DEVICETOKEN');
// getUserStatus();

// stoptask();

// setTag();

$title = '我的通知';
$text = '跑完了';
$logo = '';
pushMessageToSingle ( $title, $text, $logo );

// pushMessageToList();

// pushMessageToApp();

// pushAPN();
function pushAPN() {
    $igt = new IGeTui ( HOST, APPKEY, MASTERSECRET );
    $template = new IGtAPNTemplate ();
    $template->set_pushInfo ( "", 1, "dd", "", "", "", "", "", 1 );
    $message = new IGtSingleMessage ();
    
    $message->set_data ( $template );
    $ret = $igt->pushAPNMessageToSingle ( APPID, DEVICETOKEN, $message );
    var_dump ( $ret );
}

// 用户状态查询
function getUserStatus() {
    $igt = new IGeTui ( HOST, APPKEY, MASTERSECRET );
    $rep = $igt->getClientIdStatus ( APPID, CID );
    var_dump ( $rep );
    echo ("<br><br>");
}

// 推送任务停止
function stoptask() {
    $igt = new IGeTui ( HOST, APPKEY, MASTERSECRET );
    $igt->stop ( "OSA-0225_d5GB1otdWLAsTb3gckDXY7" );
}

// 通过服务端设置ClientId的标签
function setTag() {
    $igt = new IGeTui ( HOST, APPKEY, MASTERSECRET );
    $tagList = array (
            '',
            '2',
            '3' 
    );
    $rep = $igt->setClientTag ( APPID, CID, $tagList );
    var_dump ( $rep );
    echo ("<br><br>");
}

//
// 服务端推送接口，支持三个接口推送
// 1.PushMessageToSingle接口：支持对单个用户进行推送
// 2.PushMessageToList接口：支持对多个用户进行推送，建议为50个用户
// 3.pushMessageToApp接口：对单个应用下的所有用户进行推送，可根据省份，标签，机型过滤推送
//

// 单推接口案例
function pushMessageToSingle($title, $text, $logo) {
    $igt = new IGeTui ( HOST, APPKEY, MASTERSECRET );
    
    // 消息模版：
    // 1.TransmissionTemplate:透传功能模板
    // 2.LinkTemplate:通知打开链接功能模板
    // 3.NotificationTemplate：通知透传功能模板
    // 4.NotyPopLoadTemplate：通知弹框下载功能模板
    
    // $template = IGtNotyPopLoadTemplateDemo();
    // $template = IGtLinkTemplateDemo();
    $template = IGtNotificationTemplateDemo ( $title, $text, $logo );
    // $template = IGtTransmissionTemplateDemo();
    
    // 个推信息体
    $message = new IGtSingleMessage ();
    
    $message->set_isOffline ( true ); // 是否离线
    $message->set_offlineExpireTime ( 3600 * 12 * 1000 ); // 离线时间
    $message->set_data ( $template ); // 设置推送消息类型
    $message->set_PushNetWorkType ( 1 ); // 设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
                                      // 接收方
    $target = new IGtTarget ();
    $target->set_appId ( APPID );
    $target->set_clientId ( CID );
    
    $rep = $igt->pushMessageToSingle ( $message, $target );
}

// 多推接口案例
function pushMessageToList() {
    putenv ( "needDetails=true" );
    $igt = new IGeTui ( HOST, APPKEY, MASTERSECRET );
    // 消息模版：
    // 1.TransmissionTemplate:透传功能模板
    // 2.LinkTemplate:通知打开链接功能模板
    // 3.NotificationTemplate：通知透传功能模板
    // 4.NotyPopLoadTemplate：通知弹框下载功能模板
    
    // $template = IGtNotyPopLoadTemplateDemo();
    // $template = IGtLinkTemplateDemo();
    // $template = IGtNotificationTemplateDemo();
    $template = IGtTransmissionTemplateDemo ();
    // 个推信息体
    $message = new IGtListMessage ();
    
    $message->set_isOffline ( true ); // 是否离线
    $message->set_offlineExpireTime ( 3600 * 12 * 1000 ); // 离线时间
    $message->set_data ( $template ); // 设置推送消息类型
                                   // $message->set_PushNetWorkType(0); //设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
    $contentId = $igt->getContentId ( $message );
    // $contentId = $igt->getContentId($message,"toList任务别名功能"); //根据TaskId设置组名，支持下划线，中文，英文，数字
    
    // 接收方1
    $target1 = new IGtTarget ();
    $target1->set_appId ( APPID );
    $target1->set_clientId ( CID );
    
    $targetList [] = $target1;
    
    $rep = $igt->pushMessageToList ( $contentId, $targetList );
    
    var_dump ( $rep );
    echo ("<br><br>");
}

// 群推接口案例
function pushMessageToApp() {
    $igt = new IGeTui ( HOST, APPKEY, MASTERSECRET );
    // 消息模版：
    // 1.TransmissionTemplate:透传功能模板
    // 2.LinkTemplate:通知打开链接功能模板
    // 3.NotificationTemplate：通知透传功能模板
    // 4.NotyPopLoadTemplate：通知弹框下载功能模板
    
    // $template = IGtNotyPopLoadTemplateDemo();
    // $template = IGtLinkTemplateDemo();
    $template = IGtNotificationTemplateDemo ();
    // $template = IGtTransmissionTemplateDemo();
    
    // 个推信息体
    // 基于应用消息体
    $message = new IGtAppMessage ();
    
    $message->set_isOffline ( true );
    $message->set_offlineExpireTime ( 3600 * 12 * 1000 ); // 离线时间单位为毫秒，例，两个小时离线为3600*1000*2
    $message->set_data ( $template );
    // $message->set_PushNetWorkType(0); //设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
    
    $message->set_appIdList ( array (
            APPID 
    ) );
    // $message->set_phoneTypeList(array('ANDROID'));
    // $message->set_provinceList(array('浙江','北京','河南'));
    // $message->set_tagList(array('开心'));
    
    $rep = $igt->pushMessageToApp ( $message, 'toApp任务别名' ); // 根据TaskId设置组名，支持下划线，中文，英文，数字
    
    var_dump ( $rep );
    echo ("<br><br>");
}

// 所有推送接口均支持四个消息模板，依次为通知弹框下载模板，通知链接模板，通知透传模板，透传模板
// 注：IOS离线推送需通过APN进行转发，需填写pushInfo字段，目前仅不支持通知弹框下载功能
function IGtNotyPopLoadTemplateDemo() {
    $template = new IGtNotyPopLoadTemplate ();
    
    $template->set_appId ( APPID ); // 应用appid
    $template->set_appkey ( APPKEY ); // 应用appkey
                                    // 通知栏
    $template->set_notyTitle ( "个推" ); // 通知栏标题
    $template->set_notyContent ( "个推最新版点击下载" ); // 通知栏内容
    $template->set_notyIcon ( "" ); // 通知栏logo
    $template->set_isBelled ( true ); // 是否响铃
    $template->set_isVibrationed ( true ); // 是否震动
    $template->set_isCleared ( true ); // 通知栏是否可清除
                                     // 弹框
    $template->set_popTitle ( "弹框标题" ); // 弹框标题
    $template->set_popContent ( "弹框内容" ); // 弹框内容
    $template->set_popImage ( "" ); // 弹框图片
    $template->set_popButton1 ( "下载" ); // 左键
    $template->set_popButton2 ( "取消" ); // 右键
                                      // 下载
    $template->set_loadIcon ( "" ); // 弹框图片
    $template->set_loadTitle ( "地震速报下载" );
    $template->set_loadUrl ( "http://dizhensubao.igexin.com/dl/com.ceic.apk" );
    $template->set_isAutoInstall ( false );
    $template->set_isActived ( true );
    
    return $template;
}
function IGtLinkTemplateDemo() {
    $template = new IGtLinkTemplate ();
    $template->set_appId ( APPID ); // 应用appid
    $template->set_appkey ( APPKEY ); // 应用appkey
    $template->set_title ( "请输入通知标题" ); // 通知栏标题
    $template->set_text ( "请输入通知内容" ); // 通知栏内容
    $template->set_logo ( "" ); // 通知栏logo
    $template->set_isRing ( true ); // 是否响铃
    $template->set_isVibrate ( true ); // 是否震动
    $template->set_isClearable ( true ); // 通知栏是否可清除
    $template->set_url ( "http://www.igetui.com/" ); // 打开连接地址
                                                   // iOS推送需要设置的pushInfo字段
                                                   // $template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
                                                   // $template ->set_pushInfo("",2,"","","","","","");
    return $template;
}
function IGtNotificationTemplateDemo($title, $text, $logo) {
    $template = new IGtNotificationTemplate ();
    $template->set_appId ( APPID ); // 应用appid
    $template->set_appkey ( APPKEY ); // 应用appkey
    $template->set_transmissionType ( 1 ); // 透传消息类型
                                        // $template->set_transmissionContent("测试离线");//透传内容
    $template->set_title ( $title ); // 通知栏标题
    $template->set_text ( $text ); // 通知栏内容
    $template->set_logo ( $logo ); // 通知栏logo
    $template->set_isRing ( true ); // 是否响铃
    $template->set_isVibrate ( true ); // 是否震动
    $template->set_isClearable ( true ); // 通知栏是否可清除
                                      // iOS推送需要设置的pushInfo字段
                                      // $template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
                                      // $template ->set_pushInfo("test",1,"message","","","","","");
    return $template;
}
function IGtTransmissionTemplateDemo() {
    $template = new IGtTransmissionTemplate ();
    $template->set_appId ( APPID ); // 应用appid
    $template->set_appkey ( APPKEY ); // 应用appkey
    $template->set_transmissionType ( 1 ); // 透传消息类型
    $template->set_transmissionContent ( "测试离线" ); // 透传内容
                                                // iOS推送需要设置的pushInfo字段
                                                // $template ->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
                                                // $template ->set_pushInfo("", 0, "", "", "", "", "", "");
    return $template;
}

