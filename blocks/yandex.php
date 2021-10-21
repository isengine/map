<?php

$sets = &$this -> settings;
$instance = &$this -> instance;

?>

<script src="https://api-maps.yandex.ru/2.1/?apikey=<?= $sets['key']; ?>&lang=ru_RU"></script>
<script>
ymaps.ready(function() {
	var
		map,
		marks = <?= json_encode($sets['marks']); ?>,
		type = '<?= (!empty($sets['type'])) ? $sets['type'] : ''; ?>',
		position = [<?= $sets['coordinates'][0]; ?>, <?= $sets['coordinates'][1]; ?>],
		controls = <?= (!empty($sets['controls'])) ? json_encode($sets['controls']) : '[\'default\']'; ?>,
		placemark;
	
	if (type === 'roadmap' || type === 'terrain' || type === 'scheme' || !type) {
		type = 'map';
	}
	
	map = new ymaps.Map('map_<?= $instance; ?>', {
		center: position,
		zoom: <?= $sets['zoom']; ?>,
		type: 'yandex#' + type,
		controls: controls
	});
		
	// new browser loader
	marks.map(function(currVal){
		
		if (currVal.coordinates) {
			currVal.coordinates = [currVal.coordinates[0], currVal.coordinates[1]];
		} else {
			currVal.coordinates = map.getCenter();
		}
		
		if (currVal.image) {
			currVal.image = {
				iconLayout: 'default#image',
				iconImageHref: currVal.image.url,
				iconImageSize: [currVal.image.width, currVal.image.height]
				// Смещение левого верхнего угла иконки относительно ее "ножки" (точки привязки)
				//iconImageOffset: [<?= $sets['marks'][0]['offset'][0]; ?>, <?= $sets['marks'][0]['offset'][1]; ?>]
			}
		} else if (currVal.preset || currVal.color) {
			currVal.image = {
				preset: (currVal.preset) ? 'islands#' + currVal.preset : null,
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
			map.behaviors.disable(['scrollZoom', 'multiTouch', 'drag']);
		}
		function enableZoom(map) {
			map.behaviors.enable(['scrollZoom', 'multiTouch', 'drag']);
		}
		disableZoom(map);
		map.events.add('click', function() {
			setTimeout(function() {
				enableZoom(map);
			}, 500);
		});
	}
	
});
</script>