<?php
function XLtrace_about_class($c){
	if(is_object($c)){ $class = getname($c); }
	else { $class = $c; }
	
	if(!class_exists($c)){ return FALSE; }
	$str = NULL;
	$str .= 'Product: '.(method_exists($class, 'Product_url') ? '<a href="'.$class::Product_url().'">' : NULL).$class::Product().(method_exists($class, 'Product_url') ? '</a>' : NULL)."\n";
	$str .= 'Version: '.$class::Version()."\n";
	$str .= 'License: '.$class::License(TRUE)."\n";
	return $str;
}

function parse_template($src, $set=array(), $prefix='{', $postfix='}'){
	$str = file_get_contents($src);
	foreach($set as $tag=>$value){
		$str = str_replace($prefix.$tag.$postfix, $value, $str);
	}
	return $str;
}
?>