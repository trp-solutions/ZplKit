<?php
/*
ZplKit is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/HealDocument/blob/main/LICENSE
*/
declare(strict_types=1);
require_once __DIR__.'/../lib/autoload.php';
use TRP\ZplKit\Template;
use TRP\ZplKit\Printer;

try {
	$template = new Template([
		'label' => ['width' => 600, 'height' => 400],
		'elements' => [
			['type' => 'box', 'x' => 10, 'y' => 10, 'width' => 580, 'height' => 380, 'thickness' => 2],
			['type' => 'text', 'field' => 'title', 'x' => 30, 'y' => 30, 'width' => 540, 'font_size' => 40, 'align' => 'C'],
			['type' => 'line', 'x' => 30, 'y' => 90, 'width' => 540, 'height' => 0, 'thickness' => 2],
			['type' => 'text', 'field' => 'description', 'x' => 30, 'y' => 110, 'width' => 540, 'lines' => 3, 'line_spacing' => 5],
			['type' => 'text', 'field' => 'date', 'x' => 30, 'y' => 270, 'width' => 540],
			// value is fixed text, field binds to a value passed to render()
			['type' => 'text', 'value' => 'Made with ZplKit', 'x' => 30, 'y' => 330, 'width' => 540, 'align' => 'R'],
			// to add your image, provide a path and reserve space in the layout:
			// ['type' => 'image', 'filename' => '/path/to/logo.png', 'x' => 30, 'y' => 200],
			// for preconverted ZPL, use type 'graphic' and a .zpl filename
		],
	]);
	// supply all fields as strings
	$label = $template->render([
		'title' => 'Achievement unlocked',
		'description' => "First milestone reached\nWell done!",
		'date' => (new DateTimeImmutable('2026-10-02'))->format('j/n Y'),
	]);
	// reuse $template->render([...]) with new values for subsequent labels
	if($argc === 1){
		echo $label;
	} else {
		$printer = new Printer(host: $argv[2], port: (int) ($argv[3] ?? 9100), timeout: (float) ($argv[4] ?? 5));
		$printer->send($label);
		echo "Label sent. Physical printing is not confirmed by this connection.\n";
	}
} catch(Exception $exception){
	fwrite(STDERR, $exception->getMessage()."\n");
	exit(1);
}
