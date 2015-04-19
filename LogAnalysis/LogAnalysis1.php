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
    private static $fields = array (
            self::REQUEST_COUNT => '',
            self::ERROR_COUNT => '',
            self::ERROR_INFO => '错误详情' 
    );
    public $allAPI;
    public $allOS;
    public $allVer;
    function multiexplode($delimiters, $string) {
        $ready = str_replace ( $delimiters, $delimiters [0], $string );
        $launch = explode ( $delimiters [0], $ready );
        return $launch;
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
    function getTabelHead() {
        $filedHead = '';
        $osHead = '<tr>';
        $verHead = '<tr>';
        $osHeadspan = count ( $this->allVer );
        $filedcolspan = count ( $this->allOS ) * $osHeadspan;
        foreach ( self::$fields as $field => $fieldName ) {
            if (empty ( $fieldName )) {
                continue;
            }
            $filedHead .= '<th colspan="' . $filedcolspan . '">' . $fieldName . '</th>';
            foreach ( $this->allOS as $osKey => $osValue ) {
                $osHead .= '<th colspan="' . $osHeadspan . '">' . $osKey . '</th>';
                foreach ( $this->allVer as $verKey => $verValue ) {
                    $verHead .= '<th>' . $verKey . '</th>';
                }
            }
        }
        $verHead .= '</tr>';
        $osHead .= '</tr>';
        $filedHead .= '</tr>';
        
        return '<thead><tr><th rowspan="3">API名称</th>' . $filedHead . $osHead . $verHead . '</thead>';
    }
    function arrayToTable($arr) {
        $html = '<table id="dd" >';
        $html .= $this->getTabelHead();
        $html .= '<tbody class="tb1">';
        foreach ( $this->allAPI as $apiKey => $apiValue ) {
            $html .= '<tr>';
            $html .= '<td>' . $apiKey . '</td>';
            foreach ( self::$fields as $field=>$fieldName ) {
                if(empty($fieldName)){
                    continue;
                }
            foreach ( $this->allOS as $osKey => $osValue ) {
                foreach ( $this->allVer as $verKey => $verValue ) {
                        $html .= '<td>' . $arr [$apiKey] [$osKey] [$verKey] [$field] . '</td>';
                    }
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
        $this->allOS [self::All_OS] = 1;
        $this->allVer [self::All_VERSION] = 1;
        foreach ( $files as $file ) {
            $handle = fopen ( $file, "r" ) or die ( "can\'t open file {$file}" );
            // 循环读取每一行日志
            while ( ($line = fgets ( $handle, 8192 )) !== false ) {
                if (strpos ( $line, 'MAPI_REQUEST' ) === false) {
                    continue;
                }
                
                $apiName = $this->getRequestAPI ( $line, 'url' ); // 获取API名称
                
                if (($e = strpos ( $apiName, '?' )) !== false) { // 去掉请求的参数
                    $apiName = substr ( $apiName, 0, $e );
                }
                $apiName = str_replace ( '//', '/', $apiName ); // 去掉url中多余的/
                $apiTime = floatval ( $this->getRequestAPI ( $line, 'time' ) ); // 响应时间
                $apiErrorno = intval ( $this->getRequestAPI ( $line, 'errno' ) ); // 错误号
                $apiUA = $this->getRequestAPI ( $line, 'ua' ); // UA
                $curOS = $this->getMobileOS ( $apiUA ); // 当前系统
                $curVer = $this->getVersion ( $apiUA ); // 当前版本
                $device = $this->getRequestAPI ( $line, 'device' ); // 设备号
                $userid = $this->getRequestAPI ( $line, 'user' ); // 用户id
                $cache = $this->getRequestAPI ( $line, 'cache' ); // cache
                
                if ($apiName) {
                    $this->allAPI [$apiName] = 1;
                    if (empty ( $arrAPI [$apiName] [self::All_OS] [self::All_VERSION] [self::REQUEST_COUNT] )) {
                        $arrAPI [$apiName] [self::All_OS] [self::All_VERSION] [self::REQUEST_COUNT] = 1;
                    } else {
                        $arrAPI [$apiName] [self::All_OS] [self::All_VERSION] [self::REQUEST_COUNT] += 1;
                    }
                    if (empty ( $arrAPI [self::API_TOTAL] [self::All_OS] [self::All_VERSION] [self::REQUEST_COUNT] )) {
                        $arrAPI [self::API_TOTAL] [self::All_OS] [self::All_VERSION] [self::REQUEST_COUNT] = 1;
                    } else {
                        $arrAPI [self::API_TOTAL] [self::All_OS] [self::All_VERSION] [self::REQUEST_COUNT] += 1;
                    }
                    
                    // 计算API错误次数
                    if ($apiErrorno != 0) {
                        if (empty ( $arrAPI [$apiName] [self::All_OS] [self::All_VERSION] [self::ERROR_COUNT] )) {
                            $arrAPI [$apiName] [self::All_OS] [self::All_VERSION] [self::ERROR_COUNT] = 1;
                        } else {
                            $arrAPI [$apiName] [self::All_OS] [self::All_VERSION] [self::ERROR_COUNT] += 1;
                        }
                        if (empty ( $arrAPI [self::API_TOTAL] [self::All_OS] [self::All_VERSION] [self::ERROR_COUNT] )) {
                            $arrAPI [self::API_TOTAL] [self::All_OS] [self::All_VERSION] [self::ERROR_COUNT] = 1;
                        } else {
                            $arrAPI [self::API_TOTAL] [self::All_OS] [self::All_VERSION] [self::ERROR_COUNT] += 1;
                        }
                        
                        if (empty ( $arrAPI [$apiName] [self::All_OS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] )) {
                            $arrAPI [$apiName] [self::All_OS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] = 1;
                        } else {
                            $arrAPI [$apiName] [self::All_OS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] += 1;
                        }
                        if (empty ( $arrAPI [self::API_TOTAL] [self::All_OS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] )) {
                            $arrAPI [self::API_TOTAL] [self::All_OS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] = 1;
                        } else {
                            $arrAPI [self::API_TOTAL] [self::All_OS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] += 1;
                        }
                    }
                    
                    if (! empty ( $curOS )) {
                        $this->allOS [$curOS] = 1;
                        
                        // 计算API调用次数
                        if (empty ( $arrAPI [$apiName] [$curOS] [self::All_VERSION] [self::REQUEST_COUNT] )) {
                            $arrAPI [$apiName] [$curOS] [self::All_VERSION] [self::REQUEST_COUNT] = 1;
                        } else {
                            $arrAPI [$apiName] [$curOS] [self::All_VERSION] [self::REQUEST_COUNT] += 1;
                        }
                        if (empty ( $arrAPI [self::API_TOTAL] [$curOS] [self::All_VERSION] [self::REQUEST_COUNT] )) {
                            $arrAPI [self::API_TOTAL] [$curOS] [self::All_VERSION] [self::REQUEST_COUNT] = 1;
                        } else {
                            $arrAPI [self::API_TOTAL] [$curOS] [self::All_VERSION] [self::REQUEST_COUNT] += 1;
                        }
                        
                        // 计算API错误次数
                        if ($apiErrorno != 0) {
                            if (empty ( $arrAPI [$apiName] [$curOS] [self::All_VERSION] [self::ERROR_COUNT] )) {
                                $arrAPI [$apiName] [$curOS] [self::All_VERSION] [self::ERROR_COUNT] = 1;
                            } else {
                                $arrAPI [$apiName] [$curOS] [self::All_VERSION] [self::ERROR_COUNT] += 1;
                            }
                            if (empty ( $arrAPI [self::API_TOTAL] [$curOS] [self::All_VERSION] [self::ERROR_COUNT] )) {
                                $arrAPI [self::API_TOTAL] [$curOS] [self::All_VERSION] [self::ERROR_COUNT] = 1;
                            } else {
                                $arrAPI [self::API_TOTAL] [$curOS] [self::All_VERSION] [self::ERROR_COUNT] += 1;
                            }
                            
                            if (empty ( $arrAPI [$apiName] [$curOS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] )) {
                                $arrAPI [$apiName] [$curOS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] = 1;
                            } else {
                                $arrAPI [$apiName] [$curOS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] += 1;
                            }
                            if (empty ( $arrAPI [self::API_TOTAL] [$curOS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] )) {
                                $arrAPI [self::API_TOTAL] [$curOS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] = 1;
                            } else {
                                $arrAPI [self::API_TOTAL] [$curOS] [self::All_VERSION] [self::ERROR_DETAIL] [$apiErrorno] += 1;
                            }
                        }
                        
                        if (! empty ( $curVer )) {
                            
                            // 计算API调用次数
                            if (empty ( $arrAPI [$apiName] [$curOS] [$curVer] [self::REQUEST_COUNT] )) {
                                $arrAPI [$apiName] [$curOS] [$curVer] [self::REQUEST_COUNT] = 1;
                            } else {
                                $arrAPI [$apiName] [$curOS] [$curVer] [self::REQUEST_COUNT] += 1;
                            }
                            if (empty ( $arrAPI [self::API_TOTAL] [$curOS] [$curVer] [self::REQUEST_COUNT] )) {
                                $arrAPI [self::API_TOTAL] [$curOS] [$curVer] [self::REQUEST_COUNT] = 1;
                            } else {
                                $arrAPI [self::API_TOTAL] [$curOS] [$curVer] [self::REQUEST_COUNT] += 1;
                            }
                            
                            // 计算API错误次数
                            if ($apiErrorno != 0) {
                                if (empty ( $arrAPI [$apiName] [$curOS] [$curVer] [self::ERROR_COUNT] )) {
                                    $arrAPI [$apiName] [$curOS] [$curVer] [self::ERROR_COUNT] = 1;
                                } else {
                                    $arrAPI [$apiName] [$curOS] [$curVer] [self::ERROR_COUNT] += 1;
                                }
                                if (empty ( $arrAPI [self::API_TOTAL] [$curOS] [$curVer] [self::ERROR_COUNT] )) {
                                    $arrAPI [self::API_TOTAL] [$curOS] [$curVer] [self::ERROR_COUNT] = 1;
                                } else {
                                    $arrAPI [self::API_TOTAL] [$curOS] [$curVer] [self::ERROR_COUNT] += 1;
                                }
                                
                                if (empty ( $arrAPI [$apiName] [$curOS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] )) {
                                    $arrAPI [$apiName] [$curOS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] = 1;
                                } else {
                                    $arrAPI [$apiName] [$curOS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] += 1;
                                }
                                if (empty ( $arrAPI [self::API_TOTAL] [$curOS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] )) {
                                    $arrAPI [self::API_TOTAL] [$curOS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] = 1;
                                } else {
                                    $arrAPI [self::API_TOTAL] [$curOS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] += 1;
                                }
                            }
                        }
                    }
                    if (! empty ( $curVer )) {
                        $this->allVer [$curVer] = 1;

                        // 计算API调用次数
                        if (empty ( $arrAPI [$apiName] [self::All_OS] [$curVer] [self::REQUEST_COUNT] )) {
                            $arrAPI [$apiName] [self::All_OS] [$curVer] [self::REQUEST_COUNT] = 1;
                        } else {
                            $arrAPI [$apiName] [self::All_OS] [$curVer] [self::REQUEST_COUNT] += 1;
                        }
                        if (empty ( $arrAPI [self::API_TOTAL] [self::All_OS] [$curVer] [self::REQUEST_COUNT] )) {
                            $arrAPI [self::API_TOTAL] [self::All_OS] [$curVer] [self::REQUEST_COUNT] = 1;
                        } else {
                            $arrAPI [self::API_TOTAL] [self::All_OS] [$curVer] [self::REQUEST_COUNT] += 1;
                        }
                        
                        // 计算API错误次数
                        if ($apiErrorno != 0) {
                            if (empty ( $arrAPI [$apiName] [self::All_OS] [$curVer] [self::ERROR_COUNT] )) {
                                $arrAPI [$apiName] [self::All_OS] [$curVer] [self::ERROR_COUNT] = 1;
                            } else {
                                $arrAPI [$apiName] [self::All_OS] [$curVer] [self::ERROR_COUNT] += 1;
                            }
                            if (empty ( $arrAPI [self::API_TOTAL] [self::All_OS] [$curVer] [self::ERROR_COUNT] )) {
                                $arrAPI [self::API_TOTAL] [self::All_OS] [$curVer] [self::ERROR_COUNT] = 1;
                            } else {
                                $arrAPI [self::API_TOTAL] [self::All_OS] [$curVer] [self::ERROR_COUNT] += 1;
                            }
                            
                            if (empty ( $arrAPI [$apiName] [self::All_OS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] )) {
                                $arrAPI [$apiName] [self::All_OS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] = 1;
                            } else {
                                $arrAPI [$apiName] [self::All_OS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] += 1;
                            }
                            if (empty ( $arrAPI [self::API_TOTAL] [self::All_OS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] )) {
                                $arrAPI [self::API_TOTAL] [self::All_OS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] = 1;
                            } else {
                                $arrAPI [self::API_TOTAL] [self::All_OS] [$curVer] [self::ERROR_DETAIL] [$apiErrorno] += 1;
                            }
                        }
                    }
                }
                
                if ($i > 10000) {
                    
                    break;
                }
                $i ++;
            }
            fclose ( $handle );
        }
        
        foreach ( $arrAPI as $apiKey => $apiValue ) {
            foreach ( $this->allOS as $osKey => $osValue ) {
                foreach ( $this->allVer as $verKey => $verValue ) {
                    if (! empty ( $arrAPI [$apiKey] [$osKey] [$verKey] [self::ERROR_DETAIL] )) {
                        $totalerror = '<ul><li>错误总数:' . $arrAPI [$apiKey] [$osKey] [$verKey] [self::ERROR_COUNT] . '</li>';
                        $errorRate = sprintf ( '<li>错误率: %.4f%%</li>', $arrAPI [$apiKey] [$osKey] [$verKey] [self::ERROR_COUNT] / $arrAPI [$apiKey] [$osKey] [$verKey] [self::REQUEST_COUNT] * 100 );
                        $arrAPI [$apiKey] [$osKey] [$verKey] [self::ERROR_INFO] = $totalerror . $errorRate . $this->getErrorDetail ( $arrAPI [$apiKey] [$osKey] [$verKey] [self::ERROR_DETAIL] );
                        unset ( $arrAPI [$apiKey] [$osKey] [$verKey] [self::ERROR_DETAIL] );
                    }
                    unset ( $arrAPI [$apiKey] [$osKey] [$verKey] [self::ERROR_COUNT] );
                    unset ( $arrAPI [$apiKey] [$osKey] [$verKey] [self::REQUEST_COUNT] );
                    foreach ( self::$fields as $field => $fileldValue ) {
                        if (empty ( $arrAPI [$apiKey] [$osKey] [$verKey] [$field] ) && ! empty ( $fileldValue )) {
                            $arrAPI [$apiKey] [$osKey] [$verKey] [$field] = 0;
                        }
                    }
                }
            }
        }
        
        return $arrAPI;
    }
}
