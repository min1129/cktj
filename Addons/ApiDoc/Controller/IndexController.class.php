<?php

namespace Addons\ApiDoc\Controller;
use Common\Controller\AddonsController;
use Think\Exception;

/**
 * Class ApiDocController
 * @package Addons\ApiDoc\Controller
 */
class IndexController extends AddonsController{
     static $tags = array('return','param','author','time');
     public function generate(){
         $names = (array)I('post.names');
         $modules = M('Module')->where(array('status'=>1,'name'=>array('in',$names)))->select();
         $this->assign('list',$this->get_list($modules));
         $content = $this->fetch(T('Addons://ApiDoc@index/apiOnline'));
         $content = str_replace(C('TMPL_PARSE_STRING.__ADDONROOT__').'/asset/','',$content);
         $fileDir = JDICMS_ADDON_PATH.I('get._addons').'/asset';
         $filePath =$fileDir.'/index.html';
         file_put_contents($filePath,$content);

         $zip=new \ZipArchive();

         $zipPath = JDICMS_ADDON_PATH.I('get._addons').'/apiDoc.zip';
         if($zip->open(JDICMS_ADDON_PATH.I('get._addons').'/apiDoc.zip', \ZipArchive::CREATE)=== TRUE){
             $this->addFileToZip($fileDir, $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
             $zip->close(); //关闭处理的zip文件
         }
         header("Cache-Control: public");
         header("Content-Description: File Transfer");
         header('Content-disposition: attachment; filename='.'apiDoc'); //文件名
         header("Content-Type: application/zip"); //zip格式的
         header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
         header('Content-Length: '. filesize($zipPath)); //告诉浏览器，文件大小
         readfile($zipPath);
         unlink($zipPath);
         unlink($filePath);
         exit;
     }

     private  function addFileToZip($path,$zip){
        $handler=opendir($path); //打开当前文件夹由$path指定。
        while(($filename=readdir($handler))!==false){
            if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
                    $this->addFileToZip($path."/".$filename, $zip);
                }else{ //将文件加入zip对象
                    $zip->addFile($path."/".$filename);
                }
            }
        }
        @closedir($path);
    }



    /**
     * @param $ms
     * @return array
     */
    private function get_list($ms){
        $list = array();
        foreach($ms as $k=>$v){
            $result = $this->getModuleApiClass($v['name']);

            $api_module = array('module'=>$v,'apiClass'=>array(),'index'=>$k);
            foreach($result as $kk=>$cls){
                $reflect_class = new \ReflectionClass($cls['class']);
                $api_class = array();
                $class_comment = $reflect_class->getDocComment();
                preg_match('/@package(.+)\b/',$class_comment,$matchs);
                $api_class['package']=trim($matchs[1]); //包名
                preg_match('/@author(.+)\b/',$class_comment,$matchs);
                $api_class['author'] = trim($matchs[1]);//作者
                $api_class['name'] = $cls['name'];
                if(!$this->isComment($class_comment)){//纯注释
                    $api_class['introduce'] = str_replace("*","",str_replace("*/","",str_replace("/**","",$class_comment)));
                }else{
                    $api_class['introduce'] = str_replace("*","",str_replace('/**',"",str_replace(strstr($class_comment,"@"),"",$class_comment)));
                }
                $api_class['index'] = $k.','.$kk;
                $methods = $reflect_class->getMethods();
                $method_count = 0;
                foreach($methods as $kkk=>$method){
                    if($method->isPublic() && $method->isStatic()){
                        $method_comment = $method->getDocComment();
                        if(strstr($method_comment,"@ignore")){ //忽略
                            continue;
                        }

                        $real_param = $method->getParameters();
                        $rel_param = array();
                        for($i=0; $i<count($real_param);$i++){
                            $rel_param[$real_param[$i]->getName()] = $real_param[$i];
                        }
                        $api_method = array();
                        $api_method['name'] = $method->getName();
                        preg_match_all('/@param(.+)[\n\r]/',$method_comment,$matchs);

                        if(!$this->isComment($method_comment)){//纯注释
                            $api_method['introduce'] = str_replace("*","",str_replace("*/","",str_replace("/**","",$method_comment)));
                        }else{
                            $api_method['introduce'] = str_replace("*","",str_replace('/**',"",str_replace(strstr($method_comment,"@"),"",$method_comment)));
                        }
                        foreach($matchs[1] as $param){
                            $items = preg_split('/\s+/',trim($param),3); //变量和注释
                            $param_name = substr($items[1],1);
                            if($rel_param[$param_name]){
                                if($rel_param[$param_name]->isDefaultValueAvailable()){//是否必填
                                    $items[] = "<span style='color:#008000'>(可选,默认值为:".$rel_param[$param_name]->getDefaultValue().")</span>";
                                }else{
                                    $items[] = "<span style='color:red'>(必填)</span>";
                                }
                            }
                            $api_method['param'][] = $items;
                        }
                        preg_match('/@return(.+)[\n\r]/',$method_comment,$matchs); //返回值
                        $api_method['return'] = preg_split('/\s+/',trim($matchs[1]),2);
                        preg_match('/@author(.+)\b/',$method_comment,$matchs); //作者
                        $api_method['author'] = trim($matchs[1]);
                        $api_method['index'] = $k.','.$kk.','.$method_count;
                        $method_count++;
                        $M = $v['name'];
                        $C = substr($cls['name'],0,-3);
                        $A = $api_method['name'];
                        if($M != 'Common'){
                            $api_method['url'] = "_R=Modules&_M=$M&_C=$C&_A=$A";
                        }else{
                            $api_method['url'] = "_R=App&_M=$M&_C=$C&_A=$A";
                        }

                        $api_class['method'][]=$api_method;
                    }
                }
                $api_module['apiClass'][] = $api_class;
            }
            $list[] = $api_module;
        }
        return $list;
    }



    private function isComment($comment){
        foreach(IndexController::$tags as $v){
            if(strstr($comment,'@'.$v)){
                return true;
            }
        }
        return false;
    }

    public function apiOnline(){
        $modules = M('Module')->where(array('status'=>1))->field('name,title')->select();
        $modules[] = array('name'=>'Common','title'=>'系统');
        $modules[] = array('name'=>'JDI','title'=>'公共模块');
        try{
            $list =  $this->get_list($modules);

            $this->assign('list',$list);

            $class = "Addons\\ApiDoc\\ApiDocAddon";

            $addon = new $class;

            $config = $addon->getConfig();
            $this->assign('addons_config', $config);
            $this->display(T('Addons://ApiDoc@index/apiOnline'));
        }catch (\Exception $e){
            header('Content-Type:application/json; charset=utf-8');
            dump($e);
        }
    }

    private function getModuleApiClass($module){
        if("Common"==$module){
            $dir = APP_PATH.'Common/Api/';
        }else{
            $dir = JDICMS_MOUDLE_PATH.$module.'/Api/';
        }


        $result = array();
        if(is_dir($dir)){
            $handler = opendir($dir);
            while (($filename = readdir($handler)) !== false) {
                if ($filename != "." && $filename != "..") {
                    if (is_file($dir . $filename) &&  strpos($filename,"class")) {
                        $class_name= substr($filename,0,-10);
                        if("Common"==$module){
                            $result[] = array('path'=>$dir . $filename,'class'=>'Common\\Api\\'.$class_name,'name'=>$class_name);
                        }else{
                            $result[] = array('path'=>$dir . $filename,'class'=>'Modules\\'.$module.'\\Api\\'.$class_name,'name'=>$class_name);
                        }
                    }
                }
            }
            closedir($handler);
        }
        return $result;
    }
}
