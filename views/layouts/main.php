<div class="wrap workhorse-page">

	<div class="Breadcrumbs">
		<?= WorkHorse\View::section('breadcrumbs') ?>
	</div>

	<?php
		WorkHorse\FlashMessage::handle();
	?>

	<?= WorkHorse\View::section('content') ?>
</div>
