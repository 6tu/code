<?php
/* *
   * 批量替换指定目录（含子目录）中的文件内容
   *
*/

$path = '/var/www/html/';
$fn = 'index.html';
$find = 'index.html?p=';
$replace = 'index.php?p=';


$array = getDir($path);
// print_r($array);

$n = count($array)-1;
for($i = 0;$i < $n; $i++){
	if(strpos($array[$i], $fn)){
    $html = file_get_contents($array[$i]);
	$new_html = str_replace($find, $replace, $html);
	file_get_contents($array[$i], $new_html);
	}
	
	
function searchDir($path, & $data){
    if(is_dir($path)){
        $dp = dir($path);
        while($file = $dp -> read()){
            if($file != '.' && $file != '..'){
                searchDir($path . '/' . $file, $data);
                }
            }
        $dp -> close();
        }
    if(is_file($path)){
        $data[] = $path;
        }
    }
function getDir($path){
    $data = array();
    searchDir($path, $data);
    return $data;
    }
?>

