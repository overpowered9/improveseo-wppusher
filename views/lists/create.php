<?php

use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a>

&raquo;

<a href="<?= admin_url('admin.php?page=improveseo_lists') ?>">Improve SEO Lists</a>

&raquo;

<span>Create New List</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<h1 class="hidden">Create New List</h1>

<div class="global-wrap">
        <div class="head-bar">
            <img src="<?php echo WT_URL . '/assets/images/latest-images/seo-latest-logo.svg'?>" alt="project-list-logo"> 
            <h1> ImproveSEO | 2.0.11 </h1>
            <span>Pro</span>
        </div>
        <div class="box-top">
            <ul class="breadcrumb-seo">
                <li><a href="<?= admin_url('admin.php?page=improveseo_dashboard') ?>">Improve SEO</a></li>
                <li><a href="<?= admin_url('admin.php?page=improveseo_lists') ?>"> Keyword Lists </a></li>
                <li>Create New List</li>
            </ul>
        </div>
        <div class="improve-seo-form-box">
            <form class="improve-seo-form-global" action="<?= admin_url('admin.php?page=improveseo_lists&action=do_create&noheader=true') ?>" method="post" >
                <div class="seo-form-field">
                    <label> Shortcode Name </label>
                    <input type="text" name="name"  placeholder="Ex. List 1"> 
                </div> 
                <div class="seo-form-field">
                    <label> List of Keywords (one per line) </label>
                    <textarea  name="list" placeholder="Type Here..."></textarea> 
                </div>      
                <div class="seo-form-field">
                    <input type="submit" style="max-width:max-content;" class="styling_post_page_action_buttons2 styling_post_page_action_buttons" value="Create New List">
                </div>          
            </form>     
        </div>  
    </div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>