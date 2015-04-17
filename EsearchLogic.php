<?php
class ESearchContainAll
{
    public $_arr;

    public function __construct(array $arr)
    {
        $this->_arr = $arr;
    }

    public function getArray()
    {
        return  $this->_arr;
    }
}

$abc = array('asdf'=>'112','abc'=>'sdf');

$b= new ESearchContainAll($abc);
var_dump($b->_arr);
var_dump($abc);