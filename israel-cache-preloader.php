<?php
/*
Plugin Name: Israel Cache Preloader
Description: This plugin speeds up response time of your pages and posts by applying a caching preload
Version: 1.0
Author: Israel Akin-Akinsanya
Author URI: https://israelakinakinsanya.tech
Text Domain: israel-preloader
*/

//Preventing direct access
if (!defined('ABSPATH')) {
    die;
}
// Creating Enqueues
function israel_preloader_enqueues() {
    if (is_admin()) {
        //enqueue javascript
        wp_enqueue_script('israel-preloader', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'), '1.0', true);
        //enqueue css
        wp_enqueue_style('israel-preloader-css', plugin_dir_url(__FILE__) . 'css/style.css');
    }
}
add_action('admin_enqueue_scripts', 'israel_preloader_enqueues');

// Create meta boxes to display button
function create_preloader_meta_boxes() {
    // Create meta box for posts
    add_meta_box(
        'israel-preloader-post',
        __('Cache Preload', 'israel-preloader'),
        'metabox_callback',
        'post',
        'normal',
        'high'
    );

    // Create meta box for pages
    add_meta_box(
        'israel-preloader-page',
        __('Cache Preload', 'israel-preloader'),
        'metabox_callback',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'create_preloader_meta_boxes');

// Metabox call back function to display html
function metabox_callback($post) {
    $post_id = $post->ID;
    $preload_button_text = __('Run now', 'israel-preloader');
    $post_type_text = __('Preload cache for this ', 'israel-preloader') . get_post_type($post);
    ?>
    <div class="cache-preloader">
        <p><?php echo $post_type_text; ?></p>
        <button id="preload-button" class="button button-primary" data-post-id="<?php echo $post_id; ?>"><?php echo $preload_button_text; ?></button>
        <h3 id="messenger"></h3>
    </div>
    <?php
}

// AJAX handler for cache preloading
function preload_cache_handler() {
    //Protect Ajax with nonce
    if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'preload_cache_nonce')) {
        $post_id = intval($_POST['post_id']);
        // Retrieve cached content if it exists
        $cached_content = get_transient('cache_preload_' . $post_id);

        if ($cached_content === false) {
            // If the cached content doesn't exist, generate new using transient
            $cached_content = generate_cache_content($post_id);
            set_transient('cache_preload_' . $post_id, $cached_content, 60); // Cache for one hour
        }
        // Send a response with the cached content
        wp_send_json_success(array('cached_content' => $cached_content));
    }else{
        wp_send_json_error(array('message' => 'Verification failed.'));
    }
    // Stop execution
    wp_die();
}
add_action('wp_ajax_preload_cache_handler', 'preload_cache_handler');

function generate_cache_content($post_id) {
    // Retrieve post object
    $post = get_post($post_id);
    // Check if the post exists and is published
    if ($post && $post->post_status === 'publish') {
        // Return content if post exists
        $post_content = $post->post_content;
        return wp_kses_post($post_content); //sanitizing content before caching
    }
    return '';
}

// Load translations
function preload_cache_load_textdomain() {
    load_plugin_textdomain('israel-preloader', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'preload_cache_load_textdomain');
