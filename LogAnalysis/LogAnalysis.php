<?php
class LogAnalysis {
    const REQUEST_COUNT = 'request_count';
    const ERROR_COUNT = 'error_count';
    const AVG_TIME = 'avg_time';
    const TOTAL_TIME = 'total_time';
    const MAX_TIME = 'max_time';
    const DEVICE = 'device';
    const DEVICE_COUNT = 'device_count';
    const LOGON_USER = 'logon_user';
    const LOGON_USER_COUNT = 'logon_user_count';
    const ERROR_DETAIL = 'error_detail';
    const ERROR_INFO = 'error_info';
    const CACHE = 'cache';
    const CACHE_RATE = 'cache_rate';
    const API_NAME = 'API名称';
    const All_OS = 'all_OS';
    const API_TOTAL = ' 合计'; // 前面有空格，保证排在第一位
    const Android = 'Android';
    const IOS = 'iOS';
    const All_VERSION = 'all_version';
    private static $mobileOS = array (
            self::All_OS => '全部',
            self::Android => 'Android',
            self::IOS => 'iOS' 
    );
    private static $fields = array (
            self::REQUEST_COUNT => '请求次数',
            self::CACHE => '',
            self::CACHE_RATE => 'cache命中率',
            self::ERROR_COUNT => '',
            self::ERROR_INFO => '错误详情',
            self::TOTAL_TIME => '',
            self::AVG_TIME => '平均时间',
            self::MAX_TIME => '最大时间',
            self::DEVICE_COUNT => '设备数量',
            self::LOGON_USER_COUNT => '登录用户数' 
    );
    function multiexplode($delimiters, $string) {
        $ready = str_replace ( $delimiters, $delimiters [0], $string );
        $launch = explode ( $delimiters [0], $ready );
        return $launch;
    }
    function getVersion($ua) {
        $key = 'HomeLink ';
        if (strpos ( $ua, $key ) !== false) {
            
            // 日志中的格式：[HomeLink 6.0.1;Teclast P98 3Gk8(A3HY); Android 4.4.2]
            $s1 = strpos ( $ua, $key ) + strlen ( $key );
            if (strpos ( $ua, ';' ) !== false) {
                $e = strpos ( $ua, ';', $s1 );
            } else {
                $e = strlen ( $ua );
            }
            return substr ( $ua, $s1, $e - $s1 );
        }
    }
    function getRequestAPI($str, $key) {
        if (strpos ( $str, '[' . $key ) !== false) {
            
            // 日志中的格式：[key:value]
            $s1 = strpos ( $str, '[' . $key );
            $s = strpos ( $str, ':', $s1 ) + 1;
            $e = strpos ( $str, ']', $s );
            
            return substr ( $str, $s, $e - $s );
        }
        return null;
    }
    function getMobileOS($ua) {
        if (strpos ( $ua, 'iOS' ) !== false) {
            return self::IOS;
        } else if (strpos ( $ua, 'Android' ) !== false) {
            return self::Android;
        }
        return '';
    }
    function isIOS($ua) {
        if (strpos ( $ua, 'iOS' ) === false) {
            return false;
        }
        return true;
    }
    function isAndroid($ua) {
        if (strpos ( $ua, 'Android' ) === false) {
            return false;
        }
        return true;
    }
    function getErrorDetail($errDetail) {
        $strErrorDetail = '';
        if (! empty ( $errDetail )) {
            ksort ( $errDetail );
            foreach ( $errDetail as $k1 => $v1 ) {
                $strErrorDetail .= '<li>' . $k1 . ':' . $v1 . "</li>";
            }
        }
        return $strErrorDetail;
    }
    function getLogDate($file) {
        
        // 日志的开头可能为INFO: 2015-03-13 00:04:03
        // 或ERROR: 2015-03-13 00:04:03
        // 或WARNING:2015-03-13 00:04:03
        // 取第一冒号后面11位
        $handle = fopen ( $file, "r" ) or die ( "can\'t open file {$file}" );
        $logdate = '';
        if (($line = fgets ( $handle, 8192 )) !== false) {
            $pos = strpos ( $line, ': ' ) + 2;
            $logdate = substr ( $line, $pos, 10 );
        }
        fclose ( $handle );
        return $logdate;
    }
    function arrayToTable($arr) {
        $html = '<table id="dd" >';
        
        // 表行标题
        $html .= '<thead><tr>';
        $html .= '<th rowspan="2">' . self::API_NAME . '</th>';
        $colspan = count ( self::$mobileOS );
        $fieldCount = 0;
        foreach ( self::$fields as $field ) {
            if (empty ( $field )) {
                continue;
            }
            $html .= '<th colspan="' . $colspan . '">' . $field . '</th>';
            $fieldCount ++;
        }
        $html .= '</tr>';
        
        // 手机系统分类
        $html .= '<tr>';
        for($i = 0; $i < $fieldCount; $i ++) {
            foreach ( self::$mobileOS as $os ) {
                $html .= '<th>' . $os . '</th>';
            }
        }
        $html .= '</tr></thead>';
        
        $html .= '<tbody class="tb1">';
        foreach ( $arr as $apiName => $col ) {
            $html .= '<tr>';
            $html .= '<td>' . $apiName . '</td>';
            
            foreach ( self::$fields as $apiKey => $apiValue ) {
                if (empty ( $apiValue )) {
                    continue;
                }
                if (isset ( $col [$apiKey] )) {
                    foreach ( self::$mobileOS as $osKey => $osValue )
                        $html .= '<td>' . $col [$apiKey] [$osKey] . '</td>';
                } else {
                    $html .= '<td>0</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }
    public function getContentFromFile($files) {
        $i = 0;
        $arrAPI = array ();
        
        foreach ( $files as $file ) {
            $handle = fopen ( $file, "r" ) or die ( "can\'t open file {$file}" );
            // 循环读取每一行日志
            while ( ($line = fgets ( $handle, 8192 )) !== false ) {
                if (strpos ( $line, 'MAPI_REQUEST' ) === false) {
                    continue;
                }
                
                // 获取API名称
                $apiName = $this->getRequestAPI ( $line, 'url' );
                // 去掉请求的参数
                if (($e = strpos ( $apiName, '?' )) !== false) {
                    $apiName = substr ( $apiName, 0, $e );
                }
                $apiName = str_replace ( '//', '/', $apiName );
                
                // 响应时间
                $apiTime = floatval ( $this->getRequestAPI ( $line, 'time' ) );
                
                // 错误号
                $apiErrorno = intval ( $this->getRequestAPI ( $line, 'errno' ) );
                
                // UA
                $apiUA = $this->getRequestAPI ( $line, 'ua' );
                $curOS = $this->getMobileOS ( $apiUA );
                $curVer = $this->getVersion ( $apiUA );
                // 设备号
                $device = $this->getRequestAPI ( $line, 'device' );
                
                // 用户id
                $userid = $this->getRequestAPI ( $line, 'user' );
                
                // cache
                $cache = $this->getRequestAPI ( $line, 'cache' );
                
                if (empty ( $apiName )) {
                    continue;
                }
                
                for($j = 0; $j < 1 << 3; $j ++) {
                    if (empty ( $arrAPI [self::REQUEST_COUNT] [($j >> 2) % 2 ? self::API_TOTAL : $apiName] [($j >> 1) % 2 ? self::All_OS : $curOS] [$j % 2 ? self::All_VERSION : $curVer] )) {
                        $arrAPI [self::REQUEST_COUNT] [($j >> 2) % 2 ? self::API_TOTAL : $apiName] [($j >> 1) % 2 ? self::All_OS : $curOS] [$j % 2 ? self::All_VERSION : $curVer] = 0;
                    }
                    // $arrAPI [self::REQUEST_COUNT] [($j >> 2) % 2 ? self::API_TOTAL : $apiName] [($j >> 1) % 2 ? self::All_OS : $curOS] [$j % 2 ? self::All_VERSION : $curVer] += 1;
                    // $arrAPI [self::CACHE] [($j >> 2) % 2 ? self::API_TOTAL : $apiName] [($j >> 1) % 2 ? self::All_OS : $curOS] [$j % 2 ? self::All_VERSION : $curVer] += $cache;
                    // $arrAPI [self::ERROR_COUNT] [($j >> 2) % 2 ? self::API_TOTAL : $apiName] [($j >> 1) % 2 ? self::All_OS : $curOS] [$j % 2 ? self::All_VERSION : $curVer] += 1;
                    // $arrAPI [self::ERROR_DETAIL] [$apiErrorno] [($j >> 2) % 2 ? self::API_TOTAL : $apiName] [($j >> 1) % 2 ? self::All_OS : $curOS] [$j % 2 ? self::All_VERSION : $curVer] += 1;
                    // $arrAPI [self::DEVICE] [$device] [($j >> 2) % 2 ? self::API_TOTAL : $apiName] [($j >> 1) % 2 ? self::All_OS : $curOS] [$j % 2 ? self::All_VERSION : $curVer] += 1;
                    // $arrAPI [self::LOGON_USER] [$userid] [($j >> 2) % 2 ? self::API_TOTAL : $apiName] [($j >> 1) % 2 ? self::All_OS : $curOS] [$j % 2 ? self::All_VERSION : $curVer] += 1;
                }
                
                if ($i > 10) {
                    
                    break;
                }
                $i ++;
            }
            fclose ( $handle );
        }
        return $arrAPI;
        foreach ( $arrAPI as $k => $v ) {
            
            foreach ( self::$mobileOS as $k1 => $v1 ) {
                if (! empty ( $v [self::REQUEST_COUNT] [$k1] )) {
                    $arrAPI [$k] [self::AVG_TIME] [$k1] = sprintf ( '%.3f', $v [self::TOTAL_TIME] [$k1] / $v [self::REQUEST_COUNT] [$k1] );
                }
                $arrAPI [$k] [self::DEVICE_COUNT] [$k1] = empty ( $v [self::DEVICE] [$k1] ) ? 0 : count ( $v [self::DEVICE] [$k1] );
                $arrAPI [$k] [self::LOGON_USER_COUNT] [$k1] = empty ( $v [self::LOGON_USER] [$k1] ) ? 0 : count ( $v [self::LOGON_USER] [$k1] );
                
                if (! empty ( $v [self::CACHE] [$k1] )) {
                    $arrAPI [$k] [self::CACHE_RATE] [$k1] = sprintf ( '%.2f%%', $v [self::CACHE] [$k1] / $v [self::REQUEST_COUNT] [$k1] * 100 );
                }
                
                // 格式化
                $arrAPI [$k] [self::MAX_TIME] [$k1] = sprintf ( '%.2f', $v [self::MAX_TIME] [$k1] );
                
                if (! empty ( $arrAPI [$k] [self::ERROR_DETAIL] [$k1] )) {
                    $totalerror = '<ul><li>错误总数:' . $v [self::ERROR_COUNT] [$k1] . '</li>';
                    $errorRate = sprintf ( '<li>错误率: %.4f%%</li>', $v [self::ERROR_COUNT] [$k1] / $v [self::REQUEST_COUNT] [$k1] * 100 );
                    $arrAPI [$k] [self::ERROR_INFO] [$k1] = $totalerror . $errorRate . $this->getErrorDetail ( $v [self::ERROR_DETAIL] [$k1] );
                }
            }
        }
        return $arrAPI;
    }
}
