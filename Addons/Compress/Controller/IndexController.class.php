<?php

namespace Addons\Compress\Controller;
use Common\Controller\AddonsController;

class IndexController extends AddonsController{
    private $path;
    private $out_path;
    public function index(){
        if($this->check()){
            $this->error("请检测您的java环境,确保按照了java并且环境变量配置正确!");
        }
        $name = I('name');
        $this->path = realpath(C('TEMP_PATH').'/'.$name);
        $this->out_path = realpath(C('TEMP_PATH'))."/{$name}_dist";
        $tree = array(); //模板文件夹的目录结构
        $compress =array(); //压缩文件列表
        $put = array(); //其他发布文件，包括HTML和其他的一些静态资源
        self::getFileTree($this->path,$tree,$compress,$put);
        $this->compressFiles($compress);
        $this->transOtherFiles($put);
        $this->error("发布成功!");
    }

    /**
     * 获得输出目录
     * @param $path
     * @return mixed
     */
    private function getOutPutPath($path){
        return str_replace($this->path,$this->out_path,$path);
    }

    /**
     * 移动其他的文件
     * @param $files
     */
    private function transOtherFiles($files){
        for($i=0;$i<count($files);$i++){
            $file = $files[$i];
            $dest = $this->getOutPutPath($file['path']);
            $this->checkFilePath(dirname($dest));
            $content = file_get_contents($file['path']);
            if($file['type'] == 'html') { //进行js或者css外链地址的替换
                $content = $this->parseTheme($file,$content,'__THEME__');//顶级资源替换
                $content = $this->parseTheme($file,$content,'__MODULE_THEME__');//模块资源替换
            }
            file_put_contents($dest,$content);
        }
    }

    /**
     * 模板
     * @param $file
     * @param $content
     * @param $mode
     * @return mixed
     */
    private function parseTheme($file,$content,$mode){
        preg_match_all('/("'.$mode.'(.+?)")|(\''.$mode.'(.+?)\')/is',$content,$matches);
        $find = count($matches[0]);
        if($find) {
            for($j=0;$j<$find;$j++) {
                $match = $matches[0][$j];
                if($mode == "__THEME__"){ //顶级资源匹配
                    $path = $this->path.'/asset'; //资源地址
                }else{//模块资源匹配
                    $path = $this->path.'/'.$file['module'].'/asset';//资源地址
                }
                $theme = trim($match,"\"\\'");  //匹配的css或者js引用
                $type = pathinfo($theme,PATHINFO_EXTENSION); //匹配资源类型,css或者js
                if($type=='js' || $type=='css'){
                    $real_path = str_replace($mode,$path,$theme); //匹配资源原引用地址
                    $sign = "_".$this->getFileMd5($real_path).".{$type}"; //签名
                    $real_output_path  = str_replace(".{$type}",$sign,$this->getOutPutPath($real_path)); //发布文件的地址
                    if(is_file($real_output_path)){ //如果存在签名加密后的文件就进行引用替换
                        $content = str_replace($match,str_replace(".{$type}",$sign,$match),$content);
                    }
                }
            }
        }
        return $content;
    }

    /**
     * 压缩文件
     * @param $files
     */
    private function compressFiles($files){
        for($i=0;$i<count($files);$i++){
            $file = $files[$i];
            $this->compress($file['path'],$this->getOutPutPath($file['path']),$file['type']);
        }
    }

    /**
     * 获得模板文件树
     * @param string $path 根目录
     * @param array $tree 树形结构
     * @param array $compress 要压缩的文件列表
     * @param array $put  要转存的其他文件列表
     */
    private function getFileTree($path,&$tree,&$compress,&$put){
        $i=0;
        if ($handle = opendir ($path)){
            while (false !== ($file = readdir($handle))){
                $nextpath = $path . '/' . $file;
                if (strpos($file,'.') !== 0 && $file != '.' && $file != '..' && !is_link ($nextpath)){
                    if (is_dir ($nextpath)){
                        $tree[$i] =array('dir'=>1,'name'=>$file,'children'=>array(),'path'=>$nextpath);
                        self::getFileTree($nextpath,$tree[$i]['children'],$compress,$put);
                    }
                    elseif (is_file ($nextpath)){
                        $type = pathinfo($file, PATHINFO_EXTENSION);
                        $tree[$i] = array('dir'=>0,'type'=>$type,'name'=>$file,'path'=>$nextpath);
                        if(strpos($file,'min.'.$type) === false && ($type=='js'||$type=='css')){//已经压缩过的就不在压缩了
                            $compress[] = array('file'=>$file,'type'=>$type,'path'=>$nextpath);
                        }else{
                            if($type == 'html'){
                                $module =basename(dirname(dirname($nextpath)));
                            }else{
                                $dir =dirname($nextpath);
                                $module = basename($dir);
                                while($module!='asset'){
                                    $dir =dirname($dir);
                                    $module = basename($dir);
                                }
                                $module = basename(dirname($dir));
                            }
                            $put[] = array('file'=>$file,'type'=>$type,'path'=>$nextpath,'module'=>$module);
                        }
                    }
                    $i++;
                }
            }
        }
        closedir ($handle);
    }

    /**
     * 检测java环境
     * @return bool
     */
    private function check(){
        exec('java -version',$output,$return_var);
        return $return_var;
    }

    /**
     * 压缩文件
     * @param $path
     * @param $output
     * @param $type
     * @return mixed
     */
    private function  compress($path,$output,$type){
        $this->checkFilePath(dirname($output));
        $jar_path = realpath(JDICMS_ADDON_PATH."Compress/yuicompressor-2.4.8.jar");
        $charset = 'utf-8';
        $sign = "_".$this->getFileMd5($path).".{$type}";
        $output  = str_replace(".{$type}",$sign,$output);
        exec("java -jar {$jar_path}  --type {$type} --charset {$charset} -v {$path} > {$output}",$out,$return_var);
        return $return_var;
    }

    private function checkFilePath($path){
        if (!is_dir($path)){
            $this->checkFilePath(dirname($path));
            mkdir($path, 0777);
        }
    }

    private function getFileMd5($file){
        return substr(md5(file_get_contents($file)),0,7);
    }
}
