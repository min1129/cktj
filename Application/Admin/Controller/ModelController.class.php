<?php
namespace Admin\Controller;
use Common\Controller\AdminController;
/**
 * 模型管理控制器
 * @author huajie <banhuajie@163.com>
 */
class ModelController extends AdminController {

    /**
     * 模型管理首页
     * @author huajie <banhuajie@163.com>
     */
    public function index(){
        $type = I('get.type',-1);
        $map['status'] = array('gt',-1);
        if($type>-1){
            $map['type']= $type;
            $this->assign('current_type', $type);
        }
        $this->assign('types', get_model_type());
        $list = $this->lists('Model',$map);
        int_to_string($list);
        // 记录当前列表页的cookie
        MK();

        $this->assign('_list', $list);
        $this->meta_title = '模型管理';
        $this->display();
    }

    /**
     * 新增页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function add(){
        //获取所有的模型
        $this->assign('types', get_model_type());
        $this->assign('models',get_models());
        $this->meta_title = '新增模型';
        $this->display();
    }

    /**
     * 编辑页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function edit(){
        $id = I('get.id','');
        if(empty($id)){
            $this->error('参数不能为空！');
        }

        /*获取一条记录的详细数据*/
        $Model = M('Model');
        $data = $Model->field(true)->find($id);
        if(!$data){
            $this->error($Model->getError());
        }
        $fields = M('Attribute')->where(array('model_id'=>$data['id']))->getField('id,name,title,is_show',true);
        $fields = empty($fields) ? array() : $fields;

        // 获取模型排序字段
        $field_sort = json_decode($data['field_sort'], true);
        if(!empty($field_sort)){
            foreach($field_sort as $group => $ids){
                foreach($ids as $key => $value){
                    if(isset($fields[$value])){
                        $fields[$value]['group']  =  $group;
                        $fields[$value]['sort']   =  $key;
                    }
                }
            }
        }

        //模型类型
        $this->assign('types', get_model_type());

        // 模型字段列表排序
        $fields = list_sort_by($fields,"sort");

        $this->assign('fields', $fields);
        $this->assign('info', $data);
        $this->meta_title = '编辑模型';
        $this->display();
    }

    /**
     * 删除一条数据
     * @author huajie <banhuajie@163.com>
     */
    public function del(){
        $ids = I('get.ids');
        empty($ids) && $this->error('参数不能为空！');
        $ids = explode(',', $ids);
        foreach ($ids as $value){
            $res = D('Model')->del($value);
            if(!$res){
                break;
            }
        }
        if(!$res){
            $this->error(D('Model')->getError());
        }else{
            $this->success('删除模型数据成功！');
        }
    }

    /**
     * 更新一条数据
     * @author huajie <banhuajie@163.com>
     */
    public function update(){
        $res = D('Model')->update();
        if(!$res){
            $this->error(D('Model')->getError());
        }else{
            $this->success($res['id']?'更新成功':'新增成功',LK());
        }
    }

    /**
     * 模型导出
     */
    public function export(){
        $ids =  (array)I('ids');
        if(empty($ids)){
            $this->error('请选择要导出的模型!');
        }else{
            $model = M('Model')->field(true)->select();
            $attr = M('Attribute')->field(true)->select();
            $result['model']= $model;
            $result['attr'] = $attr;
        }
    }

    /**
     * 生成一个模型
     * @author huajie <banhuajie@163.com>
     */
    public function generate(){
        if(!IS_POST){
            //获取所有的模型表
            $tables = D('Model')->field('name')->select();
            $this->assign('types', get_model_type());
            if($tables){
                $this->assign('tables', $tables);
                $this->meta_title = '生成模型';
                $this->display();
            }else{
                $this->error("还未添加模型表!");
            }

        }else{
            $table = I('post.table');
            empty($table) && $this->error('请选择要生成的数据表！');
            $res = D('Model')->generate($table,I('post.name'),I('post.title'));
            if($res){
                $this->success('生成模型成功！', U('index'));
            }else{
                $this->error(D('Model')->getError());
            }
        }
    }
}
