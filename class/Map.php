<?php

namespace is\Masters\Modules\Isengine;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Prepare;

use is\Masters\Modules\Master;
use is\Masters\View;

use is\Components\Config;

class Map extends Master {
	
	public function launch() {
		
		// если нет ключа, пробуем взять ключ из СЕО
		
		$view = View::getInstance();
		$state = $view -> get('state|settings:webmaster');
		
		$sets = &$this -> settings;
		
		//echo print_r($this -> settings, 1);
		//echo print_r($sets, 1);
		
		$service = $sets['service'];
		
		if (!$sets['key'] && $service) {
			$sets['key'] = $state[$service]['apikey'];
		}
		
		// если нет массива маркеров, то создаем пустой
		
		if ( System::type($sets['marks'], 'string') ) {
			
			$sets['marks'] = [['image' => $this -> createMapMark($sets['marks'])]];
			
		} elseif ( System::typeIterable($sets['marks']) ) {
			
			// прогоняем массив маркеров
			
			foreach ($sets['marks'] as &$item) {
				
				// устанавливаем для каждого параметры изображения, если оно есть
				
				if (!empty($item['image'])) {
					$item['image'] = $this -> createMapMark($item['image']);
				}
				
				if (!empty($item['content'])) {
					$item['content'] = $view -> get('tvars') -> launch( Prepare::clear($item['content']) );
				}
				
			}
			unset($item);
			
		} else {
			$sets['marks'] = [[]];
		}
		
	}
	
	public function createMapMark($item) {
		
		// функция проверки изображения, формирования массива данных и возвращение его обратно
		
		$config = Config::getInstance();
		
		$image = (object) array(
			'url' => '/' . $item,
			'php' => $config -> get('path:site') . $item,
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

//print_r($sets['marks']);

?>