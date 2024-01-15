<div class="PixabayWrap">
	<div class="Pixabay__body">
		<p>
			<?php esc_html_e('Spinning a large article and nothing happening? Be patient, sometimes WordAi takes a long time to spin and send back the result. If the issue persists, consider breaking your large article into smaller pieces and handling them individually.', 'improve-seo'); ?>
		</p>

		<div class="form-group">
			<label for="spun-text" class="control-label"><?php esc_html_e('Text to Spin', 'improve-seo'); ?></label>
			<textarea id="spun-text" class="form-control" style="height: 150px;"></textarea>
		</div>

		<div class="form-group">
			<label for="word-ai-quality" class="control-label"><?php esc_html_e('Quality', 'improve-seo'); ?></label>
			<input type="number" min="1" max="100" id="word-ai-quality" value="50" class="form-control">
			<span class="help-block">
				<?php esc_html_e('Higher quality = less uniqueness.', 'improve-seo'); ?>
			</span>
		</div>

		<div class="form-group">
			<div class="form-column-4 text-center">
				<label class="control-label"><?php esc_html_e('Nesting', 'improve-seo'); ?></label>
				<label>
					<input type="radio" name="nesting" value="yes">
					- <?php esc_html_e('Yes', 'improve-seo'); ?>
				</label>
				<label>
					<input type="radio" name="nesting" value="no" checked>
					- <?php esc_html_e('No', 'improve-seo'); ?>
				</label>
				<span class="help-block">
					<?php esc_html_e('Turning it off [setting it to Yes] will help readability but hurt uniqueness.', 'improve-seo'); ?>
				</span>
			</div>

			<div class="form-column-4 text-center">
				<label class="control-label"><?php esc_html_e('Paragraph spinning', 'improve-seo'); ?></label>
				<label>
					<input type="radio" name="paragraph_spinning" value="yes" checked>
					- <?php esc_html_e('Yes', 'improve-seo'); ?>
				</label>
				<label>
					<input type="radio" name="paragraph_spinning" value="no">
					- <?php esc_html_e('No', 'improve-seo'); ?>
				</label>
				<span class="help-block">
					<?php esc_html_e('Keeps articles unique and great for large builds.', 'improve-seo'); ?>
				</span>
			</div>

			<div class="form-column-4 text-center">
				<label class="control-label"><?php esc_html_e('No original words', 'improve-seo'); ?></label>
				<label>
					<input type="radio" name="no_original_words" value="yes">
					- <?php esc_html_e('Yes', 'improve-seo'); ?>
				</label>
				<label>
					<input type="radio" name="no_original_words" value="no" checked>
					- <?php esc_html_e('No', 'improve-seo'); ?>
				</label>
				<span class="help-block">
					<?php esc_html_e('Keeps no unique words from the original spin which results in a super unique spin. Only recommend for very large builds.', 'improve-seo'); ?>
				</span>
			</div>
		</div>

		<div class="form-group">
			<label for="word-ai-protected-words" class="control-label"><?php esc_html_e('Protected words', 'improve-seo'); ?></label>
			<input type="text" id="word-ai-protected-words" class="form-control">
			<span class="help-block">
				<?php esc_html_e('Comma-separated words to not spin; no spaces between words.', 'improve-seo'); ?>
			</span>
		</div>

		<!-- Spinned text -->
		<div id="word-ai-result-wrap" class="form-group" style="display: none;">
			<label for="word-ai-protected-words" class="control-label"><?php esc_html_e('Spintax', 'improve-seo'); ?></label>
			<textarea id="word-ai-result" class="form-control" style="height: 150px"></textarea>
		</div>
	</div>

	<div class="Pixabay__footer">
		<div class="fl_r Pixabay__buttons">
			<button id="word-ai-spin-btn" class="button-primary" onclick="WordAI.spin(event)">
				<?php esc_html_e('Spin', 'improve-seo'); ?>
			</button>
			<button id="word-ai-post-btn" class="button" onclick="WordAI.post(event)" disabled>
				<?php esc_html_e('Insert to post/page', 'improve-seo'); ?>
			</button>
		</div>
	</div>
</div>