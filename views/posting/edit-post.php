<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>
	<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>
	&raquo;
	<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_projects') ); ?>">Improve SEO Projects</a>
	&raquo;
	<span>Edit Project</span>
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>
<?php $_isDraft = isset($task) && isset($task->state) && $task->state === 'Draft'; ?>
<h1 class="hidden"><?php echo $_isDraft ? 'Draft: Edit Post' : 'Edit Post'; ?></h1>
<div class="CreatePost improveseo_wrapper">
	<section class="project-section border-bottom d-flex flex-row  justify-content-between align-items-center pb-2">
		<div class="project-heading d-flex flex-row">
			<img class="mr-2" src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSeo">
			<h1><?php echo $_isDraft ? 'Draft: Edit Post' : 'Edit Project'; ?></h1>
		</div>
	</section>
	<?php
	$form_action = isset($_GET['update']) ? 'do_update_post' : 'do_create_post';
	?>
	<form id="main_form" class="form-wrap" action="<?php echo esc_url( admin_url("admin.php?page=improveseo_dashboard&action={$form_action}&id={$task->id}&noheader=true") ); ?>" method="post">
		<?php 
			$post_type = $task->content['post_type'];

			improveseo\View::render('posting.form', compact('post_type', 'task'));
		?>
	</form>
</div>
<script>
/* Project names must be unique across single projects. Check the name with the server
   before this form submits, and show a refusal under the Project Name field — the page
   stays put, so nothing else typed into the form is lost. The controller repeats the
   check (improveseo_refuse_duplicate_project_name) as a backstop. */
(function ($) {
	var $form = $('#main_form');
	var $name = $form.find('input[name="name"]');
	if (!$form.length || !$name.length) { return; }

	var projectId = <?php echo (int) $task->id; ?>;
	var nonce     = <?php echo wp_json_encode( wp_create_nonce( 'rename_project_nonce' ) ); ?>;
	var verified  = false;

	function setError(msg) {
		var $wrap = $name.closest('.PostForm__name-wrap');
		$wrap.find('.PostForm__error').remove();
		if (msg) {
			$wrap.addClass('PostForm--error');
			$('<span class="PostForm__error" role="alert"></span>').text(msg).insertAfter($name);
			$name.trigger('focus');
			if ($name[0].scrollIntoView) { $name[0].scrollIntoView({ block: 'center' }); }
		} else {
			$wrap.removeClass('PostForm--error');
		}
	}

	$name.on('input', function () { verified = false; setError(''); });

	$form.on('submit', function (e) {
		if (verified) { return; }
		e.preventDefault();

		// The controller branches on which button submitted (create vs draft), so the
		// resubmit below has to come from that same button.
		var submitter = (e.originalEvent && e.originalEvent.submitter) || null;

		$.post(ajaxurl, {
			action: 'improveseo_check_project_name',
			id:     projectId,
			name:   $.trim($name.val()),
			nonce:  nonce
		}).done(function (res) {
			if (res && res.success && res.data && res.data.taken) {
				setError(res.data.message);
				if (window.ImproveSEOLoading && ImproveSEOLoading.hide) { ImproveSEOLoading.hide(); }
				return;
			}
			resubmit(submitter);
		}).fail(function () {
			// Can't reach the check — let the server-side backstop decide.
			resubmit(submitter);
		});
	});

	function resubmit(submitter) {
		verified = true;
		if (submitter && typeof $form[0].requestSubmit === 'function') {
			$form[0].requestSubmit(submitter);
		} else {
			if (submitter && submitter.name) {
				$('<input type="hidden">').attr('name', submitter.name).val(submitter.value || '1').appendTo($form);
			}
			$form[0].submit();
		}
	}
})(jQuery);
</script>
<?php View::endSection('content') ?>

<?php View::make('layouts.main'); ?>