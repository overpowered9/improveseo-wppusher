<?php

use ImproveSEO\View;
use ImproveSEO\Validator;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<a href="<?= admin_url('admin.php?page=improveseo_authors') ?>">Authors List</a>

&raquo;

<span>Create Authors</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<h1 class="hidden">Create New Authors</h1>

<div class="global-wrap">
        <div class="head-bar">
            <img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg' ?>" alt="project-list-logo"> 
            <h1> ImproveSEO | 2.0.11 </h1>
            <span>Pro</span>
        </div>
        <div class="box-top">
            <ul class="breadcrumb-seo">
                <li><a href="<?php echo admin_url('admin.php?page=improveseo_dashboard'); ?>">Improve SEO</a></li>
                <li><a href="<?php echo admin_url('admin.php?page=improveseo_authors'); ?>"> Authors List </a></li>
                <li>Create Authors</li>
            </ul>
        </div>
        <div class="improve-seo-form-box new-improve-create-list">
            <form class="improve-seo-form-global" action="<?php echo admin_url('admin.php?page=improveseo_authors&action=do_create&noheader=true'); ?>" method="post" >
                <div class="seo-form-field">
                    <label> Number of authors to create </label>
                    <input type="text" placeholder="Ex. 06" name="users" value="<?= Validator::old('users') ?>" required  > 
                </div>  
                <div class="seo-form-field">
                    <h5> It will take some time to create all authors. </h5>  
                </div>    
                <div class="seo-form-field">
                    <input type="submit" class="styling_post_page_action_buttons2 styling_post_page_action_buttons"   value="<?php _e('Create Now') ?>" >
                </div>          
            </form>     
        </div>  
    </div>

<?php View::endSection('content') ?>

<?php echo View::make('layouts.main') ?>