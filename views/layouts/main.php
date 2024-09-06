<?php if (! defined('ABSPATH')) exit; ?>
<div class="wrap improveseo-page">

    <div class="Breadcrumbs">
        <?php echo wp_kses_post(ImproveSEO\View::section('breadcrumbs')); ?>
    </div>

    <?php
    ImproveSEO\FlashMessage::handle();
    ?>

    <div>

        <?php
        $input_attributes = array(
            'type'        => true,
            'name'        => true,
            'value'       => true,
            'class'       => true,
            'id'          => true,
            'placeholder' => true,
            'checked'     => true,
            'disabled'    => true,
            'readonly'    => true,
            'size'        => true,
            'maxlength'   => true,
            'min'         => true,
            'max'         => true,
            'step'        => true,
            'autofocus'   => true,
            'required'    => true,
            'autocomplete'=> true,
        );
        // Allowed attributes for the div element
        $div_attributes = array(
            'id'    => true,
            'class' => true,
            'style' => true,
        );

        // Allowed attributes for the link element
        $link_attributes = array(
            'rel'   => true,
            'href'  => true,
            'media' => true,
        );

        // Allowed attributes for the button element
        $button_attributes = array(
            'type'         => true,
            'id'           => true,
            'class'        => true,
            'aria-label'   => true,
            'value'        => true,
            'role'         => true,
            'aria-haspopup' => true,
            'aria-pressed' => true,
        );

        // Allowed attributes for the iframe element
        $iframe_attributes = array(
            'frameborder'  => true,
            'allowtransparency' => true,
            'title'        => true,
            'style'        => true,
        );

        // Allowed attributes for the textarea element
        $textarea_attributes = array(
            'class'        => true,
            'style'        => true,
            'autocomplete' => true,
            'cols'         => true,
            'name'         => true,
            'id'           => true,
            'aria-hidden'  => true,
        );

        $select_attributes = array(
            'name'        => true,
            'class'       => true,
            'id'          => true,
            'disabled'    => true,
            'autofocus'   => true,
            'required'    => true,
            'multiple'    => true,
            'size'        => true,
        );

        $option_attributes = array(
            'value'       => true,
            'selected'    => true,
            'disabled'    => true,
        );

        $form_attributes = array(
            'action'      => true,
            'method'      => true,
            'class'       => true,
            'id'          => true,
            'enctype'     => true,
            'target'      => true,
            'autocomplete'=> true,
            'novalidate'  => true,
            'accept-charset' => true,
        );
        // Merging the allowed HTML tags with existing elements and new ones
        $allowed_html = array_merge(
            array(
                'input'   => $input_attributes,
                'select'  => $select_attributes,
                'option'  => $option_attributes,
                'form'    => $form_attributes,
                'div'     => $div_attributes,
                'link'    => $link_attributes,
                'button'  => $button_attributes,
                'iframe'  => $iframe_attributes,
                'textarea' => $textarea_attributes,
            ),
            wp_kses_allowed_html('post')
        );

        // Echoing the content with the allowed HTML elements including input, select, and option
        echo wp_kses(ImproveSEO\View::section('content'), $allowed_html);
        // echo (ImproveSEO\View::section('content'));
        ?>
    </div>
</div>