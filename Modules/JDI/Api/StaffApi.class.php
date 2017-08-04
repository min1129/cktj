<?php
/**
 * Created by PhpStorm.
 * User: haoli
 * Date: 15/1/27
 * Time: 下午3:27
 */

namespace Modules\JDI\Api;

/**
 * staff 应该是 stuff //TODO 变更
 * 用户事务接口
 * 用户收藏(collect),点赞(like)
 * 以及对用户各种事务行为的统计<br/>
 * 投递简历附加bundle信息未简历id<br/>
 * @package Modules\Person\Api
 * @author lh
 * @time 2015-03-07 09:53:21
 */
class StaffApi {
    /**
     * 获取当前用户的事务记录
     * @param string $topic_table   如果是企业的话为company，如果是内容的话为对应内容模型的name
     * @param int $topic_id 记录id  如果是企业的话为企业资料的id(<span style="color:red">注意不是uid</span>),如果是内容为记录id
     * @param string $action 事务类型 收藏(collect),投递简历(resume),点赞(like) 默认为收藏
     * @param int $page 页数
     * @param int $page_size 页面大小
     * @param array $where 筛选条件
     * @param string $order 排序
     * @param int $width  图片压缩宽度 只有当width 和 height都不为0时才进行压缩
     * @param int $height 图片压缩高度
     * @return bool true为成功 false为失败
     */
    static public function staffs($topic_table,$topic_id=-1,$action="collect",$page=1,$page_size=10,$where=array(),$order='create_time DESC',$width=200,$height=100){
        $map['topic_table'] = $topic_table;
        $map['action'] = $action;
        if($topic_id != -1){
            $map['topic_id']  =$topic_id;
        }
        $map['uid'] = UID;
        if(is_string($where)){ //字符串查询
            $map["_string"] = $where;
        }else{
            $map = array_merge($map,$where);
        }
        api_page(array('total'=>D('UserStaff')->where($map)->field('topic_table,topic_id,id,bundle')->order($order)->count()));
        $result = D('UserStaff')->where($map)->field('topic_table,topic_id,id,bundle')->order($order)->page($page,$page_size)->select();
        $return = array();
        if(!$result){
            $str = "";
            if($action == "collect"){
                $str = "收藏";
            }else if($action=="like"){
                $str = "点赞";
            }
            if($page == 1){
                //api_msg("还没有".$str."记录哦,赶快去".$str."一个吧!");
            }else{
                api_msg("没有更多的".$str."记录喽");
            }
            return false;
        }else{
            if($topic_table == "company") {
                for($i=0;$i<count($result);$i++){
                    $return[] = PersonApi::company($result[$i]['topic_id'],'id');
                }
                return $return;
            }elseif($topic_table == "recruitment"){
                return RecruitmentApi::getRecruitmentByIds(array_column($result,'topic_id'));
            }else{
                return $result;
            }

        }
    }

    /**
     * 添加事务(<strong style="color:red">需要传递参数!</strong>)<br/>
     * 需要传递的参数:<br/>
     * topic_table  如果是企业的话为company，其他的操作就是其对应表名称，记住首字母要小写
     * topic_id    如果是企业的话为企业资料的id(<span style="color:red">注意不是uid</span>) 其他的操作就是其对应表字段的id，记住首字母要小写
     * action  collect为收藏事务,like为点赞事务,resume 为投递简历 其他待扩展
     * bundle(可选) 附加数据 投递简历的时候需要附加参数bundle代表投递的简历id
     * @return bool true为成功 false为失败
     */
    static public function addStaff(){
        $model = D('UserStaff');
        $_POST['uid']=UID;
        if($model->create() && ($res=$model->add())!==false){
            if($_POST['action'] == "collect"){
                M($_POST['topic_table'])->where(array('id'=>$_POST['topic_id']))->setInc('collect_num');
            }elseif($_POST['action'] == "like"){
                M($_POST['topic_table'])->where(array('id'=>$_POST['topic_id']))->setInc('like_num');
            }elseif($_POST['action'] == "resume"){
                M($_POST['topic_table'])->where(array('id'=>$_POST['topic_id']))->setInc('resume_num');
            }
            api_msg("操作成功!");
            return $res;
        }else{
            api_msg($model->getError());
            return false;
        }
    }

    /**
     * 删除事务
     * @param int $id 这里传的是事务的id
     * @return bool
     */
    static public function delStaff($id){
        $model = M('UserStaff');
        if($model->where(array("id"=>$id,"uid"=>UID))->delete()!==false){
            if($_POST['action'] == "collect"){
                M($_POST['topic_table'])->where(array('id'=>$_POST['topic_id']))->setDec('collect_num');
            }elseif($_POST['action'] == "like"){
                M($_POST['topic_table'])->where(array('id'=>$_POST['topic_id']))->setDec('like_num');
            }elseif($_POST['action'] == "resume"){
                M($_POST['topic_table'])->where(array('id'=>$_POST['topic_id']))->setDec('resume_num');
            }
            api_msg("删除成功!");
            return true;
        }else{
            api_msg($model->getError());
            return false;
        }
    }

    /**
     * 删除当前登录用户的指定事务<br/>
     * 比如要删除收藏企业则对应参数应该是:<br/>
     * "topic_table":"company","topic_id":"2","action":"collect"<br/>
     * 上面的topic_id为收藏企业资料的id<br/>
     * @param string $topic_table   如果是企业的话为company,如果是产品为production,帖子为tiba
     * @param int $topic_id 记录id 如果是企业的话为企业资料的id
     * @param string $action 事务类型
     * @return bool
     */
    static public function delStaffByTopic($topic_table,$topic_id,$action="collect"){
        $model = M('UserStaff');
        if($model->where(array("topic_table"=>$topic_table,"topic_id"=>$topic_id,"action"=>$action,"uid"=>UID))->delete()!==false){
            api_msg("删除成功!");
            if($_POST['action'] == "collect"){
                M($_POST['topic_table'])->where(array('id'=>$_POST['topic_id']))->setDec('collect_num');
            }elseif($_POST['action'] == "like"){
                M($_POST['topic_table'])->where(array('id'=>$_POST['topic_id']))->setDec('like_num');
            }
            return true;
        }else{
            api_msg($model->getError());
            return false;
        }
    }

    /**
     * 是否含有某个记录
     * @param string $topic_table   如果是企业的话为company
     * @param int $topic_id 记录id  如果是企业的话为企业资料的id(<span style="color:red">注意不是uid</span>)
     * @param string $action  collect为收藏事务,like为点赞事务,resume为投递简历
     * @return string
     */
    static public function hasStaff($topic_table,$topic_id,$action='collect',$where=array()){
        $map['topic_table'] = $topic_table;
        $map['topic_id'] = $topic_id;
        $map['action'] = $action;
        $map['uid']=UID;
        $map = array_merge($map,$where);
        $result = M('UserStaff')->where($map)->count();
        if($result !== false){
            if($result>0){
                return 1;
            }else{
                return 0;
            }
        }else{
            api_msg("查询出错");
            return false;
        }
    }

    /**
     * 得到某个主题的事务数 <br/>
     * @param string $topic_table   如果是企业的话为company
     * @param int $topic_id 记录id 如果是企业的话为企业资料的id
     * @param string $action 事务类型
     * @param array|string where 筛选条件
     * @return mixed
     */
     static  public function staffNum($topic_table,$topic_id,$action='collect',$where=array()){
         $map['topic_table'] = $topic_table;
         $map['topic_id'] = $topic_id;
         $map['action'] = $action;
         if(is_string($where)){ //字符串查询
             $map["_string"] = $where;
         }else{
             $map = array_merge($map,$where);
         }
        return M('UserStaff')->where($map)->count();
    }
} 