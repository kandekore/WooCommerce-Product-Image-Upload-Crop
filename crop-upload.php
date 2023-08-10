<?php
/*
* Plugin Name: WooCommerce Image Upload and Crop

* Description: This plugin allows users to upload and crop images when ordering a product.
* Version: 1.0
* Author: D Kandekore
* License: GPL2
*/

require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

function add_custom_option() {
    global $product;
    $allow_upload = get_post_meta($product->get_id(), '_allow_upload', true);
    $crop_type = get_post_meta($product->get_id(), '_crop_type', true);
    if ($allow_upload == 'yes') {
        echo '<div class="image-upload" data-crop-type="' . esc_attr($crop_type) . '">
                <input type="file" id="custom_image" name="custom_image" accept="image/*" />
                <div id="crop-container" style="display:none"></div>
                <button type="button" id="crop_image" style="display:none">Crop Image</button>
              </div>';
    }
}
add_action('woocommerce_before_add_to_cart_button', 'add_custom_option', 10);

function handle_image_upload($passed, $product_id, $quantity, $variation_id = null) {
    // Skip file handling. Image is being uploaded via AJAX.
    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'handle_image_upload', 10, 4);

function enqueue_scripts() {
    wp_enqueue_style('croppie-css', 'https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css');
    wp_enqueue_script('croppie', 'https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js', array('jquery'), '2.6.5', true);
    wp_enqueue_script('script', plugins_url('script.js', __FILE__), array('jquery', 'croppie'), '1.0', true);
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
        'Image Upload Options', // title
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
    $crop_type = get_post_meta($post->ID, '_crop_type', true);
    echo '<p><input type="checkbox" id="allow_upload" name="allow_upload" value="yes"' . checked('yes', $allow_upload, false) . '> Allow Image Upload</p>';
    echo '<p><label for="crop_type">Crop Type: </label><select id="crop_type" name="crop_type">
            <option value="circle" ' . selected($crop_type, 'circle', false) . '>Circle</option>
            <option value="square" ' . selected($crop_type, 'square', false) . '>Square</option>
          </select></p>';
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

    // Save crop type
    if (isset($_POST['crop_type'])) {
        update_post_meta($post_id, '_crop_type', $_POST['crop_type']);
    }
}
add_action('save_post', 'save_upload_checkbox_meta_box');

add_action('wp_ajax_upload_image', 'handle_ajax_image_upload');
add_action('wp_ajax_nopriv_upload_image', 'handle_ajax_image_upload');

function handle_ajax_image_upload() {
    // Check nonce and other security issues before processing.

    if (isset($_POST['image'])) {
        $data = $_POST['image'];
        $uri = substr($data, strpos($data, ",") + 1);

        $file = uniqid() . '.jpg';
        $upload_dir = wp_upload_dir();

        // Get the full server path of the uploaded image
        $path = $upload_dir['basedir'] . '/' . $file;

        // Convert this path to its corresponding URL
        $url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $path);

        // Save the decoded image to the server
        file_put_contents($path, base64_decode($uri));

        // Set image in session to be added to cart item
        WC()->session->set('custom_image', $url);

        // Send some kind of response.
        wp_send_json_success($url);
    } else {
        wp_send_json_error('No image data received.');
    }

    wp_die();
}
function save_custom_image_in_cart_item($cart_item_data, $product_id) {
    if (WC()->session->__isset('custom_image')) {
        $cart_item_data['custom_image'] = WC()->session->get('custom_image');
        WC()->session->__unset('custom_image');  // Unset the custom image from the session
    }
    return $cart_item_data;
}

add_filter('woocommerce_add_cart_item_data', 'save_custom_image_in_cart_item', 10, 2);

function add_custom_image_order_item_meta($item_id, $values, $cart_item_key) {
    if (isset($values['custom_image'])) {
        wc_add_order_item_meta($item_id, '_custom_image_url', $values['custom_image']);
    }
}
add_action('woocommerce_add_order_item_meta', 'add_custom_image_order_item_meta', 10, 3);

/*function add_image_to_email($item_id, $item, $order, $plain_text) {
    // Get the image URL from the item meta
    $image_url = $item->get_meta('_custom_image_url');

    if ($image_url) {
        // If it's not plain text email
        if (!$plain_text) {
            echo '<p><strong>Custom Image:</strong></p>';
            echo '<img src="' . esc_url($image_url) . '" alt="Custom Image" />';
            // Display link to the image below
            echo '<p><a href="' . esc_url($image_url) . '">View Custom Image</a></p>';
        } else {
            // If it is a plain text email
            echo "\nCustom Image: " . $image_url;
            echo "\nView Custom Image: " . $image_url;
        }
    }
}*/
function add_image_to_email($item_id, $item, $order, $plain_text) {
    // Get the image URL from the item meta
    $image_url = $item->get_meta('_custom_image_url');
    
    // Get the product ID and check if it allows uploads
    $product_id = $item->get_product_id();
    $allows_upload = get_post_meta($product_id, '_allow_upload', true);

    if ($image_url) {
        // If it's not plain text email
        if (!$plain_text) {
            echo '<p><strong>Custom Image:</strong></p>';
            echo '<img src="' . esc_url($image_url) . '" alt="Custom Image" />';
            // Display link to the image below
            echo '<p><a href="' . esc_url($image_url) . '">View Custom Image</a></p>';
        } else {
            // If it is a plain text email
            echo "\nCustom Image: " . $image_url;
            echo "\nView Custom Image: " . $image_url;
        }
    } elseif ($allows_upload === 'yes') {
        // If it's not plain text email
        if (!$plain_text) {
            echo '<p><strong>Custom Image:</strong> No image uploaded.</p>';
        } else {
            // If it is a plain text email
            echo "\nCustom Image: No image uploaded.";
        }
    }
}

add_action('woocommerce_order_item_meta_end', 'add_image_to_email', 10, 4);
/*
function display_custom_image_in_cart($product_name, $cart_item, $cart_item_key) {
    // Check if custom image URL exists for the item
    if (isset($cart_item['custom_image'])) {
        $image_url = $cart_item['custom_image'];
        $product_name .= '<br><strong>Custom Image:</strong>';
        $product_name .= '<img src="' . esc_url($image_url) . '" alt="Custom Image" style="width: 100px;">'; 
        $product_name .= '<br><a href="' . esc_url($image_url) . '">View Image</a>';
    }
    return $product_name;
}*/
function display_custom_image_in_cart($product_name, $cart_item, $cart_item_key) {
    $product_id = $cart_item['product_id'];
    $allows_upload = get_post_meta($product_id, '_allow_upload', true);
    
    // Check if custom image URL exists for the item
    if (isset($cart_item['custom_image'])) {
        $image_url = $cart_item['custom_image'];
        $product_name .= '<br><strong>Custom Image:</strong>';
        $product_name .= '<img src="' . esc_url($image_url) . '" alt="Custom Image" style="width: 100px;">'; 
        $product_name .= '<br><a href="' . esc_url($image_url) . '">View Image</a>';
    } elseif ($allows_upload === 'yes') {
        $product_name .= '<br><span style="color: red;">No image uploaded</span>';
    }
    return $product_name;
}

add_filter('woocommerce_cart_item_name', 'display_custom_image_in_cart', 10, 3);
