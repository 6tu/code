<?PHP
/**
 * 根据网页的HTML内容提取网页的 Encoding
 * 
 * 浏览器以HEADER中的CHARSET为主，META中次一级，但是HEADER中的编码格式大多是
 * 服务器默认的编码，并不一定是网页中被指定的编码，所以一般META中的编码是准确的
 *
 * 1.从$_response_body中获取
 * 2.如果为空，则从$_response_header中获取 Content-Type: text/plain; charset=utf-8
 * 3.如果依然为空，最后自动识别字串的编码，但是偏差很大，不建议采用
 *
 */

header("Content-type: text/html; charset=utf-8");
$url = 'http://cn.yahoo.com';
$response = httpget($url);

@list($header, $body) = explode("\r\n\r\n", $response, 2);
$_response_headers = header2arr($header);

if(!empty($body)){
    $charset = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i", $body, $temp) ? strtolower($temp[1]):"";
	# $title = preg_match("/<title>(.*)<\/title>/isU",$_response_body,$temp) ? $temp[1]:"";
}elseif(isset($_response_headers['content-type']) and strpos($_response_headers['content-type'], 'charset')){
    list($mime_type, $charset) = explode(';', str_replace('', '', strtolower($_response_headers['content-type'])), 2);
    $charset = str_replace('charset=', '', $charset);
    }else{
    $charset = mb_detect_encoding($body, array("ASCII", "GB2312", "GBK", "UTF-8"));
    }

if(strtolower($charset) !== 'utf-8'){
    $body = mb_convert_encoding($body, 'utf-8', $charset);
    }
$body = utf2html($body);

echo $body;

# UTF8转成HTML实体
function utf2html($str)
{
    $ret = "";
    $max = strlen($str);
    $last = 0;
    for($i = 0;$i < $max;$i++){
        $c = $str{$i};
        $c1 = ord($c);
        if($c1 >> 5 == 6){
            $ret .= substr($str, $last, $i - $last);
            $c1 &= 31; #remove the 3bit two bytes prefix
            $c2 = ord($str{++$i});
            $c2 &= 63;
            $c2 |= (($c1 & 3) << 6);
            $c1 >>= 2;
			

			
            $ret .= "&#" . ($c1 * 0x100 + $c2) . ";";
            $last = $i + 1;
            }
        elseif($c1 >> 4 == 14){
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

# JSencode 格式化字符串
function addJsSlashes($str, $flag)
{
    if($flag){
        $str = addcslashes($str, "\0..\006\010..\012\014..\037\042\047\134\177..\377");
        }else{
        $str = addcslashes($str, "\0..\006\010..\012\014..\037\042\047\134");
        }
    return str_replace(array(chr(7), chr(11)), array('\007', '\013'), $str);
    }


function header2arr($header){
    $header = explode("\r\n", $header);
    $num = count($header);
    $_response_headers = array();
    for($i = 0;$i < $num;$i++){
        @list($name, $value) = explode(':', $header[$i], 2);
        $name = trim($name);
        $_response_headers = $_response_headers + array(strtolower($name) => trim($value));
        }
    return $_response_headers;
    }

function httpget($url){
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, TRUE); //表示需要responseheader
    curl_setopt($ch, CURLOPT_NOBODY, FALSE); //表示需要responsebody
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    $response = curl_exec($ch);
    if(curl_getinfo($ch, CURLINFO_HTTP_CODE) == '200'){
        return $response;
        }
    else return NULL;
    curl_close($ch);
    }

