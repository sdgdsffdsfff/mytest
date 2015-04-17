<?php
header ( "Content-Type: text/html; charset=utf-8" );
$filename1 = 'C:\Users\N010D90001\Downloads\staticschool20150319141800.xml';
$filename2 = 'C:\Users\N010D90001\Downloads\staticschool20150319153056.xml';
$xml1 = simplexml_load_file ( $filename1 );
$xml2 = simplexml_load_file ( $filename2 );

$i = 0;
$diff = 0;
$str = '';
$t = count($xml1->item);
while ( $i < $t ) {
    
    $a = ( string ) ($xml1->item [$i]->gps->latlng [0]);
    $b = ( string ) ($xml2->item [$i]->gps->latlng [0]);
    
    if ($a != $b) {
        $key = ( string ) ($xml1->item [$i]->key [0]);
        $diff ++;
        $str .= '小学key: ' . $key . "\t" . '  old: ' . $a . "\t" . ' new: ' . $b . "\n";
    }
    $i ++;
}

$fp = fopen ( 'diffschool.txt', 'w' ); // 打开要写入的文件
fwrite ( $fp, $str ); // 写入
fclose ( $fp ); // 关闭文件
