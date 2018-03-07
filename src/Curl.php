<?php
namespace Common;

class Curl
{
    private static  $start_time;
    public static  $action_start_time;
    public static  $action_api_info;
    private static $common_start_time;//普通请求开始执行时间

    /**
     * jPost
     * @Author    Frank Yu
     * @Mobile    18676670369
     * @Email     imzhixun@gmail.com
     * @Copyright Dazong
     * @param     [type]             $url      [description]
     * @param     [type]             $data     [description]
     * @param     boolean            $send_log [description]
     * @return    [type]                       [description]
     */
    public static function jPost($url,$data,$send_log = true)
    {
        self::$start_time = microtime();
        $result = Curl::post( $url, $data, 'json');
        if(!self::checkIsJson($result)) {
            self::send_log(3,2,'请求接口返回结果非JSON',$data, $result,$url,'post');
            return [];
        }
        $dataArr    = json_decode($result,true);
        if(!$send_log){
            return $dataArr;
        }
        if(DEBUG){
            self::send_log(4,2,'CURL-SERVICE-REQUEST-DEBUG',$data, $dataArr,$url,'post');
        } else {
            self::send_log(1,2,'接口返回数据正常',$data, $dataArr,$url,'post');
        }
        return $dataArr;
    }

    /**
     * jPost
     * @Author    Frank Yu
     * @Mobile    18676670369
     * @Email     imzhixun@gmail.com
     * @Copyright Dazong
     * @param     [type]             $url      [description]
     * @param     [type]             $data     [description]
     * @param     boolean            $send_log [description]
     * @return    [type]                       [description]
     */
    public static function jJavaGet($url,$data,$send_log = true)
    {
        self::$start_time = microtime();
        $json = Curl::get($url,$data,'json');
        if(!self::checkIsJson($json)) {
            self::send_log(3,2,'请求接口返回结果非JSON',$data, $json,$url,'get');
            return [];
        }
        $dataArr  = json_decode($json,true);
        if(!$send_log){
            return $dataArr;
        }
        if(DEBUG){
            self::send_log(4,2,'CURL-SERVICE-REQUEST-DEBUG',$data, $dataArr,$url,'get');
        } else {
            self::send_log(1,2,'接口返回数据正常',$data, $dataArr,$url,'get');
        }
        return $dataArr;
    }

    /**
     * jPost
     * @Author    Frank Yu
     * @Mobile    18676670369
     * @Email     imzhixun@gmail.com
     * @Copyright Dazong
     * @param     [type]             $url      [description]
     * @param     [type]             $data     [description]
     * @param     boolean            $send_log [description]
     * @return    [type]                       [description]
     */
    public static function jJavaPost($url,$data,$HTTPHEADER='json',$send_log = true)
    {
        self::$start_time = microtime();
        $json = Curl::post($url,$data,$HTTPHEADER);
        if(!self::checkIsJson($json)) {
            self::send_log(3,2,'请求接口返回结果非JSON',$data, $json,$url,'post');
            return [];
        }
        $dataArr  = json_decode($json,true);
        if(!$send_log){
            return $dataArr;
        }
        if(DEBUG){
            self::send_log(4,2,'CURL-SERVICE-REQUEST-DEBUG',$data, $dataArr,$url,'post');
        } else {
            self::send_log(1,2,'接口返回数据正常',$data, $dataArr,$url);
        }
        return $dataArr;
    }

    /**
     * jPost
     * @Author    Frank Yu
     * @Mobile    18676670369
     * @Email     imzhixun@gmail.com
     * @Copyright Dazong
     * @param     [type]             $url      [description]
     * @param     [type]             $data     [description]
     * @param     boolean            $send_log [description]
     * @return    [type]                       [description]
     */
    private static function log_data_format($level, $lang_type, $content, $request, $response,$url){
        $path = $url;
        $site = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';
        $ar = explode("/", trim($_SERVER['REQUEST_URI'],"/"));
        $controller = isset($ar[0]) ? $ar[0] : '';
        $action = isset($ar[1]) ? strstr($ar[1]."?", "?" ,true) : '';
        $request_data = array();
        if(is_array($request) && !empty($request)){
            foreach ($request as $k => $v) {
                $request_data[] = $k . '=' . $v;
            }
            $request_string = implode("&", $request_data);
        }else{
            $request_string = $request;
        }

        $data = array(
            'path' => $path,
            'url' => $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
            'level' => $level,
            'lang_type' => $lang_type,
            'content' => $content,
            'site' => $site,
            'controller' => $controller,
            'action' => $action,
            'request_info' => $request_string,
            'response_info' => $response,
            'code' => ''
        );
        list($ends,$endms) = explode(" ", microtime());
        list($starts ,$startms) = explode(" ", self::$start_time);
        $data['response_time'] = ($ends - $starts)*1000 + ($endms-$startms)*1000;
        if(empty(self::$action_start_time)){
            self::$action_start_time = 0;
            self::$action_api_info = '';
        }
        self::$action_api_info .= "调用接口：".$path.",耗时：".$data['response_time']."ms/n/t/r";
        return json_encode($data);
    }

    /**
     * jPost
     * @Author    Frank Yu
     * @Mobile    18676670369
     * @Email     imzhixun@gmail.com
     * @Copyright Dazong
     * @param     [type]             $url      [description]
     * @param     [type]             $data     [description]
     * @param     boolean            $send_log [description]
     * @return    [type]                       [description]
     */
    public static function Wpost($url,$data,$headinfo=array(),$HTTPHEADER='json')
    {
        $HTTPHEADER='json';
        $headers['content-type'] = "application/{$HTTPHEADER};charset=UTF-8";
        //$headers['DZ-CLIENT-IP'] = $_SERVER['SERVER_ADDR'];
        $headers['CLIENT-IP'] = '';//Curl::get_ip();

        $headers['X-FORWARDED-FOR'] = $headers['CLIENT-IP'];
        $headers['DAZONG-FROM'] = 'web';
        if(class_exists('SLog')){
            $headers['traceid'] = SLog::RequestId();
        }
        $headerArr = array();
        foreach( $headers as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }
        if(!empty($headinfo)){
            foreach( $headinfo as $n => $v ) {
                $headerArr[] = $n .':' . $v;
            }
        }

        $defaults = array(
            CURLOPT_HEADER          => 0,
            //CURLOPT_PROXY =>'127.0.0.1:8888',
            CURLOPT_HTTPHEADER      => $headerArr,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_TIMEOUT         => 300,
            CURLOPT_CONNECTTIMEOUT  => 30,
            CURLOPT_POST            => 1,
            CURLOPT_POSTFIELDS      => is_array($data)?http_build_query($data):$data,
            CURLOPT_URL             => $url,
        );

        $ch     = curl_init();

        curl_setopt_array($ch, $defaults);

        $result = curl_exec($ch);

        if( $result === false)
        {
            self::send_log(3,2,'CURL-ACCESS-FAIL-ERROR-GET',$data, curl_error($ch),$url,'post');
            self::$common_start_time = '';
            curl_close($ch);
            return [];
        }
        self::$common_start_time = '';
        curl_close($ch);
        return $result;

    }

    /**
     * jPost
     * @Author    Frank Yu
     * @Mobile    18676670369
     * @Email     imzhixun@gmail.com
     * @Copyright Dazong
     * @param     [type]             $url      [description]
     * @param     [type]             $data     [description]
     * @param     boolean            $send_log [description]
     * @return    [type]                       [description]
     */
    public static function get($url,$data,$HTTPHEADER='x-www-form-urlencoded')
    {
        self::$common_start_time = !empty(self::$start_time) ? self::$start_time :microtime();
        $headers['content-type'] = "application/{$HTTPHEADER};charset=UTF-8";
        //$headers['DZ-CLIENT-IP'] = $_SERVER['SERVER_ADDR'];
        $headers['CLIENT-IP'] = '';//Curl::get_ip();

        $headers['X-FORWARDED-FOR'] = $headers['CLIENT-IP'];
        $headers['DAZONG-FROM'] = 'web';
        if(class_exists('SLog')){
            $headers['traceid'] = SLog::RequestId();
        }
        $headerArr = array();
        foreach( $headers as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }
        $defaults = array(
            CURLOPT_HEADER          => 0,
            CURLOPT_HTTPHEADER      => $headerArr,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_TIMEOUT         => 300,
            CURLOPT_CONNECTTIMEOUT  => 30,
            CURLOPT_URL             =>$url.(parse_url($url, PHP_URL_QUERY)?'':'?').(is_array($data)?http_build_query($data):$data)
        );

        $ch     = curl_init();

        curl_setopt_array($ch, $defaults);

        $result = curl_exec($ch);

        if( $result === false)
        {
            self::send_log(3,2,'CURL-ACCESS-FAIL-ERROR-GET',$data, curl_error($ch),$url,'get');
            self::$common_start_time = '';
            curl_close($ch);
            return [];
        }
        self::$common_start_time = '';
        curl_close($ch);
        return $result;

    }

    /**
     * jPost
     * @Author    Frank Yu
     * @Mobile    18676670369
     * @Email     imzhixun@gmail.com
     * @Copyright Dazong
     * @param     [type]             $url      [description]
     * @param     [type]             $data     [description]
     * @param     boolean            $send_log [description]
     * @return    [type]                       [description]
     */
    public static function post($url,$data,$HTTPHEADER='json',$is_wait=true)
    {
        self::$common_start_time = !empty(self::$start_time) ? self::$start_time :microtime();
        $headers['content-type'] = "application/{$HTTPHEADER};charset=UTF-8";
        //$headers['DZ-CLIENT-IP'] = $_SERVER['SERVER_ADDR'];
         $headers['CLIENT-IP'] = '';//Curl::get_ip();
        $headers['X-FORWARDED-FOR'] = $headers['CLIENT-IP'];
        $headers['DAZONG-FROM'] = 'web';
        if(class_exists('SLog')){
            $headers['traceid'] = SLog::RequestId();
        }
        $headerArr = array();
        foreach( $headers as $n => $v ) {
            $headerArr[] = $n .':' . $v;
        }
        $defaults = array(
            CURLOPT_HEADER          => 0,
            CURLOPT_HTTPHEADER      => $headerArr,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_CONNECTTIMEOUT  => 0,
            CURLOPT_NOSIGNAL =>1,
            CURLOPT_POST            => 1,
            CURLOPT_POSTFIELDS      => is_array($data)?http_build_query($data):$data,
            CURLOPT_URL             => $url
        );
        if (!empty($_COOKIE['dztoken'])) {
            $defaults[CURLOPT_COOKIE]="dztoken={$_COOKIE['dztoken']}";
        }

        $ch     = curl_init();
        if(!$is_wait){
            //不做结果等待
            $defaults[CURLOPT_TIMEOUT_MS] = 200;
        }else{
            $defaults[CURLOPT_TIMEOUT] = 120;
        }
        curl_setopt_array($ch, $defaults);

        $result = curl_exec($ch);
        if( $result === false)
        {
            self::send_log(3,2,'CURL-ACCESS-FAIL-ERROR-POST',$data, curl_error($ch),$url,'post');
            self::$common_start_time = '';
            curl_close($ch);
            return [];
        }
        self::$common_start_time = '';
        curl_close($ch);
        return $result;
    }

    /**
     * jPost
     * @Author    Frank Yu
     * @Mobile    18676670369
     * @Email     imzhixun@gmail.com
     * @Copyright Dazong
     * @param     [type]             $url      [description]
     * @param     [type]             $data     [description]
     * @param     boolean            $send_log [description]
     * @return    [type]                       [description]
     */
    public static function get_ip()
    {
        static $ip = false;

        if (false != $ip) {
            return $ip;
        }

        $keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ($keys as $item) {
            if (!isset($_SERVER[$item])) {
                continue;
            }

            $curIp = $_SERVER[$item];
            $curIp = explode('.', $curIp);
            if (count($curIp) != 4) {
                break;
            }

            foreach ($curIp as & $sub) {
                if (($sub = intval($sub)) < 0 || $sub > 255) {
                    break 2;
                }
            }

            $curIpBin = $curIp[0] << 24 | $curIp[1] << 16 | $curIp[2] << 8 | $curIp[3];
            $masks = array(// hexadecimal ip  ip mask
                array(0x7F000001, 0xFFFF0000), // 127.0.*.*
                array(0x0A000000, 0xFFFF0000), // 10.0.*.*
                array(0xC0A80000, 0xFFFF0000) // 192.168.*.*
            );
            foreach ($masks as $ipMask) {
                if (($curIpBin & $ipMask[1]) === ($ipMask[0] & $ipMask[1])) {
                    break 2;
                }
            }

            return $ip = implode('.', $curIp);
        }

        return $ip = $_SERVER['REMOTE_ADDR'];
    }

    /**
     * jPost
     * @Author    Frank Yu
     * @Mobile    18676670369
     * @Email     imzhixun@gmail.com
     * @Copyright Dazong
     * @param     [type]             $url      [description]
     * @param     [type]             $data     [description]
     * @param     boolean            $send_log [description]
     * @return    [type]                       [description]
     */
    private static function send_log($level, $lang_type, $content, $request, $response,$url="",$method = 'post')
    {
        $traceInfo = self::getDebugTrace();
        list($ends,$endms) = explode(" ", microtime());
        list($starts ,$startms) = explode(" ", !empty(self::$common_start_time) ? self::$common_start_time : self::$start_time);
        $responseTime = ($ends - $starts)*1000 + ($endms-$startms)*1000;
        $funEnum = [
            1=>'INFO',
            3=>'errorReport',
            4=>'DEBUG',
        ];
        if($level == 1) {
            $response = [];
        }
        $fun = isset($funEnum[$level]) ? $funEnum[$level] : $funEnum[1];
        if(is_string($request)) {
            try {
                if(strpos($request,'{') === 0) {
                    $tmpParam = json_decode($request,true);
                    $request = empty($tmpParam) ? $request : $tmpParam;

                }
            } catch(Exception $e) {
                $request = $request;
            }
        }
        if(class_exists('SLog')){
            SLog::$fun(
                (string)json_encode(array(
                    'type'=>'interfaceRequest',
                    'msg'=>$content,
                    'requestUrl'=>$url,
                    'requestParam'=>$request,
                    'requestMethod'=>$method,
                    'result'=>$response,
                    'requestTimes'=>$responseTime.'ms',
                    'env'=>isset($_SERVER['DZ_ENVIRONMENT']) ? $_SERVER['DZ_ENVIRONMENT'] : 'publish',
                    'accessIp'=>self::get_ip(),
                    'debugTrace'=>$traceInfo
                ),JSON_UNESCAPED_UNICODE)
            );
        }

    }

    /**
     * jPost
     * @Author    Frank Yu
     * @Mobile    18676670369
     * @Email     imzhixun@gmail.com
     * @Copyright Dazong
     * @param     [type]             $url      [description]
     * @param     [type]             $data     [description]
     * @param     boolean            $send_log [description]
     * @return    [type]                       [description]
     */
    private static function getDebugTrace()
    {
        $traceArr = debug_backtrace();
        //usage 调用方法 callProperties 使用位置 baseProperties 基础信息
        $traceInfo = ['usage'=>'','callProperties'=>[],'baseProperties'=>[]];
        $traceLeval = [1,2,3];
        if(isset($traceArr[2])) {
            if(strpos($traceArr[2]['file'],'/services/') !== false || !empty(self::$common_start_time)) {
                $traceLeval = [2,3,4];
            }
        }
        foreach($traceLeval as $key=>$val) {
            if(isset($traceArr[$val])) {
                $tmpTrace = $traceArr[$val];
                $file = '';
                if(isset($tmpTrace['file'])) {
                    $tmpFile = strstr($tmpTrace['file'], '/wwwroot/');
                    $file = $tmpFile == false ? $tmpTrace['file'] : $tmpFile;
                }
                switch($key) {
                    case 0:
                        $keyName = 'baseProperties';
                        $traceInfo[$keyName]['file'] = $file;
                        $traceInfo[$keyName]['line'] = $tmpTrace['line'];
                        $traceInfo[$keyName]['object'] = $tmpTrace['class'].'->'.$tmpTrace['function'];
                        break;
                    case 1:
                        $keyName = 'callProperties';
                        $traceInfo[$keyName]['file'] = $file;
                        $traceInfo[$keyName]['line'] = $tmpTrace['line'];
                        $traceInfo[$keyName]['args'] = isset($tmpTrace['args']) ? $tmpTrace['args'] : '';
                        $traceInfo[$keyName]['object'] = $tmpTrace['class'].'->'.$tmpTrace['function'];
                        break;
                    case 2:
                        $traceInfo['usage']['object'] = $tmpTrace['class'].'->'.$tmpTrace['function'];
                        break;
                }
            }
        }
        return $traceInfo;
    }

    /**
     * jPost
     * @Author    Frank Yu
     * @Mobile    18676670369
     * @Email     imzhixun@gmail.com
     * @Copyright Dazong
     * @param     [type]             $url      [description]
     * @param     [type]             $data     [description]
     * @param     boolean            $send_log [description]
     * @return    [type]                       [description]
     */
    public static function checkIsJson($str)
    {
        if(empty($str)) {
            return false;
        }
        json_decode($str);
        return (json_last_error() == JSON_ERROR_NONE);
    }

}
