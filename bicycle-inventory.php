<?php
/**
 * Plugin Name: Bicycle Inventory Manager
 * Description: A plugin to manage bicycle inventory
 * Version: 1.1.0
 * Author: Your Name
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue required scripts and styles
function bi_enqueue_admin_scripts() {
    if (get_post_type() === 'bicycle') {
        wp_enqueue_media();
        wp_enqueue_script('bi-admin-script', plugins_url('js/admin.js', __FILE__), array('jquery'), '1.0.0', true);
    }
}
add_action('admin_enqueue_scripts', 'bi_enqueue_admin_scripts');

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
        'supports' => ['title', 'editor', 'custom-fields', 'thumbnail']
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
    
    add_meta_box(
        'bicycle_gallery',
        'Bicycle Gallery',
        'bi_render_bicycle_gallery_meta_box',
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

// Render gallery meta box
function bi_render_bicycle_gallery_meta_box($post) {
    wp_nonce_field('bicycle_gallery_nonce', 'bicycle_gallery_nonce');
    $image_ids = get_post_meta($post->ID, '_bicycle_gallery', true);
    $image_ids = $image_ids ? explode(',', $image_ids) : array();
    ?>
    <div id="bicycle-gallery-container">
        <div id="bicycle-gallery-preview">
            <?php
            foreach ($image_ids as $image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                if ($image_url) {
                    echo '<div class="gallery-image" data-id="' . esc_attr($image_id) . '">';
                    echo '<img src="' . esc_url($image_url) . '" />';
                    echo '<button class="remove-image">×</button>';
                    echo '</div>';
                }
            }
            ?>
        </div>
        <input type="hidden" name="bicycle_gallery" id="bicycle_gallery" value="<?php echo esc_attr(implode(',', $image_ids)); ?>">
        <button type="button" id="add_bicycle_images" class="button">Add Images</button>
    </div>
    <style>
        #bicycle-gallery-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .gallery-image {
            position: relative;
            width: 150px;
            height: 150px;
        }
        .gallery-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(255, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
    </style>
    <?php
}

// Save meta box data
function bi_save_bicycle_meta_box_data($post_id) {
    // Save regular details
    if (isset($_POST['bicycle_details_nonce']) &&
        wp_verify_nonce($_POST['bicycle_details_nonce'], 'bicycle_details_nonce')) {
        
        if (!current_user_can('edit_post', $post_id) || defined('DOING_AUTOSAVE')) {
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

    // Save gallery
    if (isset($_POST['bicycle_gallery_nonce']) &&
        wp_verify_nonce($_POST['bicycle_gallery_nonce'], 'bicycle_gallery_nonce')) {
        
        if (isset($_POST['bicycle_gallery'])) {
            update_post_meta(
                $post_id,
                '_bicycle_gallery',
                sanitize_text_field($_POST['bicycle_gallery'])
            );
        }
    }
}
add_action('save_post_bicycle', 'bi_save_bicycle_meta_box_data');
