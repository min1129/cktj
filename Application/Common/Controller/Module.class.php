<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------

namespace Common\Controller;

/**
 * 模块类
 * @author lihao <lh@tiptime.com>
 */
abstract class Module{
    public $info                =   array();
    public $module_path          =   '';
    public $config_file         =   '';
    public $custom_config       =   '';
    public $access_url          =   array();

    public function __construct(){
        $this->module_path   =   JDICMS_MOUDLE_PATH.$this->getName().'/';

        if(is_file($this->module_path.'config.php')){

            $this->config_file = $this->module_path.'config.php';
        }
    }

    final public function getName(){
        $class = get_class($this);
        return substr($class,strrpos($class, '\\')+1, -6);
    }

    final public function checkInfo(){
        $info_check_keys = array('name','title','status','description','author','version');
        foreach ($info_check_keys as $value) {
            if(!array_key_exists($value, $this->info))
                return FALSE;
        }
        return TRUE;
    }

    /**
     * 获取模块的配置数组
     */
    final public function getConfig($name=''){
        static $_config = array();
        if(empty($name)){
            $name = $this->getName();
        }
        if(isset($_config[$name])){
            return $_config[$name];
        }
        $map['name']    =   $name;
        $map['status']  =   1;
        $config  =   M('Module')->where($map)->getField('config');
        if($config){
            $config   =   json_decode($config, true);
        }else{
            $temp_arr = include $this->config_file;
            foreach ($temp_arr as $key => $value) {
                if($value['type'] == 'group'){
                    foreach ($value['options'] as $gkey => $gvalue) {
                        foreach ($gvalue['options'] as $ikey => $ivalue) {
                            $config[$ikey] = $ivalue['value'];
                        }
                    }
                }else{
                    $config[$key] = $temp_arr[$key]['value'];
                }
            }
        }
        $_config[$name]     =   $config;

        return $config;
    }

    //模块安装方法
    abstract public function install();

    //模块卸装方法
    abstract public function uninstall();

    //模块更新方法
    abstract public function update();
}
