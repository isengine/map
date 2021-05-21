<?php if ($module -> settings['service'] === 'yandex') : ?>

<script src="https://api-maps.yandex.ru/2.1/?apikey=<?= $module -> settings['key']; ?>&lang=ru_RU"></script>
<script>
ymaps.ready(function() {
	var
		map,
		marks = <?= json_encode($module -> settings['marks']); ?>,
		type = '<?= (!empty($module -> settings['type'])) ? $module -> settings['type'] : ''; ?>',
		position = [<?= $module -> settings['coordinates'][0]; ?>, <?= $module -> settings['coordinates'][1]; ?>],
		controls = <?= (!empty($module -> settings['controls'])) ? json_encode($module -> settings['controls']) : '[\'default\']'; ?>,
		placemark;
	
	if (type === 'roadmap' || type === 'terrain' || type === 'scheme' || !type) {
		type = 'map';
	}
	
	map = new ymaps.Map('map_<?= $module -> param; ?>', {
		center: position,
		zoom: <?= $module -> settings['zoom']; ?>,
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
				//iconImageOffset: [<?= $module -> settings['marks'][0]['offset'][0]; ?>, <?= $module -> settings['marks'][0]['offset'][1]; ?>]
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

<?php elseif ($module -> settings['service'] === 'google') : ?>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=<?= $module -> settings['key']; ?>"></script>
<script>
	var
		map,
		marks = <?= json_encode($module -> settings['marks']); ?>,
		type = '<?= (!empty($module -> settings['type'])) ? $module -> settings['type'] : ''; ?>',
		position = {
			lat: <?= $module -> settings['coordinates'][0]; ?>,
			lng: <?= $module -> settings['coordinates'][1]; ?>
		},
		content = [],
		infowindow = [],
		marker = [];
	
	if (type === 'map' || type === 'scheme' || !type) {
		type = 'roadmap';
	}
	
	google.maps.event.addDomListener(window, 'load', initMap);
	
	function initMap() {
		map = new google.maps.Map(document.getElementById('map_<?= $module -> param; ?>'), {
			center: position,
			zoom: <?= $module -> settings['zoom']; ?>,
			mapTypeId: type,
			<?php if (!empty($module -> settings['controls'])) : ?>
				disableDefaultUI: true,
				<?php foreach ($module -> settings['controls'] as $item) : ?>
					<?= $item; ?>: true,
				<?php endforeach; ?>
			<?php endif; ?>
		});
		
		marks.forEach(function(currVal, i){
			
			if (currVal.coordinates) {
				currVal.coordinates = {lat: Number(currVal.coordinates[0]), lng: Number(currVal.coordinates[1])};
			} else {
				currVal.coordinates = position;
			}
			
			marker[i] = new google.maps.Marker({
				position: currVal.coordinates,
				map: map,
				icon: (currVal.image && currVal.image.url) ? currVal.image.url : null,
				title: (currVal.hint) ? currVal.hint : null
			});
			
			content[i] = '';
			
			if (currVal.header || currVal.content || currVal.footer) {
				
				if (currVal.header) {
					content[i] = content[i] + '<div class="map_<?= $module -> param; ?>_header">' + currVal.header + '</div>';
				}
				if (currVal.content) {
					content[i] = content[i] + '<div class="map_<?= $module -> param; ?>_content">' + currVal.content + '</div>';
				}
				if (currVal.footer) {
					content[i] = content[i] + '<div class="map_<?= $module -> param; ?>_footer">' + currVal.footer + '</div>';
				}
				
				google.maps.event.addListener(marker[i], 'click', function(){
					infowindow[i] = new google.maps.InfoWindow({
						content: content[i],
						position: currVal.coordinates,
					});
					infowindow[i].open(map);
				});
				
				if (currVal.autoopen) {
					infowindow[i] = new google.maps.InfoWindow({
						content: content[i],
						position: currVal.coordinates,
					});
					infowindow[i].open(map);
				}
				
			}
			
			marker[i].setMap(map);
		});
	}
</script>

<?php endif; ?>