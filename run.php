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
//$yaoHao->start();
$redis = new Redis();
$redis->connect('127.0.0.1');

$key = 'yaohao';

var_dump($redis->sIsMember($key, '0800102192629'));
exit;

$ids = array('yaohao');
foreach($yaoHao->getHappyIds() as $id) {
    $ids[] = $id;
    if(count($ids) > 500) {
        var_dump(call_user_func_array(array($redis, "sAdd"), $ids));
        $ids = array('yaohao');
    }
}
if(count($ids) > 0) {
    var_dump(call_user_func_array(array($redis, "sAdd"), $ids));
    $ids = array('yaohao');
}


var_dump($redis->sCard('yaohao'));
exit;
