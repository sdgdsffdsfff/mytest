<?php
class Sogou {
    static $QUOTA = array (
            0 => '一房一名额',
            1 => '无名额限制',
            2 => '不对外招生',
            - 1 => '暂无数据' 
    );
    static $TYPE = array (
            0 => '普通',
            1 => '市重点',
            2 => '区重点' 
    );
    static $TYPEID = array (
            0 => '2',
            1 => '0',
            2 => '1' 
    );
    private function writeToFile($filename, $str) {
        $fp = fopen ( $filename, 'w' ); // 打开要写入 XML数据的文件
        fwrite ( $fp, $str ); // 写入 XML数据
        fclose ( $fp ); // 关闭文件
    }
    // 小学信息
    private $query1 = <<<SQL1
    SELECT a.mobile,a.ctime web_ctime,b.ctime mobile_ctime,b.mobile_client_last_login_time 
    FROM lianjia_web_client a join lianjia_web_client_setting b on a.client_id=b.client_id 
    WHERE b.ctime>'20150310' 
SQL1;
    private function queryArray($query) {
        // conection:
        $link = mysqli_connect ( "172.30.0.20:5000", "prod", "123456", "lianjia" ) or die ( "Error " . mysqli_error ( $link ) );
        $link->query ( "SET NAMES utf8" );
        // consultation:
        
        $result = $link->query ( $query ) or die ( "Error in the consult.." . mysqli_error ( $link ) );
        
        $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC );
        $arr= array ();
        while ( $row ) {
            $arr [] = $row;
            $row = mysqli_fetch_array ( $result, MYSQLI_ASSOC );
        }
        $link->close ();
        return $arr;
    }
    private function queryUsers($query) {
        $users = self::queryArray ( $query );
        $result = array ();
        foreach ( $users as $user ) {
            $result [] = array (
                    'mobile' => $user ['mobile'],
                    'web_ctime' => $user ['web_ctime'], // 落户年限
                    'mobile_ctime' => $user ['mobile_ctime'], // 落户名额
                    'mobile_client_last_login_time' => $user ['mobile_client_last_login_time'],
            );
        }
        return $result;
    }
    public function writeJson() {
        $data = self::queryArray($this->query1);
        self::writeToFile('user.json', json_encode($data));
    }
}
// header ( "Content-Type: text/html; charset=utf-8" );
header ( 'Content-type: application/json; charset=utf-8' );
$sougou = new Sogou ();
$sougou->writeJson ();

