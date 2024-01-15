<div class="project-import-box">
    <form action="<?php echo admin_url('admin.php?page=improveseo_projects'); ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="upload_csv"><?php _e("Select CSV File:","improve-seo")?></label>
            <input type="file" class="form-control" name="upload_csv" id="upload_csv" required>
        </div>
        <input type="submit" name="submit" class="btn btn-outline-primary btn-small" value="<?php _e("Upload","improve-seo") ?>">
    </form>
</div>
