<?php
/**
 * Created by PhpStorm.
 * User: 赵家宽
 * Date: 2018/3/13
 * Time: 14:34
 */

namespace app\index\controller;


use think\Request;

class Info{

    public function index(Request $request)
    {
        $info = \think\Request::instance() -> header();
        echo $info['accept'];
        echo '<br/>';
        echo $info['accept-encoding'];
        echo '<br/>';
        echo $info['user-agent'];

    }
}

?>