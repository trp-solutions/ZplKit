<?php
/*
ZplKit is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/HealDocument/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\ZplKit;

class ZplConverter {
	public static function convert(string $filename): string {
		return '^XA ^FO0,0'.self::graphic($filename)."\n^XZ\n";
	}

	public static function graphic(string $filename): string {
		if(!extension_loaded('imagick')){
			throw new \RuntimeException('The Imagick extension is required.');
		}
		if(!is_file($filename) || !is_readable($filename)){
			throw new \InvalidArgumentException('The PNG file must exist and be readable.');
		}
		$contents = file_get_contents($filename);
		if($contents === false){
			throw new \RuntimeException('Unable to read the PNG file.');
		}
		if(!str_starts_with($contents, "\x89PNG\r\n\x1a\n")){
			throw new \InvalidArgumentException('The file must be a PNG image.');
		}

		$image = new \Imagick();
		try {
			$image->readImageBlob($contents);
			$image->setIteratorIndex(0);
			$width = $image->getImageWidth();
			$height = $image->getImageHeight();
			$bytes_per_row = intdiv($width + 7, 8);
			$byte_count = $bytes_per_row * $height;
			if($byte_count > 99999){
				throw new \InvalidArgumentException('The image exceeds the ZPL graphic field limit of 99999 bytes.');
			}

			$image->setImageBackgroundColor('white');
			$image->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
			$image->transformImageColorspace(\Imagick::COLORSPACE_GRAY);
			$image->orderedDitherImage('o8x8,2');

			$data = '';
			for($y = 0; $y < $height; ++$y){
				$pixels = $image->exportImagePixels(0, $y, $width, 1, 'I', \Imagick::PIXEL_CHAR);
				for($x = 0; $x < $width; $x += 8){
					$byte = 0;
					for($bit = 0; $bit < 8 && $x + $bit < $width; ++$bit){
						if($pixels[$x + $bit] === 0){
							$byte |= 1 << (7 - $bit);
						}
					}
					// each row is padded with white bits to the next full byte.
					$data .= sprintf('%02X', $byte);
				}
			}

			return "^GFA,$byte_count,$byte_count,$bytes_per_row,$data^FS";
		} finally {
			$image->clear();
		}
	}
}
