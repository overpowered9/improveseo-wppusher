
<?php if ( ! defined( 'ABSPATH' ) ) exit;?>
<div class="project-import-box">
    <form action="<?php echo esc_url(admin_url('admin.php?page=improveseo_projects')); ?>" method="post" enctype="multipart/form-data">
    <?php
        // Generate a nonce field for this form
        echo wp_nonce_field('improveseo_project_uploadcsv', 'my_secure_form_nonce_field');
        ?>

        <div class="form-group">
            <label for="upload_csv"><?php esc_html_e("Select CSV File:","improve-seo")?></label>
            <input type="file" class="form-control" name="upload_csv" id="upload_csv" required>
        </div>
        <input type="submit" name="submit" class="btn btn-outline-primary btn-small" value="<?php esc_html_e("Upload","improve-seo") ?>">
    </form>
</div>
