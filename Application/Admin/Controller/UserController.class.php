<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Common\Controller\AdminController;
use Think\Model;
/**
 * 后台用户控制器
 * @author lihao<lh@tiptime.com>
 */
class UserController extends AdminController {

    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $nickname       =   I('nickname');
        $map['status']  =   array('egt',0);
        if(is_numeric($nickname)){
            $map['id']=   array('id'=>$nickname);
        }else if(isset($nickname)){
            $map['nickname']    =   array('like', '%'.(string)$nickname.'%');
        }
        $model =D('Member');
        $list   = $this->lists($model, $map,'id asc');
        int_to_string($list);
        $group = D('AuthGroup')->getGroups();
        
        foreach($list as $k=>$v){
        	$num=ltrim($v['username'],substr($v['username'],0,1));
        	$res=M('tree')->where('id='.$num)->find();
        	if($res['pid'] && $res['pid']!=503){
        		$p=M('tree')->where('id='.$res['pid'])->find();
        		$list[$k]['parent']=$p['name'];
        	}
        }
        $this->assign('group',$group);
        $this->assign('_list', $list);
        $this->meta_title = '用户信息';
        $this->display();
    }

    public function  setUserGroup(){
        $uid =  I('post.uid',0);
        $groups = array_unique((array)I('groups_id',0));
        if(is_numeric($uid) && $uid>0){
            if ( is_administrator($uid) ) {
                $this->error('该用户为超级管理员');
            }
            $model = M('Member');
            if( $model->where(array('id'=>$uid))->count()<=0 ){
                $this->error('用户不存在');
            }
            $model_access = D('AuthGroup');
            //删除原有分组
            $_REQUEST['batch'] = true;

            if($model_access->addToGroup($uid,$groups)){
                $this->success('分组设置成功!');
            }else{
                $this->error($model_access->getDbError());
            }

        }else{
            $this->error('参数非法!');
        }
    }
    /**
     * 用户行为列表
     * @author huajie <banhuajie@163.com>
     */
    public function action(){
        //获取列表数据
        $Action =   M('Action')->where(array('status'=>array('gt',-1)));
        $list   =   $this->lists($Action);
        int_to_string($list);
        // 记录当前列表页的cookie
        MK();

        $this->assign('_list', $list);
        $this->meta_title = '用户行为';
        $this->display();
    }

    /**
     * 新增行为
     * @author huajie <banhuajie@163.com>
     */
    public function addAction(){
        $this->meta_title = '新增行为';
        $this->assign('data',null);
        $this->display('editaction');
    }

    /**
     * 编辑行为
     * @author huajie <banhuajie@163.com>
     */
    public function editAction(){
        $id = I('get.id');
        empty($id) && $this->error('参数不能为空！');
        $data = M('Action')->field(true)->find($id);

        $this->assign('data',$data);
        $this->meta_title = '编辑行为';
        $this->display();
    }

    /**
     * 更新行为
     * @author huajie <banhuajie@163.com>
     */
    public function saveAction(){
        $res = D('Action')->update();
        if(!$res){
            $this->error(D('Action')->getError());
        }else{
            $this->success($res['id']?'更新成功！':'新增成功！', LK());
        }
    }

    /**
     * 会员状态修改
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function changeStatus($method=null){
        $id = array_unique((array)I('id',0));
        if( in_array(C('USER_ADMINISTRATOR'), $id)){
            $this->error("不允许对超级管理员执行该操作!");
        }
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] =   array('in',$id);
        switch ( strtolower($method) ){
            case 'forbiduser':
                $this->forbid('Member', $map );
                break;
            case 'resumeuser':
                $this->resume('Member', $map );
                break;
            case 'deleteuser':
                $this->delete('Member', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }

    public function add($username = '', $password = '', $nickname='' ,$repassword = '', $email = ''){
        if(IS_POST){
            /* 检测密码 */
            if($password != $repassword){
                $this->error('密码和重复密码不一致！');
            }
            if($nickname === ''){
                $nickname = $username;
            }

            $member = D('Member');
            $uid    =   $member->register($username, $password,$nickname,0,$status=1); //后台用户模块添加默认是管理员
            if(0 < $uid){ //注册成功
                $this->success('用户添加成功！',U('index'));
            } else { //注册失败，显示错误信息
                $this->error(\Common\Model\MemberModel::showRegError($uid));
            }
        } else {
            $this->meta_title = '新增用户';
            $this->display();
        }
    }

}
