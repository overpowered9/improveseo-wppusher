<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="improve_seo_notices_wrapper improveseo_wrapper">
	<div id="postbox-container-0" class="postbox-container">
		<div id="normal-sortables" class="meta-box-sortables ui-sortable">
			<div class="PostForm__boxes">
				<div class="postbox closed">
					<button type="button" class="handlediv button-link" aria-expanded="false">
						<span class="toggle-indicator" aria-hidden="false"></span>
					</button>
					<h3 class="hndle"><span><?php esc_html_e('Tips & Best Practices', 'improve-seo'); ?></span></h3>
					<div class="inside">
						<!-- Your notices go here with translation functions -->
						<div class="is-dismissible notice-improveseo">
							<p>
								<?php esc_html_e('ImproveSEO tags for local SEO feature:', 'improve-seo'); ?>
							</p>
							<p>
								<strong>@city</strong> - <?php esc_html_e('current city (i.e Rochester)', 'improve-seo'); ?> <br>
								<strong>@state</strong> - <?php esc_html_e('current state (i.e Michigan)', 'improve-seo'); ?> <br>
								<strong>@stateshort</strong> - <?php esc_html_e('current state abbreviation (i.e MI)', 'improve-seo'); ?> <br>
								<strong>@country</strong> - <?php esc_html_e('current country (i.e United States)', 'improve-seo'); ?> <br>
								<strong>@countryshort</strong> <?php esc_html_e('current country abbreviation (i.e US)', 'improve-seo'); ?>
							</p>
							<button type="button" class="notice-improveseo-dismiss"></button>
						</div>
						<div class="is-dismissible notice-improveseo">
							<p><?php esc_html_e('Spintax is supported everywhere! You can use {1|2|3} and either 1, 2, or 3 will be the outcome.', 'improve-seo'); ?></p>
							<button type="button" class="notice-improveseo-dismiss"></button>
						</div>

						<div class="is-dismissible notice-improveseo">
							<p>
								<?php esc_html_e('ImproveSEO is a powerful software! Make sure that your server can handle the large amount of traffic that your site will be getting and is powerful enough to handle the page creation. If you are on a smaller server, consider breaking a large project into multiple smaller ones.', 'improve-seo'); ?>
							</p>
							<button type="button" class="notice-improveseo-dismiss"></button>
						</div>

						<div class="is-dismissible notice-improveseo">
							<p>
								<?php esc_html_e('Don\'t use any unnecessary features! See something you don\'t understand? It\'s probably best not to mess with it. Almost all errors come from using features that you don\'t understand. Barebones Improve SEO is more than powerful so please don\'t feel the need to enable anything you don\'t know. If you don\'t need it, it\'s probably not for you.', 'improve-seo'); ?>
							</p>
							<button type="button" class="notice-improveseo-dismiss"></button>
						</div>

						<div class="is-dismissible notice-improveseo">
							<p>
								<?php
								printf(
									esc_html__('Looking to use Improve SEO for non-local, such as affiliate or Amazon? Check out the %sImproveSEO lists feature!%s', 'improve-seo'),
									'<a href="' . esc_url(admin_url('admin.php?page=improveseo_lists')) . '">',
									'</a>'
								);
								?>
							</p>
							<button type="button" class="notice-improveseo-dismiss"></button>
						</div>

						<div class="is-dismissible notice-improveseo">
							<p><?php esc_html_e('Struggling to find content? No worries!', 'improve-seo'); ?></p>
							<p>
								<?php esc_html_e('You can use the same article across all your posts (you can even use free content from sites like Ezine, just be sure to link back to the source). It is recommended that you sprinkle the keyword that you’re trying to rank for within the article to make it unique and more relevant!', 'improve-seo'); ?>
							</p>
							<button type="button" class="notice-improveseo-dismiss"></button>
						</div>

						<div class="is-dismissible notice-improveseo">
							<p>
								<?php esc_html_e('To use gateway / channel pages call @citylist for state area and @ziplist for city area. This will greatly increase the load on your server from the project.', 'improve-seo'); ?>
							</p>
							<button type="button" class="notice-improveseo-dismiss"></button>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>