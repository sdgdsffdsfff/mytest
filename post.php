<?php
class ESearch {
    // 排序方式
    const ASC = 'asc';
    const DESC = 'desc';
    
    // 最小值和最大值均用*表示
    const MIN = '*';
    const MAX = '*';
    
    /**
     * ************************* channel start ********************************
     */
    // 二手房
    const CHANNEL_ERSHOUFANG = 'ershoufang';
    
    // 二手房下的地铁房频道
    const CHANNEL_ERSHOUFANG_DITIE = 'ershoufang_ditie';
    
    // 学区房
    const CHANNEL_SCHOOL = 'school';
    
    // 学区房（找学校）
    const CHANNEL_SCHOOL_LIST = 'school_list';
    
    // 新房
    const CHANNEL_XINFANG = 'xinfang';
    
    // 租房
    const CHANNEL_ZUFANG = 'zufang';
    
    // 租房（地铁找房）
    const CHANNEL_ZUFANG_DITIE = 'zufang_ditie';
    
    // 商铺出租
    const CHANNEL_SHANGPUCHUZU = 'shangpuchuzu';
    
    // 商铺出售
    const CHANNEL_SHANGPUCHUSHOU = 'shangpuchushou';
    
    // 小区
    const CHANNEL_XIAOQU = 'xiaoqu';
    /**
     * ************************* channel end ********************************
     */
    public $serviceId;
    public $serviceVersion;
    public $timeout = 4;
    public $connectTimeout = 1;
    protected $_urls;
    protected $_ch;
    protected $_currentUrlId;

    // 搜索GROUP
    public function queryGroup($cityId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount,$group=FALSE)
    {
        $this->serviceId = 101; // 参考 bs search 的接口文档，每个搜索接口的serviceId不同
        $channelId = self::CHANNEL_ERSHOUFANG;
        return $this->_query($cityId, $channelId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount,$group);
    }
    
    // 搜索在售二手房
    public function queryHouseSell($cityId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount,$group=FALSE)
    {
        $this->serviceId = 2; // 参考 bs search 的接口文档，每个搜索接口的serviceId不同
        $channelId = self::CHANNEL_ERSHOUFANG;
        return $this->_query($cityId, $channelId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount,$group);
    }
    
    /*
     * 搜索二手房成交数据
     */
    public function queryHouseSold($cityId, $channelId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount)
    {
        $this->serviceId = 4; // 参考 bs search 的接口文档，每个搜索接口的serviceId不同
        return $this->_query($cityId, $channelId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount);
    }
    
    /*
     * 搜索出租房
     */
    public function queryHouseRent($cityId, $channelId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount)
    {
        $this->serviceId = 3; // 参考 bs search 的接口文档，每个搜索接口的serviceId不同
        return $this->_query($cityId, $channelId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount);
    }
    
    /*
     * 搜索出租房成交
     */
    public function queryHouseRented($cityId, $channelId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount)
    {
        $this->serviceId = 6; // 参考 bs search 的接口文档，每个搜索接口的serviceId不同
        return $this->_query($cityId, $channelId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount);
    }
    
    
    /*
     * 搜索学校
     */
    public function querySchool($cityId, $channelId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount)
    {
        $this->serviceId = 5; // 参考 bs search 的接口文档，每个搜索接口的serviceId不同
        if(empty($channelId))
        {
            $channelId = self::CHANNEL_SCHOOL_LIST;
        }
        return $this->_query($cityId, $channelId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount);
    }
    
    /*
     * 搜索小区
     */
    public function queryCommunity($cityId,  $queryStr, $queryExp, $sort, $limitOffset, $limitCount)
    {
        $this->serviceId = 7; // 参考 bs search 的接口文档，每个搜索接口的serviceId不同
        if(empty($channelId))
        {
            $channelId = self::CHANNEL_XIAOQU;
        }
        return $this->_query($cityId, $channelId, $queryStr, $queryExp, $sort, $limitOffset, $limitCount);
    }
    
    private function _query($cityId, $channelId, $queryStr, $queryExp, $sort, $pageOffset, $pageNum,$group=FALSE) {
        $result = array (
                'errno' => - 1,
                'error' => '',
                'body' => array () 
        );
        
        if (empty ( $queryExp )) {
            $queryExp = "";
        } else {
            $queryExp = ( string ) $queryExp;
        }
        
        $req = array (
                'cityId' => $cityId,
                'channelId' => $channelId,

                'query' => array (
                        'queryStr' => $queryStr,
                        'queryExp' => $queryExp 
                ),
                'sort' => self::getSortExpressionByArray ( $sort ),
                'page' => array (
                        'offset' => $pageOffset,
                        'num' => $pageNum 
                ) 
        );
        if($group){
          $req[ 'group'] = array (
                    "aggregates" => array (
                            array (
                                    "aggregate" => "count",
                                    "fieldName" => "*"
                            ),
                            array (
                                    "aggregate" => "min",
                                    "fieldName" => "priceUnit"
                            ),
                            array (
                                    "aggregate" => "max",
                                    "fieldName" => "priceUnit"
                            )
                    ),
                    "groupBy" => "subwayStationId",
                    "num" => 10,
                    "offset" => 0
            );
        }
        
        $reqStr = json_encode ( $req );
        
        $resStr = $this->_send ( "params=" . $reqStr );
        if ($resStr) {
            $res = json_decode ( $resStr, TRUE );
            if (isset ( $res ['header'] ['status'] )) {
                $result ['errno'] = $res ['header'] ['status'];
                $result ['error'] = $res ['header'] ['errMsg'];
                $result ['body'] = isset ( $res ['body'] ) ? $res ['body'] : "";
            }
        }
        
        return $result;
    }
    public function init() {
        if (! is_array ( $this->_urls )) {
            return;
        }
        if (empty ( $this->_urls )) {
            return;
        }
    }
    public function setUrls($urls) {
        foreach ( $urls as $url ) {
            if ("http" != parse_url ( $url, PHP_URL_SCHEME )) {
                
                continue;
            }
            $this->_urls [] = $url;
        }
    }
    protected function _getCurlHandle() {
        if (empty ( $this->_urls )) {
            
            return false;
        }
        
        $this->_currentUrlId = array_rand ( $this->_urls );
        $url = $this->_urls [$this->_currentUrlId];
        
        $ua = "ESearch-Client/0.1";
        $ua .= "-" . 'dev';
        
        $opt = array (
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => TRUE, // return web page
                CURLOPT_HEADER => FALSE, // don't return headers
                CURLOPT_FOLLOWLOCATION => FALSE, // follow redirects
                CURLOPT_ENCODING => "", // handle all encodings
                CURLOPT_USERAGENT => $ua,
                CURLOPT_AUTOREFERER => TRUE, // set referer on redirect
                CURLOPT_CONNECTTIMEOUT => $this->connectTimeout, // timeout on connect
                CURLOPT_TIMEOUT => $this->timeout, // timeout on response
                CURLOPT_SSL_VERIFYHOST => 0, // don't verify ssl
                CURLOPT_SSL_VERIFYPEER => FALSE, //
                CURLOPT_VERBOSE => FALSE, //
                CURLOPT_HTTPHEADER => array (
                        "provider: bs-search",
                        "magic: 123456",
                        "logid: " . mt_rand (),
                        "serviceid: ".$this->serviceId,
                        "serviceversion: 1",
                        "protocol: 0",
                        "reserved1: 0",
                        "reserved2: 0",
                        "bodylength: 0",
                        "Content-Type: application/json" 
                ) 
        );
        
        $ch = curl_init ();
        curl_setopt_array ( $ch, $opt );
        return $ch;
    }
    protected function _send($body = array()) {
        $ch = $this->_getCurlHandle ();
        if ($ch == false) {
            return false;
        }
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $body );
        $raw = curl_exec ( $ch );
        $errno = curl_errno ( $ch );

        if ($errno == CURLE_COULDNT_CONNECT) {
            
            unset ( $this->_urls [$this->_currentUrlId] );
            $this->_currentUrlId = null;
            return $this->_send ( $body );
        }
        
        if ($errno != CURLE_OK) {
            $errstr = curl_error ( $ch );
            
            return false;
        }
        
        $code = curl_getinfo ( $ch, CURLINFO_HTTP_CODE );
        
        if ($code != 200) {
            
            return false;
        }
        
        if (empty ( $raw )) {
            
            return false;
        }
        return $raw;
    }
    private static function getSortExpressionByArray($sort) {
        if (! is_array ( $sort )) {
            return $sort;
        }
        
        $orderBy = '';
        $orderByArr = $sort;
        foreach ( $orderByArr as $filed => $orderType ) {
            if ($orderType == self::ASC || $orderType == self::DESC) {
                if (! empty ( $orderBy )) {
                    $orderBy .= ',';
                }
                $orderBy .= $filed . ' ' . $orderType;
            } else {
            }
        }
        
        return $orderBy;
    }
}

header ( "Content-Type: text/html; charset=utf-8" );

// $url = 'http://172.16.3.139:8081/';
 $url='http://172.16.3.141:8081/';
// 联调地址
//$url = 'http://172.30.11.148:8581';
//$url = 'http://172.30.0.20:8581/';

$esearch = new ESearch ();
$esearch->setUrls ( array (
        $url 
) );
$esearch->init ();
$res = $esearch->queryHouseSell( '110000', '', '(seStatus:105000000003 AND houseCode:"BJHD88985842")', '', 0, 1 );
//$res = $esearch->queryCommunity ( '110000', '', '', '', 1, 1 );
echo json_encode ( $res );

// $url = 'http://172.16.3.139:8081/';
$url = 'http://172.16.3.141:8081/';
//联调地址
//$url = 'http://172.30.0.20:8581';


