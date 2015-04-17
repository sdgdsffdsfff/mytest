<?php
$a = array (
        '20140501' => array (
                'avg' => '123' 
        ) ,
        '20140502' => array (
                'avg' => '124'
        )
);
var_dump($a);
echo reset($a)['avg'];
echo next($a)['avg'];
echo current($a)['avg'];

const BUILDING_URL = 'http://m.lianjia.com/wap/guide/communitydetail?cityId=%s&commCode=%s';
const URL_TRACE = 'utm_source=sogou&utm_medium=alading';
$row=array();
$field=array();
$i=0;
echo sprintf(BUILDING_URL,'110000','12321302121');
echo substr('2015-03-21abc',0,11);


if(isset($field[$i])){
    echo 'asdf';
}
echo '<br />';
echo md5('sd68f74915ads98fw9q4e');