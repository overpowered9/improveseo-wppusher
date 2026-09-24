<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;
use ImproveSEO\Validator;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>

&raquo;

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_lists') ); ?>">Improve SEO Lists</a>

&raquo;

<span>Create New List</span>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<h1 class="hidden">Create New List</h1>

<div class="global-wrap">
        <div class="head-bar">
            <img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSEO logo"> 
            <h1>ImproveSEO | <?php echo esc_html( IMPROVESEO_VERSION ); ?></h1>
        </div>
        <div class="box-top">
            <ul class="breadcrumb-seo">
                <li><a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a></li>
                <li><a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_lists') ); ?>"> Keyword Lists </a></li>
                <li>Create New List</li>
            </ul>
        </div>
        <div class="improve-seo-form-box">
            <?php View::render( 'lists.best-practices-notice' ); ?>
            <form class="improve-seo-form-global" action="<?php echo esc_url( admin_url('admin.php?page=improveseo_lists&action=do_create&noheader=true') ); ?>" method="post" >
                <?php // A refused save (empty field, or a name already taken) redirects back here. The
                      // edit screen already showed why and kept what was typed; this one used to come
                      // back blank and silent. ?>
                <div class="seo-form-field<?php if ( Validator::hasError( 'name' ) ) echo ' PostForm--error'; ?>">
                    <label for="iseo-kwl-name"> Keyword List Name </label>
                    <input type="text" id="iseo-kwl-name" name="name"  placeholder="Ex. List 1" value="<?php echo esc_attr( wp_unslash( (string) Validator::old( 'name', '' ) ) ); ?>">
                    <?php if ( Validator::hasError( 'name' ) ) : ?>
                        <span class="PostForm__error"><?php echo esc_html( Validator::get( 'name' ) ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="seo-form-field<?php if ( Validator::hasError( 'list' ) ) echo ' PostForm--error'; ?>">
                    <label for="iseo-kwl-keywords"> List of Keywords (one per line) </label>
                    <textarea id="iseo-kwl-keywords" name="list" placeholder="Type Here..."><?php echo esc_textarea( wp_unslash( (string) Validator::old( 'list', '' ) ) ); ?></textarea>
                    <?php if ( Validator::hasError( 'list' ) ) : ?>
                        <span class="PostForm__error"><?php echo esc_html( Validator::get( 'list' ) ); ?></span>
                    <?php endif; ?>
                </div>
                <div class="seo-form-field iseo-kwl-form-actions">
                    <input type="submit" style="max-width:max-content;" class="styling_post_page_action_buttons2 styling_post_page_action_buttons" value="Create New List">
                    <a class="iseo-kwl-cancel" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_lists' ) ); ?>">Cancel</a>
                </div>
            </form>
        </div>
    </div>

<?php View::endSection('content') ?>

<?php View::make('layouts.main') ?>