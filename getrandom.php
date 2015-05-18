<?php
$r=mt_rand();
$u = uniqid($r, true);
var_dump($r);
var_dump($u);
echo md5($u);