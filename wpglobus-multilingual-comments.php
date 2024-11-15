<?php
/*
 * Plugin Name: Multilingual Comments for WPGlobus
 * Description: Multilingual Comments for WPGlobus - an unofficial plugin for creating multilingual comments using the WPGlobus plugin.
 * Version: 1.5.2
 * Author: seojacky 
 * Author URI: https://t.me/big_jacky
 * Plugin URI: https://github.com/seojacky/wpglobus-multilingual-comments
 * GitHub Plugin URI: https://github.com/seojacky/wpglobus-multilingual-comments
 * License: GPL-3.0-or-later
 * License URI: https://spdx.org/licenses/GPL-3.0-or-later.html
 * Text Domain: wpglobus-multilingual-comments
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load plugin text domain for translations
function wpglobus_multilingual_comments_load_textdomain() {
    load_plugin_textdomain( 'wpglobus-multilingual-comments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'wpglobus_multilingual_comments_load_textdomain' );

// Function for filtering comments based on the language of the current post
function comment_language_filter_comments_by_post_language($comments) {
    // Get the language of the current post    
    $post_language = WPGlobus::Config()->language;

    // Filter comments by the language of the current post
    $filtered_comments = array_filter($comments, function($comment) use ($post_language) {
        $comment_language = get_comment_meta($comment->comment_ID, 'comment_language', true);
        return $comment_language === $post_language;
    });

    return $filtered_comments;
}
add_filter('comments_array', 'comment_language_filter_comments_by_post_language', 10, 2);

// Add language selection field to the comment form
function comment_language_add_language_field($fields) {
    // Get the current language
    $current_language = WPGlobus::Config()->language;

    // Add language selection field if language is defined
    if (!empty($current_language)) {
        $fields['comment_language'] = '<p class="comment-form-language" style="display:none"><label for="comment_language">' . esc_html__('Language', 'wpglobus-multilingual-comments') . '</label>' .
            '<input id="comment_language" name="comment_language" type="hidden" value="' . esc_attr($current_language) . '">' .
            wp_nonce_field('save_comment_language', 'comment_language_nonce', true, false) . // Add nonce field
            '</p>';
    }

    return $fields;
}
add_filter('comment_form_default_fields', 'comment_language_add_language_field', 20);

// Save the selected language when sending a comment
function comment_language_save_comment_meta($comment_id) {
    if (isset($_POST['comment_language_nonce']) && wp_verify_nonce($_POST['comment_language_nonce'], 'save_comment_language')) {
        if (isset($_POST['comment_language'])) {
            $comment_language = sanitize_text_field($_POST['comment_language']);
            add_comment_meta($comment_id, 'comment_language', $comment_language, true);
        }
    }
}
add_action('comment_post', 'comment_language_save_comment_meta');

// Add the "Language" column to the comments admin panel
function comment_language_add_language_column($columns) {
    $columns['language'] = __('Language', 'wpglobus-multilingual-comments');
    return $columns;
}
add_filter('manage_edit-comments_columns', 'comment_language_add_language_column');

// Output the language data in the "Language" column
function comment_language_display_language_column_data($column, $comment_id) {
    if ($column === 'language') {
        $language = get_comment_meta($comment_id, 'comment_language', true);

        if ($language) {
            echo esc_html($language);
        } else {
            echo esc_html(__('Not assigned', 'wpglobus-multilingual-comments'));
        }
    }
}
add_action('manage_comments_custom_column', 'comment_language_display_language_column_data', 10, 2);

// Add actions for mass editing of comments
function comment_language_add_language_bulk_actions($actions) {
    $languages = WPGlobus::Config()->enabled_languages;
    foreach ($languages as $language) {
        $assign = esc_html__('Assign', 'wpglobus-multilingual-comments') . ' ' . strtoupper($language);
        $actions['assign_' . $language] = $assign;
    }
    return $actions;
}
add_filter('bulk_actions-edit-comments', 'comment_language_add_language_bulk_actions');

// Handling actions for bulk editing of comments
function comment_language_handle_language_bulk_actions($redirect_to, $action, $comment_ids) {
    $languages = WPGlobus::Config()->enabled_languages;
    foreach ($languages as $language) {
        if ($action == 'assign_' . $language) {
            foreach ($comment_ids as $comment_id) {
                update_comment_meta($comment_id, 'comment_language', $language);
            }
            $redirect_to = add_query_arg('bulk_language_updated', count($comment_ids), $redirect_to);
            break;
        }
    }
    return $redirect_to;
}
add_filter('handle_bulk_actions-edit-comments', 'comment_language_handle_language_bulk_actions', 10, 3);
?>
