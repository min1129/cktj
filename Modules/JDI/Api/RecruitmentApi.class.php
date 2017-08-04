<?php
namespace Modules\JDI\Api;
use Common\Api\CategoryApi;
use Common\Api\DocumentApi;
/**
 * 此文档提供功能<br/>
 * 招聘信息相关
 * @author lh
 */
class RecruitmentApi {
    /**
     * 查询招聘信息
     * @param int $page 页数
     * @param int $page_size 页面大小
     * @param int $queryType 查询类型 0代表查看所有信息，一般是用户信息显示，1表示企业在个人中心查看，2表示审核查看
     * @param int $status 查询条件选择 要查询的状态 <span style="color:red">只有在$queryType等于2的时候有效</span>
     * @param null $search 搜索条件
     * @param int $category 类别
     * @param int $order 排序
     * @param int $base64 是否base64转码
     * @param int $category_id 筛选栏目
     * @param int $profess 所需专业
     * @return bool|mixed
     */
    public  static  function lists($page=1,$page_size=10,$queryType=0,$status=0,$search=null,$category=-1,$order=-1,$base64=0,$category_id=1,$profess=-1){
        return self::m_lists($page,$page_size,$queryType,$status,$search,$category,$order,null,$base64,$category_id,$profess);
    }

    /**
     * 取特定招聘信息
     * @param string $id 要取的招聘信息 例如 1,2,3
     * @return bool|mixed
     */
    public static function getRecruitmentByIds($id){
        if(is_array($id)){
            $id = arr2str($id);
        }
        return self::m_lists(1,10,0,1,null,-1,-1,$id,0,-1);
    }
    
    public static function getRecruitmentAllByIds($id){
    	if(is_array($id)){
    		$id = arr2str($id);
    	}
    	return self::m_lists(1,10,3,0,null,-1,-1,$id,0,-1);
    }

    /**
     * 查询招聘信息
     * @param int $page 页数
     * @param int $page_size 页面大小
     * @param int $queryType 查询类型 0代表查看所有信息，一般是用户信息显示，1表示企业在个人中心查看，2表示审核查看
     * @param int $status 查询条件选择 要查询的状态 <span style="color:red">只有在$queryType等于2的时候有效</span>
     * @param null $search 搜索条件
     * @param int $category 类别
     * @param int $order 排序
     * @param null $id 如果填写此项则查询特点id的记录
     * @param int $base64 是否base64转码
     * @param int $category_id 栏目id 默认是招聘信息栏目
     * @param int $profess 所需专业
     * @return bool|mixed
     */
    private  static function m_lists($page=1,$page_size=10,$queryType=0,$status=0,$search=null,$category=-1,$order=-1,$id=null,$base64=0,$category_id=1,$profess=-1){
        //排序
        if($order == 1){
            $order = "r.create_time desc";
        }elseif($order == 2){
           // $order = "r.view desc";
	   $order = "rate desc,r.view desc";
        }else{
            $order = "r.recommand desc,r.create_time desc";
        }
        //关键字查询
        $join0 =  C('DB_PREFIX').'member m ON r.uid=m.id';
        $join1 = C('DB_PREFIX').'tree t ON r.position3=t.id';
        $join2 = C('DB_PREFIX').'company c ON r.uid=c.uid';
        $field = "r.*,t.name as position,c.name as com_name,c.description as com_description,c.logo as com_logo,c.company_attr as com_attr";
	//最新热度计算方法
        if($order=='rate desc,r.view desc'){
        	$field.=',(r.resume_num/r.number) as rate';
        }
        $query_str =  "m.status=1 AND";
        if($category_id > 0){
            $query_str  .= " r.category_id={$category_id} AND ";
        }
        
        if($profess!=-1){
        	$query_str.= "r.profess={$profess} AND";
        }
        
        //公司在个人中心请求则只返回改公司的招聘信息
        if($queryType == 1){
            $uid =UID;
            $query_str .= "  r.uid={$uid} AND r.status>-1";
        }elseif($queryType == 0){
            $query_str .= " r.status=1 ";
        }elseif($queryType == 2){
            $filter = get_create_uid();
            $query_str .= "  r.uid in({$filter}) AND r.status={$status} ";
        }elseif($queryType == 3){
        	$query_str .= " r.status>=-1 ";
        }

        //筛选公司属性
        if($category != -1){
        	$query_str.= "AND c.company_attr={$category} ";
        }

        //筛选特定的招聘信息
        if($id){
            $query_str.= "AND r.id in({$id})";
        }

        //关键字查询
        if($search){
            if($base64){
                $search = base64_decode($search);
            }
            //关键字匹配
            $key_word = preg_split('/[,;\s+]+/', trim($search));
            //查询条件构造
            //循环构造每个关键词的查询条件
            for($i=0;$i<count($key_word);$i++){
                $key = $key_word[$i];
                if($key){
                    $query_str .= " AND (BINARY r.title LIKE '%{$key}%' OR BINARY r.place LIKE '%{$key}%' OR BINARY r.other_welfare LIKE '%{$key}%' OR BINARY r.position_des LIKE '%{$key}%' OR BINARY t.name LIKE '%{$key}%' OR BINARY c.name LIKE '%{$key}%') ";
                }
            }
        }

        $map['_string'] =$query_str;
        //计算总的记录数
        $total = M("Recruitment")
            ->alias("r")
            ->join($join0)
            ->join($join1,'left')
            ->join($join2,'left')
            ->field($field)
            ->where($map)
            ->count();
        $model = M("Recruitment")
            ->alias("r")
            ->join($join0)
            ->join($join1,'left')
            ->join($join2,'left')
            ->field($field)
            ->where($map);
        
        api_page($total);
        if(is_numeric($page)){
            $model->page($page,$page_size);
        }else{
            $model->limit($page);
        }
        $result = $model->order($order)->select();
        if($result){
            if(is_numeric($id)){
                M('Recruitment')->where(array('id'=>$id))->setInc('view',1);
            }
            //结果处理
            $result = self::parseRecruitment($result);
            return $result;
        }else{
            api_msg("暂无数据~~");
            return false;
        }
    }

    /**
     * 添加或者招聘信息
     */
    public static function modifyRecruitment(){
        if(!UID){
            api_msg("用户必须先登录!");
            return false;
        }
        if($_POST['id']){
            $re_uid = M('Recruitment')->where(array('id'=>$_POST['id']))->field('uid')->find();
            if($re_uid['uid']!= UID){
                api_msg("您只能编辑自己发布的信息!");
                return false;
            }
        }

		
        /*安全性设置*/
        unset($_POST['create_time']);
        unset($_POST['update_time']);
        unset($_POST['is_up']);
        unset($_POST['collect_num']);
        unset($_POST['resume_num']);
        unset($_POST['view']);
        unset($_POST['deadline']);
        unset($_POST['link']);
        unset($_POST['recommand']);
        unset($_POST['recruited']);

        $_POST['uid'] = UID;
        if(!is_tsw_company()){
            $_POST['category_id'] = 1; //只能添加在招聘信息栏目
            $_POST['status'] = 1; // 2016-03-12 直接通过 不审核 待审核=2
            if(is_numeric($_POST['number'])){
                if($_POST['number']<1){
                    api_msg("招聘人数不能为小于0");
                    return false;
                }else{
                		//2016-3-16 企业发布招聘 名额不再受限
//                     $max_number = get_company_quota(UID,$_POST['id']);
//                     if($_POST['number'] > $max_number){
//                         api_msg("招聘名额超过了最大限制!");
//                         return false;
//                     }
                }
            }else{
                api_msg("招聘人数必须是正整数");
                return false;
            }
        }
        //其他福利
		if($_POST['welfare']){
			$tem=array();
			for($i=0;$i<count($_POST['welfare']);$i++){
				array_push($tem,C("WELFARE.".$_POST['welfare'][$i]));
			}
			$tem=implode(',',$tem);
			if($_POST['other_welfare']){
				$_POST['other_welfare']=$tem.','.$_POST['other_welfare'];
			}else{
				$_POST['other_welfare']=$tem;
			}
		}
		
        if(!$_POST['welfare']){
            $_POST['welfare'] = array(0);
        }
        
        $Model  =   checkAttr(M('Recruitment'),"Recruitment");
        $has_image = false;
        if(!empty($_FILES)){
            $has_image = true;
            $images = upload_image();
            if($images['status'] == 0){// 图片上次失败
                api_msg($images['msg']);
                return false;
            }
        }
        if($Model->create() !== false){
            if($_POST["id"]){
                $result = $Model->save();
            }else{
                $result = $Model->add();
            }
            if($has_image){
                $data['id'] = $result;
                $imagesData = $images['data'];
                $ids = array_column($imagesData,"id");
                $data['cover'] =   arr2str($ids);
                M('Recruitment')->save($data); //保存上传的图片
            }
            return $result;
        } else {
            api_msg($Model->getError());
            return false;
        }
    }

    /**
     * 处理简历信息
     * @param $result
     * @return mixed
     * @ignore
     */
    private  static function parseRecruitment($result){
        $r = content_url($result,function(&$e){
            /* $e['com_logo'] =thumb(get_cover_path($e['com_logo']),72,72); */
        	$e['com_logo'] =get_cover_path($e['com_logo']);
            $e['position_des'] = htmlspecialchars_decode($e['position_des']);
            $e['com_description'] = htmlspecialchars_decode($e['com_description']);
            $e['is_collect'] = StaffApi::hasStaff('recruitment',$e['id']);
            $e['is_resume'] = StaffApi::hasStaff('recruitment',$e['id'],'resume',array('status'=>array('gt',-1)));
			//不明原因注释掉了 开启
			if(APP_MODE=='api'){

                //$e['url'] = str_replace('api.php','index.php',self::U('Zm/Recruitment/detail?id='.$e['id']));
				$e['url'] = __ROOT__.'/index.php/Zm/Recruitment/detail?id='.$e['id'];

            }
            $e['is_over'] = ($e['number']==$e['recruited'])?1:0;

            if($e['welfare']){
                $arr= str2arr($e['welfare']);
                $e['welfare'] = "";
                for($i=0;$i<count($arr);$i++){
                    $e['welfare'] .= ",".C("WELFARE.".$arr[$i]);
                }
               $e['welfare'] = substr($e['welfare'],1);
            }else{
                $e['welfare'] = "";
            }
            if($e['other_welfare']){
            	$e['welfare']=$e['other_welfare'];
            }
            if(!$e['recommend_link']){
            	$e['recommend_link']='';
            }
            if($e['cover_pic']){
            	$e['cover_pic']=get_cover_path($e['cover_pic']);
            }
            $e['number'] = $e['number']==0?"不限":$e['number'];
            $e['salary'] = C("MONTH_SALARY.".$e['salary']);
        });
        return $r;
    }

    /**
     * thinkphp的U函数，因为api模式默认是不装载U函数的但是在api中我们用到了
     * 所以在这里重新写一个
     * @i
     */
    private static function  U($url='',$vars='',$suffix=true,$domain=false){
            if(!defined('__APP__')){
                $varPath        =   C('VAR_PATHINFO');
                if(isset($_GET[$varPath])) { // 判断URL里面是否有兼容模式参数
                    $_SERVER['PATH_INFO'] = $_GET[$varPath];
                    unset($_GET[$varPath]);
                }elseif(IS_CLI){ // CLI模式下 index.php module/controller/action/params/...
                    $_SERVER['PATH_INFO'] = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
                }
                $urlMode        =   C('URL_MODEL');
                if($urlMode == URL_COMPAT ){// 兼容模式判断
                    define('PHP_FILE',_PHP_FILE_.'?'.$varPath.'=');
                }elseif($urlMode == URL_REWRITE ) {
                    $url    =   dirname(_PHP_FILE_);
                    if($url == '/' || $url == '\\')
                        $url    =   '';
                    define('PHP_FILE',$url);
                }else {
                    define('PHP_FILE',_PHP_FILE_);
                }
                // 当前应用地址
                define('__APP__',strip_tags(PHP_FILE));
            }
            // 解析URL
            $info   =  parse_url($url);
            $url    =  !empty($info['path'])?$info['path']:ACTION_NAME;
            if(isset($info['fragment'])) { // 解析锚点
                $anchor =   $info['fragment'];
                if(false !== strpos($anchor,'?')) { // 解析参数
                    list($anchor,$info['query']) = explode('?',$anchor,2);
                }
                if(false !== strpos($anchor,'@')) { // 解析域名
                    list($anchor,$host)    =   explode('@',$anchor, 2);
                }
            }elseif(false !== strpos($url,'@')) { // 解析域名
                list($url,$host)    =   explode('@',$info['path'], 2);
            }
            // 解析子域名
            if(isset($host)) {
                $domain = $host.(strpos($host,'.')?'':strstr($_SERVER['HTTP_HOST'],'.'));
            }elseif($domain===true){
                $domain = $_SERVER['HTTP_HOST'];
                if(C('APP_SUB_DOMAIN_DEPLOY') ) { // 开启子域名部署
                    $domain = $domain=='localhost'?'localhost':'www'.strstr($_SERVER['HTTP_HOST'],'.');

                    // '子域名'=>array('模块[/控制器]');
                    foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                        $rule   =   is_array($rule)?$rule[0]:$rule;
                        if(false === strpos($key,'*') && 0=== strpos($url,$rule)) {
                            $domain = $key.strstr($domain,'.'); // 生成对应子域名
                            $url    =  substr_replace($url,'',0,strlen($rule));
                            break;
                        }
                    }
                }
            }

            // 解析参数
            if(is_string($vars)) { // aaa=1&bbb=2 转换成数组
                parse_str($vars,$vars);
            }elseif(!is_array($vars)){
                $vars = array();
            }
            if(isset($info['query'])) { // 解析地址里面参数 合并到vars
                parse_str($info['query'],$params);
                $vars = array_merge($params,$vars);
            }

            // URL组装
            $depr       =   C('URL_PATHINFO_DEPR');
            $urlCase    =   C('URL_CASE_INSENSITIVE');
            if($url) {
                if(0=== strpos($url,'/')) {// 定义路由
                    $route      =   true;
                    $url        =   substr($url,1);
                    if('/' != $depr) {
                        $url    =   str_replace('/',$depr,$url);
                    }
                }else{
                    if('/' != $depr) { // 安全替换
                        $url    =   str_replace('/',$depr,$url);
                    }
                    // 解析模块、控制器和操作
                    $url        =   trim($url,$depr);
                    $path       =   explode($depr,$url);
                    $var        =   array();
                    $varModule      =   C('VAR_MODULE');
                    $varController  =   C('VAR_CONTROLLER');
                    $varAction      =   C('VAR_ACTION');
                    $var[$varAction]       =   !empty($path)?array_pop($path):ACTION_NAME;
                    $var[$varController]   =   !empty($path)?array_pop($path):CONTROLLER_NAME;
                    if($maps = C('URL_ACTION_MAP')) {
                        if(isset($maps[strtolower($var[$varController])])) {
                            $maps    =   $maps[strtolower($var[$varController])];
                            if($action = array_search(strtolower($var[$varAction]),$maps)){
                                $var[$varAction] = $action;
                            }
                        }
                    }
                    if($maps = C('URL_CONTROLLER_MAP')) {
                        if($controller = array_search(strtolower($var[$varController]),$maps)){
                            $var[$varController] = $controller;
                        }
                    }
                    if($urlCase) {
                        $var[$varController]   =   parse_name($var[$varController]);
                    }
                    $module =   '';

                    if(!empty($path)) {
                        $var[$varModule]    =   implode($depr,$path);
                    }else{
                        if(C('MULTI_MODULE')) {
                            if(MODULE_NAME != C('DEFAULT_MODULE') || !C('MODULE_ALLOW_LIST')){
                                $var[$varModule]=   MODULE_NAME;
                            }
                        }
                    }
                    if($maps = C('URL_MODULE_MAP')) {
                        if($_module = array_search(strtolower($var[$varModule]),$maps)){
                            $var[$varModule] = $_module;
                        }
                    }
                    if(isset($var[$varModule])){
                        $module =   $var[$varModule];
                        unset($var[$varModule]);
                    }

                }
            }

            if(C('URL_MODEL') == 0) { // 普通模式URL转换
                $url        =   __APP__.'?'.C('VAR_MODULE')."={$module}&".http_build_query(array_reverse($var));
                if($urlCase){
                    $url    =   strtolower($url);
                }
                if(!empty($vars)) {
                    $vars   =   http_build_query($vars);
                    $url   .=   '&'.$vars;
                }
            }else{ // PATHINFO模式或者兼容URL模式
                if(isset($route)) {
                    $url    =   __APP__.'/'.rtrim($url,$depr);
                }else{
                    $module =   (defined('BIND_MODULE') && BIND_MODULE==$module )? '' : $module;
                    $url    =   __APP__.'/'.($module?$module.MODULE_PATHINFO_DEPR:'').implode($depr,array_reverse($var));
                }
                if($urlCase){
                    $url    =   strtolower($url);
                }
                if(!empty($vars)) { // 添加参数
                    foreach ($vars as $var => $val){
                        if('' !== trim($val))   $url .= $depr . $var . $depr . urlencode($val);
                    }
                }
                if($suffix) {
                    $suffix   =  $suffix===true?C('URL_HTML_SUFFIX'):$suffix;
                    if($pos = strpos($suffix, '|')){
                        $suffix = substr($suffix, 0, $pos);
                    }
                    if($suffix && '/' != substr($url,-1)){
                        $url  .=  '.'.ltrim($suffix,'.');
                    }
                }
            }
            if(isset($anchor)){
                $url  .= '#'.$anchor;
            }
            if($domain) {
                $url   =  (is_ssl()?'https://':'http://').$domain.$url;
            }
            return $url;
    }

    /**
     * 或的逐梦专项的栏目分类
     * @return mixed
     */
    public  static function  getZmzxCategory(){
        $category =   M('Node')->where(array('pid'=>1,'status'=>1))->field('id,name')->select();
        return $category;
    }
    /**
     * 青锐相关文章
     * @param string $op 0 易就业  1职派学院
     * @param string $page
     * @param string $page_size 
     * @return boolean|unknown
     */
    public static function getQingruiArtical($op=0,$page=1,$page_size=10){
    	$db=M('article');
    	$map['status']=1;
    	if(!$op){
    		$map['category_id']=16;
    	}else{
    		$map['category_id']=15;
    	}
    	$db->page($page,$page_size);
    	$res=$db->where($map)
    	->field('id,title,category_id,description,update_time,cover,link')
    	->order('weight desc,update_time desc')
    	->select();
    	if(!$res){
    		api_msg('暂无数据~~');
    		return false;
    	}
    	for($i=0;$i<count($res);$i++){
    		$res[$i]['cover']=get_cover_path($res[$i]['cover']);
    		$res[$i]['des']=$res[$i]['description'];
    		$res[$i]['url']=__ROOT__.'/index.php/Home/Index/content/cate/'.$res[$i]['category_id'].'/id/'.$res[$i]['id'];
    	}
    	return $res;
    }
    
}
