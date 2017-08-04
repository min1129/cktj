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
 * Class LinkApi
 * @package Common\Api
 * @author lh
 * @time 2015-03-07 09:57:04
 */
class LinkApi {
    /**
     * 获得外部链接
     * @param string $group 组别
     * @param  mix $what 如果是数字则取组别下面的纪录,如果是’count‘则取组别的链接数量,如果不填的话就获取改组别所有链接
     * @return int|string 链接集合
     */
    public static function get_link($group,$what=''){
        static $list;

        /* 非法分类ID */
        if(empty($group)){
            return '';
        }

        /* 读取缓存数据 */
        if(!empty($list)){
            $list = S('sys_link_list');
        }

        if(empty($list[$group])){
            $link = M('Link')->where(array('status'=>1,'group'=>$group))->order('sort asc,id asc')->select();

            if(empty($link)){//不存在该组别
                return '';
            }

            $list[$group] =array();
            $link = link_url($link);

            foreach($link as $v){
                $list[$group][$v['id']]=$v;
            }
            S('sys_link_list', $list); //更新缓存
        }
        if(!empty($what)){
            if($what=='count'){
                return count($list[$group]);
            }else{
                return $list[$group][$what];
            }
        }else{
            //关联数组 转成 索引数组
            $real_list = array();
            foreach($list[$group] as $k=>$v){
                $real_list[] =$v;
            }
            return $real_list;
        }
    }
}