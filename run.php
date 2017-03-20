<?php
require 'random.php';
require 'yaohao.php';

$config = array(
    //要查询的期数
    'cycle' =>  201701,
    //当期6位随机种子
    'seed'  =>  374478,
    //压缩包路径
    'path'  =>  '/Users/zhjx922/Downloads',
);
$yaoHao = new YaoHao($config);
$yaoHao->start();