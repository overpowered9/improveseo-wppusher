<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Best-practices message at the top of the keyword list create and edit screens.
 *
 * "Learn more" opens the public Tutorials page (no account login needed, same page as the
 * Dashboard's Quick Start Guide link) in a new tab. There is no keyword-list tutorial of its own
 * yet; when there is, change $iseo_kwl_tutorial_url — this is the only place, for both screens.
 */
$iseo_kwl_tutorial_url = 'https://account.improveseoplugin.com/tutorials';

?>
<div class="iseo-kwl-notice" role="note">
	<svg class="iseo-kwl-notice-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
	<p class="iseo-kwl-notice-text">
		For best results, keep the keywords in a list related to each other. The wizard applies one set of business details to every post it creates, so unrelated keywords produce weaker results. For keywords that don't fit together, build a separate list or use the Single Post Wizard.
		<a href="<?php echo esc_url( $iseo_kwl_tutorial_url ); ?>" target="_blank" rel="noopener noreferrer">Learn more about keyword list best practices</a>
	</p>
</div>
