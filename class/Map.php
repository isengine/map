<?php

namespace is\Masters\Modules\Isengine;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Prepare;
use is\Masters\Modules\Master;
use is\Masters\View;

class Map extends Master
{
    public $tvars;

    public function launch()
    {
        // если нет ключа, пробуем взять ключ из СЕО

        $view = View::getInstance();
        $state = $view->get('state|settings:webmaster');

        $sets = $this->settings;

        $this->tvars = $view->get('tvars');
        $sets = $this->tvars($sets);

        //$position = $this->settings['position'];
        $position = $sets['position'];
        //echo print_r($this->settings, 1);
        //echo print_r($sets, 1);

        $service = $sets['service'];

        if (!$sets['key'] && $service) {
            $sets['key'] = $state[$service]['apikey'];
        }

        // если нет массива маркеров, то создаем пустой

        if ( System::type($sets['marks'], 'string') ) {
            $sets['marks'] = [['image' => $this->createMapMark($sets['marks'])]];
        } elseif ( System::typeIterable($sets['marks']) ) {
            // прогоняем массив маркеров

            foreach ($sets['marks'] as &$item) {
                // устанавливаем для каждого параметры изображения, если оно есть

                if (!empty($item['image'])) {
                    $item['image'] = $this->createMapMark($item['image'], $item['offset']);
                }

                if (!empty($item['content'])) {
                    $item['content'] = $view->get('tvars')->launch( Prepare::clear($item['content']) );
                }
            }
            unset($item);
        } else {
            $sets['marks'] = $position ? [] : [[]];
        }

        // задаем настройки позиции нахождения пользователя на карте (с автоопределением)

        if ($position) {
            if (!System::typeIterable($position)) {
                $position = [];
            }
            if (!empty($position['image'])) {
                $position['image'] = $this->createMapMark($position['image'], $position['offset']);
                // Смещение левого верхнего угла иконки относительно ее "ножки" (точки привязки)
                //$position['iconImageOffset'] = [' . $sets['marks'][0]['offset'][0] . ', ' . $sets['marks'][0]['offset'][1] . ']
                unset($position['preset'], $position['color']);
            } else {
                if (empty($position['preset'])) {
                    $position['preset'] = 'geolocationIcon';
                }
            }
        }
    }

    public function tvars($item)
    {
        // вызывает обработку текстовых переменных для элемента
        if (is_array($item)) {
            $item = Objects::each($item, function($i){
                return $this->tvars($i);
            });
        } else {
            $item = $this->tvars->launch($item);
        }
        return $item;
    }

    public function createMapMark($item, $offset = null)
    {
        // функция проверки изображения, формирования массива данных и возвращение его обратно

        $image = (object) [
            'url' => '/' . $item,
            'php' => DI . $item,
            'data' => '',
            'type' => '',
            'width' => '',
            'height' => '',
            'offset' => (object) [
                // Смещение левого верхнего угла иконки относительно ее "ножки" (точки привязки)
                'width' => $offset['width'],
                'height' => $offset['height']
            ]
        ];

        if (
            file_exists($image->php) &&
            in_array('fileinfo', get_loaded_extensions()) &&
            in_array('gd', get_loaded_extensions())
        ) {
            $image->type = mime_content_type($image->php);
            if (substr($image->type, 0, strpos($image->type, '/')) === 'image') {
                $image->data = getimagesize($image->php);
                $image->width = $image->data[0];
                $image->height = $image->data[1];
                unset($image->data, $image->php);
            } else {
                return false;
            }
        } elseif (!file_exists($image->php)) {
            return false;
        }

        return($image);
    }
}

//print_r($sets['marks']);
