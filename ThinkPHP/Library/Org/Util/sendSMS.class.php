<?php
namespace Org\Util;

class sendSMS{
    static  private $username = '70203902';

    public  static function send($phone,$content,$time='',$sid='1738'){
        $http = 'http://smsapi.duanxin.cm/';
        $data = array
        (
            'ac'=>'send',
            'uid'=>self::$username,
            'passkey'=>'3FC05CA8771B46696536947157989602',
            'gid'=>'273',
            'sid'=>$sid,
            'mobile'=>$phone,
            'content'=>$content,
            'time'=>$time,
            'encode'=>'utf8'
        );
        if(!$data['time']){
            unset($data['time']);
        }
        $re= self::postSMS($http,$data);
        return $re;
        $re = (array)simplexml_load_string($re);

        if($re["@attributes"]["result"]==1)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    // Xml 转 数组, 包括根键
    public  static  function xml_to_array( $xml )
    {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if(preg_match_all($reg, $xml, $matches))
        {
            $count = count($matches[0]);
            for($i = 0; $i < $count; $i++)
            {
                $subxml= $matches[2][$i];
                $key = $matches[1][$i];
                if(preg_match( $reg, $subxml ))
                {
                    $arr[$key] = xml_to_array( $subxml );
                }else{
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

    private static function postSMS($http,$data){
        $post='';
        $row = parse_url($http);
        $host = $row['host'];
        $port = !empty($row['port']) ? $row['port']:80;
        $file = $row['path'];
        while (list($k,$v) = each($data))
        {
            $post .= rawurlencode($k)."=".rawurlencode($v)."&";
        }
        $post = substr( $post , 0 , -1 );
        $len = strlen($post);
        $fp = @fsockopen( $host ,$port, $errno, $errstr, 10);
        if (!$fp) {
            return "$errstr ($errno)\n";
        } else {
            $receive = '';
            $out = "POST $file HTTP/1.0\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Content-type: application/x-www-form-urlencoded\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Content-Length: $len\r\n\r\n";
            $out .= $post;
            fwrite($fp, $out);
            while (!feof($fp)) {
                $receive .= fgets($fp, 128);
            }
            fclose($fp);
            $receive = explode("\r\n\r\n",$receive);
            unset($receive[0]);
            return implode("",$receive);
        }
    }
}
?>