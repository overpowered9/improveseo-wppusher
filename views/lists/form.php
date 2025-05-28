<?php

use ImproveSEO\Validator;

?>



<div class="BasicForm__row<?php if (Validator::hasError('name')) echo ' PostForm--error' ?>">

	<label class="form-label">Shortcode Name</label>

	<div class="input-prefix">

		<input type="text" class="form-control" name="name" placeholder="List 1" value="<?= Validator::old('name', $list->name) ?>">

		<span>Ex.</span>

	</div>

	<?php if (Validator::hasError('name')): ?>

	<span class="PostForm__error"><?= Validator::get('name') ?></span>

	<?php endif; ?>

</div>



<div class="BasicForm__row<?php if (Validator::hasError('list')) echo ' PostForm--error' ?>">

	<label class="form-label">List of keywords (one per line)</label>

	<textarea class="textarea-control" name="list" rows="5" placeholder="Type here..."><?= Validator::old('list', $list->list) ?></textarea>

	<?php if (Validator::hasError('list')): ?>

	<span class="PostForm__error"><?= Validator::get('list') ?></span>

	<?php endif; ?>

</div>