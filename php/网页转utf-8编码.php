<?php
/*
 *
 * 网页转utf-8编码
 *
*/


# 提取网页中的 charset，转换成utf-8编码，再转成HTML实体
function toutf8($output){
    $wcharset = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i",$output,$temp) ? strtolower($temp[1]):"";
    //$wtitle = preg_match("/<title>(.*)<\/title>/isU",$filecnt,$temp) ? $temp[1]:"";
    $output = mb_convert_encoding ($output,'utf-8',$wcharset);
    $output = utf2html($output);
    return $output;
}

# UTF8转成HTML实体
function utf2html($str)
{
    $ret = "";
    $max = strlen($str);
    $last = 0;
    for ($i = 0;$i < $max;$i++){
        $c = $str{$i};
        $c1 = ord($c);
        if ($c1 >> 5 == 6){
            $ret .= substr($str, $last, $i - $last);
            $c1 &= 31; # remove the 3 bit two bytes prefix
            $c2 = ord($str{++$i});
            $c2 &= 63;
            $c2 |= (($c1 & 3) << 6);
            $c1 >>= 2;
            $ret .= "&#" . ($c1 * 0x100 + $c2) . ";";
            $last = $i + 1;
            }
        elseif ($c1 >> 4 == 14){
            $ret .= substr($str, $last, $i - $last);
            $c2 = ord($str{++$i});
            $c3 = ord($str{++$i});
            $c1 &= 15;
            $c2 &= 63;
            $c3 &= 63;
            $c3 |= (($c2 & 3) << 6);
            $c2 >>= 2;
            $c2 |= (($c1 & 15) << 4);
            $c1 >>= 4;
            $ret .= '&#' . (($c1 * 0x10000) + ($c2 * 0x100) + $c3) . ';';
            $last = $i + 1;
            }
        }
    $str = $ret . substr($str, $last, $i);
    return $str;
}
