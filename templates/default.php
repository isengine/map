<?php

namespace is\Masters\Modules\Isengine\Map;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Prepare;

$instance = Strings::after($this->instance, ':', null, true);
$sets = $this->settings;

?>

<div
    id="<?= $instance; ?>"
    class="
        <?= $sets['classes'] ? $sets['classes'] : null; ?>
    "
    <?php if ($sets['width'] || $sets['height']) { ?>
    style="
        <?= $sets['width'] ? 'width: ' . $sets['width'] . ';' : null; ?>
        <?= $sets['height'] ? 'height: ' . $sets['height'] . ';' : null; ?>
    "
    <?php } ?>
></div>

<?php $this->block( $sets['service'] . ($sets['api'] ? '.' . $sets['api'] : null) ); ?>
