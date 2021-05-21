<?php

namespace is\Masters\Modules\Isengine;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;

use is\Masters\Modules\Master;

class Map extends Master {
	
	public function launch() {
		
		// если нет ключа, пробуем взять ключ из СЕО
		
		if (empty($module -> settings['key'])) {
			global $seo;
			if (
				$module -> settings['service'] === 'yandex' &&
				!empty($seo -> webmaster['yandex-apikey'])
			) {
				$module -> settings['key'] = $seo -> webmaster['yandex-apikey'];
			} elseif (
				$module -> settings['service'] === 'google' &&
				!empty($seo -> webmaster['google-apikey'])
			) {
				$module -> settings['key'] = $seo -> webmaster['google-apikey'];
			}
		}
		
		// если нет массива маркеров, то создаем пустой
		
		if (is_string($module -> settings['marks'])) {
			$module -> settings['marks'] = [['image' => $this -> createmapmark($module -> settings['marks'])]];
		} elseif (
			!empty($module -> settings['marks']) &&
			is_array($module -> settings['marks'])
		) {
			
			// прогоняем массив маркеров
			foreach ($module -> settings['marks'] as &$item) {
				// устанавливаем для каждого параметры изображения, если оно есть
				if (!empty($item['image'])) {
					$item['image'] = $this -> createmapmark($item['image']);
				}
				
				if (!empty($item['content'])) {
					//$item -> content = clear($item -> content, 'onestring');
					$item['content'] = dataprint($item['content'], 'tospaces', true);
				}
				
			}
			unset($item);
			
		} else {
			$module -> settings['marks'] = [[]];
		}
		
	}
	
	public function createmapmark($item) {
		
		// функция проверки изображения, формирования массива данных и возвращение его обратно
		
		$image = (object) array(
			'url' => URL_LOCAL . $item,
			'php' => PATH_LOCAL . $item,
			'data' => '',
			'type' => '',
			'width' => '',
			'height' => ''
		);
		
		if (
			file_exists($image -> php) &&
			in_array('fileinfo', get_loaded_extensions()) &&
			in_array('gd', get_loaded_extensions())
		) {
			$image -> type = mime_content_type($image -> php);
			if (substr($image -> type, 0, strpos($image -> type, '/')) === 'image') {
				$image -> data = getimagesize($image -> php);
				$image -> width = $image -> data[0];
				$image -> height = $image -> data[1];
				unset($image -> data, $image -> php);
			} else {
				return false;
			}
		} elseif (!file_exists($image -> php)) {
			return false;
		}
		
		return($image);
		
	}
	
}

//print_r($module -> settings['marks']);

?>