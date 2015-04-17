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
    private function arrToXml($arr, $dom = 0, $item = 0, $itemname = 'item') {
        if (! $dom) {
            $dom = new DOMDocument ( "1.0", 'gbk' );
            $dom->formatOutput = true;
        }
        
        if (! $item) {
            $item = $dom->createElement ( "DOCUMENT" );
            $dom->appendChild ( $item );
        }
        if (empty ( $arr )) {
            return;
        }
        foreach ( $arr as $key => $val ) {
            if (is_string ( $key ) && $key == 'pmschool') {
                $itemx = $item;
                $itemname = 'pmschool';
            } else {
                $itemx = $dom->createElement ( is_string ( $key ) ? $key : $itemname );
                $item->appendChild ( $itemx );
            }
            if (is_string ( $key ) && $key == 'pschooltypeid') {
                $itemname = 'item';
            }
            if (! is_array ( $val )) {
                $text = $dom->createCDATASection ( $val );
                $itemx->appendChild ( $text );
            } else {
                $this->arrToXml ( $val, $dom, $itemx, $itemname );
            }
        }
        return $dom->saveXML ();
    }
    private function writeXmlToFile($filename, $xml) {
        $fp = fopen ( $filename, 'w' ); // 打开要写入 XML数据的文件
        fwrite ( $fp, $xml ); // 写入 XML数据
        fclose ( $fp ); // 关闭文件
    }
    // 小学信息
    private $query1 = <<<SQL1
select id , school_name,avail_year,places_rule,address,phone,label,school_grade
from era_school_district_info
where shcool_grade_type=1
SQL1;
    // 小学周边幼儿园
    private $query2 = <<<SQL2
select school_name,base_sid
from era_school_counterpart_periphery
where school_type=2
order by base_sid
SQL2;
    // 小学对应中学信息
    private $query3 = <<<SQL
select a.school_name,b.address,b.phone,b.school_grade,a.base_sid
from era_school_counterpart_periphery a join era_school_district_info b on a.school_id=b.id
where b.shcool_grade_type=2
and a.school_type=1
order by a.base_sid
SQL;
    // 小学特色
    private $query4 = <<<SQL
select id,label
from era_school_label
where type=1
SQL;
    private function queryArray($query) {
        // conection:
        $link = mysqli_connect ( "172.30.0.20:5000", "prod", "123456", "homelink" ) or die ( "Error " . mysqli_error ( $link ) );
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
    private function querySchools($query) {
        $schools = self::queryArray ( $query );
        $result = array ();
        foreach ( $schools as $school ) {
            $result [$school ['id']] = array (
                    'name' => $school ['school_name'],
                    'settleyear' => $school ['avail_year'], // 落户年限
                    'settlequota' => $school ['places_rule'], // 落户名额
                    'paddress' => $school ['address'],
                    'ptele' => $school ['phone'],
                    'pfeature' => $school ['label'],
                    'pschooltypeid' => $school ['school_grade'] 
            );
        }
        return $result;
    }
    private function queryKinders($query) {
        $kinders = self::queryArray ( $query );
        $result = array ();
        $tmpkey = null;
        foreach ( $kinders as $kinder ) {
            if ($tmpkey == $kinder ['base_sid']) {
                $result [$kinder ['base_sid']] [] = $kinder ['school_name'];
            } else {
                $tmpkey = $kinder ['base_sid'];
                $result [$kinder ['base_sid']] = array (
                        $kinder ['school_name'] 
                );
            }
        }
        return $result;
    }
    private function queryMiddles($query) {
        $middles = self::queryArray ( $query );
        $result = array ();
        $tmpkey = null;
        foreach ( $middles as $middle ) {
            if ($tmpkey == $middle ['base_sid']) {
                $result [$middle ['base_sid']] [] = array (
                        'pmschoolName1' => $middle ['school_name'],
                        'pmtype' => self::$TYPE [$middle ['school_grade']],
                        'pmadress' => $middle ['address'],
                        'pmtele' => $middle ['phone'] 
                );
            } else {
                $tmpkey = $middle ['base_sid'];
                $result [$middle ['base_sid']] = array (
                        array (
                                'pmschoolName1' => $middle ['school_name'],
                                'pmtype' => self::$TYPE [$middle ['school_grade']],
                                'pmadress' => $middle ['address'],
                                'pmtele' => $middle ['phone'] 
                        ) 
                );
            }
        }
        return $result;
    }
    private function queryFeatures($query) {
        $features = self::queryArray ( $query );
        $result = array ();
        foreach ( $features as $feature ) {
            $result [$feature ['id']] = $feature ['label'];
        }
        return $result;
    }
    public function mapFeature($key) {
        $features = self::queryFeatures ( $this->query4 );
        return $features [$key];
    }
    private function getNewSchoolNames() {
        $myFile = fopen ( 'schools.json', 'r' );
        $myLine = '';
        while ( ! feof ( $myFile ) ) {
            // Read each line and add to $myLine
            $myLine .= fgets ( $myFile, 255 );
        }
        fclose ( $myFile );
        return json_decode ( $myLine, 1 );
    }
    private function assembleArray() {
        $schools = self::querySchools ( $this->query1 );
        $kinders = self::queryKinders ( $this->query2 );
        $middles = self::queryMiddles ( $this->query3 );
        $newNames = self::getNewSchoolNames ();
        $result = array ();
        foreach ( $schools as $id => $school ) {
            
            $newName = '';
            if (! empty ( $newNames [$id] )) {
                $newName = $newNames [$id];
            } else {
                
                continue;
            }
            
            // 小学特色
            $feature = '';
            if ($school ['pfeature']) {
                $feature = implode ( ';', array_map ( array (
                        $this,
                        'mapFeature' 
                ), explode ( ',', $school ['pfeature'] ) ) );
            }
            
            // 周边幼儿园
            $kindergarten = '';
            if (! empty ( $kinders [$id] )) {
                $kindergarten = implode ( ';', $kinders [$id] );
            }
            
            // 对口中学
            $pmschool = array ();
            if (! empty ( $middles [$id] )) {
                $pmschool = $middles [$id];
            }
            
            $result [] = array (
                    //'key' => $school ['name'] . '+' . $id,
                    'key' => $newNames [$id][1] . '+' . $newNames [$id][0],
                    'display' => array (
                            //'pschoolId' => $id,
                            'pschoolId' => $newNames [$id][0],
                            'settleyear' => $school ['settleyear'],
                            'settlequota' => self::$QUOTA [$school ['settlequota'] ? $school ['settlequota'] : - 1],
                            'paddress' => $school ['paddress'],
                            'ptele' => $school ['ptele'],
                            'pfeature' => $feature,
                            'pkindergarten' => $kindergarten 
                    ),
                    // 'pschoolName' => $school ['name'],
                    'pschoolName' => $newNames [$id][1],
                    'pschooltype' => self::$TYPE [$school ['pschooltypeid']],
                    'pmschool' => $pmschool,
                    'alltype' => '市重点;区重点;普通',
                    'pschooltypeid' => self::$TYPEID [$school ['pschooltypeid']] 
            );
        }
        return $result;
    }
    public function writSchoolXml() {
        $data = self::assembleArray ();
        $xml = self::arrToXml ( $data );
        self::writeXmlToFile ( 'newxml.xml', $xml );
    }
}
// header ( "Content-Type: text/html; charset=utf-8" );
header ( 'Content-type: application/json; charset=utf-8' );
$sougou = new Sogou ();
$sougou->writSchoolXml ();

