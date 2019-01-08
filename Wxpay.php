<?php
/*
 * (c) DataLinkage.Inc.,
 *
 * Author: 李益达 - Ekey.Lee <ekey.lee@gmail.com>
 *
 */


class Wxpay
{
    const UNIFIEDORDER = 'https://api.mch.weixin.qq.com/pay/unifiedorder'; //统一下单请求地址
    private $appid; //appid
    private $body; //商品描述
    private $key; //秘钥
    private $mch_id; //商户号
    private $nonce_str; //随机数
    private $notify_url; //通知地址
    private $openid;//微信用户ID
    private $out_trade_no; //商户订单号
    private $package; //统一下单订单号
    private $resign; //二次签名
    private $sign; //签名
    private $spbill_create_ip; //终端ip
    private $timestamp; //交易时间戳
    private $total_fee; //标价金额
    private $trade_type = 'JSAPI'; //交易类型
   
    /**
     * 初始化
     * @param int $total_fee 标价金额
     * @param string $body 商品描述
     * @param string $openid 微信用户id
     */
    public function __construct($total_fee, $body, $openid)
    {
        $rand = rand(11, 99);//订单号的随机串
        $mp_info = $this->wechatInfo();//获取公众号信息
        $this->appid = $mp_info['appid'];//appid
        $this->body = $body;//商品描述
        $this->key = $mp_info['paykey'];//支付秘钥
        $this->mch_id = $mp_info['mch_id'];//商户号
        $this->nonce_str = $this->createNonceStr(32);//随机串
        $this->notify_url = 'http://uedream.cn/index.php';//支付成功回调域名，需能访问
        $this->openid = $openid;//微信用户ID
        $this->out_trade_no = time() . $rand; //单号
        $this->sign;//第一次的签名，为获取到prepay_id
        $this->sign_type = 'MD5';//加密方式
        $this->spbill_create_ip = $_SERVER['REMOTE_ADDR'];//获取客户端IP
        $this->timestamp = time();//时间戳
        $this->total_fee = $total_fee * 100;//支付金额（单位：分）
        //方法初始化
        $this->createsign(); //方法调用：生成第一次签名的方法
    }
    
    /**
     * 微信配置（假定值）
     * @return array
     */
    private function wechatInfo(){
        $mp = [
            'appid'=>'xxxxxxxxx',//微信appid
            'paykey'=>'xxxxxxxxxxx',//微信支付密码
            'mch_id'=>'xxxxxxxx',//商户号
        ];
        return $mp;
    }
    
    /**
     * 返回微信支付参数 Main
     * @return array
     */
    public function getWxpayConfig(){
        $param = $this->unifiedorder();
        return $param;
    }

    /**
     * 统一下单
     * @return array
     */
    private function unifiedorder()
    {

        $data = [
            'appid' => $this->appid,
            'body' => $this->body,
            'mch_id' => $this->mch_id,
            'nonce_str' => $this->nonce_str,
            'notify_url' => $this->notify_url,
            'openid' => $this->openid,
            'out_trade_no' => $this->out_trade_no,
            'sign' => $this->sign,
            'sign_type' => 'MD5',
            'spbill_create_ip' => $this->spbill_create_ip,
            'timeStamp' => $this->timestamp,
            'total_fee' => $this->total_fee * 1,
            'trade_type' => $this->trade_type,
        ];
        $xml = $this->arrayToXml($data);
        $result = $this->http_post(self::UNIFIEDORDER, $xml);
        $return = $this->xmlToArray($result);
        $this->package = 'prepay_id=' . $return['prepay_id'];
        $this->renCreatesign();
        $returns = [
            'appid' => $this->appid,
            'noncestr' => $this->nonce_str,
            'signtype' => $this->sign_type,
            'package' => $this->package,
            'sign' => $this->resign,
            'timestamp' => $this->timestamp,
        ];
        return $returns;
    }

    /**
     * 二次签名
     */
    private function renCreatesign()
    {

        $build_data = [
            'appId' => $this->appid,
            'nonceStr' => $this->nonce_str,
            'package' => $this->package,
            'signType' => $this->sign_type,
            'timeStamp' => $this->timestamp,
            'key' => $this->key,
        ];
        $result = http_build_query($build_data);
        $put_data = str_replace('%3D', '=', $result); //格式化网址
        $signatrue = md5($put_data);

        $this->resign = strtoupper($signatrue);

    }

    /**
     * 一次生成签名
     */
    private function createsign()
    {
        $string = 'appid=' . $this->appid . '&body=' . $this->body . '&mch_id=' . $this->mch_id . '&nonce_str=' . $this->nonce_str . '&notify_url=' . $this->notify_url . '&openid=' . $this->openid . '&out_trade_no=' . $this->out_trade_no . '&sign_type=' . $this->sign_type . '&spbill_create_ip=' . $this->spbill_create_ip . '&timeStamp=' . $this->timestamp . '&total_fee=' . $this->total_fee . '&trade_type=' . $this->trade_type . '&key=' . $this->key;
        $md5 = md5($string);
        $this->sign = strtoupper($md5);
    }
    
    /**
     * 随机字符串
     * @param int $length 长度
     * @return string
     */
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    
    /**
     * xml转array
     * @return array
     */
    private function xmlToArray($xml)
    {
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }
    
    /**
     * array转xml
     * @return string
     */
    private function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";
            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        return $xml;
    }
    
    /**
     * POST请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file
     */
    private function http_post($url, $param, $post_file = false)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== false) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile')) {
            $is_curlFile = true;
        } else {
            $is_curlFile = false;
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        if (is_string($param)) {
            $strPOST = $param;
        } elseif ($post_file) {
            if ($is_curlFile) {
                foreach ($param as $key => $val) {
                    if (substr($val, 0, 1) == '@') {
                        $param[$key] = new \CURLFile(realpath(substr($val, 1)));
                    }
                }
            }
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }
}

