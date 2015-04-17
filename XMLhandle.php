<?php
function arrtoxml($arr, $dom = 0, $item = 0) {
    if (! $dom) {
        $dom = new DOMDocument ( "1.0",'gbk' );
    }
    if (! $item) {
        $item = $dom->createElement ( "DOCUMENT" );
        $dom->appendChild ( $item );
    }
    foreach ( $arr as $key => $val ) {
        $itemx = $dom->createElement ( is_string ( $key ) ? $key : "item" );
        $item->appendChild ( $itemx );
        if (! is_array ( $val )) {
            $text = $dom->createCDATASection( $val );
            $itemx->appendChild ( $text );
        } else {
            arrtoxml ( $val, $dom, $itemx );
        }
    }
    return $dom->saveXML ();
}
$a = array (
        array (
                'key' => '小学名称+ID',
                'display' => array (
                        'districtID' => '110210;110211',
                        'bizcircleID' => '110256;110258',
                        'cityID' => '110000',
                        'building' => array (
                                'synonym' => '小区名同义词',
                                'buildingID' => '1111027375225',
                                'buildingURL' => 'http://m.lianjia.com/wap/guide/communitydetail?cityId=110000&commCode=1111027376239',
                                'bphoto' => 'http://image.homelink.com.cn/era/newCommunity/2014/5/23/1111027376239/360575_big.jpg',
                                'price' => '64166.08',
                                'pricetrend' => '-13.4',
                                'year' => '2001',
                                'housetype' => '普通住宅',
                                'buildingtype' => '塔楼',
                                'shousenumber' => '26',
                                'rhousenumber' => '21',
                                'linkurl' => 'http://liajia.com/xiaoqu.d?m=map&amp;city=kunming&amp;id=3411121152&amp;s=sougouwise_esf_map',
                                'buildingName1' => '华清嘉园',
                                'houseurl' => 'http://m.lianjia.com/wap/guide/housebycomm?cityId=110000&commCode=1111046327215' 
                        ),
                        'pschool' => array (
                                'pschoolId' => '2225874;2226856',
                                'settleyear' => '3',
                                'settlequota' => '一房一名额',
                                'padress' => '北京市海淀区蓝靛厂路18号',
                                'ptele' => '010-88863978',
                                'pfeature' => '奥数',
                                'pkindergarten' => '红黄蓝幼儿园;佳佳幼儿园',
                                'pbuildingnumber' => '17',
                                'ptopprice' => '6.5',
                                'pphoto' => 'http://image.lianjia.com/picdata/data/dl/1/43381824/60253720/60254402/705778.jpg.280x210.jpg',
                                'pbottomprice' => '3.5' 
                        ),
                        'title' => '华清嘉园',
                        'url' => 'http://open.sogou-inc.com/admin.php/openXmlFormatOffline/create/vrId/70016400',
                        'pmschool' => array (
                                'pmschoolName1' => '人民大学附属中学',
                                'pmtele' => '010-88863978',
                                'pmadress' => '北京市海淀区蓝靛厂路18号',
                                'pmtype' => '市重点' 
                        ) 
                ),
                'buildingName' => '华清嘉园',
                'bizcircle' => '中关村;五道口',
                'district' => '海淀区;西城区',
                'pschoolName' => '中关村第二小学',
                'pschooltype' => '市重点',
                'location' => '湖南   长沙',
                'pmschoolName' => '人民大学附属中学;清华大学附属中学',
                'city' => '北京',
                'gps' => array (
                        'type' => '百度',
                        'latlng' => '39.9932345112,116.3325614' 
                ),
                'alltype' => '市重点;区重点;普通',
                'pschooltypeid' => '1' 
        )
         
);
$abc = arrtoxml ( $a );

// $xml = simplexml_load_file('example.xml'); //读取 XML数据
// $newxml = $xml->asXML(); //标准化 XML数据
$fp = fopen ( 'newxml.xml', 'w'); // 打开要写入 XML数据的文件

fwrite ( $fp, $abc ); // 写入 XML数据
fclose ( $fp ); //关闭文件