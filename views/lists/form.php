<?php use ImproveSEO\Validator; ?>

<div class="BasicForm__row<?php echo Validator::hasError('name') ? ' PostForm--error' : ''; ?>">
    <label class="form-label"><?php esc_html_e('Shortcode Name', 'improve-seo'); ?></label>
    <div class="input-prefix">
        <input type="text" class="form-control" name="name" placeholder="<?php esc_attr_e('List 1', 'improve-seo'); ?>" value="<?php echo esc_attr(Validator::old('name', $list->name)); ?>">
        <span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
    </div>
    <?php if (Validator::hasError('name')): ?>
        <span class="PostForm__error"><?php echo esc_html(Validator::get('name')); ?></span>
    <?php endif; ?>
</div>

<div class="BasicForm__row<?php echo Validator::hasError('list') ? ' PostForm--error' : ''; ?>">
    <label class="form-label"><?php esc_html_e('List of keywords (one per line)', 'improve-seo'); ?></label>
    <textarea class="textarea-control" name="list" rows="5" placeholder="<?php esc_attr_e('Type here...', 'improve-seo'); ?>"><?php echo esc_textarea(Validator::old('list', $list->list)); ?></textarea>
    <?php if (Validator::hasError('list')): ?>
        <span class="PostForm__error"><?php echo esc_html(Validator::get('list')); ?></span>
    <?php endif; ?>
</div>
