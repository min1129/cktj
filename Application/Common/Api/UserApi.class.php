<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Common\Api;
use Common\Model\MemberModel;

/**
 * Class UserApi
 * @package Common\Api
 * @author lh
 * @time 2015-03-07 09:57:48
 */
class UserApi {
    /**
     * 检测用户是否登录
     * @return integer 0-未登录，大于0-当前登录用户ID
     */
    public static function is_login(){
        if(APP_MODE == 'api'){
            return UID;
        }
        $user = session('user_auth');
        if (empty($user)) {
            return 0;
        } else {
            return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
        }
    }

    /**
     * 获取当前用户的信息
     * @param string $field 字段
     * @return string 用户信息
     */
    public static function user_field($field){
        if(APP_MODE == 'api'){
            if(UID){
                $User = M('Member')->field($field)->find(UID);
                return $User[$field];
            }else{
                api_msg('用户未定义!');
                return false;
            }
        }
        $user = session('user_auth');
        if (empty($user)) {
            return 0;
        } else {
            return session('user_auth_sign') == data_auth_sign($user) ? $user[$field] : 0;
        }
    }

    /**
     * @param int $uid 用户id
     * @return boolean true-管理员，false-非管理员
     */
    public static function is_administrator($uid = null){
        $uid = is_null($uid) ? self::is_login() : $uid;
        return $uid && (intval($uid) === C('USER_ADMINISTRATOR'));
    }

    /**
     * 根据用户ID获取用户名
     * @param  integer $uid 用户ID
     * @return string       用户名
     */
    public static function get_username($uid = 0){
        return UserApi::get_user_field($uid,'username');
    }

    /**
     * 获得指定用户的信息
     * @param int $uid 用户id
     * @param string $field 字段可不写
     * @return array|mixed 用户信息
     */
    public static function get_user_field($uid=0,$field){
        static $list;
        if(!($uid && is_numeric($uid))){ //获取当前登录用户名
            $uid = UID;
        }

        /* 获取缓存数据 */
        if(empty($list)){
            $list = array();
        }

        /* 查找用户信息 */
        $key = "u{$uid}";
        if(isset($list[$key])){ //已缓存，直接使用
            $User = $list[$key];
        } else { //调用接口获取用户信息
            $User = M('Member')->where(array('id'=>$uid))->find();
            if($User){
                $list[$key] = $User;
            } else {
                $User = array();
            }
        }
        return !empty($field)?$User[$field]:$User;
    }


    /**
     * 根据用户ID获取用户昵称
     * @param  integer $uid 用户ID
     * @return string       用户昵称
     */
    public static function get_nickname($uid = 0){
       return UserApi::get_user_field($uid,'nickname');
    }


    /**
     * 登陆
     * <hr/>
     * <h5>返回结果说明:</h5>
     * uid:用户id<br/>
     * nickname:用户昵称<br/>
     * username:用户名<br/>
     * type:用户类型,0为管理员,1为企业,2为普通用户<br/>
     * head:用户头像,没有则返回空字符串<br/>
     * sid:用户登陆key,相当于浏览器的session标识,请求时会用到,代表当前登陆用户
     * @param string $u 用户名
     * @param string $p 密码
     * @retrun array
     * @ignore
     */
    public static  function login($u,$p){
        $result = D('Member')->apiLogin($u,$p,-1,'2');
        if(is_array($result)){//登陆成功返回accesskey
            $data['sid'] =  think_encrypt($result['id'],C('UID_KEY'));
            $data['nickname'] = $result['nickname'];
            $data['username'] = $result['username'];
            $data['type'] = $result['type'];
            $data['uid'] = $result['id'];
            $data['head'] = get_cover_path($result['head']);
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
     *等出只是简单的销毁session,如果是用sid方式,则不做任何事情.<br/>
     * 这时候登陆登出由客户端自己控制
     */
    public static function loginOut(){
        session('[destroy]');
        return true;
    }

    /**
     * 普通用户注册
     * @param string $email 邮箱
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $password 密码
     * @param string $re_password 重复密码
     * @return string 结果
     * @ignore
     */
    public static  function register($phone,$username,$nickname,$password,$re_password){
        if($phone && $username && $nickname && $password && $re_password){
            /* 检测密码 */
            if($password != $re_password){
                api_msg('密码和重复密码不一致！');
                return false;
            }
            $member = D('Member');
            $uid    =   $member->register($username, $password,$nickname,2,$status=1,'',$phone); //添加
            if(0 < $uid){ //注册成功
                //D('AuthGroup')->addToGroup($uid,13);//添加分组权限
                return self::login($username,$password);
            } else { //注册失败，显示错误信息
                api_msg(MemberModel::showRegError($uid));
                return false;
            }
        }else{
            api_msg("填写信息不全");
            return false;
        }
    }
	/**
	 * 获取注册手机验证码
	 * @param string $phoneNum
	 */
    public static function verifyPhoneRegister($phoneNum){
    	if(!preg_match("/^(13[0-9]|15[0-9]|153|156|18[1-9]|17[0-9])[0-9]{8}$/",$phoneNum)){
    		api_msg("手机号码有误!");
    		return false;
    	}
        $re = M('Member')->field("id")->where(array('mobile'=>$phoneNum))->find();
        if(!$re){
            $verify = rand(100000,500000);
//            if(send_sms($phoneNum,array($verify),14478)){
//                return $verify;
//            }else{
//                api_msg("验证码获取出错!");
//                return false;
//            }
            //if(\Org\Util\sendSMS::send($phoneNum,"您的注册验证码为{$verify}")){
            if(send_sms($phoneNum,array($verify),18437)){
                return $verify;
            }else{
                api_msg("验证码获取出错!");
                return false;
            }
        }else{
            api_msg("手机号码已被占用!");
            return false;
        }

    }

    public static function verifyPhoneForgerPassword($phoneNum){
        $verify = rand(100000,500000);
        if(send_sms($phoneNum,array($verify),88012)){
            return $verify;
        }else{
            api_msg("验证码获取出错!");
            return false;
        }
    }

    /**
     * 当前用户的昵称
     * @author huajie <banhuajie@163.com>
     */
    public static function updateNickname($nickname,$password){
        if(UID <=0){
            api_msg("用户未登录");
            return false;
        }
        $data['nickname'] = $nickname;
        $member = D('Member');
        $res    =   $member->updateInfo(UID, $password, $data);
        if($res['status']){
            api_msg("修改密码成功");
            return true;
        }else{
            api_msg($res['info']);
            return false;
        }
    }

    public static function updatePasswordWithOldPassword($password,$oldPassword){
        if(UID <=0){
            api_msg("用户未登录");
            return false;
        }
        $data['password'] = $password;
        $member = D('Member');
        $res    =   $member->updateInfo(UID, $oldPassword, $data);
        if($res['status']){
            api_msg("修改密码成功");
            return true;
        }else{
            api_msg($res['info']);
            return false;
        }
    }
    /**
     * 修改密码初始化
     *
     */
    public static function updatePasswordWithPhone($password,$phone){
        $data['password'] = $password;
        $member = D('Member');
        $user = $member->where(array("mobile"=>$phone))->field('id')->find();
        if($user){
            $uid = $user['id'];
            $data['id'] = $uid;
        }else{
            api_msg("该手机未注册!");
            return false;
        }
        if($member->create($data) && $member->save()!==false){
            api_msg("修改密码成功");
            return true;
        }else{
            api_msg($member->getError());
            return false;
        }
    }

    /**
     * 更新当前用户的头像
     * 图片上传
     */
    public static function updateHead(){
        $uid=UID?UID:$_POST['uid'];
    	if($uid <=0){
            api_msg("用户未登录");
            return false;
        }
        $result = upload_image();
        
        if($result['status']==0){
            api_msg($result['msg']);
            return false;
        }else{
            $data['head'] = $result['data']['file']['id'];
            if(M('Member')->where(array('id'=>$uid))->save($data)!==false){
            	 M('student')->where(array('uid'=>$uid))->save($data);
                 api_msg("更改成功!");
                 $result = __ROOT__.$result['data']['file']['path'];
                 return $result;
            }else{
                api_msg("更改失败!未知错误");
                return false;
            }

        }
    }
}