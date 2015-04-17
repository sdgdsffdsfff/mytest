<?php
header('Content-type: application/json; charset=utf-8');
$myFile=fopen('schools.txt','r');
$myLine='';
while(!feof($myFile))
{
    // Read each line and add to $myLine
    $myLine.=fgets($myFile,255);
}
fclose($myFile);
$a = json_decode($myLine,1);
echo $a['ff80808139244b34013928ead3d30062'];