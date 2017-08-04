<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Common\Model;
/**
 * 用户模型
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 * @author lihao 修改<lh@tiptime.com>
 */

class MemberModel extends \Think\Model{
    /* 用户模型自动验证 */
    protected $_validate = array(
        /* 验证用户名 */
        array('username', '1,30', -1, self::EXISTS_VALIDATE, 'length'), //用户名长度不合法
        array('username', 'checkDenyMember', -2, self::EXISTS_VALIDATE, 'callback'), //用户名禁止注册
        array('username','/\w+[\w\d]+/',-13,self::EXISTS_VALIDATE,'regex',self::MODEL_BOTH),
        array('username', '', -3, self::EXISTS_VALIDATE, 'unique'), //用户名被占用

        /* 验证密码 */
        array('password', '6,30', -4, self::EXISTS_VALIDATE, 'length'), //密码长度不合法

        /* 验证邮箱 */
        array('email', 'email', -5, self::EXISTS_VALIDATE), //邮箱格式不正确
        array('email', '1,32', -6, self::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
        array('email', 'checkDenyEmail', -7, self::EXISTS_VALIDATE, 'callback'), //邮箱禁止注册
        array('email', '', -8, self::EXISTS_VALIDATE, 'unique'), //邮箱被占用

        /* 验证手机号码 */
        array('mobile', '//', -9, self::EXISTS_VALIDATE), //手机格式不正确 TODO:
        array('mobile', 'checkDenyMobile', -10, self::EXISTS_VALIDATE, 'callback'), //手机禁止注册
        array('mobile', '', -11, self::EXISTS_VALIDATE, 'unique'), //手机号被占用

        array('nickname', '2,40', -12, self::EXISTS_VALIDATE, 'length'), //昵称长度不合法
    );

    /* 用户模型自动完成 */
    protected $_auto = array(
        array('password', 'user_encrypt', self::MODEL_BOTH, 'function'),
        array('reg_time', NOW_TIME, self::MODEL_INSERT),
        array('reg_ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
        array('update_time', NOW_TIME),
    );

    public function lists($status = 1, $order = 'id DESC', $field = true){
        $map = array('status' => $status);
        return $this->field($field)->where($map)->order($order)->select();
    }

    /**
     * 检测用户名是不是被禁止注册
     * @param  string $username 用户名
     * @return boolean          ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyMember($username){
        return true; //TODO: 暂不限制，下一个版本完善
    }

    /**
     * 检测邮箱是不是被禁止注册
     * @param  string $email 邮箱
     * @return boolean       ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyEmail($email){
        return true; //TODO: 暂不限制，下一个版本完善
    }

    /**
     * 检测手机是不是被禁止注册
     * @param  string $mobile 手机
     * @return boolean        ture - 未禁用，false - 禁止注册
     */
    protected function checkDenyMobile($mobile){
        return true; //TODO: 暂不限制，下一个版本完善
    }

    protected function _after_select(&$result,$option=''){
        foreach($result as $k=>$v){
            $groups = AuthGroupModel::getUserGroup($v['id']);
            $result[$k]['groups'] = arr2str(array_column($groups,'title'));
            $result[$k]['groups_id'] = arr2str(array_column($groups,'group_id'));
        }
    }

    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($uid){
        /* 检测是否在当前应用注册 */
        $user = $this->field(true)->find($uid);
        if(!$user || 1 != $user['status']) {
            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
            return false;
        }

        //记录行为
        action_log('user_login', 'member', $uid, $uid);

        /* 登录用户 */
        $this->autoLogin($user);
        return true;
    }

    /**
     * 注册一个新用户
     * @param  string $username 用户名
     * @param  string $password 用户密码
     * @param  string $email    用户邮箱
     * @param  string $mobile   用户手机号码
     * @return integer          注册成功-用户信息，注册失败-错误编号
     */
    public function register($username, $password,$nickname,$type,$status=1, $email, $mobile,$extend=0){
        $create_user =  user_field('username');
        $data = array(
            'username' => $username,
            'nickname' =>$nickname,
            'password' => $password,
            'type' =>$type,
            'email'    => $email,
            'mobile'   => $mobile,
            'status'   =>$status,
            'extend'  =>$extend,
            'create_user' =>$create_user?$create_user:"" //创建用户，如果是空的话就是自行注册的
        );

        //验证手机
        if(empty($data['mobile'])) unset($data['mobile']);
        if(empty($data['email']))unset($data['email']);
		
        /* 添加用户 */
        if($this->create($data)){
            $uid = $this->add();
			
            return $uid ? $uid : 0; //0-未知错误，大于0-注册成功
        } else {
            return $this->getError(); //错误详情见自动验证注释
        }
    }

    /**通过用户ID获得字段
     * @param $id
     * @param string $filed_name
     * @return mixed
     */
    public function  getFiledById($id,$filed_name='nickname'){
        return $this->where(array('id'=>(int)$id))->getField($filed_name);
    }


    /**
     * 更新用户信息
     * @param int $uid 用户id
     * @param string $password 密码，用来验证
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     */
    public function updateUserFields($uid, $password, $data){
        if(empty($uid) || empty($password) || empty($data)){
            $this->error = '参数错误！';
            return false;
        }

        //更新前检查用户密码
        if(!$this->verifyUser($uid, $password)){
            $this->error = '验证出错：密码不正确！';
            return false;
        }

        //更新用户信息
        $data = $this->create($data);

        if($data){
            return $this->where(array('id'=>$uid))->save($data);
        }
        return false;
    }

    /**
     * 更新用户信息
     * @param int $uid 用户id
     * @param string $password 密码，用来验证
     * @param array $data 修改的字段数组
     * @return true 修改成功，false 修改失败
     */
    public function updateInfo($uid, $password, $data){
        if($this->updateUserFields($uid, $password, $data) !== false){
            $return['status'] = true;
        }else{
            $return['status'] = false;
            $return['info'] = $this->getError();
        }
        return $return;
    }


    /**
     * 验证用户密码
     * @param int $uid 用户id
     * @param string $password_in 密码
     * @return true 验证成功，false 验证失败
     */
    protected function verifyUser($uid, $password_in){
        $password = $this->getFieldById($uid, 'password');
        if(user_encrypt($password_in) === $password){
            return true;
        }
        return false;
    }

    public function apiLogin($username, $password, $type = 1, $checkType=""){
        $map = array();
        switch ($type) {
            case -1: //智能判断
                $map['_string'] = "`username`='{$username}' OR `mobile`='{$username}'";
                break;
            case 1:
                $map['username'] = $username;
                break;
            case 3:
                $map['mobile'] = $username;
                break;
            case 2:
                $map['email'] = $username;
                break;
            case 4:
                $map['id'] = $username;
                break;
            default:
                return 0; //参数错误
        }

        /* 获取用户数据 */
        $user = $this->where($map)->find();
        if(is_array($user) && $user['status']==1){
            if($checkType){
                $types = str2arr($checkType,',');
                if(!in_array($user['type'],$types)){
                    return -3;
                }
            }
            /* 验证用户密码 */
            if(user_encrypt($password) === $user['password']){
                $this->autoLogin($user);
                return $user; //登录成功，返回用户信息
            } else {
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在或被禁用
        }
    }
    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public  function  checkLogin($username, $password, $type = 1, $auto_login=true){
        $map = array();
        switch ($type) {
            case 1:
                $map['username'] = $username;
                break;
            case 2:
                $map['email'] = $username;
                break;
            case 3:
                $map['mobile'] = $username;
                break;
            case 4:
                $map['id'] = $username;
                break;
            default:
                return 0; //参数错误
        }

        /* 获取用户数据 */
        $user = $this->where($map)->find();
        if(is_array($user) && $user['status']==1){
            /* 验证用户密码 */
            if(user_encrypt($password) === $user['password']){
                if($auto_login){
                    $this->autoLogin($user); //更新用户登录信息
                }
                return $user['id']; //登录成功，返回用户ID
            } else {
                return -2; //密码错误
            }
        } else {
            return -1; //用户不存在或被禁用
        }
    }

    public function cookie_login($username){
        $user = $this->where(array('username'=>$username,'status'=>1))->find();
        if($user){
            $this->autoLogin($user); //存在用户进行自动登陆
            return true;
        }else{
            return false;
        }
    }


    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        session('user_auth', null);
        session('user_auth_sign', null);
    }

    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
        $data = array(
            'id'             => $user['id'],
            'login_times'           => array('exp', '`login_times`+1'),
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
        );
        $this->save($data);

        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid'             => $user['id'],
            'username'        => $user['username'],
            'nickname'        => $user['nickname'],
            'type'            => $user['type'],
            'last_login_time' => $user['last_login_time'],
        );

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));

    }


    /**得到特定用户的数据签名
     * @param $username
     * @return mixed|string
     */
    public function get_user_auth_sign($username){
        if(isset($username)){
            $user = $this->where(array('username'=>$username))->find();
            $auth = array(
                'uid'             => $user['id'],
                'username'        => $user['username'],
                'nickname'        => $user['nickname'],
                'type'            => $user['type'],
                'last_login_time' => $user['last_login_time'],
            );
            return  data_auth_sign($auth);
        }else{
           return session('user_auth_sign');
        }
    }



    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    static public   function showRegError($code = 0){
        switch ($code) {
            case -1:  $error = '用户名长度必须在16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
            case -12: $error = '昵称长度应该在2到40个字符之间！'; break;
			case -13: $error = '用户名必须字母开头,只能含有字母或者数字！';break;
            default:  $error = '未知错误';
        }
        return $error;
    }
}
