<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
 if ($message): ?>
<div class="notice notice-<?php echo esc_attr( $type ) ?> is-dismissible notice-improveseo">
	<p><?php echo wp_kses_post( $message ) ?></p>
</div>
<?php endif; ?>