<?php if (!empty($message)) : ?>
    <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible notice-improveseo">
        <p><?php echo esc_html($message); ?></p>
    </div>
<?php endif; ?>