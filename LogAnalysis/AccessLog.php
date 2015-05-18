<?php
require __DIR__ . '/PHPMailer/class.phpmailer.php';
require __DIR__ . '/PHPMailer/class.smtp.php';
class NginxLog {
    public $remote_user;
    public $http_host;
    // public $time_local;
    public $request;
    public $status;
    public $request_length;
    public $body_bytes_sent;
    public $request_time;
    public $time;
    // public $http_referer;
    // public $http_user_agent;
    // public $remote_addr;
    public function __construct($str) {
        // $s = array( ' - ', ' [', '] "', '" ', ' ', ' ', ' "', '" "', '" "', '" ',);
        $arr = explode ( "\t", $str );
        $this->setTime($arr[3]);
        $this->http_x_forwarded_for = $arr [0];
        $this->http_host = $arr [2];
        // $this->time_local = $arr[3];
        $this->setRequest ( $arr [4] );
        $this->request_length = $arr [5];
        $this->status = $arr [6];
        $this->body_bytes_sent = $arr [7];
        $this->request_time = $arr [8];
        // $this->http_referer = $arr[9];
        // $this->http_user_agent = $arr[10];
        // $this->remote_addr = $arr[11];
    }
    public function setTime($strAPI) {
        // 去掉请求的参数
        $s = strpos ( $strAPI, ':' )+1;
        $t = strpos($strAPI, ' ');

        $time = substr($strAPI,$s,$t-$s);
        $this->time = strtotime($time);
    }
    public function setRequest($strAPI) {
        // 去掉请求的参数
        $strAPI = explode ( ' ', $strAPI )[1];
        if (($e = strpos ( $strAPI, '?' )) !== false) {
            $this->request = substr ( $strAPI, 0, $e );
        } else {
            $this->request = $strAPI;
        }
    }
}
class AccessLog {
    const PV = 'PV';
    const AVG_TIME = 'avg_time';
    const TOTAL_TIME = 'total_time';
    const FLOW_IN = 'flow_in';
    const FLOW_OUT = 'flow_out';
    const FOUR_COUNT = 'four_count';
    const FIVE_COUNT = 'five_count';
    const FOUR_RATE = 'four_rate';
    const FIVE_RATE = 'five_rate';
    const DOMAIN = '域名';
    const TOP_FOUR = 'top_four';
    const TOP_FIVE = 'top_five';
    private static $fields = array (
            self::PV => 'PV',
            self::TOTAL_TIME => '',
            self::AVG_TIME => '平均时间',
            self::FLOW_IN => '流入流量(G)',
            self::FLOW_OUT => '流出流量(G)',
            self::FOUR_COUNT => 'HTTP_4XX数量',
            self::FIVE_COUNT => 'HTTP_5XX数量',
            self::FOUR_RATE => 'HTTP_4XX比例',
            self::FIVE_RATE => 'HTTP_5XX比例',
            self::TOP_FOUR => 'TOP10_4XX',
            self::TOP_FIVE => 'TOP10_5XX'
    );
    function multiexplode($delimiters, $string) {
        $ready = str_replace ( $delimiters, $delimiters [0], $string );
        $launch = explode ( $delimiters [0], $ready );
        return $launch;
    }
    function getLogDate($file) {
        
        // 日志的开头可能为INFO: 2015-03-13 00:04:03
        // 或ERROR: 2015-03-13 00:04:03
        // 或WARNING:2015-03-13 00:04:03
        // 取第一冒号后面11位
        $handle = fopen ( $file, "r" ) or die ( "can\'t open file {$file}" );
        $logdate = '';
        if (($line = fgets ( $handle, 8192 )) !== false) {
            $pos = strpos ( $line, ': ' ) + 2;
            $logdate = substr ( $line, $pos, 10 );
        }
        fclose ( $handle );
        return $logdate;
    }
    function arrayToTable($arr) {
        $html = '<table id="dd" >';
        
        // 表行标题
        $html .= '<thead><tr>';
        $html .= '<th>' . self::DOMAIN . '</th>';
        
        foreach ( self::$fields as $field ) {
            if (empty ( $field )) {
                continue;
            }
            $html .= '<th>' . $field . '</th>';
        }
        $html .= '</tr>';
        
        $html .= '</tr></thead>';
        
        $html .= '<tbody class="tb1">';
        foreach ( $arr as $apiName => $col ) {
            $html .= '<tr>';
            $html .= '<td>' . $apiName . '</td>';
            
            foreach ( self::$fields as $apiKey => $apiValue ) {
                if (empty ( $apiValue )) {
                    continue;
                }
                if (isset ( $col [$apiKey] )) {
                    $html .= '<td>' . $col [$apiKey] . '</td>';
                } else {
                    $html .= '<td>0</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }
    protected function getTopN($arr) {
        $result = '';
        if (! empty ( $arr )) {
            arsort ( $arr );
            $i = 0;
            foreach ( $arr as $k => $v ) {
                $result .= '<li>' . $k . ':' . $v . "</li>";
                if (++ $i >= 20)
                    break;
            }
        }
        return $result;
    }
    protected function invalidHost($str) {

        if (strpos ( $str, 'lianjia' ) === false) {
            return true;
        }
        
        return false;
    }
    public function getContentFromFile($files) {
        $i = 0;
        $arrAPI = array ();
        $arrFour = array ();
        $arrFive = array ();
        foreach ( $files as $file ) {
            $handle = fopen ( $file, "r" ) or die ( "can\'t open file {$file}" );
            // 循环读取每一行日志
            while ( ($line = fgets ( $handle, 8192 )) !== false ) {
                $nginxlog = new NginxLog ( $line );
                if ($this->invalidHost ( $nginxlog->http_host )) {
                    continue;
                }
                if (empty ( $arrAPI [$nginxlog->http_host] [self::PV] )) {
                    $arrAPI [$nginxlog->http_host] [self::PV] = 1;
                } else {
                    $arrAPI [$nginxlog->http_host] [self::PV] += 1;
                }
            }
            fclose ( $handle );
        }
        return $arrAPI;
    }
}
function sendMail($subject, $content, $address = '') {
    $mailer = new PHPmailer ();
    $mailer->Host = 'mail.lianjia.com';
    $mailer->IsSMTP ();
    $mailer->SMTPAuth = true;
    
    // 链家邮件发件人设置
    $mailer->Username = "noreply@lianjia.com";
    $mailer->Password = '123456';
    
    $mailer->From = "noreply@lianjia.com";
    $mailer->FromName = "API日志分析";
    $mailer->CharSet = "UTF-8";
    
    // 收件人设置
    $mailer->Encoding = "base64";
    $mailer->AddAddress ( 'zuoerdong@lianjia.com' );
    // $mailer->AddAddress ( 'cuiguangbin@lianjia.com' );
    if ($address) {
        foreach ( ( array ) $address as $tmp ) {
            $mailer->AddAddress ( $tmp );
        }
    }
    // $mailer->AddCC('webrd@lianjia.com');
    
    // $filename = $subject.".html";
    // $file = fopen($filename, "w+");
    // fwrite($file, $content);
    // fclose($file);
    // 文件太大，采取压缩后附件形式
    // shell_exec("tar zcvf $filename.tar $filename ");
    // shell_exec("zip -r $filename.zip $filename ");
    
    $mailer->IsHTML ( true );
    $mailer->Subject = $subject;
    $mailer->Body = $content;
    // $tarName = $filename.'.zip';
    // $mailer->AddAttachment('./'.$tarName, "$tarName");//附件的路径和附件名称
    
    if ($mailer->Send ()) {
        echo "send email $subject successful!";
    } else {
        echo "sendmail wrong" . $mailer->ErrorInfo;
    }
    // shell_exec("rm $tarName");
    // shell_exec("rm $filename");
}

header ( "Content-Type: text/html; charset=utf-8" );

$rl = new AccessLog ();
$filename = 'C:\Users\N010D90001\Downloads\abc.log';

// 对文件名的编码，避免中文文件名乱码
$filename = iconv ( "UTF-8", "GBK", $filename );
$files = array (
        $filename
);

$logdate = $rl->getLogDate ( $files [0] );
$title = 'Nginx日志统计 ' . $logdate;
$html = <<<HTML
<html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>$title</title>
        </head>
<style type="text/css">
body {font-size: 12px; font-family: Arial, Helvetica, sans-serif; }
table#dd {background-color: #6CADD9;white-space:nowrap;}
table#dd thead th {background-color: #6CADD9;color: #FFFFFF;font-size: 12px;}
table#dd td {padding: 6px;width: 120px;}
table#dd tbody.tb1 td {background-color: #FFFFFF;}
table#dd tbody.tb2 td {background-color: #F7F7F7;}
table#dd tbody td:hover {background-color: #BFEDF9;}
table#dd tbody td ul {list-style-type:none;margin:0px;padding:0px;}
center {font-size: 24px;margin:50px}
</style>
<body>
<center>$title</center>
HTML;
$s = time ();
$arr = $rl->getContentFromFile ( $files );

ksort ( $arr );

$html .= $rl->arrayToTable ( $arr );
$html .= '</body></html>';
echo $html;
//sendMail ( $title, $html );