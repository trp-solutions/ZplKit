<?php
/*
ZplKit is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/HealDocument/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\ZplConverter;

class Label {
	private array $fields = [];

	public function __construct(
		private int $width,
		private ?int $height = null,
		private int $top = 0,
		private int $left = 0,
		private ?int $darkness = null
	){
		self::range('width', $width, 1, 32000);
		if($height !== null){
			self::range('height', $height, 1, 32000);
		}
		self::range('top', $top, -120, 120);
		self::range('left', $left, -9999, 9999);
		if($darkness !== null){
			self::range('darkness', $darkness, 0, 30);
		}
	}

	public function text(string $value, int $x, int $y, int $width, int $font_size = 30, int $lines = 1, int $line_spacing = 0, string $align = 'L'): self {
		self::position($x, $y);
		self::range('text width', $width, 1, 32000);
		self::range('font size', $font_size, 10, 32000);
		self::range('lines', $lines, 1, 9999);
		self::range('line spacing', $line_spacing, -9999, 9999);
		if(!in_array($align, ['L', 'C', 'R', 'J'], true)){
			throw new \InvalidArgumentException('Alignment must be L, C, R, or J.');
		}
		if(preg_match('//u', $value) !== 1){
			throw new \InvalidArgumentException('Text must be valid UTF-8.');
		}
		// hex encode field bytes so values cannot become printer commands
		$paragraphs = preg_split('/\r\n|\r|\n/', $value);
		$encoded = [];
		foreach($paragraphs as $paragraph){
			$encoded[] = implode('', array_map(static function($byte){
				return sprintf('_%02X', ord($byte));
			}, str_split($paragraph)));
		}
		$data = implode('\\&', $encoded);
		$this->fields[] = "^FO$x,$y^A0N,$font_size,$font_size^FB$width,$lines,$line_spacing,$align,0^FH_^FD$data^FS";
		return $this;
	}

	public function box(int $x, int $y, int $width, int $height, int $thickness = 1): self {
		self::position($x, $y);
		self::range('box width', $width, 0, 32000);
		self::range('box height', $height, 0, 32000);
		self::range('thickness', $thickness, 1, 32000);
		if($width === 0 && $height === 0){
			throw new \InvalidArgumentException('A box or line must have a nonzero dimension.');
		}
		$this->fields[] = "^FO$x,$y^GB$width,$height,$thickness,B,0^FS";
		return $this;
	}

	public function graphic(Graphic $graphic, int $x, int $y): self {
		self::position($x, $y);
		$this->fields[] = "^FO$x,$y".$graphic;
		return $this;
	}

	public function image(string $filename, int $x, int $y): self {
		return $this->graphic(Graphic::from_png($filename), $x, $y);
	}

	public function render(): string {
		$header = "^XA\n^FWN^CI28^LH0,0^LS{$this->left}^LT{$this->top}^PW{$this->width}";
		if($this->height !== null){
			$header .= "^LL{$this->height}";
		}
		if($this->darkness !== null){
			$header .= "~SD{$this->darkness}";
		}
		return $header."\n".implode("\n", $this->fields)."\n^XZ\n";
	}

	public function __toString(): string {
		return $this->render();
	}

	private static function position(int $x, int $y): void {
		self::range('x', $x, 0, 32000);
		self::range('y', $y, 0, 32000);
	}

	private static function range(string $name, int $value, int $min, int $max): void {
		if($value < $min || $value > $max){
			throw new \InvalidArgumentException("$name must be between $min and $max.");
		}
	}
}
