<?php
/*
 * Plugin Name: Multilingual Comments for WPGlobus
 * Description: Multilingual Comments for WPGlobus - an unofficial plugin for creating multilingual comments using the WPGlobus plugin.
 * Version: 1.5.4
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

// Улучшенная функция для сохранения языка комментария
function comment_language_save_comment_meta($comment_id) {
    // Обработка для фронтенда (с nonce)
    if (isset($_POST['comment_language_nonce']) && wp_verify_nonce($_POST['comment_language_nonce'], 'save_comment_language')) {
        if (isset($_POST['comment_language'])) {
            $comment_language = sanitize_text_field($_POST['comment_language']);
            add_comment_meta($comment_id, 'comment_language', $comment_language, true);
            return; // Выходим, чтобы не дублировать обработку
        }
    }
    
    // Обработка для админки (без nonce, но с проверкой прав)
    if (is_admin() && current_user_can('moderate_comments')) {
        $comment = get_comment($comment_id);
        if ($comment) {
            // Если язык уже установлен, не перезаписываем
            $existing_language = get_comment_meta($comment_id, 'comment_language', true);
            if (!empty($existing_language)) {
                return;
            }
            
            // Проверяем, есть ли родительский комментарий
            if ($comment->comment_parent > 0) {
                $parent_language = get_comment_meta($comment->comment_parent, 'comment_language', true);
                if (!empty($parent_language)) {
                    add_comment_meta($comment_id, 'comment_language', $parent_language, true);
                    return;
                }
            }
            
            // Если нет родительского комментария или у него нет языка,
            // берем язык поста или язык по умолчанию
            if (class_exists('WPGlobus')) {
                $post_id = $comment->comment_post_ID;
                $post_language = get_post_meta($post_id, '_wpglobus_language', true);
                
                if (empty($post_language)) {
                    $post_language = WPGlobus::Config()->default_language;
                }
                
                if (!empty($post_language)) {
                    add_comment_meta($comment_id, 'comment_language', $post_language, true);
                }
            }
        }
    }
}
add_action('comment_post', 'comment_language_save_comment_meta');

// Функция для автоматического присвоения языка комментарию-ответу
function comment_language_assign_to_reply($comment_id) {
    $comment = get_comment($comment_id);
    
    // Проверяем, является ли это ответом на другой комментарий
    if ($comment && $comment->comment_parent > 0) {
        // Получаем язык родительского комментария
        $parent_language = get_comment_meta($comment->comment_parent, 'comment_language', true);
        
        // Если у родительского комментария есть язык, присваиваем его дочернему
        if (!empty($parent_language)) {
            add_comment_meta($comment_id, 'comment_language', $parent_language, true);
        } else {
            // Если у родительского комментария нет языка, пытаемся получить язык поста
            $post_id = $comment->comment_post_ID;
            if ($post_id && class_exists('WPGlobus')) {
                // Получаем язык поста через WPGlobus
                $post_language = get_post_meta($post_id, '_wpglobus_language', true);
                if (empty($post_language)) {
                    // Если мета не найдена, используем текущий язык WPGlobus
                    $post_language = WPGlobus::Config()->default_language;
                }
                
                if (!empty($post_language)) {
                    add_comment_meta($comment_id, 'comment_language', $post_language, true);
                }
            }
        }
    } else {
        // Если это не ответ, но комментарий добавлен через админку
        // и у него нет языка, пытаемся определить язык поста
        $existing_language = get_comment_meta($comment_id, 'comment_language', true);
        
        if (empty($existing_language)) {
            $post_id = $comment->comment_post_ID;
            if ($post_id && class_exists('WPGlobus')) {
                // Получаем язык поста
                $post_language = get_post_meta($post_id, '_wpglobus_language', true);
                if (empty($post_language)) {
                    $post_language = WPGlobus::Config()->default_language;
                }
                
                if (!empty($post_language)) {
                    add_comment_meta($comment_id, 'comment_language', $post_language, true);
                }
            }
        }
    }
}

// Добавляем хук для обработки комментариев, созданных через админку
add_action('comment_post', 'comment_language_assign_to_reply', 15); // Приоритет 15, чтобы выполнялось после основной функции

// Альтернативный хук для комментариев, созданных/обновленных через админку
add_action('edit_comment', 'comment_language_assign_to_reply', 10);

// Дополнительно: обработка для wp_insert_comment (для полноты)
function comment_language_handle_insert_comment($comment_id, $comment) {
    // Проверяем, что это не дублирование обработки
    $existing_language = get_comment_meta($comment_id, 'comment_language', true);
    if (empty($existing_language)) {
        comment_language_assign_to_reply($comment_id);
    }
}
add_action('wp_insert_comment', 'comment_language_handle_insert_comment', 10, 2);

// Add the "Language" column to the comments admin panel
function comment_language_add_language_column($columns) {   
	$columns['language'] = '<span class="dashicons dashicons-translation" title="'.__('Language', 'wpglobus-multilingual-comments').'"></span>';
    return $columns;
}
add_filter('manage_edit-comments_columns', 'comment_language_add_language_column');

function comment_language_display_language_column_data($column, $comment_id) {
    if ($column === 'language') {
        $language = get_comment_meta($comment_id, 'comment_language', true);

        if ($language) {
            // Get the site URL and append the WPGlobus flags path
            $flag_url = home_url('/wp-content/plugins/wpglobus/flags/' . strtolower($language) . '.png');
            $output = $language . ' <img src="' . esc_url($flag_url) . '" alt="' . esc_attr($language) . ' flag" style="width: 18px; height: 12px; vertical-align: middle;" />';
            echo wp_kses_post($output);
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
