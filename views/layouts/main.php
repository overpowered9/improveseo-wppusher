<div class="wrap improveseo-page">

	<div class="Breadcrumbs">
		<?= ImproveSEO\View::section('breadcrumbs') ?>
	</div>

	<?php
		ImproveSEO\FlashMessage::handle();
	?>

	<?= ImproveSEO\View::section('content') ?>
</div>
