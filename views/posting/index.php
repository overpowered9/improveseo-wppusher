<?php
use ImproveSEO\View;
if($_POST['cat_name']){
    $cat_slug = $_POST['cat_name'];
    $cat_slug = preg_replace('/\s*/', '-', $cat_slug);
    $cat_slug = strtolower($cat_slug);
    wp_insert_term(
        // the name of the category
        $_POST['cat_name'], 
        // the taxonomy, which in this case if category (don't change)
        'category', 
        array(
            // what to use in the url for term archive
            'slug' => $_POST['cat_name'],  
        )
    );
}
?>

<?php View::startSection('breadcrumbs') ?>
	<!-- <span>Improve SEO</span> -->
<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content'); ?>
<h1 class="hidden"> Posting </h1>
<div class="Posting improveseo_wrapper text-center">
	<section class="wellcom-section border-bottom">
	    	<img class="d-block mx-auto mb-4" src="<?php echo WT_URL.'/assets/images/improve-logo.png'?>" alt="ImproveSeo">
	    	<h1 class="Posting__header">Welcome to Improve SEO!</h1>
	    </section>
	      	<section class="create_sec col-lg-12 border-bottom mx-auto p-4">
	   		<h3 class="Posting__subheader h1">What would you like to create?</h3>
      		<div class="Posting__buttons clearfix d-flex justify-content-center flex-column flex-sm-row align-items-center mt-4">
      			<a href="<?php echo admin_url("admin.php?page=improveseo_dashboard&action=create_post"); ?>" class="btn-trans btn btn-outline-primary Posting__post-button mr-0 mr-sm-3 mb-3 mb-sm-0">Create Post</a>
      			<a href="<?php echo admin_url("admin.php?page=improveseo_dashboard&action=create_page"); ?>" class="btn-trans btn btn-outline-primary mb-3 mb-sm-0 Posting__page-button">Create Page</a>
      		</div>
	  	</section>
	  	<section class="create_cat_sec col-lg-12 mx-auto">
	<h3 class="Posting__subheader h1">Chose Or Create Category</h3>
	<div class="card mx-auto p-3 p-sm-4">
		<div class="category_improveseo clearfix card-body text-center p-0">
			<div class="cta-check clearfix d-flex justify-content-center flex-column flex-sm-row  align-items-start align-items-sm-center"> 
			<?php
			$args = array("hide_empty" => 0,
			"type"      => "post",
			"orderby"   => "name",
			"order"     => "ASC" );
			$cats = get_categories($args);
			foreach($cats as $category){
				
			$select.= "<span><input type='checkbox' value='".$category->term_id."' id='".$category->term_id."' name='cats[]'><label for='".$category->term_id."'>".$category->name."</label></span>";
				
			}
			echo $select;
			?>
			</div>
			<div class="add_cat">
				<form  method="post" class="form-wrap m-0">
					<div class="input-group mb-4">
						<input type="text" class="form-control" name="cat_name" placeholder="Default input" value="" aria-label="default input example" required>
					</div>
					<div class="input-group">
						<input type="submit" class="btn-trans btn btn-outline-primary btn-lg px-5 mx-auto" value="Add Category">
					</div>
				</form>
			</div>
		</div>
	</div>
	
</section>
</div>

<?php View::endSection('content') ?>

<?php echo View::make('layouts.main') ?>