<?php

namespace is\Masters\Modules\Isengine\Map;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

use is\Masters\View;

$view = View::getInstance();

$sets = &$this -> settings;
$instance = Strings::after($this -> instance, ':', null, true);

$position = $sets['position'];
$selector = [];
if ($position) {
	if (!System::typeIterable($position)) {
		$position = [];
	}
	if ($position['image']) {
		$image = DI . $position['image'];
		$position['iconLayout'] = 'default#image';
		$position['iconImageHref'] = $position['image'];
		$position['iconImageSize'] = [
			'currVal.image.width', 'currVal.image.height'
		];
		// Смещение левого верхнего угла иконки относительно ее "ножки" (точки привязки)
		//$position['iconImageOffset'] = [' . $sets['marks'][0]['offset'][0] . ', ' . $sets['marks'][0]['offset'][1] . ']
		unset($position['image']);
	} else {
		if ($position['preset']) {
			$position['preset'] = 'islands#' . $position['preset'];
		} else {
			$position['preset'] = 'islands#geolocationIcon';
		}
		if ($position['color']) {
			$position['iconColor'] = $position['color'];
			unset($position['color']);
		}
	}
	if ($position['selector']) {
		$selector = $position['selector'];
		unset($position['selector']);
	}
	$position['draggable'] = true;
}

$view -> get('display') -> addBuffer('

<script src="https://api-maps.yandex.ru/2.1/?apikey=' . $sets['key'] . '&lang=ru_RU"></script>
<script>
ymaps.ready(function() {
	var
		' . ($position ? 'um, geolocation = ymaps.geolocation,' : null) . '
		map,
		marks = ' . json_encode($sets['marks']) . ',
		type = "' . (!empty($sets['type']) ? $sets['type'] : null) . '",
		position = [' . $sets['coordinates'][0] . ', ' . $sets['coordinates'][1] . '],
		controls = ' . (!empty($sets['controls']) ? json_encode($sets['controls']) : '["default"]') . ',
		placemark;
	
	if (type === "roadmap" || type === "terrain" || type === "scheme" || !type) {
		type = "map";
	}
	
	map = new ymaps.Map("' . $instance . '", {
		center: position,
		zoom: ' . $sets['zoom'] . ',
		type: "yandex#" + type,
		controls: controls
	});
	
	' . ($position ? '
	var selector = function(coords) {
		' . ($selector['lat'] ? '$("' . $selector['lat'] . '").val(coords[0].toPrecision(8));' : null) . '
		' . ($selector['lon'] ? '$("' . $selector['lon'] . '").val(coords[1].toPrecision(8));' : null) . '
		' . ($selector['common'] ? '$("' . $selector['common'] . '").val(coords[0].toPrecision(8) + ":" + coords[1].toPrecision(8));' : null) . '
		' . ($selector['address'] ? '
		ymaps.geocode(coords).then(function(res){
			let geoname = res.geoObjects.get(0).getAddressLine();
			$("' . $selector['address'] . '").val(geoname);
		});
		' : null) . '
	}
	
	geolocation.get({
		provider: "yandex",
		mapStateAutoApply: true,
		autoReverseGeocode: true
	}).then(function (result) {
		map.geoObjects.add(result.geoObjects);
		um = new ymaps.Placemark(
			result.geoObjects.position,
			{},
			' . json_encode($position) . '
		);
		um.events.add("dragend", function(e){
			selector( e.get("target").geometry.getCoordinates() );
		});
		map.geoObjects.add(um);
		selector(result.geoObjects.position);
	});
	
	map.events.add("click", function(e) {
		var coords = e.get("coords");
		um.geometry.setCoordinates(coords);
		selector(coords);
	});
	' : null) . '
	
	// new browser loader
	marks.map(function(currVal){
		
		if (currVal.coordinates) {
			currVal.coordinates = [currVal.coordinates[0], currVal.coordinates[1]];
		} else {
			currVal.coordinates = map.getCenter();
		}
		
		if (currVal.image) {
			currVal.image = {
				iconLayout: "default#image",
				iconImageHref: currVal.image.url,
				iconImageSize: [currVal.image.width, currVal.image.height]
				// Смещение левого верхнего угла иконки относительно ее "ножки" (точки привязки)
				//iconImageOffset: [' . $sets['marks'][0]['offset'][0] . ', ' . $sets['marks'][0]['offset'][1] . ']
			}
		} else if (currVal.preset || currVal.color) {
			currVal.image = {
				preset: (currVal.preset) ? "islands#" + currVal.preset : null,
				iconColor: (currVal.color) ? currVal.color : null
			}
		} else {
			currVal.image = null;
		}
		
		//console.log(currVal.image);
		
		placemark = new ymaps.Placemark(
			currVal.coordinates,
			{
				iconCaption: (currVal.caption) ? currVal.caption : null,
				hintContent: (currVal.hint) ? currVal.hint : null,
				balloonContentHeader: (currVal.header) ? currVal.header : null,
				balloonContentBody: (currVal.content) ? currVal.content : null,
				balloonContentFooter: (currVal.footer) ? currVal.footer : null,
			},
			currVal.image);
		map.geoObjects.add(placemark);
		
		if (currVal.autoopen) {
			placemark.balloon.open();
		}
		
	});
	
	setupScrollZoom(map);
	
	function setupScrollZoom(map) {
		var mapHoverTimer;
		function disableZoom(map) {
			map.behaviors.disable(["scrollZoom", "multiTouch", "drag"]);
		}
		function enableZoom(map) {
			map.behaviors.enable(["scrollZoom", "multiTouch", "drag"]);
		}
		disableZoom(map);
		map.events.add("click", function() {
			setTimeout(function() {
				enableZoom(map);
			}, 500);
		});
	}
	
});
</script>

');
?>