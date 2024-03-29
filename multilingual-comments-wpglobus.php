<?php
/*
Plugin Name: Multilingual Comments WPGlobus
Description: Unofficial Plugin for Multilingual Comments WPGlobus.
Version: 1.0
Author: seojacky
*/

// Функция для фильтрации комментариев на основе языка текущего поста
function comment_language_filter_comments_by_post_language($comments) {
    // Получаем язык текущего поста
    $post_language = get_locale() === 'ru_RU' ? 'ru_RU' : 'en_US';

    // Фильтруем комментарии по языку текущего поста
    $filtered_comments = array_filter($comments, function($comment) use ($post_language) {
        $comment_language = get_comment_meta($comment->comment_ID, 'comment_language', true);
        return $comment_language === $post_language;
    });

    return $filtered_comments;
}

// Переопределяем функцию, отвечающую за вывод комментариев
add_filter('comments_array', 'comment_language_filter_comments_by_post_language', 10, 2);

// Добавляем поле выбора языка в форму комментария
add_filter('comment_form_default_fields', function($fields) {
    // Получаем текущий язык
    $current_language = get_locale();

    // Добавляем поле выбора языка, если язык определен
    if (!empty($current_language)) {
        $fields['comment_language'] = '<p class="comment-form-language" style="display:none"><label for="comment_language">' . esc_html__('Language', 'generatepress') . '</label>' .
            '<select id="comment_language" name="comment_language">' .
            '<option value="' . esc_attr($current_language) . '" selected>' . esc_html($current_language) . '</option>' .
            '</select></p>';
    }

    return $fields;
}, 20);

// Сохраняем выбранный язык при отправке комментария
add_action('comment_post', function($comment_id) {
    if (isset($_POST['comment_language'])) {
        $language = sanitize_text_field($_POST['comment_language']);
        add_comment_meta($comment_id, 'comment_language', $language);
    }
});

// Добавляем столбец "Language" в административную панель комментариев
add_filter('manage_edit-comments_columns', 'comment_language_add_language_column');
function comment_language_add_language_column($columns) {
    $columns['language'] = __('Language', 'generatepress');
    return $columns;
}

// Выводим данные языка в столбце "Language"
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

// Добавляем действия для массового редактирования комментариев
add_filter('bulk_actions-edit-comments', 'comment_language_add_language_bulk_actions');
function comment_language_add_language_bulk_actions($actions) {
    $actions['assign_en_US'] = __('Assign en_US', 'textdomain');
    $actions['assign_ru_RU'] = __('Assign ru_RU', 'textdomain');
    return $actions;
}

// Обрабатываем действия для массового редактирования комментариев
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
