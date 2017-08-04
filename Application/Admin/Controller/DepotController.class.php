<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Common\Controller\AdminController;
/**
 * 仓库管理
 * 管理仓库，增删改查等功能
 * @author min
 */
class DepotController extends AdminController {

    /**
     * 链接
     * @author lihao 修改<lh@tiptime.com>
     */
    public function index(){
        $map['status'] = array('gt',-1);

	    $model = M('Depot');

        $list = $this->lists($model,$map);
        int_to_string($list);
        // 记录当前列表页的cookie
        MK();
        $this->assign('list', $list);
        $this->meta_title = '仓库列表';
        $this->display();
    }

    /**
     * 新增链接
     * @author lihao 修改<lh@tiptime.com>
     */
    public function add(){
       $id = I('get.id');

	    dump($id);exit();
	    if(IS_POST){

	    }else{
		    if($id){

		    }else{

		    }


	    }


    }

    /**
     * 编辑链接
     * @author lihao <lh@tiptime.com>
     * @param int $id
     */
    public function edit($id = 0){
        if(IS_POST){
            $Menu = D('Link');
            $data = $Menu->create();
            if($data){
                if($Menu->save()!== false){
                    S('sys_link_list', null);
                    $this->success('更新成功',LK());
                } else {
                    $this->error('更新失败');
                }
            } else {
                $this->error($Menu->getError());
            }
        } else {
            $info = D('Link')->where(array('id'=>$id))->find();
            if(false === $info){
                $this->error('获取链接信息错误');
            }
            if($info['picture_id']){
                $info['path'] = get_cover_path($info['picture_id']);
            }

            $this->assign('groups', C('LINK_GROUP'));
            $this->assign('info', $info);
            $this->meta_title = '编辑链接';
            $this->display();
        }
    }

    /**
     * 链接状态修改
     * @author lihao <lh@tiptime.com>
     */
    public function changeStatus($method=null){
        $id = array_unique((array)I('ids',0));
        $id = is_array($id) ? implode(',',$id) : $id;

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] =   array('in',$id);
        switch ( strtolower($method) ){
            case 'forbid':
                $this->forbid('Link', $map );
                break;
            case 'resume':
                $this->resume('Link', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }

    /**
     * 删除链接
     * @author lihao <lh@tiptime.com>
     */
    public function del(){
        $id = array_unique((array)I('ids',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(M('Link')->where($map)->delete()){
            S('sys_link_list', null);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }



    /**
     * 链接排序
     * @author lihao <lh@tiptime.com>
     */
    public function sort(){
        if(IS_GET){
            $ids = I('get.ids');
            $map['id'] = array(in,$ids);
            $list = M('Link')->where($map)->field('id,name')->order('sort asc,id asc')->select();
            $this->assign('list', $list);
            $this->meta_title = '链接排序';
            $this->display();
        }elseif (IS_POST){
            $ids = I('post.ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$value){
                $res = M('Link')->where(array('id'=>$value))->setField('sort', $key+1);
            }
            if($res !== false){
                S('sys_link_list', null);
                $this->success('排序成功！');
            }else{
                $this->eorror('排序失败！');
            }
        }else{
            $this->error('非法请求！');
        }
    }

}
