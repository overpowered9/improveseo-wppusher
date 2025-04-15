<?php

use ImproveSEO\View;




?>

<?php View::startSection('breadcrumbs') ?>
<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>
&raquo;
<span>View AI Content</span>

<?php View::endSection('breadcrumbs') ?>


<?php View::startSection('content') ?>

<?php View::render('import/import') ?>


<h1 class="hidden">View AI Content</h1>
<div class="show_loading alert-modal">
	<h1 class="hidden">View AI Content</h1>
	<h2 id="mid_notice"><a href="<?php echo $url; ?>">Refresh List</a></h2>
</div>
<div class="projectes improveseo_wrapper intro_page  p-3 p-lg-4">

	
   
	<section class="project-table-wrapper">
		
        <?php foreach ($projects as $key => $value) : ?>
        <div class="row">
            <div class="leftcolumn">
                <h2><?php echo $value->ai_title; ?></h2>
                    <div style="margin: 50px 0px 50px 0;"><img src="<?php echo base64_decode($value->ai_image); ?>" style="width:100%"></div>
                <p><?php echo nl2br(base64_decode($value->ai_content)); ?></p>
                <p>
                <?php if(!empty($value->testimonial)) { 
                    $testimonial_ids = '';
                    $all_testimonial = explode("||",$value->testimonial); 
                    foreach($all_testimonial as $key1 => $value1) {
                        if(!empty($value1)) {
                            $testimonial_ids = $value1.','.$testimonial_ids;
                        }
                    }
                    echo do_shortcode('[improveseo_testimonial id="'.$testimonial_ids.'"]');
                } ?>
                </p>

               

                <p>
                <?php if(!empty($value->Button_SC)) { 
                    echo do_shortcode('[improveseo_buttons id="'.$value->Button_SC.'"]');
                } ?>
                </p>

                <p> 
                <?php if(!empty($value->GoogleMap_SC)) { 
                    echo do_shortcode('[improveseo_googlemaps id="'.$value->GoogleMap_SC.'"]');
                } ?>
                </p>


                <p> 
                <?php if(!empty($value->Video_SC)) { 
                    echo do_shortcode('[improveseo_video id="'.$value->Video_SC.'"]');
                } ?>
                </p>

            </div>
        </div>
        <?php endforeach; ?>
					
			
	</section>

	
</div>

<?php 

function process_content($content) {
    // Decode HTML entities
    //$content = html_entity_decode($content);

    // Convert newlines to <br> tags
    //$content = nl2br($content); 

    // Allow only safe HTML
    $content = base64_decode($content);
    //$content = wp_kses_post($content);

    //$content = htmlspecialchars($content);

    return $content;
}

?>
<style>


/* Header/Blog Title */

/* Create two unequal columns that floats next to each other */
/* Left column */
.leftcolumn {
  float: left;
  width: 100%;
}



/* Fake image */
.fakeimg {
  background-color: #aaa;
  width: 100%;
  padding: 20px;
}

/* Add a card effect for articles */
.card {
  background-color: white;
  padding: 20px;
  margin-top: 20px;
}

/* Clear floats after the columns */
.row:after {
  content: "";
  display: table;
  clear: both;
}

/* Footer */
.footer {
  padding: 20px;
  text-align: center;
  background: #ddd;
  margin-top: 20px;
}

</style>
<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>