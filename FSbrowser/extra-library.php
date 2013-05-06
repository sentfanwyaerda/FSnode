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
		#/*debug*/ else { $str .= '<!-- ignore: '.$gdc.' -->'."\n"; }
	}
	$str .= 'License: '.implode(', ', $licenses)."\n";
	return $str;
}

function parse_template($src, $set=array(), $prefix='{', $postfix='}'){
	$str = file_get_contents($src);
	foreach($set as $tag=>$value){
		$str = str_replace($prefix.$tag.$postfix, $value, $str);
	}
	if(preg_match_all("#".escape_preg_chars($prefix)."([^\?".escape_preg_chars($postfix)."]{0,})\?([^:]+)[:]([^".escape_preg_chars($postfix)."]{0,})".escape_preg_chars($postfix)."#i", $str, $buffer)){
		//*debug*/ print '<!-- '; print_r($buffer); print ' -->';
		if(isset($buffer[0]) && is_array($buffer[0])){foreach($buffer[0] as $i=>$original){
			$str = str_replace($original, $buffer[(isset($set[$buffer[1][$i]]) && ( is_bool($set[$buffer[1][$i]]) ? $set[$buffer[1][$i]] : TRUE) ? 2 : 3)][$i], $str);
		}}
	}
	if(preg_match_all("#".escape_preg_chars($prefix)."([^\|".escape_preg_chars($postfix)."]{0,})[\|]([^".escape_preg_chars($postfix)."]{0,})".escape_preg_chars($postfix)."#i", $str, $buffer)){
		if(isset($buffer[0]) && is_array($buffer[0])){foreach($buffer[0] as $i=>$original){
			$str = str_replace($original, (isset($set[$buffer[1][$i]]) ? $set[$buffer[1][$i]] : $buffer[2][$i]), $str);
		}}
	}
	return $str;
}
function escape_preg_chars($str, $qout=array(), $merge=FALSE){
	if($merge !== FALSE){
		$qout = array_merge(array('\\'), (is_array($qout) ? $qout : array($qout)), array('[',']','(',')','{','}','$','+','^','-'));
		#/*debug*/ print_r($qout);
	}
	if(is_array($qout)){
		$i = 0;
		foreach($qout as $k=>$v){
			if($i == $k){
				$str = str_replace($v, '\\'.$v, $str);
			} else{
				$str = str_replace($k, $v, $str);	
			}
			$i++;
		}
	}
	else{ $str = str_replace($qout, '\\'.$qout, $str); }
	return $str;
}
?>