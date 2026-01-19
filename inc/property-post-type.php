<?php

/**
 * Register Custom Post Type: Property
 */
function start_theme_register_property_post_type()
{
	$labels = array(
		'name'                  => _x('Properties', 'Post Type General Name', 'start-theme'),
		'singular_name'         => _x('Property', 'Post Type Singular Name', 'start-theme'),
		'menu_name'             => __('Properties', 'start-theme'),
		'name_admin_bar'        => __('Property', 'start-theme'),
		'archives'              => __('Property Archives', 'start-theme'),
		'attributes'            => __('Property Attributes', 'start-theme'),
		'parent_item_colon'     => __('Parent Property:', 'start-theme'),
		'all_items'             => __('All Properties', 'start-theme'),
		'add_new_item'          => __('Add New Property', 'start-theme'),
		'add_new'               => __('Add New', 'start-theme'),
		'new_item'              => __('New Property', 'start-theme'),
		'edit_item'             => __('Edit Property', 'start-theme'),
		'update_item'           => __('Update Property', 'start-theme'),
		'view_item'             => __('View Property', 'start-theme'),
		'view_items'            => __('View Properties', 'start-theme'),
		'search_items'          => __('Search Property', 'start-theme'),
		'not_found'             => __('Not found', 'start-theme'),
		'not_found_in_trash'    => __('Not found in Trash', 'start-theme'),
		'featured_image'        => __('Featured Image', 'start-theme'),
		'set_featured_image'    => __('Set featured image', 'start-theme'),
		'remove_featured_image' => __('Remove featured image', 'start-theme'),
		'use_featured_image'    => __('Use as featured image', 'start-theme'),
		'insert_into_item'      => __('Insert into property', 'start-theme'),
		'uploaded_to_this_item' => __('Uploaded to this property', 'start-theme'),
		'items_list'            => __('Properties list', 'start-theme'),
		'items_list_navigation' => __('Properties list navigation', 'start-theme'),
		'filter_items_list'     => __('Filter properties list', 'start-theme'),
	);
	$args = array(
		'label'                 => __('Property', 'start-theme'),
		'description'           => __('Property listings', 'start-theme'),
		'labels'                => $labels,
		'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
		'taxonomies'            => array(),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'           => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-building',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'show_in_rest'          => false,
	);
	register_post_type('property', $args);
}
add_action('init', 'start_theme_register_property_post_type', 0);

/**
 * AJAX Handler: Load Properties
 */
function start_theme_load_properties_ajax()
{
	// Verify nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'properties_nonce')) {
		wp_send_json_error(array('message' => 'Invalid nonce'));
	}

	$sort_order = isset($_POST['sort_order']) ? sanitize_text_field($_POST['sort_order']) : 'DESC';
	$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
	$per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 6;

	// Validate sort order
	if (!in_array($sort_order, array('ASC', 'DESC'))) {
		$sort_order = 'DESC';
	}

	$args = array(
		'post_type'      => 'property',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'post_status'    => 'publish',
		'meta_key'       => 'price',
		'orderby'        => 'meta_value_num',
		'order'          => $sort_order,
	);

	$properties_query = new WP_Query($args);
	$total_properties = $properties_query->found_posts;

	ob_start();
	if ($properties_query->have_posts()) {
		while ($properties_query->have_posts()) {
			$properties_query->the_post();
			get_template_part('template-parts/property-card');
		}
		wp_reset_postdata();
	}
	$html = ob_get_clean();

	$has_more = $properties_query->max_num_pages > $page;

	wp_send_json_success(array(
		'html'     => $html,
		'total'    => $total_properties,
		'has_more' => $has_more,
		'page'     => $page,
	));
}
add_action('wp_ajax_load_properties', 'start_theme_load_properties_ajax');
add_action('wp_ajax_nopriv_load_properties', 'start_theme_load_properties_ajax');
