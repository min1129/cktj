<?php
namespace Home\Controller;
use Common\Api\CategoryApi;
use Common\Api\DocumentApi;
use Common\Api\ModelApi;
use Common\Controller\JDIController;
use Modules\JDI\Api\ResumeApi;
use Modules\JDI\Api\RecruitmentApi;
use Think\Controller;
use Think\Page;



/**
 * 站点主模块控制器
 * Class IndexController
 * @package Home\Controller
 * @author by li
 */
class IndexController extends JDIController {
    /**
     * 首页
     * 主要用于显示首页和加载招聘信息
     * @param int $ajax 是否是ajax请求，如果是则是前端的异步查询操作
     * @param int $p 页数
     * @param int $category 筛选条件 企业属性 具体含义参照 企业模型字段company_attr
     * @param int $order 排序 1代表最新 2代表最热
     * @param null $title 输入关键字筛选
     * @param int $profess 所需专业
     */
    public function index($ajax=0,$p=1,$category=-1,$order=-1,$title=null,$profess=-1){
    	$flag=I('get.flag')?I('get.flag'):0;
        //青锐专项
    	if(!$ajax){
    		$re['qrzx']= RecruitmentApi::lists(1,4,0,1,null,-1,1,0,5000);
    	}
    	if(ismobile()){
    		$this->assign('HOME',true);
    		$this->assign('list',$re);
    		$this->assign('info',RecruitmentApi::lists($p));
    		$this->page(api_page(),10);
    		$this->assign('btm_btn',1);
    		$this->_display();
    		exit;
    	}
    	$re['jgsy']= RecruitmentApi::lists(1,4,0,1,$title,2,$order,0,1,$profess);
    	$re['gyqy']= RecruitmentApi::lists(1,4,0,1,$title,0,$order,0,1,$profess);
    	$re['wzqy']= RecruitmentApi::lists(1,4,0,1,$title,3,$order,0,1,$profess);
    	$re['myqy']= RecruitmentApi::lists(1,4,0,1,$title,1,$order,0,1,$profess);
    	$re['sqnc']= RecruitmentApi::lists(1,4,0,1,$title,4,$order,0,1,$profess);
    	foreach($re as $k=>$v){
    		if($v){
    			$bool=true;
    		}
    	}
        //如果有结果则处理
        if($bool){
            //对结果进行处理
            foreach($re as $k=>$v){
            	if($v){
            		for($i=0;$i<count($v);$i++){
            			$re[$k][$i]['title'] = msubstr($v[$i]['title'],0,16);
            			$re[$k][$i]['description'] = msubstr( $v[$i]['description'],0,100);
            			$re[$k][$i]['url'] = U('Zm/Recruitment/detail?id='.$v[$i]['id']);
            			$re[$k][$i]['create_time'] = date("Y-m-d H:i", $v[$i]['create_time']);
            		}
            	}else{
            		$re[$k]='';
            	}
            }


            if($ajax){
                //ajax返回
                $this->J_J(1,$re,"");
            }else{
                //直接渲染首页
                $this->assign('HOME',true);
                $this->assign('title',C('WEB_SITE_TITLE'));
                $this->assign('list',$re);
                $this->assign("page_size",10);
                $this->assign("category",$category);
                $this->assign("title",$title);
                $this->assign('flag',$flag);
            }
        }else{
            if($ajax){
                $this->J_J(0,"","没有更多信息了!");
            }
        }
        $this->_display();
    }


    //TODO 默认是搜索文章模型,可以给用户选择搜索其他模型或者都搜索
    /**搜索操作
     * @param $search
     * @param int $p
     */
    public function search($search,$p=1){
        $array = preg_split('/[,;\r\n]+/', trim($search, ",;\r\n"));
        if(!$array){
            $this->error('查询条件不能为空!');
        }else{
            $list = api('Document/search',array('search'=>$array,'page'=>$p,'page_num'=>10));
            $total  = api('Document/searchCount',array('search'=>$array));
            $page = new Page($total,10,array('search'=>$search));
            if($total>10){
                $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
            }
            $page->setConfig('div_class','pagination');
            $page->setConfig('current_class','active');
            $show       = $page->home_show();
            $this->assign('list',$list);
            $this->assign('page',$show);
            $nav[] = array('name'=>'首页','url'=>U('Index/index'));
            $nav[] = array('name'=>'搜索:'.$search,'url'=>"#");
            $this->assign('nav',$nav);
            $this->assign('tip','没找到您要的搜索结果,换个关键词试试吧!');
            $this->assign('title',"搜索－$search");
            $this->_display('list'); //TODO 添加搜索模版
        }
    }


    /**
     * 二级页面统一入口
     * @param mixed $cate 栏目
     * @param int $p  页数
     */
    public function  category($cate,$p=1){
        $Category = CategoryApi::get_category($cate);
        $cate = $Category['id'];
        if(!$Category){
            $this->error('没找到该分类~~~');
        }

        $this->assign('cat',$Category);
        $this->assign('title',$Category['name']);
        $this->parse_nav($Category,'');

        if($Category['type']==1){
            //栏目
            if(api('Category/get_children_count',array('id'=>$cate))>0){
                //有子栏目,渲染频道模板
                $this->_display($Category['temp_category']);
            }else{//渲染列表页模版
                $list =  DocumentApi::lists($cate,$p);
                //解决有外链url变成外链地址的问题
                foreach($list as $k=>$v){
                	if($v['link']){
                		$list[$k]['url']=U('Home/Index/content?cate='.$cate.'&id='.$v['id']);
                	}
                }
                
                $total = DocumentApi::listCount($cate);
                $page = new Page($total,$Category['list_num'],array('cate'=>$cate));
                if($total>$Category['list_num']){
                    $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                }
                $page->setConfig('div_class','pagination');
                $page->setConfig('current_class','active');
                $show       = $page->home_show();
                $this->assign('list',$list);
                $this->assign('page',$show);
                $this->assign('cate',$Category);
                $this->assign('btm_btn',1);
                $this->_display($Category['temp_list']);
            }
        }elseif($Category['type']==2){
            //单页面
            $model_id =  CategoryApi::get_category($cate,'model_id');
            if(empty($model_id)){//分类不存在或者被禁用
                $this->error('分类不存在或者被禁用!');
            }
            $model_name = ModelApi::get_model_by_id($model_id,'name');


            if(empty($model_name)){//模型不存在或者被禁用
                $this->error('模型不存在或者被禁用!');
            }
            $info =  DocumentApi::record($cate,$model_name);
            $this->assign("PAGE_DESC",$info['description']);
            $this->assign('cat',$Category);
            $this->assign('info',$info);
            $this->_display($Category['temp_content']);
        }elseif($Category['type']==3){
            //外部链接
            JDIRedirect($Category['url']);
        }
    }

    /**
     * 解析导航
     * @param array $cat 当前栏目id
     * @param string $content_title 内容页标题
     * @param int $pop_num  控制导航显示深度
     */
    private  function  parse_nav($cat,$content_title,$pop_num=1){
        //导航
        $nav =array();
        $cat_name = $cat['name'];
        if(!empty($content_title)){ //内容页导航
            $cat_url = list_url($cat);
            $cat_url = $cat_url['url'];
        }else{ //列表页导航
            $cat_url = "#";
        }

        if($cat['pid']==0){
            array_push($nav,array('name'=>$cat['name'],'url'=>$cat_url));
            $root_nav = $cat['id'];//根栏目,控制导航显示
        }else{
            while(true){
                $cat = CategoryApi::get_category($cat['pid']);
                $cat = list_url($cat);
                array_push($nav,array('name'=>$cat['name'],'url'=>$cat['url']));
                if($cat['pid']==0){
                    $root_nav = $cat['id'];//根栏目,控制导航显示
                    for($i=0; $i<$pop_num;$i++){
                        array_pop($nav); //控制导航显示深度
                    }
                    if(!empty($content_title)){
                        array_push($nav,array('name'=>$content_title,'url'=>'#'));
                    }
                    array_push($nav,array('name'=>$cat_name,'url'=>$cat_url));
                    break;
                }
            }
        }
        array_push($nav,array('name'=>'首页','url'=>U('Index/index')));
        $nav = array_reverse($nav);
        $this->assign('rootNav',$root_nav);
        $this->assign('nav',$nav);
    }

    /**
     * 内容页统一入口
     * @param mixed $cate 栏目
     * @param int $id 对应内容id
     */
    public  function content($cate,$id){
        $Category =  CategoryApi::get_category($cate);
        $cate = $Category['id'];
        $model_id =  $Category['model_id'];
        if(empty($model_id)){//分类不存在或者被禁用
            $this->error('分类不存在或者被禁用!');
        }
        $model_name = api('Model/get_model_by_id',array('id'=>$model_id,'field'=>'name'));
        if(empty($model_name)){//模型不存在或者被禁用
            $this->error('模型不存在或者被禁用!');
        }
        $info = DocumentApi::record($cate,$model_name,$id);
        $this->assign('title',$info['title']);
        if(!$info){
            $this->error('您查询的内容不存在!');
        }else{
            //文件下载
            if(isset($info['file'])){
                if($this->download($info['file'])===false){
                    $this->error('文件找不到了..');
                }
            }else{
                $Category =  CategoryApi::get_category($cate);
                //导航
                $this->parse_nav($Category,$info['title']);
                $this->assign("meta_title",$info['title']);
                $this->assign("PAGE_DESC",$info['description']);
                $this->assign('cat',$Category);
                $this->assign('info',$info);
                $this->_display($Category['temp_content']);
            }
        }
    }

    /**
     * 助力逐梦 列表页入口
     * 助力逐梦是 分类id 2 和 3 的列表数据
     * @param int $p 页数
     */
    public  function zlzm($p=1){
        $map = array('category_id'=>array('in',"2,3"),"status"=>1);
        $map['create_time'] = array('lt', NOW_TIME);
        $map['_string']     = 'deadline <= 0 OR deadline > ' . NOW_TIME;
        $total = M('article')->where($map)->count();
        $list = M('article')->where($map)->page($p,10)->select();
        $list = content_url($list);
        $page = new Page($total,10);
        if($total>10){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page->setConfig('div_class','pagination');
        $page->setConfig('current_class','active');
        $show       = $page->home_show();
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->_display('list_zhuli');
    }

//    /**登陆
//     * @param null $username 用户名
//     * @param null $password 密码
//     * @param null $verify 验证码
//     */
//    public function login($username = null, $password = null, $verify = null){
//        if(IS_POST){
//            if(!check_verify($verify)){
//                $this->error("验证码输入错误");
//            }
//            $Member = D('Member');
//            $uid = $Member->checkLogin($username, $password,1,false);
//            if(0 < $uid){
//                /* 登录用户 */
//                if($Member->login($uid)){ //登录用户
//                    define(UID,$uid);
//                    $this->success("登录成功!");
//                } else {
//                    $this->error("登录失败！");
//                }
//
//            } else { //登录失败
//                switch($uid) {
//                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
//                    case -2: $error = '密码错误！'; break;
//                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
//                }
//                $this->error($error);
//            }
//        } else {
//            if(is_login()){
//                $this->redirect('Index/index');
//            }else{
//                $nav[] = array('name'=>'首页','url'=>U('Index/index'));
//                $nav[] = array('name'=>'登陆','url'=>"#");
//                $this->assign('nav',$nav);
//                $this->display();
//            }
//        }
//    }
//
    /**
     * 退出登陆
     */
    public function logout(){
        if(is_login()){
            D('Member')->logout();
            session('[destroy]');
            $this->success('退出成功！', U('Index/index'));
        } else {
            $this->redirect('Index/index');
        }
    }
    public function zmxl(){
    	if(!UID){
    		$this->ajaxReturn(0);
    		exit;
    	}
    	else{
    		$this->ajaxReturn(1);
    	}
    	 
    }
    public function zmxl_login(){
    	$zan=M('zan')->where(array('uid'=>UID))->select();
    	$db=M('xinlu');
    	$count=$db->where(array('status'=>1))->count();
    	$page = new \Think\Page($count,5);
    	$show=$page->show();
    	$result=$db
    	->limit($page->firstRow.','.$page->listRows)
    	->where(array('status'=>1))
    	->order('create_time desc')
    	->select();
    	$this->assign('list',$result);
    	$this->assign('zan',json_encode($zan));
    	$this->_display('zmxl');
    }
    public function zmxl_confirm(){
    	if(!UID){
    		$this->ajaxReturn(0);
    	}
    	$data['title']=I('post.title');
    	$data['content']=I('post.text');
    	$data['uid']=UID;
    	$data['create_time']=time();
    	$data['status']=0;
    	$res=M('xinlu')->add($data);
    	if($res){
    		$this->ajaxReturn(1);
    	}
    	else{
    		$this->ajaxReturn(0);
    	}
    }
    public function zmxl_more(){
    	$id=I('get.id');
    	$res=M('xinlu')->where(array('id'=>$id))->find();
    	$this->assign('info',$res);
    	$this->_display();
    }
    public function zmxl_zan(){
    	$id=I('post.id');
    	$res=M('xinlu')->where(array('id'=>$id))->find();
    	M('xinlu')->where(array('id'=>$id))->save(array('zan'=>$res['zan']+1));
    	$data=array(
    		'uid'=>UID,
    		'cid'=>$id	
    	);
    	M('zan')->add($data);
    }
    
    public function recruitmentDetailTimeline(){
    	$id=I('get.id');
    	$uid=I('get.uid');
    	$timeLine = ResumeApi::getResumeTimeLine($id,$uid);
    	$list = ResumeApi::getResumeRecord($id,1,10,0,$uid);
    
    	$info = $list[0];
    	$re=RecruitmentApi::getRecruitmentAllByIds($info['topic_id']);
    	array_splice($timeLine,0,1);
    	//调用接口
    	$this->assign('info',$info);
    	$this->assign('re',$re[0]);
    	$this->assign('id',$id);
    	$this->assign('timeLine',$timeLine);
    	$this->_display();
    }
    
    
    //微信搜索筛选
    public function mobileSearch(){
    	$this->_display();
    }
    
    
    public function badge(){
    	dump(getBadge(I('get.uid')));
    }
    public function xiaxian(){
    	$uid=I('get.uid');
    	$res=pushSingle($uid,'提示','您已在别处登录','2000','');
    	var_dump($res);
    }
    public function tuisong(){
    	$uid=I('get.uid');
    	$res=pushSingle($uid,'提示','您已在别处登录','1020','');
    	var_dump($res);
    }
//
//
//    /**
//     * 验证码获取
//     */
//    public function verify(){
//        $verify = new \Think\Verify(array('imageH'=>30,'imageW'=>100,'length'=>4,'codeSet'=>'23456789','fontSize'  =>  12,'useCurve'  =>  false,'useNoise'  =>  false));
//        $verify->entry(1);
//    }


	public function test(){
		$model = M('Member');
		$resume = M('Resume');

		$map['r.status'] = array('gt',-1);


		$list = $resume
			->alias('r')
			->where($map)
			->field('r.id,r.name,r.birthday,r.sex,r.email,r.phone,r.evaluation,r.intension,r.head')

			->order('r.sex asc,r.head asc')
			->select();

		foreach ($list as $key=>$value){
			$value['sex'] = ($value['sex'] == 0)?'女':'男';
			$value['head'] = get_cover_path($value['head']);
			$list[$key] = $value;
		}
		$count = $resume->field('sex, count(id) as totle')->group('sex')->select();
		//dump($list);exit();
		$this->assign('count',$count);
		$this->assign('list',$list);
		$this->_display();
	}
}