<?php

/**
 * 解压一个ZIP文件
 * @param  [string] $toName   解压到哪个目录下
 * @param  [string] $fromName 被解压的文件名
 * @return [bool]             成功返回TRUE, 失败返回FALSE
 */
function unzip($fromName, $toName)
{
    echo filesize($fromName);
    if(!file_exists($fromName)){
        return FALSE;
    }
    $zipArc = new ZipArchive();
    if(!$zipArc->open($fromName)){
        return FALSE;
    }
    if(!$zipArc->extractTo($toName)){
        $zipArc->close();
        return FALSE;
    }
    return $zipArc->close();
}
if(unzip('kodexplorer4.25.zip', './')){
    echo "success";
}
else{
    echo "failed";
}
