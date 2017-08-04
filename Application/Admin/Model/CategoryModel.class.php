<?php
namespace Admin\Model;
use Think\Model;

class CategoryModel extends Model{
    protected $tableName = 'node';

    protected  $_auto = array(
      array('create_time',NOW_TIME,self::MODEL_INSERT),
      array('update_time',NOW_TIME,self::MODEL_BOTH)
    );

    protected $_validate = array(
        array('name', 'require','栏目名称必须填写',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
        array('symbol','/\w{1,20}/','英文名称必须是字母且长度必须大于1小于20！',self::MUST_VALIDATE,'regex',self::MODEL_BOTH),
        array('symbol', '', "名称已经存在", self::EXISTS_VALIDATE, 'unique'),
        array('list_num','/[1-9][0-9]*/','列表页必须是正整数',self::EXISTS_VALIDATE,'regex',self::MODEL_BOTH),
        array('url','url','url格式不合法',self::EXISTS_VALIDATE,'regex',self::MODEL_BOTH)
    );

    public function update(){
        $data = $this->create();
        if(!$data){
            return false;
        }
        /* 添加或更新数据 */
        if(empty($data['id'])){
            $res = $this->add();
        }else{
            $res = $this->save();
        }
        return $res;
    }



    /**
     * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
     * @param  integer $id    分类ID
     * @param  boolean $field 查询字段
     * @return array          分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getTree($id = 0, $field = true){
        /* 获取当前分类信息 */
        if($id){
            $info = $this->info($id);
            $id   = $info['id'];
        }

        /* 获取所有分类 */
        $map  = array('status' => array('gt', -1));
        $list = $this->field($field)->where($map)->order('sort')->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_', $root = $id);

        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }

        return $info;
    }
}
