<?php
function XLtrace_about_class($c){
	if(is_object($c)){ $class = getname($c); }
	else { $class = $c; }
	
	if(!class_exists($c)){ return FALSE; }
	$str = NULL; $licenses = array();
	$str .= 'Product: '.(method_exists($class, 'Product_url') ? '<a href="'.$class::Product_url().'">' : NULL).$class::Product().(method_exists($class, 'Product_url') ? '</a>' : NULL).' '.$class::Version()."\n";
	$licenses[$class::License()] = $class::License(TRUE);
	foreach(get_declared_classes() as $gdc){
		if(method_exists((string) $gdc, 'Product') && method_exists((string) $gdc, 'Version') && $gdc !== $class){
			$str .= ' + with: '.'<!-- '.$gdc.' -->'.(method_exists((string) $gdc, 'Product_url') ? '<a href="'.$gdc::Product_url().'">' : NULL).$gdc::Product().(method_exists((string) $gdc, 'Product_url') ? '</a>' : NULL).' '.$gdc::Version(TRUE).(!isset($licenses[$gdc::License()]) ? '<sup>('.(count($licenses)+1).')</sup>' : NULL)."\n";
			if(!isset($licenses[$gdc::License()])){ $licenses[$gdc::License()] = '<sup>'.(count($licenses)+1).':</sup>'.$gdc::License(TRUE); }
		}
	}
	$str .= 'License: '.implode(', ', $licenses)."\n";
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