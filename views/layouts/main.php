
<?php if ( ! defined( 'ABSPATH' ) ) exit;?>
<div class="wrap improveseo-page">

	<div class="Breadcrumbs">
		<?php echo ImproveSEO\View::section('breadcrumbs') ?>
	</div>

	<?php
		ImproveSEO\FlashMessage::handle();
	?>

	<?php echo ImproveSEO\View::section('content') ?>
</div>
