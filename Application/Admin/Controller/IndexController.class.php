<?php
namespace Admin\Controller;
use Common\Controller\AdminController;
use Think\Controller;

/**
 * 后台首页
 * Class IndexController
 * @package Admin\Controller
 * @author lihao <lh@tiptime.com>
 */
class IndexController extends AdminController {
    public function index(){
        $this->assign('__INDEXCLASS__','active');
        $this->display();
    }

    /**
     * 修改昵称初始化
     * @author lihao <lh@tiptime.com>
     */
    public function updateNickname(){
        if(IS_POST){
            //获取参数
            $nickname = I('post.nickname');
            $password = I('post.password');
            empty($nickname) && $this->error('请输入昵称');
            empty($password) && $this->error('请输入密码');
            $Member =   D('Member');

            //密码验证
            $uid    =   $Member->checkLogin(UID, $password, 4);
            ($uid == -2) && $this->error('密码不正确');
            $data   =   $Member->create(array('nickname'=>$nickname,'password'=>$password));
            if(!$data){
                $this->error($Member->getError());
            }

            $res = $Member->where(array('id'=>$uid))->save($data);

            if($res){
                $this->success('修改昵称成功！');
            }else{
                $this->error('修改昵称失败！');
            }
        }else{
            $this->meta_title = '修改昵称';
            $this->display();
        }
    }

    /**
     * 修改密码初始化
     * @author lihao <lh@tiptime.com>
     */
    public function updatePassword(){
        if(IS_POST){
            //获取参数
            $password   =   I('post.old');
            empty($password) && $this->error('请输入原密码');
            $data['password'] = I('post.password');
            empty($data['password']) && $this->error('请输入新密码');
            $repassword = I('post.repassword');
            empty($repassword) && $this->error('请输入确认密码');
            if($data['password'] !== $repassword){
                $this->error('您输入的新密码与确认密码不一致');
            }
            $member = D('Member');
            $res    =   $member->updateInfo(UID, $password, $data);
            if($res['status']){
                $this->success('修改密码成功！');
            }else{
                $this->error($res['info']);
            }
        }else{
            $this->meta_title = '修改密码';
            $this->display();
        }
    }
}