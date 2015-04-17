<?php
class Sogou {
    static $QUOTA = array (
            0 => '一房一名额',
            1 => '无名额限制',
            2 => '不对外招生',
            - 1 => '' 
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
select id,school_name, school_alias,places_rule,avail_year,
            address,phone,label,school_grade,
            case when lng >0 then lng 
			    	when longitude>0 then longitude
			end as longitude,
            case when lat >0 then lat 
					when latitude>0 then latitude
			end as latitude,
            min_aver_price,max_aver_price,city_id
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
    // 对应小区
    private $query5 = <<<SQL
select distinct a.id,b.community_code 
from era_school_district_info a join era_school_building_relation b on a.id=b.school_id 
where shcool_grade_type=1 
SQL;
    // 小学图片
    private $query6 = <<<SQL
select sid id,max(pic_big_path) pic
from era_agent_school_pic 
where cid in(
SELECT max(cid)
FROM `era_agent_school_pic` 
group by sid)
and pic_big_path like '%uploadfile%'
group by sid
SQL;
    // 小区图片
    private $query7 = <<<SQL
select b.community_code,max(c.pic_list_path) pic 
from era_school_district_info a 
join era_school_building_relation b on a.id=b.school_id 
join community_pic c on b.community_code = c.community_code
where shcool_grade_type=1 
group by a.id,b.community_code
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
            $position = '';
            if ($school ['latitude'] && $school ['longitude']) {
                $position = $school ['latitude'] . ',' . $school ['longitude'];
            }
            $result [$school ['id']] = array (
                    'city_id' => $school ['city_id'],
                    'name' => $school ['school_name'],
                    'settleyear' => $school ['avail_year'], // 落户年限
                    'settlequota' => $school ['places_rule'], // 落户名额
                    'paddress' => $school ['address'],
                    'ptele' => $school ['phone'],
                    'pfeature' => $school ['label'],
                    'pschooltypeid' => $school ['school_grade'],
                    'position' => $position,
                    'min_aver_price' => $school ['min_aver_price'],
                    'max_aver_price' => $school ['max_aver_price'],
                    'education_code' => $school ['school_alias'] 
            );
        }
        return $result;
    }
    private function queryKinders($query) {
        $kinders = self::queryArray ( $query );
        $result = array ();
        foreach ( $kinders as $kinder ) {
            $result [$kinder ['base_sid']] [] = $kinder ['school_name'];
        }
        return $result;
    }
    private function queryMiddles($query) {
        $middles = self::queryArray ( $query );
        $result = array ();
        $names = array ();
        
        foreach ( $middles as $middle ) {
            $id = $middle ['base_sid'];
            $result [$id] ['name'] [] = $middle ['school_name'];
            $result [$id] ['info'] [] = array (
                    'pmschoolName1' => $middle ['school_name'],
                    'pmtele' => $middle ['phone'],
                    'pmaddress' => $middle ['address'],
                    'pmtype' => self::$TYPE [$middle ['school_grade']] 
            );
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
    private function queryCommunities($query) {
        $communities = self::queryArray ( $query );
        $result = array ();
        
        foreach ( $communities as $community ) {
            $id = $community ['id'];
            // $result[$id][]=array($community ['community_code']=>$community ['pic']);
            $result [$id] [] = $community ['community_code'];
        }
        return $result;
    }
    private function queryPictures($query) {
        $pictures = self::queryArray ( $query );
        $result = array ();
        
        foreach ( $pictures as $picture ) {
            $result [$picture ['id']] = $picture ['pic'];
        }
        return $result;
    }

    private function getCommPic($query) {
        $commPics = self::queryArray ( $query );
        $result = array ();
        foreach ( $commPics as $commPic ) {
            $result [$commPic ['community_code']] = $commPic ['pic'];
        }
        return $result;
    }
    private function assembleArray() {
        $schools = self::querySchools ( $this->query1 );
        $kinders = self::queryKinders ( $this->query2 );
        $middles = self::queryMiddles ( $this->query3 );
        $features = self::queryFeatures ( $this->query4 );
        $communities = self::queryCommunities ( $this->query5 );
        $pictures = self::queryPictures ( $this->query6 );
        $result = array ();
        foreach ( $schools as $id => $school ) {
            
            // 对应小区
            $community = array ();
            if (! empty ( $communities [$id] )) {
                $community = $communities [$id];
            }
            
            // 小学特色
            $feature = '';
            if ($school ['pfeature']) {
                $fea=array();
                foreach(explode ( ',', $school ['pfeature'] ) as $f){
                    $fea[]=$features[$f];
                }
                $feature = implode(';', $fea);
            }
            // 周边幼儿园
            $kindergarten = '';
            if (! empty ( $kinders [$id] )) {
                $kindergarten = implode ( ';', $kinders [$id] );
            }
            // 对口中学
            $pmschool = array (
                    array (
                            'pmschoolName1' => '',
                            'pmtele' => '',
                            'pmaddress' => '',
                            'pmtype' => '' 
                    ) 
            );
            $pmschoolNames = '';
            if (! empty ( $middles [$id] )) {
                $pmschool = $middles [$id] ['info'];
                $pmschoolNames = implode ( ';', $middles [$id] ['name'] );
            }
            
            // 图片
            $picture = '';
            if (! empty ( $pictures [$id] )) {
                $picture = $pictures [$id];
            }
            
            $result [] = array (
                    'city_id' => $school ['city_id'],
                    'school_id' => $id,
                    'school_related_resblock_id' => $community,
                    'school_info' => array (
                            'organization_names' => $school ['name'],
                            'education_code' => $school ['education_code'],
                            'settle_limit' => $school ['settleyear'],
                            'total_limit' => isset($school ['settlequota']) ? self::$QUOTA [$school ['settlequota']] : '',
                            'address' => $school ['paddress'],
                            'phone' => $school ['ptele'],
                            'pfeature' => $feature,
                            'pkindergarten' => $kindergarten,
                            'is_right_academy_school' => $pmschoolNames,
                            'position' => $school ['position'] 
                    ),
                    
                    'school_list_picture_url' => $picture,
                    'school_stat' => array (
                            'unit_price_max' => $school ['max_aver_price'],
                            'unit_price_min' => $school ['min_aver_price'] 
                    ),
                    'pschoolName' => $school ['name'],
                    'pschooltype' => self::$TYPE [$school ['pschooltypeid']],
                    'pmschool' => $pmschool,
                    'pschooltypeid' => self::$TYPEID [$school ['pschooltypeid']] 
            );
        }
        return $result;
    }
    public function writSchoolXml() {
        $data = self::assembleArray ();
        // $xml = self::arrToXml ( $data );
        // self::writeXmlToFile ( 'newxml.xml', $xml );
        return $data;
    }
    public function writeStringToFile($filename) {
        $data = self::assembleArray ();
        $str = json_encode ( $data );
        $fp = fopen ( $filename, 'w' ); // 打开要写入 XML数据的文件
        fwrite ( $fp, $str ); // 写入 XML数据
        fclose ( $fp ); // 关闭文件
    }
    public function writeCommPicToFile($filename) {
        $data = self::getCommPic ( $this->query7 );
        $str = json_encode ( $data );
        $fp = fopen ( $filename, 'w' ); // 打开要写入 XML数据的文件
        fwrite ( $fp, $str ); // 写入 XML数据
        fclose ( $fp ); // 关闭文件
    }
}
// header ( "Content-Type: text/html; charset=utf-8" );
header ( 'Content-type: application/json; charset=utf-8' );
$sougou = new Sogou ();
$arr = $sougou->writeStringToFile ( 'staticschool.json' );
$arr = $sougou->writeCommPicToFile ( 'commpic.json' );


