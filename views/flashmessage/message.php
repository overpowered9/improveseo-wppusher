<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
 if ($message): ?>
<div class="notice notice-<?= $type ?> is-dismissible notice-improveseo">
	<p><?= $message ?></p>
</div>
<?php endif; ?>