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
$name = $position && $position['name'] ? $position['name'] : $instance . ':coords';
$selector = $position['selector'] ? $position['selector'] : [];
unset($position['selector']);

$view -> get('display') -> addBuffer('

<script src="https://api-maps.yandex.ru/2.1/?apikey=' . $sets['key'] . '&lang=ru_RU"></script>
<script>
ymaps.ready(function() {
	var
		map,
		marks = ' . json_encode($sets['marks']) . ',
		type = "' . (!empty($sets['type']) ? $sets['type'] : null) . '",
		placemark;
	
	if (type === "roadmap" || type === "terrain" || type === "scheme" || !type) {
		type = "map";
	}
	
	map = new ymaps.Map("' . $instance . '", {
		center: ' . json_encode($sets['coordinates']) . ',
		zoom: ' . $sets['zoom'] . ',
		type: "yandex#" + type,
		controls: ' . (!empty($sets['controls']) ? json_encode($sets['controls']) : '["default"]') . '
	});
	
	' . ($position ? '
	var selector = function(coords) {
		is.Helpers.Sessions.setSession("' . $name . '", coords);
		' . ($selector['lat'] ? '$("' . $selector['lat'] . '").val(coords[0].toPrecision(8));' : null) . '
		' . ($selector['lon'] ? '$("' . $selector['lon'] . '").val(coords[1].toPrecision(8));' : null) . '
		' . ($selector['common'] ? '$("' . $selector['common'] . '").val(coords[0].toPrecision(8) + ":" + coords[1].toPrecision(8));' : null) . '
		' . ($selector['address'] || $selector['street'] ? '
		ymaps.geocode(coords).then(function(res){
			let geoname = res.geoObjects.get(0);
			' . ($selector['street'] ? 'let stnew = [
				//geoname.getLocalities().length ? geoname.getLocalities() : geoname.getAdministrativeAreas(),
				geoname.getLocalities(),
				geoname.getThoroughfare(),
				geoname.getPremise(),
				geoname.getPremiseNumber()
			].filter(Boolean).join(", ");
			if (stnew) {
				$("' . $selector['street'] . '").html(stnew);
			}' : null) . '
			' . ($selector['address'] ? '$("' . $selector['address'] . '").val( geoname.getAddressLine() );' : null) . '
		});
		' : null) . '
	}
	
	var selectorReverse = function() {
		let coords = is.Helpers.Sessions.getSession("' . $name . '");
		if (!coords) {
			return null;
		}
		coords = coords.split(",");
		coords[0] = parseFloat(coords[0]);
		coords[1] = parseFloat(coords[1]);
		return coords;
	}
	
	ymaps.geolocation.get().then(function (result) {
		let coords = selectorReverse();
		if (coords) {
			result.geoObjects.position = coords;
		} else {
			coords = result.geoObjects.position;
		}
		map.setCenter(coords);
		
		let v = ' . json_encode($position) . ';
		if (v.image) {
			v.image = {
				iconLayout: "default#image",
				iconImageHref: v.image.url,
				iconImageSize: [v.image.width, v.image.height],
				iconImageOffset: [v.image.offset.width, v.image.offset.height]
			}
		} else if (v.preset || v.color) {
			v.image = {
				preset: (v.preset) ? "islands#" + v.preset : undefined,
				iconColor: (v.color) ? v.color : undefined
			}
		} else {
			v.image = {};
		}
		if (v.draggable) {
			v.image.draggable = v.draggable;
		}
		
		let um = new ymaps.Placemark(
			coords,
			{},
			v.image
		);
		
		if (v.draggable) {
			um.events.add("dragend", function(e){
				selector( e.get("target").geometry.getCoordinates() );
			});
		} else {
			map.events.add("actiontick", function(e) {
				let current_state = map.action.getCurrentState();
				let geoCenter = map.options.get("projection").fromGlobalPixels(
					current_state.globalPixelCenter,
					current_state.zoom
				);
				um.geometry.setCoordinates(geoCenter);
				selector(geoCenter);
			});
		}
		
		map.geoObjects.add(um);
		selector(coords);
		
	});
	
	' : null) . '
	
	// new browser loader
	marks.map(function(v){
		
		console.log(v);
		
		if (v.coordinates) {
			v.coordinates = [v.coordinates[0], v.coordinates[1]];
		} else {
			v.coordinates = map.getCenter();
		}
		
		if (v.image) {
			v.image = {
				iconLayout: "default#image",
				iconImageHref: v.image.url,
				iconImageSize: [v.image.width, v.image.height],
				iconImageOffset: [v.image.offset.width, v.image.offset.height]
			}
		} else if (v.preset || v.color) {
			v.image = {
				preset: (v.preset) ? "islands#" + v.preset : undefined,
				iconColor: (v.color) ? v.color : undefined
			}
		} else {
			v.image = {};
		}
		
		//console.log(v.image);
		
		placemark = new ymaps.Placemark(
			v.coordinates,
			{
				iconCaption: (v.caption) ? v.caption : null,
				hintContent: (v.hint) ? v.hint : null,
				balloonContentHeader: (v.header) ? v.header : null,
				balloonContentBody: (v.content) ? v.content : null,
				balloonContentFooter: (v.footer) ? v.footer : null,
			},
			v.image);
		map.geoObjects.add(placemark);
		
		if (v.autoopen) {
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