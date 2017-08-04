<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Common\Controller;
use Think\Controller;

/**
 * 模块控制器
 * @author lihao <lh@tiptime.com>
 */
abstract class ModuleController extends ThinkController{

    protected   function _initialize(){
        parent::_initialize();
        $menu_path = JDICMS_MOUDLE_PATH.__CURRENT_MODULE__.'/menu.yml'; //获取菜单配置
        $menu = yaml_parse_file($menu_path); //解析菜单配置
        $group = '';
        $c_url = __CURRENT_CONTROLLER__.'/'.__CURRENT_ACTION__;

        //左侧菜单构造
        foreach($menu as $k=>$v){
            foreach($v as $kk=>$vv){
                if($this->_getGroup($menu[$k][$kk],$c_url)){
                    $group = $k;
                }
            }
        }
        $this->assign('_extra_menu',array(
            'ext'=>$menu,
            'group'=>$group
        ));
    }

    /**
     * 迭代构造左侧菜单
     * @param $node
     * @param $url
     * @return bool
     * @author lihao <lh@tiptime.com>
     */
    protected function _getGroup(&$node,$url){
        if(strtolower($node['url']) == strtolower($url)){
          $node['class'] = 'active';
          return true;
        }else{
            if($node['children']){
                foreach($node['children'] as $k=>$v){
                    if($this->_getGroup($node['children'][$k],$url)){
                        $node['class'] = 'active';
                        return true;
                    }
                }
            }else{
                return false;
            }
        }
    }

    /**
     * 模块内资源视图展示
     * @author lihao <lh@tiptime.com>
     * @param $s
     */
    protected function _display($s=''){
        if(empty($s)){
            $s = __CURRENT_CONTROLLER__.'/'.__CURRENT_ACTION__;
        }else{
            if(!strpos($s,'/')){ //当前控制器
                $s = __CURRENT_CONTROLLER__.'/'.$s;
            }
        }
        $this->display(T('Modules://'.__CURRENT_MODULE__.'@'.$s));
    }
}

