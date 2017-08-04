<?php
namespace Modules\JDI\Controller;
use Common\Controller\ModuleController;
use Think\Controller;
use Think\Page;


class LuYongController extends ModuleController {
    /**
     * 学校录用信息
     */
    public  function  index($base_where,$flag='normal'){
        $p = I('p',1);
        $page_size = I('r',10);
        $pre = C('DB_PREFIX');
        $where = $base_where.self::search_parse();
        $total =  M('UserStaff')->alias('u')
            ->join($pre.'cresume r ON u.bundle=r.id','left')
            ->join($pre.'recruitment rt ON rt.id = u.topic_id ','left')
            ->join($pre.'company c ON c.uid = rt.uid','left')
            ->where(array('_string'=>$where))
            ->count();

        $model =  M('UserStaff')->alias('u')
            ->join($pre.'cresume r ON u.bundle=r.id','left')
            ->join($pre.'recruitment rt ON rt.id = u.topic_id ','left')
            ->join($pre.'company c ON c.uid = rt.uid','left')
            ->where(array('_string'=>$where))
            ->field('r.*,c.name as com_name,u.update_time as time');

        $type = I('type');
        unset($_REQUEST['type']);
        if($type>0){//导出
            $list = $type==2?$model->select():$model->page($p,$page_size)->select();
            for($i=0;$i<count($list);$i++){
                $list[$i]['sex'] = $list[$i]['sex'] ==0?"女":"男";
                $list[$i]['school1'] = get_tree_name($list[$i]['school1']);
                $list[$i]['school2'] = get_tree_name($list[$i]['school2']);
                $list[$i]['time'] = date('Y-m-d',$list[$i]['time']);
            }
            exportExcel(array('录用信息导出',array('name'=>'学生姓名'
            ,'sex'=>'学生性别'
            ,'school1'=>'所在学校'
            ,'school2'=>'所在学院'
            ,'studentID'=>'学号'
            ,'com_name'=>'录取公司名称'
            ,'time' =>'录取时间')),$list);
        }else{
            $list = $model->page($p,$page_size)->select();
            if($list){
                $page = new Page($total,$page_size,$_REQUEST);
                if($total>$page_size){
                    $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                }
                $page->setConfig('div_class','pagination');
                $page->setConfig('current_class','active');
                $this->assign("_page",$page->home_show());
                $this->assign('list',$list);
            }
         	if($flag=='normal'){
            	$this->display(T('Modules://JDI@LuYong/index'));
            }elseif($flag=='school'){
            	self::getSchoolId();
            	$this->display(T('Modules://JDI@LuYong/school'));
            }elseif($flag='college'){
            	$this->display(T('Modules://JDI@LuYong/college'));
            }
        }

    }
	
    public function getSchoolId(){
    	$info=get_table_field(UID,'id','','member');
    	$s1=(substr($info['username'],1,(strlen($info['username'])-1)));
    	$this->assign('sc',$s1);
    }
    
    private function search_parse(){
        $where = "";
        $team_map[] = array();
        foreach($_REQUEST as $k => $v){
            $kk = str2arr($k,'_');
            if($kk[0] == 'query'){ //查询字段
                if(trim($_REQUEST[$k]) === ""){
                    continue;
                }
                if($kk[1] == 'company'){//查询公司
                    $company_ids = arr2str(array_column(M('Company')->where(array('_string'=>"BINARY `name` LIKE '%{$v}%'"))->field('id')->select(),'id'));
                    $where .= " AND c.id in ({$company_ids})";
                }

                if($kk[1] == 'school'){ //查询学校
                    $school_ids = arr2str(array_column(M('Tree')->where(array('_string'=>"BINARY `id` LIKE '%{$v}%'"))->field('id')->select(),'id'));
                    $where .= " AND r.school1 in ({$school_ids})";

                }
				
                if($kk[1]=='school2'){
                	$school2_ids = arr2str(array_column(M('Tree')->where(array('_string'=>"BINARY `id` LIKE '%{$v}%'"))->field('id')->select(),'id'));
                	$where .= " AND r.school2 in ({$school2_ids})";
                }
                
                if($kk[1] == 'start'){
                    $start = strtotime($v);
                    $where .=  " AND u.update_time >".$start;
                }

                if($kk[1] == 'end'){
                    $end = strtotime($v);
                    $end && ($where .=  " AND u.update_time <".$end);
                }
                $team_map[$k] = $v;
            }
        }
        if(I('r')){
            $team_map['r']= I('r');
        }
        $this->assign('where',$team_map);
        $this->assign('s2',$school2_ids);
        return $where;
    }
}