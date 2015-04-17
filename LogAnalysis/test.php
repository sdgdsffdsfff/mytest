<?php
$arr= array (
        ' 合计' => array (
                '请求次数' => array (
                        'all' => 101002,
                        'Android' => 2225,
                        'iOS' => 33
                ),
                array (
                        'cache命中率' => array (
                                'all' => 10002,
                                'Android' => 2225,
                                'iOS' => 33
                        )
                )
        ) ,
        ' /config/get/version' => array (
                '请求次数' => array (
                        'all' => 10002,
                        'Android' => 2225,
                        'iOS' => 33
                ),
                array (
                        'cache命中率' => array (
                                'all' => 10002,
                                'Android' => 2225,
                                'iOS' => 33
                        )
                )
        )
);
//echo $arr[' 合计']['请求次数'][ 'all' ];

$weidu = 4;
for($i=0;$i<1<<$weidu;$i++){
    echo ($i>>3)%2;
    echo ($i>>2)%2;
    echo ($i>>1)%2;
    echo $i%2;
    echo '<br/>';
}
echo json_encode($arr);
echo strlen(json_encode($arr));
