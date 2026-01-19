<?php

/**
 * Template Helper Functions
 * 
 * @package Start_Theme
 */

/**
 * Дозволяємо створеній через адмінку сторінці використовувати front-page.php шаблон
 */
function start_theme_front_page_template($template) {
	// Якщо це головна сторінка (статична сторінка)
	if (is_front_page() && is_page()) {
		$front_page_template = get_template_directory() . '/front-page.php';
		if (file_exists($front_page_template)) {
			return $front_page_template;
		}
	}
	return $template;
}
add_filter('template_include', 'start_theme_front_page_template');
