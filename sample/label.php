<?php
/*
ZplKit is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/HealDocument/blob/main/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/../lib/autoload.php';
use TRP\ZplConverter\Label;
use TRP\ZplConverter\Graphic;

$label = new Label(width: 600, height: 800, top: 0, left: 0, darkness: 16);
$label->text('Hello world', x: 30, y: 30, width: 540, font_size: 50, align: 'C')
	->box(x: 30, y: 100, width: 540, height: 0, thickness: 3)
	->text("A reusable label\nwith multiple lines", x: 30, y: 130, width: 540, lines: 3, line_spacing: 5);
$label->box(x: 20, y: 20, width: 560, height: 760, thickness: 2);

try {
	if(isset($argv[1])){
		if(strtolower(pathinfo($argv[1], PATHINFO_EXTENSION)) === 'zpl'){
			$label->graphic(Graphic::from_zpl($argv[1]), x: 30, y: 320);
		} else {
			$label->image($argv[1], x: 30, y: 320);
			// To convert once and reuse: $graphic = Graphic::from_png($argv[1]);
			// $label->graphic($graphic, x: 30, y: 320);
		}
	}
} catch(Exception $exception){
	fwrite(STDERR, $exception->getMessage()."\n");
	exit(1);
}

// equivalent to echo $label->render()
echo $label;
// or save directly: file_put_contents('/tmp/label.zpl', $label->render());
