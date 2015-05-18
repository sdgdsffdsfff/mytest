<?php
require 'IGt.Push.php';
class GeTui {
    private $alias;
    private $message;
    private $messageNoContent;
    private $content;

    public $host;

    //掌上链家
    public $appkey;
    public $masterSecret;
    public $appid;
    
    

    public function init1() {
        $this->appkey = 'F2V28Cp0TQ9QuoU7RxXpN7';
        $this->masterSecret= 'hveGuH9d8e9GGPqRd8w6L9';    //没有用
        $this->appid = 'cR7sdpYnI78fQTjLu5rch1';        
        $this->host = 'http://sdk.open.api.igexin.com/apiex.htm';
    }

    public function pushToSingleByAlias($alias, $msg) {

        $this->alias = $alias;
        $this->message = json_encode ( $msg );

        // for ios set_pushinfo
        $this->content = array_pop ( $msg );
        $this->messageNoContent = json_encode ( $msg );

        //使用透传模板
        $template = $this->IGtTransmissionTemplateDemo ();

        // 个推信息体
        $message = new IGtSingleMessage ();
        $message->set_isOffline ( true ); // 是否离线
        $message->set_offlineExpireTime ( 72 * 3600 * 1000 ); // 离线时间,最大值72小时
        $message->set_data ( $template ); // 设置推送消息类型
        $message->set_PushNetWorkType ( 0 ); // 设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送

        // 接收方
        $target = new IGtTarget ();
        $target->set_appId ( $this->appid );
        $target->set_alias ( $this->alias );

        //推送
        $igt = new IGeTui($this->host, $this->appkey, $this->masterSecret);
        return $igt->pushMessageToSingle ( $message, $target );
    }
    private function IGtTransmissionTemplateDemo() {
        $template = new IGtTransmissionTemplate ();
        $template->set_appId ( $this->appid); // 应用appid
        $template->set_appkey ( $this->appkey); // 应用appkey
        $template->set_transmissionType ( 0 ); // 透传消息类型,1会自动启动应用
        $template->set_transmissionContent ( $this->message ); // 透传内容

        // iOS推送需要设置的pushInfo字段
        $template->set_pushInfo ( '', 0, $this->content, 'default', $this->messageNoContent, '', '', '' );
        return $template;
    }
}
class ZuGeTui {
    var $alias;
    var $message;
    var $messageNoContent;
    var $content;
    
    const HOST = 'http://sdk.open.api.igexin.com/apiex.htm';
    
    // 个推演示
//     const APPKEY = 'YTf8dPFUuk5ZfaIXgh0yN8';
//     const MASTERSECRET = 'bUh8KKcU5n8CztOkUMvpDA';    //没有用
//     const APPID = 'OTKxCDCNVM9VbTqcAk74m9';
    
    //新的掌上链家
        const APPKEY = 'F2V28Cp0TQ9QuoU7RxXpN7';
        const MASTERSECRET = 'hveGuH9d8e9GGPqRd8w6L9';    //没有用
        const APPID = 'cR7sdpYnI78fQTjLu5rch1';
    
    //新的掌上链家
//     const APPKEY = 'X5nJ0ieflU5wSjdbrA3wm1';
//     const MASTERSECRET = 'wYd3dlgaEX8xw4EmOFFl76';    //没有用
//     const APPID = 'Yaee7iJYbl9b3OxuvGiei4';
    
    //旧的掌上链家
//     const APPKEY = 'dBHTyRF5YF8xVu2tgKnc15';
//     const MASTERSECRET = 'ejIaSe25Xv87IXajc9Z1Y1';    //没有用
//     const APPID = 'Z62qOoG7SN8FI0TkWmcNIA';
    
    public function init1($alias, $message) {
        
        $this->alias = $alias;
        $this->message = json_encode ( $message );
        
        //for ios set_pushinfo
        $this->content = array_pop ( $message );
        $this->messageNoContent = json_encode ( $message );
    }
    public function pushToSingleByAlias() {
        $template = $this->IGtTransmissionTemplateDemo ();
        
        // 个推信息体
        $message = new IGtSingleMessage ();
        $message->set_isOffline ( true ); // 是否离线
        $message->set_offlineExpireTime ( 72 * 3600 * 1000 ); // 离线时间,最大值72小时
        $message->set_data ( $template ); // 设置推送消息类型
        $message->set_PushNetWorkType ( 0 ); // 设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
                                             
        // 接收方
        $target = new IGtTarget ();
        $target->set_appId ( self::APPID);
        $target->set_alias($this->alias);
        //$target->set_clientId('df0e2f1a754c7cf106086307c17a16cb');
        
        $igt=new IGeTui(self::HOST,self::APPKEY,self::MASTERSECRET);
        
        return $igt->pushMessageToSingle ( $message, $target );
    }
    private function IGtTransmissionTemplateDemo() {
        $template = new IGtTransmissionTemplate ();
        $template->set_appId ( self::APPID ); // 应用appid
        $template->set_appkey ( self::APPKEY ); // 应用appkey
        $template->set_transmissionType ( 1 ); // 透传消息类型,1会自动启动应用
        $template->set_transmissionContent ( $this->content ); // 透传内容
        
        // iOS推送需要设置的pushInfo字段
        $template->set_pushInfo ( '', 0, $this->content, 'default', $this->messageNoContent, '', '', '' );
        return $template;
    }
}
header("Content-Type: text/html; charset=utf-8");
$g=new GeTui;
$message = array (
        'name' => 'house_feed',
        'id' => 'SZWZ88392343',
        'content' => '把我帐号退了就收不到了'
);

$g->init1();
$g->pushToSingleByAlias('841018',$message);
