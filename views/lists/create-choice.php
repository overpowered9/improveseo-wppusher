<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * "Create Keyword List" — the two ways to make a list, as cards.
 *
 * Rendered in exactly one place per page load by views/lists/index.php: inline when the site has
 * no keyword list yet (it is the whole screen then), or inside the "+ New List" dialog once it
 * has some. Each card is a single link, so the whole card is the click and keyboard target.
 */

?>
<section class="iseo-kwl-create" aria-labelledby="iseo-kwl-create-title">
	<h2 id="iseo-kwl-create-title" class="iseo-kwl-create-title">Create Keyword List</h2>
	<p class="iseo-kwl-create-lead">You need a keyword list to use Bulk Post Generation. Choose how you want to create it.</p>
	<div class="iseo-kwl-cards">
		<a class="iseo-kwl-card" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_lists&action=create' ) ); ?>">
			<span class="iseo-kwl-card-icon" aria-hidden="true">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><rect x="8" y="2" width="8" height="4" rx="1"></rect><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><line x1="8" y1="11" x2="16" y2="11"></line><line x1="8" y1="15" x2="16" y2="15"></line><line x1="8" y1="19" x2="13" y2="19"></line></svg>
			</span>
			<span class="iseo-kwl-card-body">
				<span class="iseo-kwl-card-title">Paste My Keywords</span>
				<span class="iseo-kwl-card-text">Use a list you've already prepared.</span>
			</span>
			<svg class="iseo-kwl-card-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="9 18 15 12 9 6"></polyline></svg>
		</a>
		<a class="iseo-kwl-card" href="<?php echo esc_url( admin_url( 'admin.php?page=improveseo_keyword_generator' ) ); ?>">
			<span class="iseo-kwl-card-icon" aria-hidden="true">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M10 3l1.9 5.1L17 10l-5.1 1.9L10 17l-1.9-5.1L3 10l5.1-1.9z"></path><path d="M18 14l.9 2.1L21 17l-2.1.9L18 20l-.9-2.1L15 17l2.1-.9z"></path><path d="M18 3v3M16.5 4.5h3"></path></svg>
			</span>
			<span class="iseo-kwl-card-body">
				<span class="iseo-kwl-card-title">Generate Keywords for Me</span>
				<span class="iseo-kwl-card-text">Build a list of related keywords automatically.</span>
			</span>
			<svg class="iseo-kwl-card-chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="9 18 15 12 9 6"></polyline></svg>
		</a>
	</div>
</section>
