<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\BaseController;
use Common\Model\MemberModel;

/**
 * 后台首页控制器
 * @author lihao<lh@tiptime.com>
 */
class PublicController extends BaseController {
    public function index(){
        $this->display();
    }
    /**
     * 后台用户登录
     * @author lihao<lh@tiptime.com>
     */
    public function login($username = null, $password = null, $verify = null){
        if(IS_POST){
            /* 检测验证码 TODO: */
        	/* if(cookie('username') == $username)
        	{
        		$this->error('同时每次登陆间隔需大于20秒');
        	} */
            if(!check_verify($verify)){
                $this->error("验证码输入错误");
            }
            $usn=M('member')->where(array('username'=>$username))->find();
            if($usn && $usn['type']!=0){
            	$this->error("没有登录权限");
            }
            $Member = D('Member');
            $uid = $Member->checkLogin($username, $password,1,false);
            if(0 < $uid){
                /* 登录用户 */
                if($Member->login($uid)){ //登录用户
                    //TODO:跳转到登录前页面
                    $this->success("登录成功!",U("Admin/index/index"));
                } else {
                    $this->error("登录失败！");
                }
            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
                
            }
        } else {
            if(is_login()){
                $this->redirect('Admin/Index/index');
            }else{
                $this->display();
            }
        }
    }

    /**
     * 企业用户注册
     * @author lihao<lh@tiptime.com>
     */
    public function register($email,$username,$nickname,$password,$re_password){
         if($email && $username && $nickname && $password && $re_password){
             if(!regex($email,'email')){
                 $this->error("email格式不正确！");
             }
             /* 检测密码 */
             if($password != $re_password){
                 $this->error('密码和重复密码不一致！');
             }
             $member = D('Member');
             $uid    =   $member->register($username, $password,$nickname,1,$status=1,$email); //添加企业
             if(0 < $uid){ //注册成功
                 D('AuthGroup')->addToGroup($uid,13);//添加分组权限
                 if($member->login($uid)){ //登录
                     //TODO:跳转到登录前页面
                     $this->success("注册成功!");
                 } else {
                     $this->error("注册成功!请人工登录");
                 }
             } else { //注册失败，显示错误信息
                 $this->error(MemberModel::showRegError($uid));
             }
         }else{
             $this->error("填写信息不全!");
         }
    }

    /* 退出登录 */
    public function logout(){
        if(is_login()){
            session('[destroy]');
            $this->success('退出成功！', U('Index/index'));
        } else {
            $this->redirect('login');
        }
    }

    public function verify(){
        $verify = new \Think\Verify(array('length'=>4,'codeSet'=>'23456789','fontSize'  =>  12,'useCurve'  =>  false,'useNoise'  =>  false));
        $verify->entry(1);
    }
	
	public function phone_verify(){
        $verify = new \Think\Verify(array('length'=>4,'codeSet'=>'23456789','fontSize'  =>  12,'useCurve'  =>  false,'useNoise'  =>  false));
        $verify->entry(2);
    }
    /**
     * @author lihao<lh@tiptime.com>
     */
    public function getPasswordBack(){
        $email = I('post.email');
        if(empty($email)){
            $this->error("邮箱不能为空!");
        }
        if(!regex($email,'email')){
            $this->error("邮箱格式不对!");
        }
        $model = M("Member");
        $user = $model->where(array("email"=>$email))->find();
        if(!$user){
            $this->error("该邮箱还未注册!");
        }else{
            $token = think_encrypt($user['id'].','.$user['password'].','.NOW_TIME); //token
            $url  =U('resetPassword?token='.$token,'',true, true);
            $body  = "<h1>时代科技</h1>"."您提交了找回密码请求。请点击下面的链接重置密码
（按钮1分钟内有效）。<br/><a href='".$url."'target='_blank'>".$url."</a>";
            if(jdi_send_mail($email,$user['nickname'],"[佰邦科技]找回密码",$body)){
                $this->success("找回密码邮件已经发送,请注意查收！");
            }else{
                $this->error("操作失败!未知错误");
            }
        }
    }

    public function resetPassword($token){
        $param = str2arr(think_decrypt($token));
        $user = null;
        $model = D("Member");
        if(count($param) != 3){
            exit('illegal request');
        }else{
            $uid = $param[0];
            $password = $param[1];
            $time = $param[2];
            if(NOW_TIME - $time > 6000){ // 超过一分钟
                exit('request failure(cause time out)');
            }else{
                $user = $model->where(array("id"=>$uid,"password"=>$password))->find();
                if(!$user){
                    exit('User does not exist');
                }
            }
        }
        if(IS_POST){
            $password = I('post.password');
            $re_password = I('post.re_password');
            if(empty($password)){
                $this->error('数据不能为空!');
            }
            if($password != $re_password){
                $this->error('密码和重复密码不一致！');
            }
            $data['password'] = $password;
            $data['id'] = $user['id'];
            if($model->create($data) && $model->save() !== false){
                $this->success("修改成功!",U("index/index"));
            }else{
                $this->error(MemberModel::showRegError($model->getError()));
            }

        }else{
            if(empty($token)){
                $this->display("404!");
            }else{
                $this->assign("token",$token);
                $this->display("password_back");
            }
        }
    }
}
