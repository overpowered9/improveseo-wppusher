<?php
use ImproveSEO\Validator;
?>

<div class="BasicForm__row<?php echo Validator::hasError('shortcode') ? ' PostForm--error' : ''; ?>">
    <label class="form-label"><?php esc_html_e('Shortcode', 'improve-seo'); ?></label>
    <input type="text" class="form-control" rows="5" name="shortcode" placeholder="<?php esc_attr_e('Type here...', 'improve-seo'); ?>" value="<?php echo isset($shortcode) ? esc_attr($shortcode->shortcode) : ''; ?>">
    <?php if (Validator::hasError('shortcode')): ?>
        <span class="PostForm__error"><?php echo esc_html(Validator::get('shortcode')); ?></span>
    <?php endif; ?>
</div>

<?php if (!isset($shortcode)): ?>
    <div class="BasicForm__row<?php echo Validator::hasError('type') ? ' PostForm--error' : ''; ?>">
        <label class="form-label"><?php esc_html_e('Select Shortcode Type', 'improve-seo'); ?></label>
        <div class="input-prefix">
            <select name="type" class="select-control" id="select-rap">
                <option value="static"><?php esc_html_e('Static', 'improve-seo'); ?></option>
                <option value="dynamic"><?php esc_html_e('Dynamic', 'improve-seo'); ?></option>
            </select>
            <span><?php esc_html_e('Ex.', 'improve-seo'); ?></span>
        </div>
        <?php if (Validator::hasError('type')): ?>
            <span class="PostForm__error"><?php echo esc_html(Validator::get('type')); ?></span>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="BasicForm__row<?php echo Validator::hasError('content') ? ' PostForm--error' : ''; ?>">
    <label class="form-label"><?php esc_html_e('Content', 'improve-seo'); ?></label>
    <textarea class="textarea-control" name="content" rows="5" placeholder="<?php esc_attr_e('Type here...', 'improve-seo'); ?>" <?php echo (isset($shortcode) && $shortcode->type == 'static') ? 'disabled' : ''; ?>><?php echo isset($shortcode) ? esc_textarea($shortcode->content) : ''; ?></textarea>
    <?php if (Validator::hasError('content')): ?>
        <span class="PostForm__error"><?php echo esc_html(Validator::get('content')); ?></span>
    <?php endif; ?>
</div>
