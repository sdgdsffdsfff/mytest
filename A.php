<?php
trait Hello {
    public function sayHello() {
        echo 'Hello ';
    }
}

trait World {
    public function sayWorld() {
        echo 'World';
    }
}
trait HW{
    use Hello,World;
}
class MyHelloWorld {
    use HW;
    public function sayExclamationMark() {
        echo '!';
    }
}

$o = new MyHelloWorld();
$o->sayHello();
$o->sayWorld();
$o->sayExclamationMark();
$a= 2E+15 +123124;
$num = number_format($a,0,'','');//后面三个参数为空
echo $num; //输出“123132231234230000”

