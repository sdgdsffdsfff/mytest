<?php
class CartLine {
    public $price = 0;
    public $qty = 0;
    public function total() {
        return $this->price * $this->qty;
    }
}
class Cart {
    protected $lines = array ();
    public function addLine($line) {
        $this->lines [] = $line;
    }
    public function calcTotal() {
        $total = 0;
        foreach ( $this->lines as $line ) {
            $total += $line->total ();
        }
        $total += $this->calcSalesTax ( $total );
        return $total;
    }
    protected function calcSalesTax($amount) {
        return $amount * 0.07;
    }
}
$a=new CartLine();
$a->price=12;
$a->qty=12.1;
echo $a->total();
$b=new Cart();
$b->addLine($a);
echo $b->calcTotal();
echo date('Y-m-d H:i:s');

var_dump('  123fg456'==123);
var_dump('some string' == 0);
var_dump(123.0 == '123d456');
var_dump(0 == "a");
var_dump("1" == "01");
var_dump("1" == "1e0");