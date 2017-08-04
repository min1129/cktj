<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Common\Controller;
/**
 * 模型数据管理控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class ThinkController extends AdminController {
    /**通用显示
     * @param $model
     * @param array $where
     * @param string $order
     * @param array $base
     * @param bool $field
     * @return array|false
     */
    protected function p_lists ($model,$where=array(),$order='',$base = array('status'=>array('egt',0)),$field=true){
        return parent::lists($model,$where,$order,$base,$field);
    }

    /**
     * 显示指定模型列表数据
     * @param  String $model 模型标识
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function lists($model, $p = 0, $title=''){
        $model || $this->error('模型名标识必须！');
        $page = intval($p);
        $page = $page ? $page : 1; //默认显示第一页数据

        //获取模型信息
        if(is_numeric($model)){
            $model = M('Model')->find($model);
        }else{
            $model =  M('Model')->where(array('name'=>$model))->find();
        }

        $model || $this->error('模型不存在！');

        //解析列表规则
        $fields = array();
        $grids  = preg_split('/[;\r\n]+/s', trim($model['list_grid']));
        foreach ($grids as &$value) {
        	if(trim($value) === ''){
        		continue;
        	}
            // 字段:标题:链接
            $val      = explode(':', $value);
            // 支持多个字段显示
            $field   = explode(',', $val[0]);
            $value    = array('field' => $field, 'title' => $val[1]);
            if(isset($val[2])){
                // 链接信息
                $value['href']	=	$val[2];
                // 搜索链接信息中的字段信息
                preg_replace_callback('/\[([a-z_]+)\]/', function($match) use(&$fields){$fields[]=$match[1];}, $value['href']);
            }
            if(strpos($val[1],'|')){
                // 显示格式定义
                list($value['title'],$value['format'])    =   explode('|',$val[1]);
            }
            foreach($field as $val){
                $array	=	explode('|',$val);
                $fields[] = $array[0];
            }
        }
        // 过滤重复字段信息
        $fields =   array_unique($fields);

        // 关键字搜索
        $map	=	array();
        $key	=	$model['search_key']?$model['search_key']:'title';
        if(isset($_REQUEST[$key])){
            $map[$key]	=	array('like','%'.$_GET[$key].'%');
            unset($_REQUEST[$key]);
        }
        // 条件搜索
        foreach($_REQUEST as $name=>$val){
            if(in_array($name,$fields)){
                $map[$name]	=	$val;
            }
        }
        $row    = empty($model['list_row']) ? 10 : $model['list_row'];


        array_push($fields, 'id');
        $name = parse_name(get_table_name($model['id']), true);
        $data = M($name)
            /* 查询指定字段，不指定则查询所有字段 */
            ->field(empty($fields) ? true : $fields)
            // 查询条件
            ->where($map)
            /* 默认通过id逆序排列 */
            ->order('id DESC')
            /* 数据分页 */
            ->page($page, $row)
            /* 执行查询 */
            ->select();

        int_to_string($data);

        /* 查询记录总数 */
        $count = M($name)->where($map)->count();

        //分页
        if($count > $row){
            $page = new \Think\Page($count, $row);
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            $this->assign('_page', $page->show());
        }

        $data   =   parseDocumentList($data,$model['id']);
        $this->assign('model', $model);
        $this->assign('list_grids', $grids);
        $this->assign('list_data', $data);
        $this->meta_title = $title?$title:($model['title'].'列表');
        $this->display($model['template_list']?$model['template_list']:'Think/lists');
    }

    public function del($model){
        //获取模型信息
        if(is_numeric($model)){
            $model = M('Model')->find($model);
        }else{
            $model =  M('Model')->where(array('name'=>$model))->find();
        }
        $model || $this->error('模型不存在！');

        $ids = array_unique((array)I('ids',0));
        $ids = arr2str($ids);
        if ( empty($ids) ) {
            $this->error('请选择要操作的数据!');
        }

        $Model = M(get_table_name($model['id']));
        $map = array('id' => array('in', $ids) );
        if($Model->where($map)->delete()){
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    public function edit($model, $id = 0,$title='',$success="保存成功"){

        //获取模型信息
        if(is_numeric($model)){
            $model = M('Model')->find($model);
        }else{
            $model =  M('Model')->where(array('name'=>$model))->find();
        }
        $model || $this->error('模型不存在！');

        if(IS_POST){
            $Model  =   D(parse_name(get_table_name($model['id']),1));
            // 获取模型的字段信息
            $Model  =   checkAttr($Model,$model['id']);
            if($Model->create() && $Model->save()!==false){
                $this->success('保存成功!',LK());
            } else {
                $this->error($Model->getError());
            }
        } else {
            $fields     = get_model_attribute($model['id']);

            //获取数据
            $data       = M(get_table_name($model['id']))->find($id);
            $data || $this->error('数据不存在！');
            $this->assign('model', $model);
            $this->assign('fields', $fields);
            $this->assign('data', $data);
            $this->meta_title = ($title?$title:$model['title']);
            $this->display($model['template_edit']?$model['template_edit']:'Think/edit');
        }
    }
    public function add($model,$title='',$fun=null){
        if(!isset($model)){
            $model = I('get.model');
        }
        if(is_numeric($model)){
            $model = M('Model')->where(array('status' => 1))->find($model);
        }else{
            $model = M('Model')->where(array('status' => 1,'name'=>$model))->find();
        }
        $model || $this->error('模型不存在或被禁用！');

        if(IS_POST || defined('FORCE_POST')){
            $Model  =   D(parse_name(get_table_name($model['id']),1));
            // 获取模型的字段信息
            $Model  =  checkAttr($Model,$model['id']);
            if($Model->create() ){
                $result = $Model->add();
                if($result){
                    if(is_string($fun)){
                        $fun($result);
                    }
                    $this->success('操作成功！',LK());
                }else{
                    $this->error($Model->getError());
                }
            } else {
                $this->error($Model->getError());
            }
        } else {
            $fields = get_model_attribute($model['id']);
            $this->assign('model', $model);
            $this->assign('fields', $fields);
            $this->meta_title = ($title?$title:$model['title']);
            $this->display($model['template_add']?$model['template_add']:'Think/add');
        }
    }

    public function info($model =null,$map,$title,$temp=''){
        if(is_numeric($model)){
            $model = M('Model')->find($model);
        }else{
            $model = M('Model')->where(array('name'=>$model))->find();
        }
        if(is_numeric($map)){ //默认
            $map = array('id'=>$map);
        }
        $model || $this->error('模型不存在！');
        $fields     = get_model_attribute($model['id']);

        //获取数据
        $data       = M(parse_name(get_table_name($model['id'])))->where($map)->find();
        $data || $this->error('model not exist！');
        $this->assign('model', $model);
        $this->assign('fields', $fields);
        $this->assign('data', $data);
        $this->meta_title = ($title?$title:$model['title']);
        $this->display((!empty($temp))?$temp:'Think/info');
    }
}