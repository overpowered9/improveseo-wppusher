<div class="project-import-box">
	<form action="<?php admin_url('admin.php?page=improveseo_projects'); ?>" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field('import_project_nonce'); ?>
		<input type="file" class="form-control" name="upload_csv">
		<input type="submit" name="submit" class="btn btn-outline-primary btn-small" value="Upload">
	</form>
</div>
