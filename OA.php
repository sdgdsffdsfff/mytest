<?php
    class A{
        public $a; 
        public $b; 
    }   
$o = new A;
$o->a = '123';
$o->b = '123';

var_dump(empty($o));