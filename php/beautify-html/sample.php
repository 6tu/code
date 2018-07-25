<?php
# HTML 格式化

function beautify_html($html){
    $tidy_config = array(
        'clean' => false,
        'indent' => true,
        'indent-spaces' => 4,
        'output-xhtml' => false,
        'show-body-only' => false,
        'wrap' => 0
        );
    if(function_exists('tidy_parse_string')){ 
        $tidy = tidy_parse_string($html, $tidy_config, 'utf8');
        $tidy -> cleanRepair();
        return $tidy;
    }else{
        require_once('./beautify-html.php');

        // Set the beautify options
        $beautify = true;
        if($shouldBeautify = true) {
        	$beautify = new Beautify_Html(array(
        	  'indent_inner_html' => false,
        	  'indent_char' => " ",
        	  'indent_size' => 4,
        	  'wrap_line_length' => 32786,
        	  'unformatted' => ['code', 'pre'],
        	  'preserve_newlines' => false,
        	  'max_preserve_newlines' => 32786,
        	  'indent_scripts'	=> 'normal' // keep|separate|normal
        	));
        }
        //$beautify = new Beautify_Html;
        $html = $beautify->beautify($html);
        return $html;
    }
}
