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
 * Class DocumentApi
 * @package Common\Api
 * @author lh
 * @time 2015-03-07 09:56:53
 */
class DocumentApi {
    /**
     * 设置where查询条件sdadsa
     * @param  mixed  $category 分类ID获取英文名称
     * @param  number  $pos      推荐位
     * @param  integer $status   状态
     * @ignore
     * @return array             查询条件
     */
    public  static function listMap($category='', $status = 1, $pos = null){
        /* 设置状态 */
        $map = array('status' => $status);
        /* 设置分类 */
        if($category){
            $cat=  CategoryApi::get_category($category);
            if($cat){//栏目可用
                $map['category_id'] = $cat['id'];
            }else{//栏目不可用
                $map['status']= -10000;//舍得查询结果为空
            }

        }

        $map['create_time'] = array('lt', NOW_TIME);
        $map['_string']     = 'deadline <= 0 OR deadline > ' . NOW_TIME;
        /* 设置推荐位 */
        if(is_numeric($pos)){
            $pos = $pos.',';
            $map[] = "position like '%{$pos}%'";
        }

        return $map;
    }

    /**
     * 获取内容列表
     * @param string|int $category 栏目名称或者id
     * @param mixed $where 筛选
     * @param int $page 页数
     * @param string $order 排序
     * @param int $status 状态
     * @return array|bool|mixed|string 结果
     */
    public static function lists($category,$page=1,$where=array(),$order = '`weight` DESC, `update_time` DESC',$status = 1){
        $map = DocumentApi::listMap($category, $status);
        $cat=  CategoryApi::get_category($category);
        $model_id = $cat['model_id'];
        if(empty($model_id)){//分类不存在或者被禁用
            return false;
        }
        $model_name = ModelApi::get_model_by_id($model_id,'name');
        if(empty($model_name)){//模型不存在或者被禁用
            return false;
        }
        if(is_string($where)){ //字符串查询
            $map["_string"] = $where;
        }else{
            $map = array_merge($map,$where);
        }
        $model = M($model_name)->field(true)->where($map)->order($order);
        if(is_numeric($page)){
            $model->page($page,$cat['list_num']);
        }else{
            $model->limit($page);
        }
        $result = $model->select();
        if($result){
            $result = content_url($result);
            return $result;
        }else{
            api_msg("暂无数据~~");
            return false;
        }
    }




    /**
     * 搜索
     * @param string $search 搜索的关键字
     * @param int $page 页码
     * @param int $page_num 分页大小
     * @param string $order 拍序
     * @param int $status 搜索的文章状态
     * @return array|mixed|string 搜索结果
     */
    public  static function  search($search,$page,$page_num=10,$order = '`weight` DESC, `update_time` DESC',$status=1){
        $map = DocumentApi::listMap(null, $status);
        $search_sql = arr2str($search,'%');
        $map[] ="BINARY `title` LIKE '%{$search_sql}%'";
        $model = M('article')->field(true)->where($map)->order($order);
        if(is_numeric($page)){
            $model->page($page,$page_num);
        }else{
            $model->limit($page);
        }
        $result = $model->select();
        if($result){
            $result = content_url($result);
            return $result;
        }else{
            api_msg("暂无数据~~");
            return false;
        }
    }

    /**
     * 搜索数量
     * @param string $search 搜索关键字
     * @param string $order 排序
     * @param int $status 状态
     * @return mixed 搜索数量
     */
    public  static function  searchCount($search,$order = '`weight` DESC, `update_time` DESC',$status=1){
        $map = DocumentApi::listMap(null, $status);
        $search_sql = arr2str($search,'%');
        $map[] ="BINARY `title` LIKE '%{$search_sql}%'";
        $count = M('article')->where($map)->order($order)->count();
        return $count;
    }



    /**
     * 计算列表总数
     * @param  number  $category 分类ID
     * @param  integer $status   状态
     * @return integer           总数
     */
    public static function listCount($category, $status = 1){
        $map = DocumentApi::listMap($category, $status);
        $model_id =  CategoryApi::get_category($category,'model_id');

        if(empty($model_id)){//分类不存在或者被禁用
            return false;
        }
        $model_name = ModelApi::get_model_by_id($model_id,'name');
        if(empty($model_name)){//模型不存在或者被禁用
            return false;
        }
        return M($model_name)->where($map)->count('id');
    }


    /**
     * @param int $category 栏目id
     * @param int $id 记录id
     * @return array|bool|mixed|string 文章详情
     */
    public static  function detail($category,$id){
        $model_id =  CategoryApi::get_category($category,'model_id');
        $map = DocumentApi::listMap($category, 1);
        if(empty($model_id)){//分类不存在或者被禁用
            return false;
        }

        $model_name = ModelApi::get_model_by_id($model_id,'name');
        if(empty($model_name)){//模型不存在或者被禁用
            return false;
        }

        if(isset($id)){
            $map['id']=$id;
        }
        $result =  M($model_name)->where($map)->find();
        if($result){
            M($model_name)->where($map)->setInc('view'); //浏览数量＋1
        }
        $result = content_url($result);
        return $result;
    }

    /**
     * 找到对应模型下的某条记录
     * 一定要确保表市继承字基本内容模型的
     * 这里不做判断,调用者自行决定
     * 慎用！
     * @ignore
     */
    public static function record($category,$modelName,$id=''){

        $map = DocumentApi::listMap($category, 1);
        if(!empty($id)){
            $map['id']=$id;
        }
        $result =  M($modelName)->where($map)->find();
        if($result){
            M($modelName)->where($map)->setInc('view'); //浏览数量＋1
        }

        $result = content_url($result);
        return $result;
    }

    /**
     * 获取上一条新闻
     * @param int $id 当前文章id
     * @param int $category_id 当前文章的栏目id
     * @return array|bool|mixed|string 上一条新闻的详细信息
     */
    public static function prev($id,$category_id){
        $map = array(
            'id'          => array('gt',$id),
            'category_id' => $category_id,
            'status'      => 1,
            'create_time' => array('lt', NOW_TIME),
            '_string'     => 'deadline <= 0 OR deadline > ' . NOW_TIME,
        );

        $model_id =  CategoryApi::get_category($category_id,'model_id');

        if(empty($model_id)){//分类不存在或者被禁用
            return false;
        }
        $model_name = ModelApi::get_model_by_id($model_id,'name');

        if(empty($model_name)){//模型不存在或者被禁用
            return false;
        }

        $result = M($model_name)->field(true)->where($map)->order('`id` asc')->find();
        $result = content_url($result);
        return $result;
    }

    /**
     * 获取下一条新闻
     * @param int $id 当前文章id
     * @param int $category_id 当前文章的栏目id
     * @return array|bool|mixed|string 下一条新闻的详细信息
     */
    public static function next($id,$category_id){
        $map = array(
            'id'          => array('lt', $id),
            'category_id' => $category_id,
            'status'      => 1,
            'create_time' => array('lt', NOW_TIME),
            '_string'     => 'deadline <= 0 OR deadline > ' . NOW_TIME,
        );
        $model_id =  CategoryApi::get_category($category_id,'model_id');
        if(empty($model_id)){//分类不存在或者被禁用
            return false;
        }
        $model_name = ModelApi::get_model_by_id($model_id,'name');
        if(empty($model_name)){//模型不存在或者被禁用
            return false;
        }
        $result = M($model_name)->field('content',true)->where($map)->order('`id` Desc')->find();

        $result = content_url($result);
        return $result;
    }

    /**
     * 获取推荐位数据列表,紧紧是文章的推荐位置
     * @param  number  $pos      推荐位 1:列表页推荐 2:频道页推荐 3:网站首页推荐
     * @param  number  $category 分类ID
     * @param  number  $limit    列表行数
     * @param  boolean $field    查询字段
     * @return array             数据列表
     */
    public static  function position($pos, $category = '', $limit, $field = true){
        $map = DocumentApi::listMap($category, 1, $pos);
        $model = M('article');
        /* 设置列表数量 */
        $limit && $model->limit($limit);
        $result =  $model->field($field)->where($map)->select();
        $result = content_url($result);
        return $result;
    }

    /**
     * 获取热点新闻
     * @param int|string $cate 栏目
     * @param int|string $limit 数量
     * @param string $order 排序
     * @param bool $field 要取的字段
     * @return array|mixed|string  热点新闻
     */
    public static function hot_list($cate='',$limit,$order='`view` DESC, `is_up` DESC',$field = true){
        $map = DocumentApi::listMap($cate, 1);
        $model = M('article');
        $limit && $model->limit($limit);
        $result =  $model->field($field)->where($map)->order($order)->select();

        $result = content_url($result);
        return $result;
    }
}