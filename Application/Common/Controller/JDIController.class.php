<?php
namespace Common\Controller;
use  Think\Page;

/**
 * 前台模块控制器父类
 */
class JDIController extends BaseController {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		echo '404';
	}

    protected function _initialize(){
        parent::_initialize();
        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
        C('VIEW_PATH',temp_path().'/');
        C('TMPL_PARSE_STRING.__THEME__', __ROOT__.substr(C('TEMP_PATH'),1).'/'.C('JDI_THEME').'/asset');//模版资源路径
        C('TMPL_PARSE_STRING.__MODULE_THEME__', __ROOT__.substr(C('TEMP_PATH'),1).'/'.C('JDI_THEME').'/'.MODULE_NAME.'/asset');//模块私有资源路径
        // 获取当前用户ID
        define('UID',is_login());
    }

    /**
     * 下载
     * @param $id
     * @return bool
     */
    protected  function download($id){
        $File = D('File');
        $root = C('DOWNLOAD_UPLOAD.rootPath');
        if(false === $File->download($root, $id)){
             $this->error($File->getError());
        }
    }

    /**
     * 模块内资源视图展示
     * @author lihao <lh@tiptime.com>
     * @param $s
     */
    /**
     * 模块内资源视图展示
     * @author lihao <lh@tiptime.com>
     * @param $s
     */
    protected function _display($s=''){
    	$base_path = temp_path().'/';
    	if(empty($s)){
    		if (ismobile()) {
    			$s = $base_path.'Mobile/'.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
    		}else{
    			$s = $base_path.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
    		}
    	}else{
    		if(!strpos($s,'/')){ //当前控制器
    			if (ismobile()) {
    				$s = $base_path.'Mobile/'.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.$s;
    			}else{
    				$s = $base_path.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.$s;
    			}
    		}else{
    			if (ismobile()) {
    				$s = $base_path.'Mobile/'.MODULE_NAME.'/'.$s;
    			}else{
    				$s = $base_path.MODULE_NAME.'/'.$s;
    			}
    			 
    		}
    	}
    	$s .=  C('TMPL_TEMPLATE_SUFFIX');
    	if(!is_file($s)){
    		$this->error('衣服丢了- -');
    	}else{
    			
    		$this->display($s);
    	}
    
    }
    /* protected function _display($s=''){
        $base_path = temp_path().'/';
        if(empty($s)){
            $s = $base_path.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        }else{
            if(!strpos($s,'/')){ //当前控制器
                $s = $base_path.MODULE_NAME.'/'.CONTROLLER_NAME.'/'.$s;
            }else{
                $s = $base_path.MODULE_NAME.'/'.$s;
            }
        }
        $s .=  C('TMPL_TEMPLATE_SUFFIX');
        if(!is_file($s)){
            $this->error('衣服丢了- -');
        }else{
            $this->display($s);
        }
    }
 */
    protected function page($total,$page_size,$other_params=array()){
        $page = new Page($total,$page_size,$other_params);
        if($total>10){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page->setConfig('div_class','pagination');
        $page->setConfig('current_class','active');
        $this->assign("_page",$page->home_show());
    }
}
