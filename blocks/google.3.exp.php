<?php

namespace is\Masters\Modules\Isengine\Map;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

use is\Masters\View;

$view = View::getInstance();

$sets = &$this -> settings;
$instance = Strings::after($this -> instance, ':', null, true);

$controls = $sets['controls'] ? 'disableDefaultUI: true, ' . Strings::join($sets['controls'], ': true, ') . ': true' : null;

$view -> get('display') -> addBuffer('

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=' . $sets['key'] . '"></script>
<script>
	var
		map,
		marks = ' . json_encode($sets['marks']) . ',
		type = "' . (!empty($sets['type']) ? $sets['type'] : null) . '",
		position = {
			lat: ' . $sets['coordinates'][0] . ',
			lng: ' . $sets['coordinates'][1] . '
		},
		content = [],
		infowindow = [],
		marker = [];
	
	if (type === "map" || type === "scheme" || !type) {
		type = "roadmap";
	}
	
	google.maps.event.addDomListener(window, "load", initMap);
	
	function initMap() {
		map = new google.maps.Map(document.getElementById("' . $instance . '"), {
			center: position,
			zoom: ' . $sets['zoom'] . ',
			mapTypeId: type,
			' . $controls . '
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
			
			content[i] = "";
			
			if (currVal.header || currVal.content || currVal.footer) {
				
				if (currVal.header) {
					content[i] = content[i] + "<div class=\"' . $instance . '_header\">" + currVal.header + "</div>";
				}
				if (currVal.content) {
					content[i] = content[i] + "<div class=\"' . $instance . '_content\">" + currVal.content + "</div>";
				}
				if (currVal.footer) {
					content[i] = content[i] + "<div class=\"' . $instance . '_footer\">" + currVal.footer + "</div>";
				}
				
				google.maps.event.addListener(marker[i], "click", function(){
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

');
?>