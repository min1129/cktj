<?php

namespace Addons\IPlistener\Controller;
use Common\Controller\AddonsController;

class IPlistenerController extends AddonsController{
	
		function updateIp() {
			
			//获得ip
			$ip = $this->getIP();
			//获得城市代码
			$cityData = $this->getCity($ip);
			
			
			//组织数据
			$data['ip'] = $ip;
			$data['visit_time'] = NOW_TIME;
			$data['visit_url'] =I('post.ip');
			
			if($cityData){
			$data['city'] = $cityData['country'].$cityData['city'];
			$data['isp'] = $cityData['isp'];
			}else{
			$data['city'] = '未知';
			$data['isp'] ='未知';
			}
			
			//var_dump($data);
			
			//$User->data($data)->add();
			
			//数据库操作
			$IPDB = M('iplistener');
			
			$result = $IPDB->add($data);
			
			if($result){
				$this->ajaxReturn('成功记录');
			}else{
				$this->ajaxReturn('失败记录');
			}
		}
		
		/**
		*@creat_time 2014-8-15上午08:42:14
		* @describe
		* @param  
		* @return
		*/
		function deleIp() {
			$this->checkAuth();//检测是否有权限
			$id = I('post.id');
			$IPDB = M('iplistener');
			$result = $IPDB->where('id='.$id)->delete();
			
			if($result){
				
				$this->ajaxReturn('删除成功');
			}else{

				$this->ajaxReturn('删除失败');
			}
		}
		
		
/**
 * 获取用户真实 IP
 */
function getIP()
{
    static $realip;
    if (isset($_SERVER)){
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")){
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else {
            $realip = getenv("REMOTE_ADDR");
        }
    }
 
 
    return $realip;
}


/**
 * 获取 IP  地理位置
 * 淘宝IP接口
 * @Return: array
 */
function getCity($ip)
{
$url="http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;
$ip=json_decode(file_get_contents($url)); 
if((string)$ip->code=='1'){
  return false;
  }
  $data = (array)$ip->data;
return $data; 
}







}
