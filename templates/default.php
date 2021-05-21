<?php



?>

<div
	id="map_<?= $module -> param; ?>"
	class="
		<?= $module -> param; ?>
		<?= (!empty($module -> settings['classes'])) ? $module -> settings['classes'] : ''; ?>
	"
	<?php
		if (
			isset($module -> settings['sizes']) &&
			is_array($module -> settings['sizes']) &&
			count($module -> settings['sizes'])
		) :
	?>
	style="
		<?= ($module -> settings['sizes'][0]) ? 'width: ' . $module -> settings['sizes'][0] . ';' : ''; ?>
		<?= ($module -> settings['sizes'][1]) ? 'height: ' . $module -> settings['sizes'][1] . ';' : ''; ?>
	"
	<?php endif; ?>
></div>