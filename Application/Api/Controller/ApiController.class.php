<?php
namespace Api\Controller;
use Common\Controller\BaseController;
use Think\Controller;

/**
 * api控制器基类
 * Class ApiController
 * @package Api\Controller
 */
class ApiController extends BaseController{
    public function  _initialize(){
        $dir = COMMON_PATH . 'Common';
        if (is_dir($dir)) {
            $handler = opendir($dir);
            while (($filename = readdir($handler)) !== false) {
                if (substr($filename, -4) == '.php' && $filename != 'function.php') {
                    include $dir . '/' . $filename;
                }
            }
            closedir($handler);
        }
        parent::_initialize();
        if(ACTION_NAME != "time" && $_REQUEST['_M']!="AppClient"){
            $this->checkSign(); //检测签名是否合法
        }
    }

    //时间校准
    public  function  time(){
        echo NOW_TIME;
        //$this->success(NOW_TIME,"请求成功!");
    }

    protected function unsetGetPost($key){
        unset($_GET[$key]);
        unset($_POST[$key]);
        unset($_REQUEST[$key]);
    }
    /**
     * 检测请求合法性
     */
    protected function checkSign(){
    	
        //if(isset($_REQUEST['_hash'])){//数据签名
            $time = $_REQUEST['_time'];//请求时间戳
            //$hash = $_REQUEST['_hash'];//数据签名
            $this->unsetGetPost('_hash');
            
            
            //if($hash == api_auth_sign($_REQUEST,C('API_PRIVATE_KEY'))){ //签名认证
                $this->unsetGetPost('_time');
//                if((NOW_TIME - $time)  > C('API_OUT_TIME')){ //检测请求是否失效
//                    $this->error("请求已经失效！");
//                }
                $access_key = isset($_REQUEST['_sid'])?$_REQUEST['_sid']:0;
                if($access_key !== 0){ //是否携带用户登陆key,用于手机客户端访问
                    define('UID',think_decrypt($access_key,C('UID_KEY')));
                }else{//检测是否存储在session中,主要用于api方式的web应用
                    $user = session('user_auth');//是否存在session中
                    if (!$user) {
                        define('UID',0);
                    } else {
                        define('UID',session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0);
                    }
                }
                $this->unsetGetPost('_sid');
            /* }else{
                $this->error("数据签名有误!错误代码:105");
            } */
        /* }else{
            $this->error("未进行数据签名!错误代码:104");
        } */
    }

    public function _empty(){
         $this->error("请求地址有误!错误代码:102");
    }


    /**ajax返回
     * @param $status
     * @param $data
     * @param string $msg
     */
    protected function  J_J($status,$data,$msg,$code){
        $datas['status'] = $status;
        $datas['data'] = $data;
        $datas['msg'] = $msg;
        $datas['code'] = $code;
        $this->ajaxReturn($datas);
    }

    /**错误返回
     * @param string $msg
     */
    protected  function  error($msg=''){
        $this->J_J(0,'',$msg,api_code());
    }

    /**成功返回
     * @param string $data
     * @param string $msg
     */
    protected function success($data,$msg=''){
        $this->J_J(1,$data,$msg,api_code());
    }
}
