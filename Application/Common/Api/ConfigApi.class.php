<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Common\Api;
/**
 * Class ConfigApi
 * @package Common\Api
 * @author lh
 * @time  2015-03-07 09:56:44
 */
class ConfigApi {
    /**
     * 获取数据库中的配置列表
     * @return array 配置数组
     */
    public static function lists(){
        /* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $map    = array('status' => 1);
            $data   = M('Config')->where($map)->field('type,name,value')->select();
            $config = array();
            if($data && is_array($data)){
                foreach ($data as $value) {
                    $config[$value['name']] = self::parse($value['type'], $value['value']);
                }
            }
            S('DB_CONFIG_DATA',$config);
        }
        return $config;
    }

    /**
     *获取指定配置
     * @param $key string 形如a,b,c 则获取a,b,c的配置
     * @return mixed
     */
    public static function getConfig($key){
        $array = str2arr($key);
        $data =array();
        foreach($array as $k){
            $data[$k] = C($k);
        }
        return $data;
    }

    /**
     * 根据配置类型解析配置
     * @param  integer $type  配置类型
     * @param  string  $value 配置值
     */
    private static function parse($type, $value){
        switch ($type) {
            case 3: //解析数组
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                if(strpos($value,':')){
                    $value  = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k]   = $v;
                    }
                }else{
                    $value =    $array;
                }
                break;
        }
        return $value;
    }	
}