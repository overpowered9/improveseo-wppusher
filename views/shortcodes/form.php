<?php
use ImproveSEO\Validator;
?>

<div class="BasicForm__row<?php if (Validator::hasError('shortcode')) echo ' PostForm--error' ?>">
	<label class="form-label">Shortcode</label>
	<input type="text" class="form-control" rows="5" name="shortcode" placeholder="Type here..." value="<?= isset($shortcode) ? $shortcode->shortcode : '' ?>">
	<?php if (Validator::hasError('shortcode')): ?>
	<span class="PostForm__error"><?= Validator::get('shortcode') ?></span>
	<?php endif; ?>
</div>


<?php if (!isset($shortcode)): ?>
<div class="BasicForm__row <?php if (Validator::hasError('type')) echo ' PostForm--error' ?>">
	<label class="form-label">Select Shortcode Type</label>
	<div class="input-prefix">
		<select name="type" class="select-control" id="select-rap">
			<option value="static">Static</option>
			<option value="dynamic">Dynamic</option>
		</select>
		<span>Ex.</span>
	</div>
	<?php if (Validator::hasError('type')): ?>
	<span class="PostForm__error"><?= Validator::get('type') ?></span>
	<?php endif; ?>
</div>
<?php endif; ?>

<div class="BasicForm__row<?php if (Validator::hasError('content')) echo ' PostForm--error' ?>">
	<label class="form-label">Content</label>
	<textarea class="textarea-control" name="content" rows="5" placeholder="Type here..." <?php if (isset($shortcode) && $shortcode->type == 'static') echo ' disabled' ?>><?= isset($shortcode) ? $shortcode->content : '' ?></textarea>
	<?php if (Validator::hasError('content')): ?>
	<span class="PostForm__error"><?= Validator::get('content') ?></span>
	<?php endif; ?>
</div>