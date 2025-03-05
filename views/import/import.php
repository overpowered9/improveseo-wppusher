<?php if ( ! defined( 'ABSPATH' ) ) exit;?>
<div class="project-import-box">
	<form action="<?php admin_url('admin.php?page=improveseo_projects'); ?>" method="post" enctype="multipart/form-data" style="display: flex;">
		<?php wp_nonce_field('import_project_nonce'); ?>
        <label for="" class="mt-1">select a CSV: </label>
		<input type="file" class="form-control pl-2" name="upload_csv">
		<input type="submit" name="submit" class="btn btn-outline-primary btn-small" value="Upload">
	</form>
</div>
