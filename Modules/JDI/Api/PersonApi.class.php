<?php
/**
 * Created by PhpStorm.
 * User: haoli
 * Date: 15/1/27
 * Time: 下午3:27
 */

namespace Modules\JDI\Api;
use Common\Api\UserApi;

/**
 * 此文档提供功能:
 * 1.获得公司资料<br/>
 * 2.获得公司列表<br/>
 * 3.获得用户通知,以及删除和标置为已读<br/>
 * @package Modules\Person\Api
 * @author lh
 * @time 2015-03-07 09:52:02
 */
class PersonApi {
    /**
     * 获得当前登陆公司的资料
     * @return  array 企业信息
     */
    public static  function company($model='Company'){
        $uid = UID;
        if($uid <= 0){
            api_msg("没有指定uid而且用户尚未登录!");
            return false;
        }
        $result = M($model)->where(array("uid"=>$uid))->find();
        if($result){
            if(is_api()){
                $result['head_path'] = get_cover_path($result['logo']);
                $result['company_attr'] = C('COMPANY_ATTR.'.$result['company_attr']);
                $result['category'] = C('COMPANY_CATEGORY.'.$result['category']);
            }else{
                $result['logo_path'] = get_cover_path($result['logo']);
            }
            
			$com=get_table_field($result['uid'],'id','','member');
			$result['email']=$com['email']?$com['email']:'';
            $result['zhizhao_path'] = get_cover_path($result['zhizhao']);
            $result['description'] = htmlspecialchars_decode($result['description']);
            $result['is_collect'] = StaffApi::hasStaff("company",$result['id']);
            return $result;
        }else{
            api_msg("企业资料还未填写!");
            return false;
        }
    }

    public static function student(){
        if(!isset($uid)){
            $uid = UID?UID:$_POST['uid'];
            if($uid <= 0){
                api_msg("没有指定uid而且用户尚未登录!");
                return false;
            }
        }
        $result = M('Student')->where(array("uid"=>$uid))->find();
        if($result){
        	$path=get_cover_path($result['head']);
        	$path=$path?$path:__ROOT__.'/Public/Home/default.png';
            $result['head_path'] = $path;
            $stu=get_table_field($result['uid'],'id','','member');
            $result['mobile']=$stu['mobile']?$stu['mobile']:'';
            return $result;
        }else{
            return false;
        }
    }

    /**
     * 公司的地理位置
     * @return bool
     */
    public static function companyPosition(){
        $map['status'] = 1;
        $result = M('Company')->where($map)->field("name,id,point")->select();
        if(!$result){
            api_msg("暂无结果!!");
            return false;
        }
        return $result;
    }

    /**
     * 添加或者修改学生资料(<strong>需要传递参数!如果参数中有id则为修改否则为添加</strong>)<br/>
     * 传递资料参考模型定义
     * @param string $email 
     * @param string $name 姓名
     * @param string $sex 性别 0 女 1男
     * @param string $birthday 时间戳 
     */
    public static function modifyStudent(){
        $Model  =   checkAttr(M('Student'),"Student");
        $_POST['uid'] = UID;
        if($Model->create()){
            if($_POST['id']){
                $result = $Model->save();
                if($result){
                    //同步到用户表中
                	$data['id'] = UID;
                    if($_POST['name']){
                    	$data['nickname'] = $_POST['name'];
                    }
                    if($_POST['mobile']){
                    	$data['mobile']=$_POST['mobile'];
                    }
                    if($_POST['head']){
                    	$data['head']=$_POST['head'];
                    }
                    M('Member')->save($data);
                }
            }else{
                $result = $Model->add();
            }
            return $result;
        } else {
            api_msg($Model->getError());
            return false;
        }
    }


    /**
     *获取指定配置<br/>
     * CAT_JOB 代表行业类别<br/>
     * CAT_UN 代表学校<br/>
     * 其他配置参照网站后台配置设定
     * @param string $key  形如a,b,c 则获取a,b,c的配置
     * @return mixed
     */
    public static function getConfig($key){
        $array = str2arr($key);
        $data =array();
        foreach($array as $k){
            if($k == "CAT_JOB"){
                $map = array('status'=>array('gt',0));
                $map['type'] = 0;
                $list = M('Tree')->where($map)->field('pid,id,name')->select();
                //得到栏目树形结构
                $tree =list_to_tree($list,'id','pid','children');
                $data['CAT_JOB'] =$tree;
            }elseif($k == "CAT_UN"){
                $map = array('status'=>array('gt',0));
                $map['type'] = 1;
                $list = M('Tree')->where($map)->field('pid,id,name')->select();
                //得到栏目树形结构
                $tree =list_to_tree($list,'id','pid','children');
                $data['CAT_UN'] =$tree[0]['children'];
            }else{
                $data[$k] = C($k);
            }
        }
        return $data;
    }

    public static function getConfigVersion(){
            return "1";
    }


    private  static function copyCompany($data){
        $res = M('Company')->where(array('id'=>$data['id']))->find();
        foreach($data as $k=>$v){
            $res[$k] = $v;
        }
        $cc = M('Ccompany')->field('id')->where(array('id'=>$data['id']))->find();
        if($cc){
            //保存
            M('Ccompany')->save($res);
        }else{
            //新建
            M('Ccompany')->add($res);
        }
    }

    /**
     * 把备份表里的数据同步到主表
     */
    public  static function copyCompanyToCompany($id,$status=1){
        $data1['status'] = $status;
        $data1['id'] = $id;
        M('Ccompany')->save($data1);

        $res = M('Ccompany')->where(array('id'=>$id))->find();
        M('Company')->save($res);

        if($res){
            //同步到用户表中
            $data['nickname'] = $res['name'];
            $data['id'] = $res['uid'];
            M('Member')->save($data);
        }
    }

    /**
     * 添加或者修改企业资料(<strong>需要传递参数!如果参数中有id则为修改否则为添加</strong>)<br/>
     * 传递资料参考模型定义
     */
    public static function modifyCompany(){
        //安全性设置
        unset($_POST['collect_num']);
        unset($_POST['comment_num']);
        unset($_POST['score']);
        unset($_POST['quota']);
        unset($_POST['collect_num']);
        $_POST['uid'] = UID;
        if($_POST['id']){
            $company =M('Company')->where(array('id'=>$_POST['id'],'uid'=>UID))->find();
            if(!$company){
                //不存在
                api_msg("操作非法!");
                return false;
            }
            if($company['status']==2){
                //正在审核中
                api_msg("您的资料正在审核中不能修改!");
                return false;
            }
        }else{
            api_msg("操作非法!");
            return false;
        }
        if($_POST['email']){
        	$flag1=M('member')->where('id='.UID)->save(array('email'=>$_POST['email']));
        }
        unset($_POST['email']);
        M('member')->where('id='.UID)->save(array('nickname'=>$_POST['name']));
        $Model  =   checkAttr(M('Company'),"Company");
        if($Model->create()){
            if(is_tsw_company()){//团市委企业不用审核
                return $Model->save();
            }else{
                //$data['status'] = 1;//2016-03-14 企业修改资料不需要审核 2为需要审核
                //$data['id'] = $_POST['id'];
                //$_POST['status'] = 2;
                //self::copyCompany($_POST);
                $data=$_POST;
                $res = M('Company')->where(array('id'=>$data['id']))->find();
                foreach($data as $k=>$v){
                	$res[$k] = $v;
                }
                $flag2=$Model->save($res);
                if($flag1 || ($flag2 !== false)){
                	return 1;
                }
                api_msg('未知错误！');
                return false;
            }
        } else {
            api_msg($Model->getError());
            return false;
        }
    }

    /**
     * 公司列表
     * @param int $page 页码
     * @param int $page_size 页面大小
     * @param array $where
     * @param string $order
     * @param int $width  图片压缩宽度 只有当width 和 height都不为0时才进行压缩
     * @param int $height 图片压缩高度
     * @return bool
     */
    public static function companyLists($page=1,$page_size=10,$where=array(),$order = '',$width=200,$height=100){
        $map['status'] = 1;
        if(is_string($where)){
            $map["_string"] = $where;
        }else{
            $map = array_merge($where,$map);
        }
        $model = M('Company')->field(true)->where($map)->order($order);
        $model->page($page,$page_size);
        $result = $model->select();
        if(!$result){
            if($page == 1){
                api_msg("暂时还未有公司资料!");
            }else{
                api_msg("一共就这么多,没有更多的公司了!");
            }
            return false;
        }else{
            for($i=0;$i<count($result);$i++){
                $result[$i]['picture_id'] = $result[$i]['picture'];
                if($width !=0 && $height!=0){
                    $result[$i]['picture'] = thumb(get_cover_path($result[$i]['picture']),$width,$height);
                }else{
                    $result[$i]['picture'] = get_cover_path($result[$i]['picture']);
                }
                $result[$i]['is_collect'] = StaffApi::hasStaff("company",$result[$i]['id']);
            }
            return $result;
        }
    }

    /**
     * 获取指定用户的通知信息<br/>
     * <hr/>
     * <h5>返回结果说明:</h5>
     * uid:通知用户<br/>
     * status:通知状态 0是已读 1是未读<br/>
     * title:通知标题<br/>
     * bundle:附加信息<br/>
     * flag:标志
     * @return array 通知列表
     */
    public static  function get_notices($page=1,$page_size=10,$where=array(),$order='status ASC,create_time DESC'){
        $map['uid'] = UID;
        $map['status'] = array('gt',-1);
        if(is_string($where)){ //字符串查询
            $map["_string"] = $where;
        }else{
            $map = array_merge($map,$where);
        }
        api_page(array('total'=>D('Notice')->where($map)->field(true)->order($order)->count()));
        $data['status'] = 1;
         D('Notice')->where(array_merge($map,array('status'=>0)))->page($page,$page_size)->save($data);
        $result =D('Notice')->where($map)->field(true)->order($order)->page($page,$page_size)->select();
        foreach($result as $k=>$v){
        	$arr=explode('|',$v['bundle']);
        	$staff=M('user_staff')->where(array('id'=>$arr[0]))->find();
        	$rec=M('recruitment')->where(array('id'=>$staff['topic_id']))->find();
        	$company=get_table_field($rec['uid'],'uid','','company');
        	$result[$k]['job']=$rec['title'];
        	$result[$k]['step']=$arr[1];
        	if($arr[1]=='0,2'){
        		$result[$k]['v_time']=$staff['v_time'];
        		$result[$k]['v_pos']=$staff['v_pos'];
        		$result[$k]['v_person']=$staff['v_person'];
        		$result[$k]['v_phone']=$staff['v_phone'];
        		$result[$k]['r_time']='';
        		$result[$k]['r_pos']='';
        		$result[$k]['r_person']='';
        		$result[$k]['r_phone']='';
        	}else if($arr[1]=='3,4'){
        		$result[$k]['v_time']='';
        		$result[$k]['v_pos']='';
        		$result[$k]['v_person']='';
        		$result[$k]['v_phone']='';
        		$result[$k]['r_time']=$staff['r_time']?$staff['r_time']:'';
        		$result[$k]['r_pos']=$staff['r_pos']?$staff['r_pos']:'';
        		$result[$k]['r_person']=$staff['r_person']?$staff['r_person']:'';
        		$result[$k]['r_phone']=$staff['r_phone']?$staff['r_phone']:'';
        	}else{
        		$result[$k]['v_time']='';
        		$result[$k]['v_pos']='';
        		$result[$k]['v_person']='';
        		$result[$k]['v_phone']='';
        		$result[$k]['r_time']='';
        		$result[$k]['r_pos']='';
        		$result[$k]['r_person']='';
        		$result[$k]['r_phone']='';
        	}
        	$result[$k]['td_time']=$staff['create_time'];
        	$result[$k]['company']=$company['name'];
        }
        if($result){
            return $result;
        }else{
            //api_msg("暂无通知!");
            return false;
        }
    }

    /**
     * 发送通知
     * @param int $uid 通知用户
     * @param string $title 通知标题
     * @param string $bundle 附加信息
     * @param int $flag 通知标识
     */
    static public function notice($uid,$title,$bundle='',$flag=0){
        return notice($uid,$title,$bundle,$flag);
    }

    /**
     * 获取未读通知
     * @return bool|int
     */
    public static function getUnReadNotice(){
        $uid = UID;
        if($uid<=0){
            api_msg("没有指定uid而且用户尚未登录!");
            return false;
        }
        $result = M('Notice')->where(array('uid'=>$uid,'status'=>0))->count();
        if($result){
            return $result;
        }else{
            api_msg("暂无通知!");
            return 0;
        }
    }

    /**
     * 获得职位类别
     * @return string 树形结构
     */
    public static function get_cate_job(){
        $map = array('status'=>array('gt',0));
        $list = M('Tree')->where($map)->select();
        //得到栏目树形结构
        $tree =list_to_tree($list,'id','pid','children');
        return json_encode($tree);
    }
	
    /**
     * 获得学校类别
     * @return string 树形结构
     */
    public static function get_cate_school(){
    	$map = array('status'=>1);
    	$map['type'] = 1;
    	$list = M('Tree')->where($map)->field('pid,id,name')->select();
    	//得到栏目树形结构
    	$tree =list_to_tree($list,'id','pid','children');
    	return json_encode($tree);
    }
    /**
     * 吧通知标志位已读
     * @param string $id 标志为已读的通知列表例如1,2,3,4
     * @return bool
     */
    public static function readNotice($id){
        if(!is_array($id)){
            $id = str2arr($id);
        }
        $data['status'] = 1;
        if(M('Notice')->where(array('id'=>array('in',$id),'uid'=>UID))->save($data) !== false){
            api_msg("操作成功!");
            return true;
        }else{
            api_msg("操作失败!");
            return false;
        }
    }

    /**
     * 把通知删除
     * @param string $id 要删除的通知列表例如1,2,3,4
     * @return bool
     */
    public static function deleteNotice($id){
        if(!is_array($id)){
            $id = str2arr($id);
        }
        $data['status'] = -1;
        if(M('Notice')->where(array('id'=>array('in',$id),'uid'=>UID))->save($data) !== false){
            api_msg("操作成功!");
            return true;
        }else{
            api_msg("操作失败!");
            return false;
        }
    }

    /**
     * 学生注册
     * @param int $phone 手机号
     * @param string $username 用户名 必须是字母开头 数字和
     * @param string $password 密码
     * @param string $real_name 真实姓名
     * @param string $email 邮箱
     * @param int $school1 大学
     * @param int $school2 学院
     * @param string $student_id 学号
     * @param int $type 1 anzuo 2 ios
     * @return bool
     */
    public static  function register($phone,$username,$password,$real_name,$email,$school1,$school2,$student_id,$type=1){
        if($type!=1){
        	$real_name=base64_decode($real_name);
        }
    	if($phone && $username && $password && $real_name && $email && $school1 && $school2 && $student_id){
            $res = UserApi::register($phone,$username,$real_name,$password,$password);
            if($res){
                $stu_data['name'] = $real_name;
                $stu_data['school1'] = $school1;
                $stu_data['school2'] = $school2;
                $stu_data['studentID'] = $student_id;
                $stu_data['email'] = $email;
                $stu_data['uid'] = $res['uid'];
                M('Student')->add($stu_data);
                return self::login($username,$password);
            }else{
                return false;
            }
        }else{
            api_msg("填写信息不全");
            return false;
        }
    }
    
   /**
    * 检测用户名是否被占用
    * @param unknown $username
    */
    public static function checkUserName($username){
    	if(!$username){
    		return false;
    	}
    	if(M('member')->where(array('username'=>$username))->find()){
    		return '1';
    	}else{
    		return '2';
    	}
    }
	/**
	 * 获取忘记密码的手机验证码
	 * @param string $phoneNum	手机号
	 */
    public static function verifyPhone($phoneNum){
    	if(!preg_match("/^(13[0-9]|15[0-9]|153|156|18[1-9]|17[0-9])[0-9]{8}$/",$phoneNum)){
    		api_msg("手机号码有误!");
    		return false;
    	}
    	$re = M('Member')->field("id")->where(array('mobile'=>$phoneNum))->find();
    	if($re){
    		$verify = rand(100000,500000);
    		if(send_sms($phoneNum,array($verify),88012)){
    			return $verify;
    		}else{
    			api_msg("验证码获取出错!");
    			return false;
    		}
    	}else{
    		api_msg("该手机号码未被注册！");
    		return false;
    	}
    
    }
    /**
     * 忘记密码
     * @param string $password 密码
     * @param string $phone	手机号
     */
    public static function updatePasswordWithPhone($password,$phone){
    	return UserApi::updatePasswordWithPhone($password,$phone);
    }
    
    /**
     * 启动页接口
     */
    public static function startUpPic(){
    	$arr=M('lunbopic')->select();
    	$arr=json_decode($arr[0]['pid'],true);
    	foreach($arr as $k=>$v){
    		$arr[$k]['cover_path']=get_cover_path($v['id']);
    	}
    	return $arr;
    }
    
    /**
     * 获得注册人数和发布岗位数量接口
     */
    public static function getRegAndPosNumber(){
    	$regNum=M('member')->where(array('type'=>2,'status'=>1))->count();
    	$posNum=M('recruitment')->where(array('status'=>1))->sum('number');
    	
    	$data=array(
    			'regNum'=>(string)(C('REG_NUMBER')+$regNum),
    			'posNum'=>(string)(C('POS_NUMBER')+$posNum)
    	);
    	return $data;
    }

    /**
     * @param string $u  登陆，智能判断是手机或者用户名登陆
     * @param string $p  密码
     * @param string $check_type 登陆限制，企业用户不能登陆手机客户端，1代表企业用户，2代表企业用户。如果只允许学生登陆就写 2 如果两者都可以就写 1,2
     * @param string $deviceId
     * @return bool
     */
    public static  function login($u,$p,$check_type="2",$deviceId=''){
        $result = D('Member')->apiLogin($u,$p,-1,$check_type);
        if(is_array($result)){//登陆成功返回accesskey
            $data['sid'] =  think_encrypt($result['id'],C('UID_KEY'));
            $data['username'] = $result['username'];
            $data['uid'] = $result['id'];
            $data['phone'] =  $result['mobile'];
            $data['type'] = $result['type'];
            if($result['type'] == 1){ //代表企业
                $company = M('Company')->where(array('uid'=>$result['id']))->field("name,logo")->find();
                $data['name'] = $company['name'];
                $data['head_path'] = get_cover_path($company['logo']);
            }else{
                $company = M('Student')->where(array('uid'=>$result['id']))->field("name,head")->find();
                $data['name'] = $company['name'];
                $path=get_cover_path($company['head']);
                $path=$path?$path:__ROOT__.'/Public/Home/default.png';
                $data['head_path'] = $path;
            }
            if($deviceId!=''){
            	$result=get_table_field($result['id'],'id','','member');
            	if($result['device']!=$deviceId){
            		if($result['device']){
            			$res=pushSingle($result['id'],'用户身份过期，重新登录','您已在别处登录','2000','');
            			$data['push_res']=$res;
            			$data['push_res_id']=$result['id'].'_'.$result['push_id'];
            		}
            		$push_id=md5(rand(1000,9000).time());
            		$arr['push_id']=$push_id;
            		$arr['device']=$deviceId;
            		M('member')->where(array('id'=>$result['id']))->save($arr);
            	}
            	$data['push_id']=$push_id?$push_id:$result['push_id'];
            }
            api_msg("登陆成功!");
            return $data;
        }else{
            switch($result){
                case 0:
                    $msg = '参数错误!';
                    break;
                case -1:
                    $msg = '用户不存在或被禁用!';
                    break;
                case -2:
                    $msg = '密码错误!';
                    break;
                case -3:
                    $msg = '没有登陆权限!';
                    break;
                default:
                    $msg= '未知错误!';
            }
            api_msg($msg);
            return false;
        }
    }

    /**
     * 获得当前用户头像
     */
    public static function get_user_logo(){
        $uid =UID;
        $res = M('Student')->where(array('uid'=>$uid))->field('head')->find();
        return get_cover_path($res);
    }



}