<?php
namespace Admin\Controller;
use Common\Controller\AdminController;
/**
 * 模版编辑控制器
 * @author lihao<lh@tiptime.com>
 */
class TempController extends AdminController {

    public function index(){
        $result = array();
        $dir = C('TEMP_PATH') . '/';
        $dir = trimPath($dir);
        $handler = opendir($dir);
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") {
                if (is_dir($dir . $filename)) {
                    $res = getDirectorySize($dir . $filename);
                    $res['name'] = $filename;
                    if(C('JDI_THEME')==$filename && C('MOBILE_THEME') == $filename){
                        $res['status']=3;
                    }else{
                        if(C('JDI_THEME') == $filename){
                            $res['status'] = 1;
                        }elseif(C('MOBILE_THEME') == $filename){
                            $res['status'] = 2;
                        }else{
                            $res['status'] = 0;
                        }
                    }
                    $result[] = $res;
                }
            }
        }
        closedir($handler);
        $this->assign('list',$result);
        $this->display();
    }

    public function mobileUse($name){
        $data = D('Config')->where(array('name'=>'MOBILE_THEME'))->find();
        $data['value'] = $name;
        if(D('Config')->save($data) !== false){
            S('DB_CONFIG_DATA',null);
            $this->success('设置成功');
        } else {
            $this->error('设置失败');
        }
    }

    public function webUse($name){
        $data = D('Config')->where(array('name'=>'JDI_THEME'))->find();
        $data['value'] = $name;
        if(D('Config')->save($data)!==false){
            S('DB_CONFIG_DATA',null);
            $this->success('设置成功');
        } else {
            $this->error('设置失败');
        }
    }


    public function editTemp(){
        $this->display();
    }

    public function  ide(){
        $this->display();
    }

    public  function op(){
        $a = isset($_GET['aa']) ? $_GET['aa'] : '';
        $ide = new IDE_Server();
        switch ($a) {
            case 'getDir':
                echo json_encode($ide->getDir($_GET['dir']));
                break;

            case 'getFile':
                echo $ide->getFile($_GET['path']);
                break;

            case 'saveFile':
                echo $ide->saveFile($_POST['path'], $_POST['content']);
                break;

            case 'newFile':
                echo $ide->newFile($_GET['path']);
                break;

            case 'newDir':
                echo $ide->newDir($_GET['path']);
                break;

            case 'moveFile':
                echo $ide->moveFile($_GET['path'], $_GET['newPath']);
                break;

            case 'moveDir':
                echo $ide->moveDir($_GET['path'], $_GET['newPath']);
                break;

            case 'removeFile':
                echo $ide->removeFile($_GET['path']);
                break;

            case 'removeDir':
                echo $ide->removeDir($_GET['path']);
                break;

            case 'uploadFile':
                echo $ide->uploadFile('file', $_GET['path']);
                break;

            case 'zipextract':
                echo $ide->zipextract($_GET['zip'], $_GET['to']);
                break;
        }
    }
}

class IDE_Server {
    // 管理代码的根目录
    public function baseDir(){
        return C('TEMP_PATH');
    }
    // 获取指定目路径的子目录和文件
    public function getDir($dir) {
        $dirs = array();
        $files = array();
        $dir = $this->baseDir() . $dir . '/';
        $dir = trimPath($dir);
        $handler = opendir($dir);
        // 务必使用!==，防止目录下出现类似文件名“0”等情况
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") {
                if (is_dir($dir . $filename)) {
                    $dirs[] = $filename;
                } else {
                    $files[] = $filename;
                }
            }
        }
        closedir($handler);
        $dir = trimPath($dir);
        $dir = str_replace($this->baseDir(), '/', $dir);
        return array(
            'path' => $dir,
            'dirs' => $dirs,
            'files' => $files
        );
    }
    // 保存文件
    public function saveFile($path, $conent) {
        $path = $this->baseDir() . $path;
        $path = trimPath($path);
        $isBOM = checkBOM($path);
        if($isBOM){
            writeUTF8WithBOMFile($path,$conent);
            return true;
        }else{
            return file_put_contents($path, $conent);
        }
        return false;
    }
    // 新建文件
    public function newFile($path) {
        $path = $this->baseDir() . $path;
        $path = trimPath($path);
        if (file_exists($path)) {
            return '文件已存在!';
        }
        if (!is_dir(dirname($path))) {
            return '目录不存在';
        }
        file_put_contents($path, '');
        return 'ok';
    }
    // 新建目录
    public function newDir($path) {
        $path = $this->baseDir() . $path;
        $path = trimPath($path);
        if (!is_dir(dirname($path))) {
            return '不支持多级创建目录!';
        }
        if (is_dir($path)) {
            return '目录已存在!';
        }
        mkdir($path);
        chmod($path, 777);
        return 'ok';
    }
    // 获取文件源代码
    public function getFile($path) {
        $path = $this->baseDir() . $path;
        $path = trimPath($path);
        return file_get_contents($path);
    }
    // 移动文件
    public function moveFile($path, $newPath) {
        $path = $this->baseDir() . $path;
        $path = trimPath($path);
        $newPath = $this->baseDir() . $newPath;
        $newPath = trimPath($newPath);
        if (rename($path, $newPath)) {
            return 'ok';
        } else {
            return '没有权限';
        }
    }
    // 移动文件夹
    public function moveDir($path, $newPath) {
        $path = $this->baseDir() . $path;
        $path = trimPath($path);
        $newPath = $this->baseDir() . $newPath;
        $newPath = trimPath($newPath);
        if (rename($path, $newPath)) {
            return 'ok';
        } else {
            return '没有权限';
        }
    }
    // 删除文件
    public function removeFile($path) {
        $path = $this->baseDir() . $path;
        $path = trimPath($path);
        return unlink($path);
    }
    // 删除文件夹
    public function removeDir($path) {
        $path = $this->baseDir() . $path;
        $path = trimPath($path);
        rrmdir($path);
        return 'ok';
    }

    // 上传文件
    public function uploadFile($fileElementName, $path) {
        $error = "";
        $msg = "";
        if (!empty($_FILES[$fileElementName]['error'])) {
            switch ($_FILES[$fileElementName]['error']) {
                case '1':
                    $error = '上传文件超过php.ini中upload_max_filesize的设置。';
                    break;

                case '2':
                    $error = '上传文件超过HTML表单中指定的MAX_FILE_SIZE。';
                    break;

                case '3':
                    $error = '上传文件只是部分上传。';
                    break;

                case '4':
                    $error = '没有上传文件。';
                    break;

                case '6':
                    $error = '缺少一个临时文件夹。';
                    break;

                case '7':
                    $error = '未能将文件写入磁盘。';
                    break;

                case '8':
                    $error = 'File upload stopped by extension';
                    break;

                case '999':
                default:
                    $error = '没有错误代码的倍率。';
            }
        } elseif (empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none') {
            $error = '没有文件上传。';
        } else {
            $msg.= ' 文件名: ' . $_FILES[$fileElementName]['name'] . ', ';
            $msg.= ' 文件大小: ' . @filesize($_FILES[$fileElementName]['tmp_name']);
            // @unlink ( $_FILES ['fileToUpload'] );
            $path = $this->baseDir() . $path;
            $path = trimPath($path);
            move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $path . $_FILES[$fileElementName]['name']);
        }
        return json_encode(array(
            'error' => $error,
            'msg' => $msg
        ));
    }
    // 解压文件
    public function zipextract($zip, $to) {
        $zip = $this->baseDir() . $zip;
        $zip = trimPath($zip);
        $to = $this->baseDir() . $to;
        $to = trimPath($to);
        $zipobj = new ZipArchive;
        $res = $zipobj->open($zip);
        if ($res === TRUE) {
            echo 'ok';
            $zipobj->extractTo($to);
            $zipobj->close();
        } else {
            echo '解压失败: ' . $res;
        }
    }
}


function writeUTF8WithBOMFile($filename,$content)
{
    $f = fopen($filename, 'w');
    fwrite($f, pack("CCC", 0xef,0xbb,0xbf));
    fwrite($f,$content);
    fclose($f);

}

function checkBOM($filename)
{
    $contents=file_get_contents($filename);
    $charset[1]=substr($contents, 0, 1);
    $charset[2]=substr($contents, 1, 1);
    $charset[3]=substr($contents, 2, 1);
    if (ord($charset[1])==239 && ord($charset[2])==187 && ord($charset[3])==191) {
        return true;
    }
    else return false;
}

// 修正path
 function trimPath($path) {
    $path = str_replace('\\', '/', $path);
    $path = str_replace('//', '/', $path);
    $path = str_replace('/./', '/', $path);
    return $path;
}

//循环删除目录和文件函数
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") {
                    rrmdir($dir . "/" . $object);
                } else {
                    unlink($dir . "/" . $object);
                }
            }
        }
        reset($objects);
        rmdir($dir);
    }
}
