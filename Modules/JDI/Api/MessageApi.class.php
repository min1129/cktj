<?php 
namespace Modules\JDI\Api;
use Think\Page;
use Think\Model;
/**
 * 消息接口文档
 * @package Modules\Message\Api
 * @author zy
 * @time 2016-07-31
 */
class MessageApi
{
	/**
	 * 给某人发送消息
	 * @param unknown $uid
	 * @param unknown $r_uid 接收uid
	 * @param unknown $content
	 */
	static public function sendChat($uid,$r_uid,$content){
		$data=array(
			'uid'=>$uid,
			'r_uid'=>$r_uid,
			'content'=>$content,
			'status'=>0,
			'create_time'=>time()
		);
		$res=M('chat')->add($data);
		$info=get_table_field($uid,'uid','','people_info');
		$data['name']=$info['name'];
		$data['head']=get_user_image($info['uid']);
		//推送
		//$push=pushSingle($r_uid,$data['content'],'','1000',$data);
		if($res){
			api_msg('发送成功！');
			return true;
		}
		api_msg('错误！');
		return false;
	}
	
	/**
	 * 留言列表
	 * @param unknown $uid
	 * @param number $page
	 * @param number $page_size
	 * @return boolean|unknown
	 */
	static public function chatList($uid,$page=1,$page_size=10){
		$sql='select * from (select * from jdi_chat order by create_time desc ) c where r_uid='.$uid.' group by uid order by create_time desc';
		$data=M()->query($sql);
		$res=page($page,$page_size,$data);
		if(!$res){
			//api_msg('没有更多了！');
			return false;
		}
		foreach($res as $k=>$v){
			$info=get_table_field(($v['uid']),'uid','','company');
			$res[$k]['name']=$info['name'];
			$res[$k]['head']=get_cover_path($info['logo']);
		}
		return $res;
	}
	
	/**
	 * 留言详情
	 * @param unknown $uid
	 * @param unknown $s_uid 发送人uid
	 * @param number $page
	 * @param number $page_size
	 */
	static public function chatContent($uid,$s_uid,$page=-1,$page_size=10){
		if(isset($_POST['page']) && $_POST['page']<1){
			//api_msg('没有更多了！');
			return false;
		}
		$map['_string']='(uid='.$s_uid.' and r_uid='.$uid.') OR (uid='.$uid.' and r_uid='.$s_uid.')';
		$count=M('chat')->where($map)->count();
		if($page==-1){
			$page=(string)ceil($count/$page_size);
		}
		$res=M('chat')
		->where($map)
		->page($page,$page_size)
		->order('create_time asc')
		->select();
		foreach($res as $k=>$v){
			$user=get_table_field($v['uid'],'uid','','company');
		}
		if(!$res){
			return false;
		}
		$chatNum=M('chat')->where(array('uid'=>$s_uid,'r_uid'=>$uid,'status'=>0))->count();
		M('chat')->where(array('uid'=>$s_uid,'r_uid'=>$uid))->save(array('status'=>1));
		$sInfo=get_table_field($s_uid,'uid','','company');
		$data['page']=$page;
		$data['head']=get_cover_path($sInfo['logo']);
		$data['list']=$res;
		$data['chatNum']=$chatNum?(string)$chatNum:'0';
		return $data;
	}
}
?>