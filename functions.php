<?php

/**
 * Start Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Start_Theme
 */

if (! defined('_S_VERSION')) {
	// Replace the version number of the theme on each release.
	define('_S_VERSION', '1.0.0');
}

if (! function_exists('start_theme_setup')) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function start_theme_setup()
	{
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Start Theme, use a find and replace
		 * to change 'start-theme' to the name of your theme in all the template files.
		 */
		load_theme_textdomain('start-theme', get_template_directory() . '/languages');

		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'menu-1' => esc_html__('Primary', 'start-theme'),
				'footer-menu' => esc_html__('Footer Menu', 'start-theme'),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'start_theme_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);

		// Add theme support for selective refresh for widgets.
		add_theme_support('customize-selective-refresh-widgets');

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);

	}
endif;
add_action('after_setup_theme', 'start_theme_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function start_theme_content_width()
{
	$GLOBALS['content_width'] = apply_filters('start_theme_content_width', 640);
}
add_action('after_setup_theme', 'start_theme_content_width', 0);

/**
 * Enqueue scripts and styles.
 */
function start_theme_scripts()
{
	// Основні стилі теми
	wp_enqueue_style('start-theme-style', get_stylesheet_uri(), array(), _S_VERSION);
	wp_style_add_data('start-theme-style', 'rtl', 'replace');

	// CSS файли з папки css/
	wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap', array(), _S_VERSION);
	wp_enqueue_style('start-theme-animation', get_template_directory_uri() . '/css/animation.css', array(), _S_VERSION);
	wp_enqueue_style('start-theme-fonts', get_template_directory_uri() . '/css/fonts.css', array(), _S_VERSION);
	wp_enqueue_style('start-theme-vendor', get_template_directory_uri() . '/css/vendor.css', array(), _S_VERSION);
	wp_enqueue_style('start-theme-main', get_template_directory_uri() . '/css/main.css', array(), _S_VERSION);

	// JavaScript файли з папки js/
	wp_enqueue_script('gsap', 'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js', array(), false, true);
	wp_enqueue_script('ScrollTrigger', 'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js', array('gsap'), false, true);
	wp_enqueue_script('lenis', 'https://unpkg.com/lenis@1.3.11/dist/lenis.min.js', array("gsap"), false, true);
	wp_enqueue_script('start-theme-animation', get_template_directory_uri() . '/js/animation.js', array(), _S_VERSION, true);
	wp_enqueue_script('start-theme-vendor', get_template_directory_uri() . '/js/vendor.js', array('jquery'), _S_VERSION, true);
	wp_enqueue_script('start-theme-main', get_template_directory_uri() . '/js/main.js', array('start-theme-vendor'), _S_VERSION, true);

	// Properties AJAX localization
	wp_localize_script('start-theme-main', 'propertiesAjax', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce'   => wp_create_nonce('properties_nonce'),
	));

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'start_theme_scripts');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Template Helper Functions
 */
require get_template_directory() . '/inc/template-helpers.php';

/**
 * Duplicate Functions
 */
require get_template_directory() . '/inc/duplicate-functions.php';

/**
 * Property Post Type and AJAX Handlers
 */
require get_template_directory() . '/inc/property-post-type.php';

/**
 * Прибираємо клас 'blog' з головної сторінки
 */
function start_theme_remove_blog_class($classes)
{
	// Якщо це головна сторінка і є клас blog, прибираємо його
	if (is_front_page() && ($key = array_search('blog', $classes)) !== false) {
		unset($classes[$key]);
	}
	return $classes;
}
add_filter('body_class', 'start_theme_remove_blog_class');

/**
 * Contact Form 7 - прибираємо автоматичні p та br теги
 */
add_filter('wpcf7_autop_or_not', '__return_false');

add_filter('wpcf7_validate_configuration', '__return_false');

/**
 * Flush rewrite rules при активації теми
 */
function start_theme_flush_rewrite_rules()
{
	flush_rewrite_rules();
}
add_action('after_switch_theme', 'start_theme_flush_rewrite_rules');

/**
 * Відключаємо Гутенберг редактор на всьому сайті
 */
function start_theme_disable_gutenberg()
{
	// Відключаємо Гутенберг для всіх типів записів
	add_filter('use_block_editor_for_post', '__return_false');
	add_filter('use_block_editor_for_post_type', '__return_false');

	// Відключаємо Гутенберг для сторінок
	add_filter('use_block_editor_for_page', '__return_false');

	// Відключаємо Гутенберг для кастомних типів записів
	add_filter('use_block_editor_for_post_type', '__return_false', 10, 2);
}
add_action('init', 'start_theme_disable_gutenberg');

/**
 * Відключаємо Гутенберг стилі та скрипти
 */
function start_theme_disable_gutenberg_assets()
{
	// Видаляємо Гутенберг стилі
	wp_dequeue_style('wp-block-library');
	wp_dequeue_style('wp-block-library-theme');
	wp_dequeue_style('wp-format-library');

	// Видаляємо Гутенберг скрипти
	wp_dequeue_script('wp-block-library');
	wp_dequeue_script('wp-format-library');
}
add_action('wp_enqueue_scripts', 'start_theme_disable_gutenberg_assets', 100);

/**
 * Відключення WordPress admin bar на фронтенді
 */
add_filter('show_admin_bar', '__return_false');

/**
 * ACF JSON збереження полів
 * Автоматично зберігає ACF поля в папку acf-json для синхронізації між середовищами
 */
add_filter('acf/settings/save_json', 'start_theme_acf_json_save_point');
function start_theme_acf_json_save_point($path)
{
	return get_stylesheet_directory() . '/acf-json';
}

add_filter('acf/settings/load_json', 'start_theme_acf_json_load_point');
function start_theme_acf_json_load_point($paths)
{
	unset($paths[0]);
	$paths[] = get_stylesheet_directory() . '/acf-json';
	return $paths;
}
