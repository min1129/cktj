<?php
namespace Admin\Model;
use Think\Model;

/**
 * 文档基础模型
 */
class ModelModel extends Model{

    /* 自动验证规则 */
    protected $_validate = array(
        array('name', 'require', '标识不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_INSERT),
        array('name', '/^[a-zA-Z]\w{0,39}$/', '文档标识不合法', self::VALUE_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '', '标识已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
        array('title', 'require', '标题不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '1,30', '标题长度不能超过30个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),

    );

    /* 自动完成规则 */
    protected $_auto = array(
        array('name', 'strtolower', self::MODEL_INSERT, 'function'),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
        array('update_time', NOW_TIME, self::MODEL_BOTH),
        array('status', '1', self::MODEL_INSERT, 'string'),
        array('field_sort', 'getFields', self::MODEL_BOTH, 'callback'),
    );

    /**
     * 检查列表定义
     * @param type $data
     */
    protected function checkListGrid($data) {
        return I("post.extend") != 0 || !empty($data);
    }

    /**
     * 新增或更新一个文档
     * @return boolean fasle 失败 ， int  成功 返回完整的数据
     * @author huajie <banhuajie@163.com>
     */
    public function update(){
        /* 获取数据对象 */
        $data = $this->create();
        if(empty($data)){
            return false;
        }

        /* 添加或新增基础内容 */
        if(empty($data['id'])){ //新增数据
            $id = $this->add(); //添加基础内容
            if(!$id){
                return false;
            }

            if(!empty($data['pid'])){//继承模型
                $newTable = C('DB_PREFIX').strtolower($data['name']);
                $oldTable = C('DB_PREFIX').$this->getFieldById($data['pid'],'name');
                $sql = <<<sql
CREATE TABLE {$newTable} LIKE {$oldTable}
sql;
                //复制表结构
                $res = M()->execute($sql);

                 if($res !== false){
                     //复制模型配置
                     $old_model = D('Model')->where(array('id'=>$data['pid']))->field('id,title,name,create_time,update_time',true)->find();
                     $old_model['id']= $id;
                     $old_model['title'] = $data['title'];
                     $old_model['name'] = $data['name'];
                     $res = M('Model')->save($old_model);
                 }
                if($res!==false){
                    //复制属性约束
                    $attr = M('Attribute')->where(array('model_id'=>$data['pid']))->field('id',true)->select();
                    foreach($attr as $v){
                        $v['model_id'] = $id;
                        M('Attribute')->add($v);
                    }
                }else{
                    //删除添加的数据
                    $this->where(array('id'=>id))->delete();
                    $this->error = '新增模型出错！';
                    return false;
                }
                return true;
            }
        } else { //更新数据
            $status = $this->save(); //更新基础内容
            if(false === $status){
                $this->error = '更新模型出错！';
                return false;
            }
        }
        // 清除模型缓存数据
        S('DOCUMENT_MODEL_LIST', null);

        //记录行为
        action_log('update_model','model',$data['id'] ? $data['id'] : $id,UID);

        //内容添加或更新完成
        return $data;
    }

    /**
     * 处理字段排序数据
     */
    protected function getFields($fields){
        return empty($fields) ? '' : json_encode($fields);
    }

    protected function getAttribute($fields) {
        return empty($fields) ? '' : implode(',', $fields);
    }

    /**
     * 获取所有模型
     */
    public function getTables(){
         return $this->db->getTables();
    }

    /**
     * 根据数据表生成模型及其属性数据
     * @author huajie <banhuajie@163.com>
     */
    public function generate($table,$name='',$title=''){
        //新增模型数据
        $old_model = $this->where(array('name'=>$table))->field('id',true)->find();
        $old_model['field_sort'] = '';
        $old_model['name'] = $name;
        $old_model['title'] = $title;
        $res = M('Model')->add($old_model);
        //复制表结构
        $newTable = C('DB_PREFIX').$name;
        $oldTable =  C('DB_PREFIX').$table;
        $sql = <<<sql
            CREATE TABLE {$newTable} LIKE {$oldTable};
sql;
        M()->execute($sql);

        $old_model = $this->where(array('name'=>$table))->field('id')->find();
        $attr_model = M('Attribute');
        $attr = $attr_model->where(array('model_id'=>$old_model['id']))->field('id',true)->select();
        for($i=0; $i<count($attr); $i++){
            $attr[$i]['model_id'] = $res;
            $attr_model->add($attr[$i]);
        }
        return $res;
    }

    /**
     * 删除一个模型
     * @param integer $id 模型id
     * @author huajie <banhuajie@163.com>
     */
    public function del($id){
        //获取表名
        $model = $this->field('name')->find($id);
        $table_name = C('DB_PREFIX').strtolower($model['name']);

        //删除属性数据
        M('Attribute')->where(array('model_id'=>$id))->delete();
        //删除模型数据
        $this->delete($id);


        //删除该表
        $sql = <<<sql
            DROP TABLE {$table_name};
sql;
        $res = M()->execute($sql);


        return true;
    }
}
