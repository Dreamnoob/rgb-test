<?php

/**
 * Duplicate Functions
 * Функції для дублювання постів та сторінок
 * 
 * @package Start_Theme
 */

// Запобігаємо прямому доступу
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Додаємо кнопку "Дублювати" в адмін панель
 */
add_filter('post_row_actions', 'add_duplicate_post_link', 10, 2);
add_filter('page_row_actions', 'add_duplicate_post_link', 10, 2);

function add_duplicate_post_link($actions, $post)
{
	// Не показуємо кнопку дублювання для сторінок
	if ($post->post_type === 'page' || $post->post_type === 'acf-field-group') {
		return $actions;
	}
	
	if (current_user_can('edit_posts')) {
		$actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=duplicate_post&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce') . '" title="Дублювати цей пост" rel="permalink">Дублювати</a>';
	}
	return $actions;
}

/**
 * Обробляємо дублювання поста
 */
add_action('admin_action_duplicate_post', 'duplicate_post_action');

function duplicate_post_action()
{
	// Перевіряємо nonce
	if (!isset($_GET['duplicate_nonce']) || !wp_verify_nonce($_GET['duplicate_nonce'], basename(__FILE__))) {
		wp_die('Помилка безпеки');
	}

	// Перевіряємо права доступу
	if (!current_user_can('edit_posts')) {
		wp_die('Недостатньо прав для дублювання поста');
	}

	// Отримуємо ID оригінального поста
	$post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
	
	if (!$post_id) {
		wp_die('Не вказано ID поста');
	}

	// Отримуємо оригінальний пост
	$original_post = get_post($post_id);
	
	if (!$original_post) {
		wp_die('Пост не знайдено');
	}

	// Створюємо новий пост
	$new_post_data = array(
		'post_title'     => $original_post->post_title . ' (Копія)',
		'post_content'   => $original_post->post_content,
		'post_excerpt'   => $original_post->post_excerpt,
		'post_status'    => 'draft', // Новий пост буде чернеткою
		'post_type'      => $original_post->post_type,
		'post_author'    => get_current_user_id(),
		'post_parent'    => $original_post->post_parent,
		'menu_order'     => $original_post->menu_order,
		'comment_status' => $original_post->comment_status,
		'ping_status'    => $original_post->ping_status,
	);

	// Вставляємо новий пост
	$new_post_id = wp_insert_post($new_post_data);

	if (is_wp_error($new_post_id)) {
		wp_die('Помилка при створенні копії поста');
	}

	// Копіюємо мета поля
	$meta_data = get_post_meta($post_id);
	foreach ($meta_data as $key => $values) {
		foreach ($values as $value) {
			add_post_meta($new_post_id, $key, maybe_unserialize($value));
		}
	}

	// Копіюємо таксономії (категорії, теги)
	$taxonomies = get_object_taxonomies($original_post->post_type);
	foreach ($taxonomies as $taxonomy) {
		$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
		wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
	}

	// Копіюємо featured image
	if (has_post_thumbnail($post_id)) {
		$thumbnail_id = get_post_thumbnail_id($post_id);
		set_post_thumbnail($new_post_id, $thumbnail_id);
	}

	// Перенаправляємо на редагування нового поста
	wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
	exit;
}

/**
 * Додаємо кнопку "Дублювати" в метабокс
 */
add_action('post_submitbox_misc_actions', 'add_duplicate_button_in_metabox');

function add_duplicate_button_in_metabox()
{
	global $post;
	
	if (!$post || $post->post_status === 'auto-draft') {
		return;
	}

	// Не показуємо кнопку дублювання для сторінок
	if ($post->post_type === 'page') {
		return;
	}

	if (!current_user_can('edit_posts')) {
		return;
	}

	$duplicate_url = wp_nonce_url('admin.php?action=duplicate_post&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce');
	?>
	<div class="misc-pub-section">
		<a href="<?php echo esc_url($duplicate_url); ?>" class="button button-secondary" style="width: 100%; text-align: center; margin-top: 5px;">
			Дублювати
		</a>
	</div>
	<?php
}

/**
 * Додаємо стилі для кнопки дублювання
 */
add_action('admin_head', 'duplicate_post_admin_styles');

function duplicate_post_admin_styles()
{
	?>
	<style>
		.misc-pub-section a.button {
			text-decoration: none;
		}
		.misc-pub-section a.button:hover {
			background: #0073aa;
			color: white;
		}
	</style>
	<?php
}

/**
 * Додаємо JavaScript для підтвердження дублювання
 */
add_action('admin_footer', 'duplicate_post_admin_script');

function duplicate_post_admin_script()
{
	?>
	<script>
	jQuery(document).ready(function($) {
		$('a[href*="action=duplicate_post"]').on('click', function(e) {
			if (!confirm('Ви впевнені, що хочете дублювати цей пост?')) {
				e.preventDefault();
			}
		});
	});
	</script>
	<?php
}
