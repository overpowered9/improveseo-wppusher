
<?php if ( ! defined( 'ABSPATH' ) ) exit;?>
<div class="wrap improveseo-page">

	<div class="Breadcrumbs">
		<?php echo htmlspecialchars_decode(ImproveSEO\View::section('breadcrumbs')) ?>
	</div>

	<?php
		ImproveSEO\FlashMessage::handle();
	?>

	<?php echo htmlspecialchars_decode(ImproveSEO\View::section('content')) ?>
</div>
