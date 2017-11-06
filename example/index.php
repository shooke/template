<?php
/**
 * Created by PhpStorm.
 * User: shooke
 * Date: 17-11-6
 * Time: 下午4:54
 */
include "../Template.php";

$view = new Template();
// 模板路径 最后以/结尾
$view->templatePath = './template/';
// 编译文件存放路径 最后以/结尾
$view->compilePath = './compile/';
// 模板文件
$view->template = 'index';
// 布局文件在末班目录下
$view->layout = 'layout';

$view->render('index',[
    'title'=>'测试页',
    'content'=>'内容',
    'array'=>[
        1,2,3
    ]
]);