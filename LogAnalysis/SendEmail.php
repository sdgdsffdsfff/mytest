<?php
require __DIR__ . '/LogAnalysis1.php';
require __DIR__ . '/PHPMailer/class.phpmailer.php';
require __DIR__ . '/PHPMailer/class.smtp.php';
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
    $mailer->AddAddress ( 'cuiguangbin@lianjia.com' );
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
//header ( "Content-Type: application/json; charset=utf-8" );
$rl = new LogAnalysis ();
//$dir = "C:\Users\N010D90001\Downloads\0315.log";
$files = array ('C:\Users\N010D90001\Downloads\0315.log');
// Open a known directory, and proceed to read its contents

// if (is_dir ( $dir )) {
//     if ($dh = opendir ( $dir )) {
//         while ( ($file = readdir ( $dh )) !== false ) {
//             if ($file == '.' || $file == '..') {
//                 continue;
//             }
//             $files [] = $dir . $file;
//         }
//         closedir ( $dh );
//     }
// }

$logdate = $rl->getLogDate ( $files [0] );
$title = '掌上链家API日志统计 ' . $logdate;
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

$arr = $rl->getContentFromFile ( $files );
ksort($arr);

$html .= $rl->arrayToTable ( $arr );
$html . '</body></html>';
 echo $html;
// $fp = fopen ( 'API日志分析.html', 'w' ); // 打开要写入的文件
// fwrite ( $fp, $html ); // 写入
// fclose ( $fp ); // 关闭文件

//sendMail ( $title, $html );

