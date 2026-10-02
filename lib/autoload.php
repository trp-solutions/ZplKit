<?php
/*
ZplKit is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/HealDocument/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\ZplConverter;

spl_autoload_register(function($name){
	if(str_starts_with($name, 'TRP\\ZplConverter\\')){
		$file = __DIR__.'/'.implode('/', array_slice(explode('\\', $name), 2)).'.php';
		if(file_exists($file)){
			require_once $file;
		}
	}
});
