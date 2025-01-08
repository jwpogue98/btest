<?php
/**
 * Plugin Name: Bicycle Inventory Manager
 * Description: A plugin to manage bicycle inventory
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

// Create custom post type for bicycles
function bi_register_bicycle_post_type() {
    register_post_type('bicycle', [
        'labels' => [
            'name' => 'Bicycles',
            'singular_name' => 'Bicycle',
            'add_new' => 'Add New Bicycle',
            'add_new_item' => 'Add New Bicycle',
            'edit_item' => 'Edit Bicycle',
            'view_item' => 'View Bicycle'
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-bike',
        'supports' => ['title', 'editor', 'custom-fields']
    ]);
}
add_action('init', 'bi_register_bicycle_post_type');

// Add custom meta boxes for bicycle details
function bi_add_bicycle_meta_boxes() {
    add_meta_box(
        'bicycle_details',
        'Bicycle Details',
        'bi_render_bicycle_details_meta_box',
        'bicycle',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'bi_add_bicycle_meta_boxes');

// Render meta box content
function bi_render_bicycle_details_meta_box($post) {
    $brand = get_post_meta($post->ID, '_bicycle_brand', true);
    $model = get_post_meta($post->ID, '_bicycle_model', true);
    $year = get_post_meta($post->ID, '_bicycle_year', true);
    $serial = get_post_meta($post->ID, '_bicycle_serial', true);
    
    wp_nonce_field('bicycle_details_nonce', 'bicycle_details_nonce');
    ?>
    <p>
        <label for="bicycle_brand">Brand:</label><br>
        <input type="text" id="bicycle_brand" name="bicycle_brand" value="<?php echo esc_attr($brand); ?>" class="widefat">
    </p>
    <p>
        <label for="bicycle_model">Model:</label><br>
        <input type="text" id="bicycle_model" name="bicycle_model" value="<?php echo esc_attr($model); ?>" class="widefat">
    </p>
    <p>
        <label for="bicycle_year">Year:</label><br>
        <input type="number" id="bicycle_year" name="bicycle_year" value="<?php echo esc_attr($year); ?>" class="widefat">
    </p>
    <p>
        <label for="bicycle_serial">Serial Number:</label><br>
        <input type="text" id="bicycle_serial" name="bicycle_serial" value="<?php echo esc_attr($serial); ?>" class="widefat">
    </p>
    <?php
}

// Save meta box data
function bi_save_bicycle_meta_box_data($post_id) {
    if (!isset($_POST['bicycle_details_nonce']) ||
        !wp_verify_nonce($_POST['bicycle_details_nonce'], 'bicycle_details_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = ['bicycle_brand', 'bicycle_model', 'bicycle_year', 'bicycle_serial'];
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta(
                $post_id,
                '_' . $field,
                sanitize_text_field($_POST[$field])
            );
        }
    }
}
add_action('save_post_bicycle', 'bi_save_bicycle_meta_box_data');