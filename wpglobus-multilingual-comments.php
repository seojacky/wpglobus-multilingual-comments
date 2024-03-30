<?php
/*
 * Plugin Name: WPGlobus Multilingual Comments
 * Description: WPGlobus Multilingual Comments - an unofficial plugin for creating multilingual comments using the WPGlobus plugin.
 * Version: 1.0
 * Author: @big_jacky 
 * Author URI: https://t.me/big_jacky
 * Plugin URI: https://github.com/seojacky/wpglobus-multilingual-comments
 * GitHub Plugin URI: https://github.com/seojacky/wpglobus-multilingual-comments
 * Domain Path: /languages
*/

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) {	return;}

// Function for filtering comments based on the language of the current post
function comment_language_filter_comments_by_post_language($comments) {
    // Get the language of the current post
    $post_language = get_locale() === 'ru_RU' ? 'ru_RU' : 'en_US';

    // Filter comments by the language of the current post
    $filtered_comments = array_filter($comments, function($comment) use ($post_language) {
        $comment_language = get_comment_meta($comment->comment_ID, 'comment_language', true);
        return $comment_language === $post_language;
    });

    return $filtered_comments;
}

// Переопределяем функцию, отвечающую за вывод комментариев
add_filter('comments_array', 'comment_language_filter_comments_by_post_language', 10, 2);

// Override the function responsible for displaying comments
add_filter('comment_form_default_fields', function($fields) {
    // Получаем текущий язык
    $current_language = get_locale();

    // Add language selection field if language is defined
    if (!empty($current_language)) {
        $fields['comment_language'] = '<p class="comment-form-language" style="display:none"><label for="comment_language">' . esc_html__('Language', 'generatepress') . '</label>' .
            '<select id="comment_language" name="comment_language">' .
            '<option value="' . esc_attr($current_language) . '" selected>' . esc_html($current_language) . '</option>' .
            '</select></p>';
    }

    return $fields;
}, 20);

// Save the selected language when sending a comment
add_action('comment_post', function($comment_id) {
    if (isset($_POST['comment_language'])) {
        $language = sanitize_text_field($_POST['comment_language']);
        add_comment_meta($comment_id, 'comment_language', $language);
    }
});

// Add the "Language" column to the comments admin panel
add_filter('manage_edit-comments_columns', 'comment_language_add_language_column');
function comment_language_add_language_column($columns) {
    $columns['language'] = __('Language', 'generatepress');
    return $columns;
}

// Output the language data in the "Language" column
add_action('manage_comments_custom_column', 'comment_language_display_language_column_data', 10, 2);
function comment_language_display_language_column_data($column, $comment_id) {
    if ($column === 'language') {
        $language = get_comment_meta($comment_id, 'comment_language', true);

        if ($language) {
            echo esc_html($language);
        } else {
            echo __('Not assigned', 'generatepress');
        }
    }
}

// Add actions for mass editing of comments
add_filter('bulk_actions-edit-comments', 'comment_language_add_language_bulk_actions');
function comment_language_add_language_bulk_actions($actions) {
    $actions['assign_en_US'] = __('Assign en_US', 'textdomain');
    $actions['assign_ru_RU'] = __('Assign ru_RU', 'textdomain');
    return $actions;
}

// Handling actions for bulk editing of comments
add_filter('handle_bulk_actions-edit-comments', 'comment_language_handle_language_bulk_actions', 10, 3);
function comment_language_handle_language_bulk_actions($redirect_to, $action, $comment_ids) {
    if ($action == 'assign_en_US' || $action == 'assign_ru_RU') {
        $language = str_replace('assign_', '', $action);
        foreach ($comment_ids as $comment_id) {
            update_comment_meta($comment_id, 'comment_language', $language);
        }
        $redirect_to = add_query_arg('bulk_language_updated', count($comment_ids), $redirect_to);
    }
    return $redirect_to;
}
