<?php
/**
 * 
 * @author quick
 *
 */
namespace Addons\LiuYan\Controller;
use Common\Controller\AddonsController;

class LiuyanController extends AddonsController{
	/**
	 * 新增留言
	 */
	public function add(){
        $model  = D('Addons://LiuYan/Liuyan');
        if($model->create() && $model->add() !== false){
            $this->success("留言成功!",U('index/index'));
        }else{
            $this->error($model->getError());
        }
	}

    /**
     * 删除留言
     */
    public function del(){
        $this->checkAuth(); //检测权限
        $id    = array_unique((array)I('ids',0));
        $id    = is_array($id) ? implode(',',$id) : $id;
        $where = array('id' => array('in', $id ));
        if(M('Liuyan')->where($where)->delete() !== false){
            $this->success("删除成功!");
        }else{
            $this->success("删除失败!");
        }
    }
}