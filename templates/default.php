<?php

namespace is\Masters\Modules\Isengine\Map;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Prepare;

$instance = $object -> get('instance');
$sets = &$object -> settings;

?>

<div
	id="map_<?= $instance; ?>"
	class="
		<?= $instance; ?>
		<?= $sets['classes'] ? $sets['classes'] : null; ?>
	"
	<?php if (System::typeIterable($sets['sizes'])) { ?>
	style="
		<?= $sets['sizes'][0] ? 'width: ' . $sets['sizes'][0] . ';' : null; ?>
		<?= $sets['sizes'][1] ? 'height: ' . $sets['sizes'][1] . ';' : null; ?>
	"
	<?php } elseif ($sets['width'] || $sets['height']) { ?>
	style="
		<?= $sets['width'] ? 'width: ' . $sets['width'] . ';' : null; ?>
		<?= $sets['height'] ? 'height: ' . $sets['height'] . ';' : null; ?>
	"
	<?php } ?>
></div>

<?php $object -> blocks( $sets['service'] ); ?>
