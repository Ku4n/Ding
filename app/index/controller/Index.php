<?php
namespace app\index\controller;

use think\Db;

class Index
{
    public function index()
    {
        return '123';
    }

    /**
     * Send a POST requst using cURL
     * @param string $url to request
     * @param array $post values to send
     * @param array $options for cURL
     * @return string
     */
    public function curl_post($url, array $post = NULL, array $options = array())
    {

        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 4,
            //CURLOPT_POSTFIELDS => http_build_query($post)
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        if( ! $result = curl_exec($ch))
        {
            trigger_error(curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    /**
     * Send a GET requst using cURL
     * @param string $url to request
     * @param array $get values to send
     * @param array $options for cURL
     * @return string
     */
    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $url
     * @return bool|mixed
     */
    static public function get($url){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    public function get_access()
    {
        $db = Db::table('dt_appid') -> find();

        $corpid = $db['CorpId'];
        $corpsecret = $db['CorpSecret'];

/*        dump($corpid);
        dump($corpsecret);*/

        if($corpid != null && $corpsecret != null)
        {
            $token = 'https://oapi.dingtalk.com/gettoken?corpid='.$corpid.'&corpsecret='.$corpsecret;

            $data = json_decode(self::get($token) , true);
            if($data['errmsg'] != 'ok')
            {
                return false;
            }
            else{
                $ding['time'] = $data['expires_in'];
                $ding['access_token'] = $data['access_token'];
            }

        }

        # dump($ding);
        return $ding;
    }



    public function get_token()
    {
        $token = self::get_access();
        $access_token = $token['access_token'];

        $ticket = 'https://oapi.dingtalk.com/get_jsapi_ticket?access_token='.$access_token.'';
        $data = json_decode(self::get($ticket) , true);

        if($data['errmsg'] !== 'ok')
        {
            return false;
        }
        else{
            $ding['ticket_time'] = $data['expires_in'];
            $ding['ticket'] = $data['ticket'];
        }

        # dump($ding);
        return $ding;
    }


    public function nonceStr()
    {
        $allChar = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $ding['noncStr'] = '';
        $randStr = str_shuffle($allChar);//打乱字符串
        $ding['noncStr'] = substr($randStr , mt_rand(0,strlen($randStr)) - 1,32 );

        $noncStr = $ding['noncStr'];

        return $noncStr;
        # var_dump($noncStr);
    }

    public function jsapi()
    {
        $token = self::get_token();
        $nonceStr = self::nonceStr();

        $db = Db::table('dt_appid') -> find();
        $url = $db['url'];
        $ticket = $token['ticket'];
        $time = strtotime('now');

/*        dump($token);
        dump($url);
        dump($nonceStr);
        dump($time);*/

        $dt = 'jsapi_ticket=' . $ticket .'&noncestr=' . $nonceStr .'&timestamp=' . $time .'&url=' . $url;
        $signature = sha1($dt);

        return$signature;
        # dump($signature);
    }


    public function get_sign()
    {

        $db = Db::table('dt_appid') -> find();

        $corpId = $db['CorpId'];
        $agentid = $db['agentId'];

        $signature = self::jsapi();
        $time = strtotime('now');
        $nonceStr = self::nonceStr();

/*        dump($corpId);
        dump($nonceStr);
        dump($agentid);
        dump($time);
        dump($signature);*/

        $string = array(
            'agentid' => $agentid,
            'corpid' => $corpId,
            'timestamp' => $time,
            'nonceStr' => $nonceStr,
            'signature' => $signature,
        );

        return json_encode($string);
    }
}
