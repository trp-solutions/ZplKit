<?php
/*
ZplKit is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/HealDocument/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\ZplKit;

class Graphic {
	private function __construct(private string $zpl){}

	public static function from_png(string $filename): self {
		return new self(ZplConverter::graphic($filename));
	}

	public static function from_zpl(string $filename): self {
		if(!is_file($filename) || !is_readable($filename)){
			throw new \InvalidArgumentException('The graphic file must exist and be readable.');
		}
		$data = file_get_contents($filename);
		if($data === false){
			throw new \RuntimeException('Unable to read the graphic file.');
		}
		$data = trim($data);
		if(preg_match('/\A\^XA\s*\^FO0,0(.*)\^XZ\z/s', $data, $label)){
			$data = trim($label[1]);
		}
		if(!preg_match('/\A\^GFA,(\d+),(\d+),(\d+),([0-9A-Fa-f\s]+)\^FS\z/', $data, $matches)){
			throw new \InvalidArgumentException('Expected a single uncompressed ASCII hexadecimal ZPL graphic.');
		}
		$count = (int) $matches[1];
		$total = (int) $matches[2];
		$stride = (int) $matches[3];
		$hex = strtoupper(preg_replace('/\s+/', '', $matches[4]));
		if($count < 1 || $count > 99999 || $count !== $total || $stride < 1 || $stride > $count || $count % $stride !== 0 || strlen($hex) !== $count * 2){
			throw new \InvalidArgumentException('The ZPL graphic byte counts do not match its data.');
		}
		return new self("^GFA,$count,$count,$stride,$hex^FS");
	}

	public function __toString(): string {
		return $this->zpl;
	}
}
