<?php
class GUdd {
    private static $tableHead = array (
            'mobile' => '用户电话',
            'web_ctime' => '注册时间',
            'mobile_ctime' => '首次登录掌上链家时间',
            'mobile_client_last_login_time' => '数据期间最后一次登录掌上链家时间' 
    );
    function queryArray($query) {
        // conection:
        // $link = mysqli_connect ( "172.30.0.20:5000", "prod", "123456", "lianjia" ) or die ( "Error " . mysqli_error ( $link ) );
        $link = mysqli_connect ( "172.16.6.183:6521", "phpmyadmin", "w2e#-s1f!^)()", "lianjia" ) or die ( "Error " . mysqli_error ( $link ) );
        $link->query ( "SET NAMES utf8" );
        // consultation:
        
        $result = $link->query ( $query ) or die ( "Error in the consult.." . mysqli_error ( $link ) );
        
        $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC );
        $schools = array ();
        while ( $row ) {
            $schools [] = $row;
            $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC );
        }
        $link->close ();
        return $schools;
    }
    public function queryUsers($query) {
        $users = $this->queryArray ( $query );
        
        $html = <<<HTML
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>掌上链家用户</title>
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
        </style>
    <body>
HTML;
        
        $html .= $this->arrayToTable ( $users );
        $html .= '</body></html>';
        echo $html;
        $this->writeStringToFile ( $html, 'ab.html' );
    }
    public function writeStringToFile($str, $filename) {
        $fp = fopen ( $filename, 'w' ); // 打开要写入 XML数据的文件
        fwrite ( $fp, $str ); // 写入 XML数据
        fclose ( $fp ); // 关闭文件
    }
    function arrayToTable($arr) {
        $html = '<table id="dd" >';
        
        // 表行标题
        $html .= '<thead><tr>';
        
        $html .= '<th>序号</th>';
        foreach ( self::$tableHead as $head ) {
            if (empty ( $head )) {
                continue;
            }
            $html .= '<th>' . $head . '</th>';
        }
        $html .= '</tr></thead>';
        
        $html .= '<tbody class="tb1">';
        foreach ( $arr as $apiName => $col ) {
            $html .= '<tr>';
            $html .= '<td>' . $apiName . '</td>';
            
            foreach ( self::$tableHead as $k => $v ) {
                if (empty ( $v )) {
                    continue;
                }
                if (isset ( $col [$k] )) {
                    $html .= '<td>' . $col [$k] . '</td>';
                } else {
                    $html .= '<td>0</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }
}
$date = date ( 'Ymd', strtotime ( '-1 day' ) );

if (date ( 'd' ) == 1) { // 每月1号发送上月的
    $date1 = date ( 'Ymd', strtotime ( '-1 month' ) );
    $query = <<<SQL
    SELECT a.mobile,a.ctime web_ctime,b.ctime mobile_ctime,b.mobile_client_last_login_time
    FROM lianjia_web_client a join lianjia_web_client_setting b on a.client_id=b.client_id
    WHERE to_days(b.ctime) between to_days('$date' ) and to_days('$date')
SQL;
} else if (date ( 'w' ) == 1) { // 每周一发送上周的
    $date1 = date ( 'Ymd', strtotime ( '-7 day' ) );
    $query = <<<SQL
    SELECT a.mobile,a.ctime web_ctime,b.ctime mobile_ctime,b.mobile_client_last_login_time
    FROM lianjia_web_client a join lianjia_web_client_setting b on a.client_id=b.client_id
    WHERE to_days(b.ctime) between to_days('$date' ) and to_days('$date')
SQL;
} else {
    $query = <<<SQL
    SELECT a.mobile,a.ctime web_ctime,b.ctime mobile_ctime,b.mobile_client_last_login_time
    FROM lianjia_web_client a join lianjia_web_client_setting b on a.client_id=b.client_id
    WHERE to_days(b.ctime) = to_days('$date' )
SQL;
}

$a = new GUdd ();
echo $a->queryUsers ( $query );
