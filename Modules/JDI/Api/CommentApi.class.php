<?php
namespace Modules\JDI\Api;
/**
 * 评论接口文档
 * @package Modules\Person\Api
 * @author lh
 * @time 2015-03-07 09:49:10
 */
class CommentApi {
    /**
     * 获取评论
     * @param string $topic_table  评论表 如果是评论企业的话为company
     * @param int $topic_id 评论记录id 如果是评论企业的话为企业资料的id
     * @param int $page 页数
     * @param int $page_size 页面大小
     * @param array $where 筛选条件
     * @param string $order 排序
     * @return bool true为成功 false为失败
     */
    static public function comments($topic_table,$topic_id,$page=1,$page_size=10,$where=array(),$order='create_time DESC'){
        $map['topic_table'] = $topic_table;
        $map['topic_id']  =$topic_id;
        if(is_string($where)){ //字符串查询
            $map["_string"] = $where;
        }else{
            $map = array_merge($map,$where);
        }

        $model  = D('Comment')->where($map)->field('content,uid,id,create_time')->order($order);
        $model->page($page,$page_size);
        $result = $model->select();
        if(!$result){
            if($page == 1){
                api_msg("还没有评论呢,赶快来评论一个吧!");
            }else{
                api_msg("没有更多评论了");
            }
            return false;
        }else{
            for($i=0;$i<count($result);$i++){
                $user = get_user_filed($result[$i]['uid']);
                $result[$i]['user_name'] = $user['nickname'];
                $result[$i]['user_head'] = get_cover_path($user['head']);
                $result[$i]['create_time'] = formatTime($result[$i]['create_time']);
            }
            return $result;
        }
    }

    /**
     * 添加评论(<strong style="color:red">需要传递参数!</strong>)<br/>
     * 需要传递的参数:<br/>
     * topic_table 要评论的表 如果是评论企业的话为company
     * topic_id 要评论的记录id  如果是评论企业的话为企业资料的id
     * uid 评论人的id<br/>
     * content 评论内容<br/>
     * bundle 扩展数据 可以是评分等信息，是企业评论的话此字段表示评分
     * @return bool true为成功 false为失败
     */
    static public function addComment(){
        if(UID <= 0){
            api_msg("用户尚未登录!");
            return false;
        }
        $model = D('Comment');
        $_POST['uid'] = UID;
        if($model->create()){
            $result = $model->add();
            if($result){
                api_msg("评论成功!");
                M($_POST['topic_table'])->where(array('id'=>$_POST['topic_id']))->setInc('comment_num');
                if($_POST['topic_table'] == 'company'){
                    $bundle = $_POST['bundle'];
                    if(is_numeric($bundle) && $bundle>0 ){
                        M('company')->where(array('id'=>$_POST['topic_id']))->setInc('score',$bundle);
                    }else if(is_numeric($bundle) && $bundle<0){
                        M('company')->where(array('id'=>$_POST['topic_id']))->setDec('score',$bundle);
                    }
                }
                return $result;
            }else{
                return false;
            }
        }else{
            api_msg($model->getError());
            return false;
        }
    }

    /**
     * 修改评论(<strong style="color:red">需要传递参数!</strong>)<br/>
     * 需要传递的参数:<br/>
     * id 修改的记录id
     * 其他参数可选 详情见addComment接口说明
     * @return bool true为成功 false为失败
     */
    static public function editComment(){
        if(UID <= 0){
            api_msg("用户尚未登录!");
            return false;
        }
        $model = D('Comment');
        if($model->create() && $model->save()!==false){
            api_msg("修改成功!");
            return true;
        }else{
            api_msg($model->getError());
            return false;
        }
    }

    /**
     * 得到某个主题的评论数 <br/>
     * @param string $topic_table  评论表 如果是评论企业的话为company
     * @param int $topic_id 评论记录id 如果是评论企业的话为企业资料的id
     * @return mixed
     */
     static  public function commentNum($topic_table,$topic_id){
         if(empty($topic_id)){
             return 0;
         }
        $map['topic_table'] = $topic_table;
        $map['topic_id'] = $topic_id;
        return D('Comment')->where($map)->count(); //评论数量,不包括回复数量
    }

    /**
     * 是否能进行企业评价
     */
    static public function canCommentCompany(){
        $diff_time = NOW_TIME - 3600*24*7;//7天进行评价
        $result = M('UserStaff')->where(array('topic_table'=>'recruitment','action'=>'resume','uid'=>UID,'status'=>5,
            'update_time'=>array('lt',$diff_time)))->find();//找出所有可以进行评价的投递记录
        if($result){
            $pre = C('DB_PREFIX');
            $com =  M('Recruitment')->alias('r')
                ->join($pre.'company c ON r.uid=c.uid')
                ->where(array('r.id'=>$result['topic_id']))
                ->field('c.name,c.uid')
                ->find();
            $data['uid'] = $com['uid'];
            $data['id'] = $result['id'];
            $data['com_name']=$com['name'];
            return $data;
        }else{
            return false;
        }
    }



    /**
     * 添加企业评价
     * @param int $id 投递记录的id
     * @param int $uid 企业的uid
     * @param int $score 评分
     * @param string $detail 评价
     * @return  array
     */
    static public function addCompanyComment($id,$uid,$score,$detail){
        $score_array = array(-2,0,5);
        $can = self::canCommentCompany();
        if(in_array($score,$score_array) && $detail && $can['uid']==$uid && $can['id']==$id){
            $data['bundle']=$score;
            $data['content'] = $detail;
            $data['topic_table']= 'member';
            $data['topic_id'] = $uid;
            $data['uid'] = UID;
            $data['create_time'] = NOW_TIME;
            $data['update_time'] = NOW_TIME;
            if(M('Comment')->add($data) !== false){
                M('Company')->where(array('uid'=>$uid))->setInc('comment_num');
                $data2['status'] = 6;
                $data2['extra'] = '5,6';
                $data2['uid'] = UID;
                M('UserStaff')->where(array('id'=>$id))->save($data2);
                return true;
            }else{
                api_msg("添加失败！系统发生错误.");
                return false;
            }

        }else{
            api_msg("参数非法!!");
            return false;
        }
    }
} 