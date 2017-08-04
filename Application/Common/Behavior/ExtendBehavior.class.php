<?php

namespace Common\Behavior;

use Think\Behavior;
use Think\Hook;

defined('THINK_PATH') or exit();

// 模块或者插件调度
class ExtendBehavior extends Behavior
{
    // 行为扩展的执行入口必须是run
    public function run(&$content)
    {
        //添加扩展函数库
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
        $_extend = I('get._extend');
        if ($_extend) {
            if ($_extend == "addons") { //插件调度
                $this->execute_addons();
            } else if ($_extend == "module" && MODULE_NAME == 'Admin') { //模块调度,模块调度只用后台才可用
                $this->execute_module();
            }
        }

    }

    /**
     * 执行插件方法
     */
    public function execute_addons()
    {
        $_addons = I('get._addons');
        $_controller = I('get._controller');
        $_action = I('get._action');
        if (C('URL_CASE_INSENSITIVE')) {
            $_addons = ucfirst(parse_name($_addons, 1));
            $_controller = parse_name($_controller, 1);
        }
        $TMPL_PARSE_STRING = C('TMPL_PARSE_STRING');
        $TMPL_PARSE_STRING['__ADDONROOT__'] = __ROOT__ . "/Addons/{$_addons}";
        C('TMPL_PARSE_STRING', $TMPL_PARSE_STRING);
        if (!empty($_addons) && !empty($_controller) && !empty($_action)) {
            if (check_addons($_addons)) {
                A("Addons://{$_addons}/{$_controller}")->$_action();
            } else {
                header('Content-Type:text/xml; charset=utf-8');
                echo '插件未安装或者被禁用';
            }
        } else {
            header('Content-Type:text/xml; charset=utf-8');
            echo '没有指定插件名称，控制器或操作!';
        }
        Hook::listen('app_end');
        exit;
    }

    /**
     * 执行模块方法
     */
    public function execute_module()
    {
        $_module = I('get._module');
        $_controller = I('get._controller');
        $_action = I('get._action');
        if (C('URL_CASE_INSENSITIVE')) {
            $_module = ucfirst(parse_name($_module, 1));
            $_controller = parse_name($_controller, 1);
        }
        $TMPL_PARSE_STRING = C('TMPL_PARSE_STRING');
        $TMPL_PARSE_STRING['__MODULEROOT__'] = __ROOT__ . "/Module/{$_module}";
        C('TMPL_PARSE_STRING', $TMPL_PARSE_STRING);

        if (!empty($_module) && !empty($_controller) && !empty($_action)) {
            $file = JDICMS_MOUDLE_PATH . 'Common' . '/Common/function.php'; //自动公共模块函数库
            if (is_file($file)) include $file;
            $file = JDICMS_MOUDLE_PATH . $_module . '/Common/function.php'; //自动加载模块函数裤
            if (is_file($file)) include $file;
            define("__CURRENT_MODULE__", $_module);
            define("__CURRENT_CONTROLLER__", $_controller);
            define("__CURRENT_ACTION__", $_action);
            $class = A("Modules://{$_module}/{$_controller}");
            if ($class) {
                $class->$_action();
            } else {
                header('Content-Type:text/plain; charset=utf-8');
                echo '访问地址不存在';
            }
        } else {
            header('Content-Type:text/xml; charset=utf-8');
            echo '没有指定模块名称，控制器或操作！';
        }
        Hook::listen('app_end');
        exit;
    }
}