<?php
class A {
    function test() {
        $fruits = array (
                "d" => "lemon",
                "a" => "orange",
                "b" => "banana",
                "c" => "apple" 
        );
        
        echo "Before ...:\n";
        array_walk ( $fruits, array($this,'test_print' ));
        
        array_walk ( $fruits, array($this, 'test_alter'), 'fruit' );
        echo "... and after:\n";
        
        array_walk ( $fruits, array($this,'test_print' ));
    }
    function test_alter(&$item1, $key, $prefix) {
        $item1 = "$prefix: $item1";
    }
    function test_print($item2, $key) {
        echo "$key. $item2<br />\n";
    }
}
class testArray implements Iterator
{
    protected $prop = array(
        1 => 'one',
        2 => 'two',
        3 => 'three',
    );

    public function rewind()
    {
        return reset( $this->prop );
    }

    public function key()
    {
        return key( $this->prop );
    }

    public function current()
    {
        return current( $this->prop );
    }

    public function next()
    {
        return next( $this->prop );
    }

    public function valid()
    {
        return ( current( $this->prop ) !== false );
    }
}

$array = new testArray();

// Expected: string(3) "one"
var_dump( $array->rewind() );

// Expected: string(3) "one"
var_dump( reset( $array ) );

// As expected: string(3) "one"
var_dump( $array->rewind() );

// Expected: string(3) "one"
var_dump( reset( $array ) );
/* Got:
    array(3) {
      [1]=>
      string(3) "one"
      [2]=>
      string(3) "two"
      [3]=>
      string(5) "three"
    }
*/
echo date('YmdHis');
var_dump( '6.1.1'>'6.1.1');