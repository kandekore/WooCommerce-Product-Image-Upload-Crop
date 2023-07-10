<?php
/*
* Plugin Name: WooCommerce Image Upload and Crop
* Plugin URI: https://yourwebsite.com/
* Description: This plugin allows users to upload and crop images when ordering a product.
* Version: 1.0
* Author: Your Name
* Author URI: https://yourwebsite.com/
* License: GPL2
*/

require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');


function add_custom_option() {
    global $product;
    $allow_upload = get_post_meta($product->get_id(), '_allow_upload', true);
    if ($allow_upload == 'yes') {
        echo '<div class="image-upload">
                <input type="file" id="custom_image" name="custom_image" accept="image/*" />
                <img id="preview_image" style="display:none" />
                <button type="button" id="crop_image" style="display:none">Crop Image</button>
              </div>';
    }
}
add_action('woocommerce_before_add_to_cart_button', 'add_custom_option', 10);

function handle_image_upload($passed, $product_id, $quantity, $variation_id = null) {
    // check if file was uploaded and no errors occurred
    if (!empty($_FILES['custom_image']['name']) && $_FILES['custom_image']['error'] == 0) {
        $attachment_id = media_handle_upload('custom_image', 0);
        if (is_wp_error($attachment_id)) {
            wc_add_notice(__('Image upload error.', 'your-plugin-name'), 'error');
            return false;
        }
        // save image attachment ID in session
        WC()->session->set('custom_image', $attachment_id);
    }
    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'handle_image_upload', 10, 4);

function enqueue_scripts() {
    wp_enqueue_style('cropper-css', 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css');
    wp_enqueue_script('cropper', 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js', array('jquery'), '1.5.12', true);
    wp_enqueue_script('script', plugins_url('script.js', __FILE__), array('jquery', 'cropper'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_scripts');


function save_image_to_order($item, $cart_item_key, $values, $order) {
    if ($image_id = WC()->session->get('custom_image')) {
        $item->add_meta_data('_custom_image', $image_id);
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'save_image_to_order', 10, 4);


function add_upload_checkbox_meta_box() {
    add_meta_box(
        'upload_checkbox_meta_box', // id
        'Allow Image Upload', // title
        'upload_checkbox_meta_box_callback', // callback
        'product', // post_type
        'side' // context
    );
}
add_action('add_meta_boxes', 'add_upload_checkbox_meta_box');

function upload_checkbox_meta_box_callback($post) {
    // Use nonce for verification
    wp_nonce_field(plugin_basename(__FILE__), 'upload_nonce');
    $allow_upload = get_post_meta($post->ID, '_allow_upload', true);
    echo '<input type="checkbox" id="allow_upload" name="allow_upload" value="yes"' . checked('yes', $allow_upload, false) . '> Allow Image Upload';
}

function save_upload_checkbox_meta_box($post_id) {
    // Verify nonce
    if (!isset($_POST['upload_nonce']) || !wp_verify_nonce($_POST['upload_nonce'], plugin_basename(__FILE__))) {
        return $post_id;
    }
    // Check permissions
    if ('product' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    }
    // Save or delete the meta data
    if (isset($_POST['allow_upload']) && $_POST['allow_upload'] == 'yes') {
        update_post_meta($post_id, '_allow_upload', 'yes');
    } else {
        delete_post_meta($post_id, '_allow_upload');
    }
}
add_action('save_post', 'save_upload_checkbox_meta_box');
