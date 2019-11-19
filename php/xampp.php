<?php

# 获取 xampp 最新版本
# 参数 ?os=win , ?os=linux , ?os=mac
# wget --content-disposition url

$baseurl = 'https://www.apachefriends.org';

$head = '<!doctype html><html lang="zh_cn">';
$head .= '<head><meta charset="utf-8"><title>用ApacheFriends下载安装XAMPP</title></head>';
$head .= '<body><h3>' . images($os_str = 'xampp') . ' XAMPP Apache + MariaDB + PHP + Perl</h3>';

$str = file_get_contents($baseurl . '/zh_cn/index.html');
if(empty($_GET['os'])){
    $tags = '<div class="large-3 columns">';
    $tags_array = explode($tags, $str);
    $div_array = explode('</div>', $tags_array[4]);
    $link_contents = $tags . $tags_array[1] . $tags . $tags_array[2] . $tags . $tags_array[3] . $tags . $div_array[0] . '</div>';
    $link_contents = str_replace('data-delayed-href="/zh_cn/download_success.html"', '', $link_contents);
    $link_contents = str_replace('/download.html', 'https://www.apachefriends.org/download.html', $link_contents);
    $link_contents = str_replace('<img src="/images', '<img src="https://www.apachefriends.org/images', $link_contents);
    $link_contents = str_replace('h2>', 'b>', $link_contents);
    $link_contents = $head . $link_contents;
    $link_contents = preg_replace ("/\s(?=\s)/", "\\1", $link_contents);
    echo $link_contents;
    exit(0);
}

$array = explode("\n", $str);
$n = count($array);
$win = '';
$linux = '';
$mac = '';
for($i = 0; $i < $n; $i++){
    if(strstr($array[$i], "installer.exe")) $win = $array[$i] . '</a>';
    if(strstr($array[$i], "installer.run")) $linux = $array[$i] . '</a>';
    if(strstr($array[$i], 'vm.dmg')) $mac = $array[$i] . '</a>';
}

if($_GET['os'] === 'win') fwd301($win);
if($_GET['os'] === 'linux') fwd301($linux);
if($_GET['os'] === 'mac') fwd301($mac);

function fwd301($str){
#    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . get_url($str));

}
function get_url($str){
    $a_tags = strip_tags($str, '<a>');
    preg_match_all('/ href="([^>]*)">[^<]*<\/a>/is', $a_tags, $matches);
    return($matches[1][0]);
}

function images($os_str){
    
    $windows = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyNpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChNYWNpbnRvc2gpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjNBNTk0QjZCNTUzNDExRTM5NzI5RDNBQTY0QzY3NDNEIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjNBNTk0QjZDNTUzNDExRTM5NzI5RDNBQTY0QzY3NDNEIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6M0E1OTRCNjk1NTM0MTFFMzk3MjlEM0FBNjRDNjc0M0QiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6M0E1OTRCNkE1NTM0MTFFMzk3MjlEM0FBNjRDNjc0M0QiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7oO40xAAAEGUlEQVR42uyZTUsbQRjHJ3FjXJPNi0lN1Ly6IbQVPIi9FD+IRy899FBoz8VrL4LQQy/9DPYDFGxzKY1iRagH8aIHxXfNe2ISNf3/lwwsUtoatyXQDDzszLgz85tnnjD/fbQtLCyIXC4nqtWqYNE0Tfh8PnFyciIuLy9Ff3+/8Hq9wul0ioODA+FwOIw+2tnZmYhGo2J2dlZks1mxtrYm7Ha7MQ/fp1UqFXFzcyNUVTXm4Tr1et1oK4oiLi4uxOnpqd/lcsXRP16r1VLoS5XL5TTaIUXco7RaLWGz2QxYghOIT0ISgPXr62sDCqDhUqmUgDP0QqGQajabKTggAaAwniONRsNF8KurK2N8X1+fsYE/AiQEB3EAIQYHBw0ATsg2FqMpxWJxDAslAZTiEzYGsAjaCbwbA4yDAPSo3JwBAWOdc/JpKiXFDEHjrglA4y4GBgYE3G/UMbmaz+djONo4AbDgt6Ojo3U8xdbW1ouVlZVFLkZgeo6F48zG+W9B/LIojDfuijvixHC3B/EXPjw8HIFHkjwKGiB0tOmJID0HQB7t/NDQ0DpjC+AB9nFDPHKrirKxsfHs+Pj4IYKSMZHE4gxWD6HpBQnPXfM46QUZawA5p6fpYYBVZfxZWZTl5eX3BJBHwAVkbPzKE3c5pnsBut3uChZziS4tdsDdiC4udtHlpQfYA+wB9gD/9k0CEap1MpCSCXevIRwoeCE6Q/v7+4bgtbBoytzc3Dwqd5YfuL9tuLs/4qoUuq6LdDr9YXJysggh0bIQsGHb3t7uWE1LEUtFw88DmtUiQuFCnQLymKU6lm3L5dbS0tLzDo+Y8iwD731PJpP8yHqyt7f31EqxahxxJBJpdfojAcxrKOo3MzMzAqGyuLq6+ooxaakHh4eHy3i6O/wV5/1+v/B4PALP81AoZKhrC0tZQVB35MH2R1ZLfmyZ6xaWVu8m6QH2AHuA/70eLJfLmkyvMfXBukyBdAXgxMTE22q1+ghSabTRaIQgQB/IRKJUKVJWmXM3/wxwamrqJbNYTL0BzAmQMabdSqWSDu+OM/WGDTApyaxXQL7bzgEGeSe372Yn/yYzYVYesTmDWvf5fDu4/Hew2Cfm+3j5t/OGKtpM44YBG4M9hrz/AjUj7+YmYSlapfdl2NzOnN0JUGo77p7GBQjERQgoBQCkfA2wu6qq7mJIluOmp6cNJZPJZJhMfxcMBj9jfATGVHAMm9drtVqC3kfbw9CROUcZ6+bQ+Vnc/zZHbVbLnJjpXqmi+bEkjxjaMBcIBL7yHSlaWWcCHe96ARuD95k21gEZY/4afRG047BROoUO4Xym3LV27yy//GSgZ/gvB0LxFKQkYz88X4B23EQ4bBKAaWeOY3gBWkFfFIBxxL4OL+voS2Euel37IcAAt22w46A/P5UAAAAASUVORK5CYII=';

    $linux = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyNpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChNYWNpbnRvc2gpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjNBNTk0QjY3NTUzNDExRTM5NzI5RDNBQTY0QzY3NDNEIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjNBNTk0QjY4NTUzNDExRTM5NzI5RDNBQTY0QzY3NDNEIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6M0E1OTRCNjU1NTM0MTFFMzk3MjlEM0FBNjRDNjc0M0QiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6M0E1OTRCNjY1NTM0MTFFMzk3MjlEM0FBNjRDNjc0M0QiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz6+Phw1AAAD1ElEQVR42sSYfWhNYRzH7515mWGM2di9xNg0My/bhGTGYkv7Q2qslJGXZqHGIv5YCv9sSZT4Y/8RkZS3mpBI8jaMednELC8xGWbe7fo+63v0dLr3nuc85+7uV5/O7tlznud3nvN7fdw+n89liNvtdmnKdJADRoLf4AE4C17pTCbr1PnDQFNKqZTPxBMwW1fB/zo5VHCdH8XMrO0uBQeCdgUFBRN1FYxw6Use6Ks4drnuIk4UHG1j7MzuUDCG1+OgRbp/AmSBG9K9VDDA5dhj7MlhcEvyZDHBc372ngw5sh2OC7cNPhahE1wBXt4ToaUc/AKLwH1pfJzOIpEOFPzKaxLt8Rh3cKxkdxEmrw/rJ17BT5cN4sFw0I//GwWKTJ+4NNxxcDG46Oe+W7K3aknBPToKOvnEzSCdQfgZGExb7A2+g2K+hCE54Q4ziWAI2E67E1nlE8NJBngKbkrj0+k4tsTtoJq5C4aBBSAfPGRsFLZ4BDSBJfzbkAaQEo5qZgPtaiN/J9BZPH7G1pqcpaqrnWQ+F3qjOH6ln+JhXlcp6KEDiMEVigpO8qNge4DddqzgKWmRbBsmcZXPNEgveDnUCiZz4i/gjkZLIJ49A/ZKL1kcylxcxKpFvPlRmwpeBwfo8X/Bed4vD2WqE5+pnuWVTneVwV0TDdXuYA6js4OTwVDQBuo4sV0R4eYDSAPR0v3CUGSSXdy11+CkZuYxOj0hvUCHtLOOFExj/yF63M/MGLryQ7oaa4vM08OJgpt57eAOOhGRp1+yjzZEVOBRTgrWbClIn3Oo4FvacAedzcNC44+uF4sJLoBV4J0D+xvBlFcJGmmPLawn9+sE6iTaRgUrFUNaaYsxNhXcAhayAjKnvWpzC2ul4FxWv8vAUnZohkyQJi5UVE687A7+vc2k3Hq2B5tUFUzkWUpCkAWrpAVKFBQcwx2Una5GqraFB5dJ/UxQBT0sMvsrtJyGknkWY+Npf8EkF+yjrVp+YuEQmRYTjpcUzLUYW8mzQpVs1ZmvrVLdT4XJ6pld1tDLA8lq2lerYgtxDcwIFqinsNq4rTDhThDLIw6zTKXiB/nbqvoWJlUA+tCrAwbqfAZTFfnGgNvEF2plEZDCFlSWFxb2l0q7PsSCNmCgFqXPVj6UpXDElqp4gFkQ4PkSNmCRqo27KCTvceFYxr057HebTX2uS2ohxbnMI/CRVc8g9sxxPKc223UUj48brbKTv1z8nhgSTQ9LZlBtY8LPpDfHsA0IZl9lVLyOLz0LnDadIYakcfdK5y4iqF/izlqJeGYav4SX3lqr0rj/E2AADeJkz7rbjAEAAAAASUVORK5CYII=';

    $apple = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAYAAACM/rhtAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyNpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChNYWNpbnRvc2gpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjU0MTBCMjNBNTUzMzExRTM5NzI5RDNBQTY0QzY3NDNEIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjU0MTBCMjNCNTUzMzExRTM5NzI5RDNBQTY0QzY3NDNEIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6NTg1NUYxNzU1NTMyMTFFMzk3MjlEM0FBNjRDNjc0M0QiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NTg1NUYxNzY1NTMyMTFFMzk3MjlEM0FBNjRDNjc0M0QiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz77vgJbAAACdElEQVR42syYS0hUURjH70wpFGMi7rIIyiQUxWS0TRBBposI3ASG1EJ8rBQVN5ngQgikXWI0PVQEUSnbielCN6HYiPgoXaj4zMdGF0JK4vg/+CmX8d655xvmnjsf/GDm3Dtnfpx7v/P4XIFAQDsNr9er2RSp4CkYB4NWN/v9/rPPbs3euAm6wW/wBuRzO7hoo1wJeA8u6NqWokWwGPiMnh63Izse8S3wyaB9GYxEg2CnSXtNOJ25bcjWHIP2YfAtGgSfGLRNgsfhdhhpweyg7+0gE/wPt0NuFouZ/A6IBes0Opu667/ANTAEOsAstceT6A0QQwkjMno3UoJVoJTk9HFIMmIy7gNNhP6Rv6QJ2hP02z3wle6fNftjl8VSdx+00dRhFUJ2CqyBREqYBMkBaAT1RktdqBEsAL3M1yWL4MZrkALKwY5MktxjykUinoHnMlksEmBAUx/iPW+VSZI6cEWxXCHokpkHL4NaxXI+MzkjwUfgkkI5MYFXclaSu4pHT4zcPkcwWbHgD+5afFWx4ApX0KNY0MUV3FMseJ0ruKVY8AFXcEmxYKHVnjT44qJiQY9+FyMjOO7AGtwAMmQFJ8BfByR/0g5K6kzS44CgeNSjdDRNshL8ojkXb0GLleB0qDOCgmiW2VFXOyR3rjxnJtgP5hwQLOIc3IsUy302GhS3xXB/VCS3RedudulD/GhVgaA44h6FW5t5aLNchRaibigjuMCQPKBazbbk/aJu/Y6zFpvFMEgHfwyu/QMfQK52UjhKIm5TpWDMpExSBl7JlCtkYwakgRdU3nBT1n0HG0H3ivdpnhDyebT3i6N3ukd2a3cswABhpXXmw70e/gAAAABJRU5ErkJggg==';

    $xampp = 'data:image/png;base64,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';

    $windows = '<img src="' .$windows. '" height="18" width="18" />';
    $linux = '<img src="' .$linux. '" height="20" width="20" />';
    $apple = '<img src="' .$apple. '" height="20" width="20" />';
    $xampp = '<img src="' .$xampp. '" height="30" width="30" />';
    
    if($os_str === 'windows') return $windows;
    if($os_str === 'linux') return $linux;
    if($os_str === 'osx') return $apple;
    if($os_str === 'xampp') return $xampp;

}

exit;

$main = '';
$array = explode('xampp-files', $str);
foreach($array as $key => $value){
    if(strpos($array[$key], 'XAMPP for') !== false){
        $link_array = explode('">', $array[$key]);
        $ver_array = explode('/', $link_array[0]);
        $ver = $ver_array[1];
        $os_array = explode('-', $link_array[0]);
        $os_str = $os_array[1];
        $link = ' <a href="' . $baseurl . '/xampp-files' . $link_array[0] . '">';
        $link = '&nbsp;&nbsp;' . images($os_str) . $link;
        $link .= 'XAMPP for<strong> ' . ucfirst($os_str) . ' </strong>' . $ver . ' (PHP ' . $ver . ')</a>';
        $main .= $link . "<br><br>";
    }
}
$more = '<b>&nbsp;&nbsp;下载 </b><a href="' . $baseurl . '/download.html">点击这里获得其他版本</a><br><br>';
$body = $head . $more . $main . '</body></html>';
//echo $body;
