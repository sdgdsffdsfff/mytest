<?php
$a = array (
        '123' => array (
                'name' => 'da',
                'age' => '12'
        ),
        '452' => array (
                'name' => 'daf',
                'age' => '22'
        )
);
$c = array (
        '123' => array (
                'name' => 'da',
                'age' => '12'
        )
);
$b = array_values ( $c );
header ( 'Content-type: application/json' );
echo json_encode ( $b );
echo hash_hmac('rudws29dmpajo08jdrxhuqh8x9yokpilwotozjenboax7hsb:2000000000786440::1431675063:8h6ds', 'secret');