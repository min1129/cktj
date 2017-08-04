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
 * Class ModelApi
 * @package Common\Api
 * @author lh
 * @time 2015-03-07 09:57:15
 */
class ModelApi {
    /**
     * 获取文档模型信息
     * @param  integer $id    模型ID
     * @param  string  $field 模型字段
     * @return array 结果
     */
    public static function get_document_model($id = null, $field = null){
        static $list;

        /* 非法分类ID */
        if(!(is_numeric($id) || is_null($id))){
            return '';
        }

        /* 读取缓存数据 */
        if(empty($list)){
            $list = S('DOCUMENT_MODEL_LIST');
        }

        /* 获取模型名称 */
        if(empty($list)){
            $map   = array('status' => 1);
            $model = M('Model')->where($map)->field(true)->select();
            foreach ($model as $value) {
                $list[$value['id']] = $value;
            }
            S('DOCUMENT_MODEL_LIST', $list); //更新缓存
        }

        /* 根据条件返回数据 */
        if(is_null($id)){
            return $list;
        } elseif(is_null($field)){
            return $list[$id];
        } else {
            return $list[$id][$field];
        }
    }

    /**
     * 获取模型名称
     * @param int $id 模型id
     * @param  string $filed 字段
     * @return array 结果
     */
    public static function get_model_by_id($id,$field='title'){
        return M('Model')->getFieldById($id,$field);
    }

    /**
     * 获取模型属性
     * @param int $model_id 模型id
     * @param bool $group 是否分组
     * @return array|mixed|\stdClass|string 结果
     */
    public static function get_model_attribute($model_id, $group = true){
        static $list;

        /* 非法ID */
        if(empty($model_id) || !is_numeric($model_id)){
            return '';
        }

        /* 读取缓存数据 */
        if(empty($list)){
            $list = S('attribute_list');
        }

        /* 获取属性 */
        if(!isset($list[$model_id])){
            $map = array('model_id'=>$model_id);
            $extend = M('Model')->getFieldById($model_id,'extend');

            if($extend){
                $map = array('model_id'=> array("in", array($model_id, $extend)));
            }
            $info = M('Attribute')->where($map)->select();
            $list[$model_id] = $info;
            //S('attribute_list', $list); //更新缓存
        }

        $attr = array();
        foreach ($list[$model_id] as $value) {
            $attr[$value['id']] = $value;
        }

        if($group){
            $sort  = M('Model')->getFieldById($model_id,'field_sort');

            if(empty($sort)){	//未排序
                $group = array(1=>array_merge($attr));
            }else{
                $group = json_decode($sort, true);

                $keys  = array_keys($group);
                foreach ($group as &$value) {
                    foreach ($value as $key => $val) {
                        $value[$key] = $attr[$val];
                        unset($attr[$val]);
                    }
                }

                if(!empty($attr)){
                    $group[$keys[0]] = array_merge($group[$keys[0]], $attr);
                }
            }
            $attr = $group;
        }
        return $attr;
    }

    /**
     * 分析属性的枚举类型字段值 格式 a:名称1,b:名称2 或者 :fun(var1,var)
     * @param string $string 分析字符串
     * @return array 结果
     */
    public static function parse_field_attr($string) {
        if(0 === strpos($string,':')){
            // 采用函数定义
            return   eval(substr($string,1).';');
        }
        $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
        if(strpos($string,':')){
            $value  =   array();
            foreach ($array as $val) {
                list($k, $v) = explode(':', $val);
                $value[$k]   = $v;
            }
        }else{
            $value  =   $array;
        }
        return $value;
    }
}