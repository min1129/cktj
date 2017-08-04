<?php
/**
 * Created by PhpStorm.
 * User: haoli
 * Date: 15/1/27
 * Time: 下午3:27
 */

namespace Modules\JDI\Api;
use Modules\JDI\Api\StaffApi;
/**
 * 此文档提供功能<br/>
 * 简历相关信息
 * 投递简历staff状态对应含义:<br/>
 * 0:代表学生已经投递等待企业处理的简历
 * 1:代表企业已发送面试通知等待学生处理的简历
 * 2:代表已经面试完成后等待企业处理的简历
 * 3:代表企业发送完录取通知等待学校处理的简历
 * 4:代表学校通过审核，等待学生同意
 * 5:代表学生同意录用
 * 6:代表学生完成企业评价
 * -1:代表实效的简历投递，这可能包括任何一个环节的拒绝操作，或者超过设定时间未处理的简历
 * -2:代表简历投递失效，学生已被其他企业录取
 * -3:3天未处理的简历投递，自动设置为失效
 * @package Modules\Person\Api
 * @author lh
 * @time 2015-03-07 09:52:02
 */
class ResumeApi {
    /**
     * 简历操作状态机
     * 比如 1=>array('action'=>array(array('next_status'=>'2','tip'=>'确认'),array('next_status'=>'-1','tip'=>'拒绝')),'type'=>2)
     * 1代表当前投递状态后面的数组包含了当前状态可以进行的操作以及操作的权限
     * type表示操作权限1代表只有企业能操作，2代表只有学生能操作，－1代表学校审核（一般不会用到这个，后台会特殊处理的）
     * action 代表操作数组
     * next_status 表示改操作之后的状态
     * tip 是操作的提示信息
     * @var array
     */
    public static  $resume_op= array(0=>array('action'=>array(array('next_status'=>'1','tip'=>'发送面试'),array('next_status'=>'3','tip'=>'发送录取'),array('next_status'=>'-1','tip'=>'拒绝')),'type'=>1),
        1=>array('action'=>array(array('next_status'=>'2','tip'=>'确认'),array('next_status'=>'-1','tip'=>'拒绝')),'type'=>2),
        2=>array('action'=>array(array('next_status'=>'3','tip'=>'录取'),array('next_status'=>'-1','tip'=>'不录取')),'type'=>1),
        3=>array('action'=>array(array('next_status'=>'4','tip'=>'通过'),array('next_status'=>'-1','tip'=>'不通过')),'type'=>-1),
        4=>array('action'=>array(array('next_status'=>'5','tip'=>'同意'),array('next_status'=>'-1','tip'=>'不同意')),'type'=>2));

    /**
     * 状态机的边
     * 主要是状态转移的时候的关联操作
     * tip是提示文字，function是后续操作的函数（在_zm 里面定义的）type代表这谁能执行这个操作1代表企业，2代表学校
     * @var array
     */
    //2016-8-16 12:17 企业发送面试不需要学生确认 直接跳到企业是否录用该学生 edge 0,2
    public static $resume_staff_notice = array(
        '0,1'=>array('tip'=>'用人单位发送面试通知','function'=>'resume_status0to1','type'=>1),
    	'0,2'=>array('tip'=>'用人单位发送面试通知','function'=>'resume_status0to2','type'=>1),
        '0,3'=>array('tip'=>'用人单位直接发送录取通知','function'=>'resume_status0to3','type'=>1),
        '0,-1'=>array('tip'=>'用人单位拒绝了简历投递','function'=>'resume_status0to_1','type'=>1),
        '1,2'=>array('tip'=>'学生确认面试通知','function'=>'resume_status1to2','type'=>2),
        '1,-1'=>array('tip'=>'学生拒绝面试通知','function'=>'resume_status1to_1','type'=>2),
        '2,3'=>array('tip'=>'用人单位录取学生','function'=>'resume_status2to3','type'=>1),
        '2,-1'=>array('tip'=>'用人单位拒绝学生','function'=>'resume_status2to_1','type'=>1),
        '3,4'=>array('tip'=>'学校审核通过','function'=>'resume_status3to4','type'=>-1),
        '3,-1'=>array('tip'=>'学校审核拒绝','function'=>'resume_status3to_1','type'=>-1),
        '4,5'=>array('tip'=>'学生同意录用','function'=>'resume_status4to5','type'=>2),
        '4,-1'=>array('tip'=>'学生拒绝录用','function'=>'resume_status4to_1','type'=>2),
        '5,6'=>array('tip'=>'录取成功,学生完成用人单位评价','function'=>'resume_status4to5','type'=>-1)
    );


    /**
     * 不同的投递状态的提示文字
     * @var array
     */
    //2016-8-16 12:17 企业发送面试不需要学生确认 直接跳到企业是否录用该学生 edge 0,2
    public static $resume_good_notice =array(
        'start'  =>array('tip'=>'学生投递简历'),
        '0,1'=>array('tip'=>'用人单位发送面试通知'),
    	'0,2'=>array('tip'=>'用人单位发送面试通知'),
        '0,3'=>array('tip'=>'用人单位直接发送录取通知'),
        '0,-1'=>array('tip'=>'用人单位拒绝了简历投递'),
        '1,2'=>array('tip'=>'学生确认面试通知'),
        '1,-1'=>array('tip'=>'学生拒绝面试通知'),
        '2,3'=>array('tip'=>'用人单位发送录取通知'),
        '2,-1'=>array('tip'=>'用人单位拒绝学生'),
        '3,4'=>array('tip'=>'学校审核通过'),
        '3,-1'=>array('tip'=>'学校审核拒绝'),
        '4,5'=>array('tip'=>'学生同意录用'),
        '4,-1'=>array('tip'=>'学生拒绝录用'),
        '5,6'=>array('tip'=>'录取成功,学生完成用人单位评价')
    );


    /**
     * 简历失效提示
     * 小于或者等于-2都表示失效
     * @var array
     */
    /* public static $resume_staff_invalid= array(
        -2=>array('已失效,学生已接受其他录取通知','已失效，你已经确认了其他企业的录取通知'),
        -3=>array('已失效,3天未处理','已失效，3天未处理')
    ); */
    public static $resume_staff_invalid= array(
    		-2=>array('已失效,学生已接受其他录取通知','已失效，你已经确认了其他单位的录取通知'),
    		-3=>array('已失效,被撤回','已失效，被撤回')
    );


    /**
     * 简历投递状态的文本提示
     * 这里是对学生的提示
     * @var array
     */
    public static $resume_staff_status_text1 = array("0"=>"待查看","1"=>"面试通知待确认",
        "2"=>"等待用人单位的面试结果","3"=>"用人单位同意录取，等待学校审核","4"=>"待确认录取","5"=>"成功录取",'6'=>"录取成功,已完成对单位的评价");

    /**
     * 简历投递状态的详细文本提示
     * 这里是对学生的提示
     * @var array
     */
    public static $resume_staff_status_detail1 = array("0"=>"待查看","1"=>"是否确认该面试通知？(如果拒绝该简历投递行为视为无效)",
        "2"=>"等待用人单位处理面试结果...","3"=>"用人单位同意录取，等待学校审核","4"=>"学校通过了您的录取审核，是否确认接受单位的录取","5"=>"已成功录取",'6'=>"录取成功,已完成对单位的评价");
    /**
     * 简历投递状态的文本提示
     * 这里是对企业的提示
     * @var array
     */
    public  static $resume_staff_status_text2 = array("0"=>"等待查看","1"=>"面试通知待确认",
        "2"=>"处理面试结果","3"=>"等待学校审核","4"=>"学校审核通过，等待学生确认录取","5"=>"成功录取",'6'=>"录取成功,已完成对单位的评价");

    /**
     * 简历投递状态的详细文本提示
     * 这里是对企业的提示
     * @var array
     */
    public static $resume_staff_status_detail2 = array("0"=>"等待用人单位查看简历","1"=>"等待学生确认面试通知",
        "2"=>"请处理对学生的面试结果","3"=>"同意录取，等待学校审核","4"=>"学校审核通过，等待学生确认录取","5"=>"成功录取",'6'=>"录取成功,已完成对单位的评价");

    /**
     *获取某个学生的简历
     * 返回数据
     * string name 学生姓名<br/>
     * birthday int 出生日期(时间戳)<br/>
     * school int 所在学校<br/>
     * graduation_time int 毕业时间<br/>
     * evaluation string 自我评价<br/>
     * intension string 求职意向<br/>
     * create_time int 创建时间<br/>
     * update_time int 更新时间<br/>
     * head int 头像(图片)
     * @return array
     */
    public static function getResumes(){
        $uid = UID;
        $result = M('Resume')->where(array('uid'=>$uid,'status'=>1))->select();
        if($result){
            for($i=0;$i<count($result);$i++){
                $result[$i]['head_path'] = get_cover_path($result[$i]['head']);
                $result[$i]['evaluation'] = htmlspecialchars_decode($result[$i]['evaluation']);
                $result[$i]['intension'] = htmlspecialchars_decode($result[$i]['intension']);
                $file=M('file')->where(array('id'=>$result[$i]['file']))->find();
                $result[$i]['file']=$file['name'];
                $result[$i]['is_lock'] = self::resumeIsLock($result[$i]['id']); //是否被锁定
            }
        }else{
            api_code(1);
            return false;
        }
        return $result;
    }

    /**
     * 获取简历投递的时间轴<br/>
     * 返回数据:<br/>
     * [{'tip':"xxx","reached":1,'time'=>14121212},{'tip':"xxx","reached":1,'time'=>14121212}]<br/>
     * tip为当前状态的提示<br/>
     * reached表示当前环节是否完成,1表示已经完成，0表示还未完成<br/>
     * time 表示完成时间，如为完成此字段为0
     * @param string $id
     * @return array
     */
    public static function getResumeTimeLine($id,$userid=-1){
        $notice = M('Notice')->where(array('flag'=>1,'_string'=>"bundle LIKE '{$id}|%'"))->order('create_time desc')->select();
        $notice_count = count($notice);
        $timeLines = array();
        $has_eg = array(); //去重，判断数组

        for($i=0;$i<$notice_count;$i++){
            $eg = str2arr($notice[$i]['bundle'],'|',1);
            if(!in_array($eg,$has_eg)){
                $timeLines[] = array('tip'=>self::$resume_good_notice[$eg]['tip'],'time'=>$notice[$i]['create_time']);
                $has_eg[] = $eg;
            }
        }
        $list=self::getResumeRecord($id,1,10,0,$userid);
        $info = $list[0];
        if($info['status'] == -3){
        	$newlines=array(array('tip'=>$info['status_text'],'time'=>$info['update_time']));
        	foreach($timeLines as $k=>$v){
        		$newlines[]=$v;
        	}
        }
        return $newlines?$newlines:$timeLines;
    }

    /**
     * 处理意见超时失效的简历投递记录
     */
    private static function processInvalid(){
        $ex_time =NOW_TIME -  60*60*24*3; //3天过期
        $model = M('UserStaff');
        $res =$model
            ->where(array("_string"=>"`topic_table`='recruitment' AND `action`='resume' AND `status`>-1 AND `status` < 5 AND `update_time` < {$ex_time}"))
            ->select();
        if($res){
            for($i=0;$i<count($res);$i++){
                $data = $res[$i];
                $data['extra'] = $data['status'].',-3';
                $data['status'] = -3;
                $model->save($data);
            }
        }
    }

    /**
     * 撤回操作
     * @param int $id 列表id;
     */
    public static function doProcessInvalid($id){
    	$model = M('UserStaff');
    	$res=$model->where(array('id'=>$id))->find();
    	if($res){
    		$data = $res;
    		/* if($data['status']==4){
    			M('lq_staff')->where(array('staff_id'=>$id))->delete();
    		} */
    		if($data['status']==-1){
    			if($resume_good_notice[$data['extra']]){
    				api_msg($resume_good_notice[$data['extra']]);
    			}else{
    				api_msg('操作失败！');
    			}
    			return false;
    		}
    		$data['extra'] = $data['status'].',-3';
    		$data['status'] = -3;
    		$data['update_time']=time();
    		$info=$model->save($data);
    		if($info){
    			api_msg('操作成功');
    			return $info;
    		}
    		else{
    			api_msg('操作失败！');
    			return false;
    		}
    	}
    }
    /**
     * 获得当前用户的投递记录
     * @param int $id 获得特定投递记录  不写则是获得所有
     * @param int $page 获得特定投递记录  不写则是获得所有
     * @param int $page_size 获得特定投递记录  不写则是获得所有
     * @param int $type
     * @return mixed
     */
    public static function getResumeRecord($id=-1,$page=1,$page_size=10,$type=0,$userid=-1){
        //self::processInvalid();
        $pre = C('DB_PREFIX');
        $uid = UID?UID:$userid;
        $type = user_field('type');
        $query_str = "u.topic_table='recruitment' AND u.action='resume' AND u.uid={$uid} ";
        if($id != -1){
            $query_str .= " AND u.id={$id}";
        }else{
            $hide_key = 'resume_hide_'.UID; //用户删除（不是删除）的记录，存储的数据库的map表.
            $hide = db_map($hide_key);
            $query_str .=  $hide?" AND u.id not in ({$hide}) ":"";
        }
        //手机分页
        if($type!=0){
        	$total =  M('UserStaff')->alias('u')
        	->join($pre.'cresume r ON u.bundle=r.id','left')
        	->join($pre.'recruitment rt ON u.topic_id=rt.id','left')
        	->join($pre.'company c ON c.uid=rt.uid','left')
        	->field('u.*,r.title as resume_title,rt.title as r_title,c.name as c_title')
        	->where(array('_string'=>$query_str))
        	->order('u.update_time desc')
        	->count();
        	api_page($total);
        	$res =  M('UserStaff')->alias('u')
        	->join($pre.'cresume r ON u.bundle=r.id','left')
        	->join($pre.'recruitment rt ON u.topic_id=rt.id','left')
        	->join($pre.'company c ON c.uid=rt.uid','left')
        	->field('u.*,r.title as resume_title,rt.title as r_title,c.name as c_title,c.company_attr as com_attr,c.uid as c_uid')
        	->where(array('_string'=>$query_str))
        	->order('u.update_time desc')
        	->page($page,$page_size)
        	->select();
        }else{
        	
        	$res =  M('UserStaff')->alias('u')
        	->join($pre.'cresume r ON u.bundle=r.id','left')
        	->join($pre.'recruitment rt ON u.topic_id=rt.id','left')
        	->join($pre.'company c ON c.uid=rt.uid','left')
        	->field('u.*,r.title as resume_title,rt.title as r_title,c.name as c_title,c.company_attr as com_attr,c.uid as c_uid')
        	->where(array('_string'=>$query_str))
        	->order('u.update_time desc')
        	->select();
        	
        }
        
        for($i=0; $i<count($res);$i++){
            //获得状态－1的状态信息
            self::getStatusText(0,$res[$i],$id);
            $op = self::$resume_op[$res[$i]['status']];
            if($op['type'] == $type){
                unset($op['type']);
                $res[$i]['op'] = $op;
            }
        }
        for($i=0;$i<count($res);$i++){
        	switch($res[$i]['status']){
        		case -1:
        		case -2:
        		case -3:
        		case 1:
        		case 3:
        		case 4:
        		case 5:
        		case 2:
        			/* $res[$i]['display']=0; */
        			break;
        		case 0:
        				if($res[$i]['op']['action']){
            				array_push($res[$i]['op']['action'],array('next_status'=>'-3','tip'=>'撤回'));
            			}
            			else{
            				$res[$i]['op']=array('action'=>array(array('next_status'=>'-3','tip'=>'撤回')));
            			}
        			break;
        	}
        	$res[$i]['url']=__ROOT__.'/index.php/Home/Index/recruitmentDetailTimeline/id/'.$res[$i][id].'/uid/'.$uid;
        	$collect=M('user_staff')->where(array('uid'=>$uid,'topic_id'=>$res[$i]['topic_id'],'action'=>'collect'))->find();
        	//return M('user_staff')->getLastSql();
        	$res[$i]['is_collect'] = $collect?1:0;
        }
        /* var_dump($res);
        exit; */
        if($res){
            return $res;
        }else{
            //api_msg("暂无投递记录!");
            return false;
        }
    }

    /**
     * 状态的提示文本
     * 0代表学生
     * 1代表企业
     * @param $type
     */
    public static function  getStatusText($type,&$res,$id){
        if($res['status'] == -1){
            //获得状态－1的状态信息
            $res['status_text']= self::$resume_staff_notice[$res['extra']]['tip'];
        }elseif($res['status'] <= -2){
            $res['status_text'] = self::$resume_staff_invalid[$res['status']][0];
        }else{
            if($id != -1){
                $res['status_text']= ($type==0?self::$resume_staff_status_detail1[$res['status']]:self::$resume_staff_status_detail2[$res['status']]);
            }else{
                $res['status_text']=($type==0?self::$resume_staff_status_text1[$res['status']]:self::$resume_staff_status_text2[$res['status']]);
            }
        }
    }

    /**
     * 获得当前企业用户接的简历列表
     * @param int $page 页面大小
     * @param int $page_size 页面大小
     * @param int $id 投递记录id 获得特定投递记录  不写则是获得所有
     * @param int $doOrUnDo 筛选条件，-1表示全部返回，0是待处理，1是已经处理 (全部返回的话根据op的type字段判断是否为待处理)
     * @return array
     */
    public static function getReceiveRecord($page=1,$page_size=10,$id=-1,$doOrUnDo=-1){
        //self::processInvalid();
        $pre = C('DB_PREFIX');
        $type = user_field('type');
        $uid = UID;
        $query_str = " u.topic_table='recruitment' AND u.action='resume' AND rt.uid={$uid}";
        if($id != -1){
            $query_str .= " AND u.id={$id}";
        }else{
            $hide_key = 'resume_hide_'.UID;
            $hide = db_map($hide_key);
            $query_str .=  $hide?" AND u.id not in ({$hide}) ":"";
        }

        if($doOrUnDo == -1){//是否做分组处理
            $total =  M('UserStaff')->alias('u')
                ->join($pre.'cresume r ON u.bundle=r.id','left')
                ->join($pre.'recruitment rt ON u.topic_id=rt.id','left')
                ->join($pre.'company c ON c.uid=rt.uid','left')
                ->field('u.id')
                ->where(array('_string'=>$query_str))
                ->count();
            api_page($total);
            $res =  M('UserStaff')->alias('u')
                ->join($pre.'cresume r ON u.bundle=r.id','left')
                ->join($pre.'recruitment rt ON u.topic_id=rt.id','left')
                ->join($pre.'company c ON c.uid=rt.uid','left')
                ->field('u.*,r.title as resume_title,rt.title as r_title')
                ->where(array('_string'=>$query_str))
                ->page($page,$page_size)
                ->order('u.update_time desc')
                ->select();
        }else{
            $res =  M('UserStaff')->alias('u')
                ->join($pre.'cresume r ON u.bundle=r.id','left')
                ->join($pre.'recruitment rt ON u.topic_id=rt.id','left')
                ->join($pre.'company c ON c.uid=rt.uid','left')
                ->field('u.*,r.title as resume_title,rt.title as r_title')
                ->where(array('_string'=>$query_str))
                ->order('u.update_time desc')
                ->select();
            $doOrUnDo_result = array();
            $type = user_field('type');
        }

        for($i=0; $i<count($res);$i++){
            self::getStatusText(1,$res[$i],$id);
            $res[$i]['op'] = self::$resume_op[$res[$i]['status']];
            if($type != $res[$i]['op']['type']){
                unset($res[$i]['op']);
            }

           
            //分组处理
            if($doOrUnDo == 0 && $res[$i]['op']['type'] == $type){
                //待完成
                unset($res[$i]['op']['type']);
                $doOrUnDo_result[] = $res[$i];
            }elseif($doOrUnDo == 1 && $res[$i]['op']['type'] != $type){
                //已完成
                unset($res[$i]['op']);
                $doOrUnDo_result[] = $res[$i];
            }
        }
        
        /* for($i=0;$i<count($res);$i++){
        	switch($res[$i]['status']){
        		case 1:
        		case 4:
        			if($res[$i]['op']['action']){
        				array_push($res[$i]['op']['action'],array('next_status'=>'-3','tip'=>'撤回'));
        			}
        			else{
        				$res[$i]['op']['action']=array(array('next_status'=>'-3','tip'=>'撤回'));
        			}
        			break;
        		case 0:
        		case 2:
        		case 3:
        		case 5:
        		case -3:
        		case -1:
        		case -2:
        			//array_push($res[$i]['op']['action'],array('next_status'=>'-3'));
        			break;
        	}
        } */
        
        if($res){
            if($doOrUnDo != -1){
                //分组处理的记录数
                api_page(count($doOrUnDo_result));
                //分组处理
                $doOrUnDo_result = array_slice($doOrUnDo_result,($page-1)*$page_size,$page_size);
                
                /* for($i=0;$i<count($doOrUnDo_result);$i++){
                	switch($doOrUnDo_result[$i]['status']){
                		case 1:
                		case 4:
                			if($doOrUnDo_result[$i]['op']['action']){
                				array_push($doOrUnDo_result[$i]['op']['action'],array('next_status'=>'-3','tip'=>'撤回'));
                			}
                			else{
                				$doOrUnDo_result[$i]['op']['action']=array(array('next_status'=>'-3','tip'=>'撤回'));
                			}
                			break;
                		case 0:
                		case 2:
                		case 3:
                		case 5:
                		case -3:
                		case -1:
                		case -2:
                			//array_push($res[$i]['op']['action'],array('next_status'=>'-3'));
                			break;
                	}
                } */
                if($doOrUnDo_result){
                    return $doOrUnDo_result;
                }else{
                    //提示信息
                    if($page == 1){
                        api_msg('暂无记录哦..');
                    }else{
                        api_msg('没有更多的记录了...');
                    }
                    return false;
                }
            }
           
            return $res;
        }else{
            api_msg("暂无投递记录!");
            return false;
        }
    }

    /**
     * 改变简历状态
     * @param int $id  简历投递记录的id
     * @param int $status 要转换称的状态
     * @param boolean $is_school app端忽略
     * @return bool 操作是否成功
     */
    public static function changeResumeStatus($id,$status,$is_school=false){
    	if(is_numeric($id) && is_numeric($status)){
            $staff = M('UserStaff')->field('status,uid,bundle,topic_id')->find($id);
            if(!$staff){
                api_msg("要操作的数据对象不存在!");
                return false;
            }

            $data['id'] = $id;
            $data['status'] = $status;
            $data['update_time']=time();
            $pre_status = $staff['status'];//之前的状态
            
            $edge = $pre_status.",".$status; //状态转换边

            if(!$is_school && (!self::$resume_staff_notice[$edge] || self::$resume_staff_notice[$edge]['type']!=user_field('type'))){
                api_msg("操作非法！");
                return false;
            }
           
            if($status == 3){//企业发送录取通知
                //检测是否还有录取名额
                $re=M('recruitment')->where(array('id'=>$staff['topic_id']))->field('number,uid')->find();
            	$model=M('lq_staff');
            	$num=$model->where(array('re_id'=>$staff['topic_id']))->count();
            	if($num<$re['number']){
            		/**该职位还没有招满**/
            		$lq['cuid']=$re['uid'];
            		$lq['suid']=$staff['uid'];
            		$lq['staff_id']=$id;
            		$lq['re_id']=$staff['topic_id'];
            		$lq['create_time']=time();
            		$model->add($lq);
            	}
            	else{
            		api_msg("您的录取名额不够了!");
            		return false;
            	}
            }
            /**学生点击拒绝企业录用通知 和 学校审批不通过 删除lq表中记录**/
            $uStaff=M('UserStaff')->where(array('id'=>$id))->find();
            if($status==-1 && ($uStaff['status']==4 || $uStaff['status']==3)){
            	$model=M('lq_staff');
            	$model->where(array('suid'=>$uStaff['uid'],'re_id'=>$uStaff['topic_id']))->delete();
            }
            
            if(M('UserStaff')->save($data) !== false){
                $fun =  self::$resume_staff_notice[$edge]['function']; //对应的处理函数
                $company = M('Recruitment')->field('uid')->find($staff['topic_id']); //获得公司的用户id
                $datas['id'] = $id;
                $datas['extra'] = $edge;
                M('UserStaff')->save($datas); //保持状态边
                if($status==5){//录取成功
                    M('Recruitment')->where(array('id'=>$staff['topic_id']))->setInc('recruited');
                }
                $fun($staff['uid'],$company['uid'],$id); //执行后续操作
                return true;
            }else{
                api_msg("系统异常,请稍后操作");
                return false;
            }
        }else{
            api_msg("参数非法!");
            return false;
        }
    }

    /**
     * 获得用户的简历用于选择
     * 返回结果只有简历的id，is_default，title
     */
    public static function getResumesSimple(){
        $uid = UID;
        $result = M('Resume')->where(array('uid'=>$uid,'status'=>1))->field("id,is_default,title")->select();
        return $result;
    }

    /**
     * 获取某个简历的详情<br/>
     * 返回数据：参照getResumes接口
     * @param int $id 简历id
     * @param string $model 从哪里面取  默认是可编辑简历表里面 如果是staff 的bundle参数 取简历 此处应该填cresume
     * @return array 简历详情
     */
    public static function getResumeById($id,$model='Resume'){
        $result =  M($model)->where(array('id'=>$id))->find();
        if($result){
            $result['head_path'] = get_cover_path($result['head']);
            $result['evaluation'] = htmlspecialchars_decode($result['evaluation']);
            $result['intension'] = htmlspecialchars_decode($result['intension']);
            return $result;
        }else{
            return false;
        }
    }

    /**
     * 简历是否被锁定
     * @param $id
     * @return mixed
     */
    private static function resumeIsLock($id){
        //$res =M('UserStaff')->where(array('topic_table'=>'recruitment','action'=>'resume','bundle'=>(int)$id,'_string'=>"`status` > -1 AND `status` < 5"))->count();
       return false;
    }

    /**
     * 删除某个简历
     * @param int $id 要删除的简历id
     * @return mixed
     */
    public static function delResume($id){
        if(self::resumeIsLock($id)){
            api_msg("此简历已经投递，不能删除!");
            return false;
        }
        $data['status'] = -1;
        return  M('Resume')->where(array('id'=>$id,'uid'=>UID))->save($data);
    }

    /**
     * 设置某个简历为默认投递的简历
     * @param  int $id  简历id
     * @return bool
     */
    public static function setDefault($id){
        $result = self::getResumesSimple();
        if($result){
            $data = array();
            for($i=0; $i<count($result);$i++){
                if($result[$i]['id'] == $id){
                    $data["is_default"] =1;
                }else{
                    $data["is_default"] =0;
                }
                $data['id'] = $result[$i]['id'];
                M('Resume')->save($data);
            }
            return true;
        }else{
            api_msg("没有找到该用户的简历信息!");
        }
    }

    /**
     * 投递简历
     * @param int $resume_id 简历id
     * @param int $recruitment_id 要投递的招聘信息id
     * @return bool
     */
    public static function doResume($resume_id,$recruitment_id){
        if(StaffApi::hasStaff("recruitment",$recruitment_id,'resume',array('status'=>array('gt',-1))) == 1){
            api_msg("您已经投递过简历了，不能重复投递!");
            return false;
        }else{
            $num = M('UserStaff')->where(array('topic_table'=>"recruitment","uid"=>UID,"action"=>"resume",'_string'=>"-1<`status` AND `status`<5"))->count();
           // $num2 =  M('UserStaff')->where(array('topic_table'=>'recruitment','action'=>'resume','uid'=>UID,'status'=>5))->count();//找出所有可以进行评价的投递记录
           // if($num2){//有正在学习审核的简历投递
           //     api_msg("你已经确认了其他企业的录取通知，暂时不能投递。进行完企业评价后，才可投递。");
           //     return false;
            //}



            if($num >= 3){
                api_msg("您最多只能同时投递三个简历!");
                return false;
            }else{


                $re = M('Resume')->where(array('id'=>$resume_id))->find();
                unset($re['id']);
                $sre_id =  M('Cresume')->add($re);
                //return M('Cresume')->getLastSql();
                if($sre_id !== false){
                    $_POST['topic_table'] = "recruitment";
                    $_POST['topic_id'] =$recruitment_id;
                    $_POST['action'] = "resume";
                    $_POST['bundle'] = $sre_id;
                    $result = StaffApi::addStaff();
                    if($result !== false){
                        $recruitment_uid =  M('Recruitment')->where(array('id'=>$recruitment_id))->field('uid')->find();
                        resume_status0(UID,$recruitment_uid['uid'],$result); //发送通知
                        return true;
                    }else{
                        return false;
                    }
                }else{
                    api_msg("系统出错!");
                    return false;
                }
            }
        }
    }

    /**
     * 添加或者修改简历
     */
    public static function modifyResume(){
        if(!UID){
            api_msg("用户必须先登录!");
            return false;
        }
        //安全设置
        unset($_POST['is_lock']);
        if($_POST["id"]){
            if(self::resumeIsLock($_POST["id"])){
                api_msg("此简历已经投递，不能编辑!");
                return false;
            }
        }
        $_POST['uid'] = UID;
        $Model  =   checkAttr(D('Resume'),"Resume");
        $has_image = false;
        if(!empty($_FILES)){
            $has_image = true;
            $images = upload_image();
            if($images['status'] == 0){// 图片上次失败
                api_msg($images['msg']);
                return false;
            }
        }
        if($Model->create()){
            if($_POST["id"]){
                $result = $Model->save();
            }else{
                $count = M('Resume')->where(array('uid'=>UID,'status'=>1))->count();
                if($count > 2){
                    api_msg("最多只能创建3个简历");
                    return false;
                }else{
                    $result = $Model->add();
                }

            }
            if($has_image){
                $data['id'] = $result;
                $imagesData = $images['data'];
                $ids = array_column($imagesData,"id");
                $data['head'] =   arr2str($ids);
                D('Resume')->save($data); //保存上传的图片
            }
            return true;
        } else {
            api_msg($Model->getError());
            return false;
        }
    }

    /**
     * 发送面试通知
     * @param int $id 投递记录id
     * @param string $place 地址
     * @param string $time 时间
     * @param string $person 联系人
     * @param string $phone  电话
     * @param strin $type=1 anzuo 2 ios
     * @return bool
     */
    public static  function  sendInterviewNotice($id,$place='',$time='',$person,$phone,$type=1){
        if($type!=1){
        	$person=base64_decode($person);
        	$place=base64_decode($place);
        }
    	$list = ResumeApi::getReceiveRecord(1,10,$id);
        $info = $list[0];
        if($info){
            if($person && $phone){
            	$member=get_table_field($info['uid'],'id','','member');
            	$com=get_table_field(UID,'id','','member');
            	M('user_staff')->where(array('id'=>$id))->save(array('v_time'=>strtotime($time),'v_pos'=>$place,'v_person'=>$person,'v_phone'=>$phone));
            	//$t=\Org\Util\sendSMS::send($member['mobile'],"请到{$place}参加面试，面试时间{$time}  联系人{$person},电话{$phone},公司名称{$member['nickname']}");
            	send_sms($member['mobile'],array($place,$time,$person,$phone,$com['nickname']),20998);
               return true;
            }else{
                api_msg("联系人或者电话必须填写");
                return false;
            }

        }else{
            api_msg("参数有误!");
            return false;
        }
    }

    /**
     * 发送录取通知
     * @param int $id 投递记录id
     * @param string $place 地址
     * @param string $time 时间
     * @param string $person 联系人
     * @param string $phone  电话
     * @param strin $type=1 anzuo 2 ios
     * @return bool
     */
    public static  function  sendRecruitNotice($id,$place='',$time='',$person,$phone,$type=1){
    	if($type!=1){
    		$person=base64_decode($person);
    		$place=base64_decode($place);
    	}
    	$list = ResumeApi::getReceiveRecord(1,10,$id);
        $info = $list[0];
        $stu=get_table_field($info['bundle'],'id','','cresume');
        $sc2=get_table_field('c'.$stu['school2'],'username','','member');
        
        if($sc2['mobile']){
        	$rec=get_table_field($info['topic_id'],'id','','recruitment');
        	$com=get_table_field($rec['uid'],'id','','member');
        	//$t=\Org\Util\sendSMS::send($sc2['mobile'],"您好,青锐成长计划温馨提示,贵学院{$stu['name']}同学拟被{$com['nickname']}录用,请您尽快登录系统审核,谢谢。");
        	if(strpos($sc2['mobile'],"|") === false){
        		send_sms($sc2['mobile'],array($stu['name'],$com['nickname']),74849);
        	}else{
        		$ph_arr=explode('|',$sc2['mobile']);
        		foreach($ph_arr as $k=>$v){
        			if(is_numeric($v)){
        				$sc2['mobile']=$v;
        				break;
        			}
        		}
        		send_sms($sc2['mobile'],array($stu['name'],$com['nickname']),74849);
        	}
        }
        
        if($info){
            if($person && $phone){
            	M('user_staff')->where(array('id'=>$id))->save(array('r_time'=>strtotime($time),'r_pos'=>$place,'r_person'=>$person,'r_phone'=>$phone));
                return true;
            }else{
                api_msg("联系人或者电话必须填写");
                return false;
            }

        }else{
            api_msg("参数有误!");
            return false;
        }
    }

    /**
     * 保险单
     * @param int $id 投递记录id
     * @param string $name 姓名
     * @param string $phone 手机号
     * @param string $sfz 身分证
     * @param strin $type=1 anzuo 2 ios
     * @return  bool
     */
    public static function baoxiandan($id,$name,$phone,$sfz,$type=1){
    	if($type!=1){
    		$name=base64_decode($name);
    	}
        $list = ResumeApi::getResumeRecord($id);
        $info = $list[0];
        if($info['uid'] == UID && $info['status'] == 4){
            if(db_map('baodan_'.$id,json_encode(array('name'=>$name,'phone'=>$phone,'sfz'=>$sfz)))){
               return true;
            }else{
                api_msg("系统出错!");
                return false;
            }

        }else{
            api_msg("参数有误!");
            return false;
        }
    }

}
