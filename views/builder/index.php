<?php

use ImproveSEO\View;
?>

<?php View::startSection('breadcrumbs') ?>
<span><?php _e('Improve SEO', 'improve-seo'); ?></span>
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content'); ?>
<div class="Posting">
	<h1 class="Posting__header"><?php _e('All posts/pages were generated!', 'improve-seo'); ?></h1>
</div>
<?php View::endSection('content') ?>

<?php echo View::make('layouts.main') ?>