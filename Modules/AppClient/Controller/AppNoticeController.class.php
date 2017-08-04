<?php
namespace Modules\AppClient\Controller;
use Common\Controller\ModuleController;
use Think\Controller;
use Think\Page;

/**
 * app通知
 * Class IndexController
 * @package Modules\AppClient\Controller
 * @author
 * @time
 */
class AppNoticeController extends ModuleController {
    public function  index(){
        MK();
        $map  = array('status' => array('gt',-1));
        $list = $this->p_lists('AppNotice',$map,'create_time desc');
        $this->assign('list', $list);
        $this->meta_title = '推送记录';
        $this->_display();
    }

    /**
     * 删除数据
     */
    public function  del(){
        parent::editRow('AppNotice',array('status'=>-1));
    }

    /**
     * 添加或者修改
     */
    public function  add(){
        if(IS_POST){
            $title = $_POST['title'];
            $content = $_POST['content'];
            $url = $_POST['url'];
            $_POST['create_time'] = NOW_TIME;
            if(!$title || !$content){
                $this->error("标题或者内容不能为空！");
            }
            $Model  =   checkAttr(M('AppNotice'),"AppNotice");
            if($Model->create() === false){
                $this->error($Model->getError());
            }else{
                $type = 0;
                if($url){
                    $type=3;
                }
                if(push($title,$content,$type,array('url'=>$url))){
                    $Model->add();
                    $this->success("推送成功!",LK());
                }else{
                    $this->error("推送失败!");
                }
            }

        }else{
            $this->_display('add');
        }
    }

    public function edit(){
        if(IS_POST){
            $title = $_POST['title'];
            $content = $_POST['content'];
            $url = $_POST['url'];
            $_POST['create_time'] = NOW_TIME;
            $Model  =   checkAttr(M('AppNotice'),"AppNotice");
            if($Model->create() === false){
                $this->error($Model->getError());
            }else{
                if(push($title,$content,3,array('url'=>$url))){
                    $Model->add();
                    $this->error("推送成功!");
                }else{
                    $this->error("推送失败!");
                }
            }

        }else{
            $id = I('get.id');
            $res = M('AppNotice')->where(array('id'=>$id))->find();
            $this->assign('info',$res);
            $this->_display('edit');
        }
    }
}