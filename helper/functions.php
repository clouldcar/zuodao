<?php
//全局工具函数
if (!function_exists('createIncrementId')) {
    function createIncrementId($type = null)
    {
        $time = time();
        $time = $time - 1071497951;//1071497951->2003-12-15 22:19:11
        $time << 18;//0-30已知类型，31是无类型
        $type = is_null($type) ? 31 : ($type%31);
        $type << 13;
        $seq = rand(1,8191);
        $num = $time|$type|$seq;
        $num -= 83629516358986; //这样保证10年内位数不会增长
        return $num;
    }
}