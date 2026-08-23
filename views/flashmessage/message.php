<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
 if ($message): ?>
<div class="notice notice-<?php echo  $type ?> is-dismissible notice-improveseo">
	<p><?php echo  $message ?></p>
</div>
<?php endif; ?>