<?php
use ImproveSEO\View;
?>

<?php View::startSection('breadcrumbs') ?>
	<span>Improve SEO</span>
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content'); ?>
<div class="Posting">
	<h1 class="Posting__header">All posts/pages were generated!</h1>
</div>
<?php View::endSection('content') ?>

<?php echo View::make('layouts.main') ?>
