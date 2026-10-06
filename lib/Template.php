<?php
/*
ZplKit is licensed under the Apache License 2.0 license
https://github.com/trp-solutions/HealDocument/blob/main/LICENSE
*/
declare(strict_types=1);
namespace TRP\ZplKit;

class Template {
	public function __construct(private array $definition){}

	public function render(array $values = []): Label {
		self::keys($this->definition, ['label', 'elements']);
		$settings = $this->definition['label'] ?? [];
		self::keys($settings, ['width', 'height', 'top', 'left', 'darkness']);
		if(!isset($settings['width'])){
			throw new \InvalidArgumentException('The template must specify a label width.');
		}
		$label = new Label(...$settings);
		$elements = $this->definition['elements'] ?? [];
		if(!is_array($elements)){
			throw new \InvalidArgumentException('Template elements must be an array.');
		}
		foreach($elements as $element){
			if(!is_array($element)){
				throw new \InvalidArgumentException('Each template element must be an array.');
			}
			$type = $element['type'] ?? null;
			if($type === 'text'){
				self::keys($element, ['type', 'field', 'value', 'x', 'y', 'width', 'font_size', 'lines', 'line_spacing', 'align']);
				self::required($element, ['x', 'y', 'width']);
				if(array_key_exists('field', $element) === array_key_exists('value', $element)){
					throw new \InvalidArgumentException('Text must specify either a field binding or a fixed value.');
				}
				if(array_key_exists('field', $element)){
					$field = $element['field'];
					if(!is_string($field) || !array_key_exists($field, $values)){
						throw new \InvalidArgumentException('A required template field is missing.');
					}
					$element['value'] = $values[$field];
				}
				if(!is_string($element['value'])){
					throw new \InvalidArgumentException('Text values must be strings.');
				}
				unset($element['type'], $element['field']);
				$label->text(...$element);
			} elseif($type === 'box' || $type === 'line'){
				self::keys($element, ['type', 'x', 'y', 'width', 'height', 'thickness']);
				self::required($element, ['x', 'y', 'width', 'height']);
				unset($element['type']);
				$label->box(...$element);
			} elseif($type === 'image' || $type === 'graphic'){
				self::keys($element, ['type', 'filename', 'x', 'y']);
				self::required($element, ['filename', 'x', 'y']);
				$graphic = $type === 'image' ? Graphic::from_png($element['filename']) : Graphic::from_zpl($element['filename']);
				$label->graphic($graphic, $element['x'], $element['y']);
			} else {
				throw new \InvalidArgumentException('Unknown template element type.');
			}
		}
		return $label;
	}

	private static function keys(array $values, array $allowed): void {
		foreach(array_keys($values) as $key){
			if(!in_array($key, $allowed, true)){
				throw new \InvalidArgumentException("Unknown template option: $key");
			}
		}
	}

	private static function required(array $values, array $required): void {
		foreach($required as $key){
			if(!array_key_exists($key, $values)){
				throw new \InvalidArgumentException("Missing template option: $key");
			}
		}
	}
}
