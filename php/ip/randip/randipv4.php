<?php
echo ipv4();
function unit(){
    $one = mt_rand(0, 2);
    if($one == 2){
        $two = mt_rand(0, 5);
        $three = mt_rand(0, 5);
    }else{
        $two = mt_rand(0, 9);
        $three = mt_rand(0, 9);
    }
    return $one . $two . $three;
}
function ipv4(){
    $a = (int)unit();
    $b = '.' . (int)unit();
    $c = '.' . (int)unit() . '.';
    $d = (int)unit();
    if($a === 0)$a = '120';
    if($d === 0)$d = '254';
    $ip = $a . $b . $c . $d;
    return $ip;
}
?>
