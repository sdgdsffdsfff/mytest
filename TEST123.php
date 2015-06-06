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
echo '{"errno":0,"error":"","request_id":"20150522223501992739","data":{"setting":{"receive_notification":{"price_changed":1,"deal":1,"community_new_house_source":1,"sound":1,"vibrate":1,"total":1}},"first_login_time":"2015-04-23 20:16:31","bind_agent":"12345678"}}';
//echo json_encode ( $b );
//echo hash_hmac('rudws29dmpajo08jdrxhuqh8x9yokpilwotozjenboax7hsb:2000000000786440::1431675063:8h6ds', 'secret');
// try {
//     $entry = array(
//             0 => 'foo',
//             1 => false,
//             2 => -1,
//             3 => null,
//             4 => ''
//     );
    
//     print_r(array_filter($entry));
//     $arr = array("0"=>null,"1"=>'123');
//     print_r(array_filter($arr));
//     $arr =array_filter($arr);
//     array_flip($arr);
// }catch (Exception $e){
//    print $e->getMessage();   
// exit();   
// }