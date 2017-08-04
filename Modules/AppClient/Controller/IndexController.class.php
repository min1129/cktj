<?php
namespace Modules\AppClient\Controller;
use Common\Controller\ModuleController;
use Think\Controller;
use Think\Page;
class IndexController extends ModuleController {
    public function  index(){
        MK();
        $map  = array('status' => array('gt',-1));
        $list = $this->p_lists('app_client',$map,'create_time desc');
        for($i=0;$i<count($list);$i++){
            $file = M('File')->where(array('status'=>1))->getById($list[$i]['file']);
            $list[$i]['size'] = round(($file['size']/(1024*1024)) ,2)." MB";
        }
        $this->assign('list', $list);
        $this->meta_title = '客户端列表';
        $this->_display();
    }

    /**
     * 删除数据
     */
    public function  del(){
        parent::editRow('app_client',array('status'=>-1));
    }

    /**
     * 添加或者修改
     */
    public function  add(){
        if(IS_POST){
            parent::add('app_client');
        }else{
            parent::add('app_client',"添加客户端");
        }
    }

    public function edit(){
        if(IS_POST){
            $id = I('post.id');
            parent::edit('app_client',$id);
        }else{
            $name = I('get.name');
            $id = I('get.id');
            parent::edit('app_client',$id,$name.'[修改]');
        }
    }

    public function push(){
       $res = push("有新的版本，赶快更新吧!","有新的版本，赶快更新吧!",1,array());
        if($res){
            $this->success("发送成功!");
        }else{
            $this->error("发送失败!");
        }
    }
}