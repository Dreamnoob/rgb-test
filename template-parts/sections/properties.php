<?php

/**
 * Properties Section Template Part
 *
 * @param array $args Масив аргументів, що передаються через get_template_part()
 */

// Отримуємо параметри з query var або з поля
$section_title = get_query_var('properties_title') ?: '';
$properties_per_page = get_query_var('properties_per_page') ?: 3;
$current_page = get_query_var('paged') ?: 1;
$sort_order = get_query_var('sort_order') ?: 'DESC';

// Запит для отримання properties
$args = array(
	'post_type'      => 'property',
	'posts_per_page' => $properties_per_page,
	'paged'          => $current_page,
	'post_status'    => 'publish',
	'meta_key'       => 'price',
	'orderby'        => 'meta_value_num',
	'order'          => $sort_order,
);

$properties_query = new WP_Query($args);
$total_properties = $properties_query->found_posts;
?>

<section class="properties" data-sort-order="<?php echo esc_attr($sort_order); ?>" data-current-page="<?php echo esc_attr($current_page); ?>" data-total="<?php echo esc_attr($total_properties); ?>">
	<div class="container">
		<div class="properties-inner">
			<?php if ($section_title) : ?>
				<h2 class="properties-title title-h2"><?php echo esc_html($section_title); ?></h2>
			<?php endif; ?>
			<div class="properties-header">
				<div class="properties-sort">
					<button class="properties-sort-btn <?php echo $sort_order === 'ASC' ? 'properties-sort-btn--low' : 'properties-sort-btn--high'; ?>" data-sort="price" aria-label="Сортувати за ціною" <?php echo $total_properties === 0 ? 'disabled' : ''; ?>>
						<span class="properties-sort-label properties-sort-label--low">Sort By price (Low to High)</span>
						<span class="properties-sort-label properties-sort-label--high">Sort By price (High to Low)</span>
						<span class="properties-sort-icon" data-order="<?php echo esc_attr($sort_order); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
								<path d="M10.3543 12.8365L13.9472 9.24362C14.2622 8.92863 14.0391 8.39006 13.5937 8.39006L6.40789 8.39006C5.96243 8.39006 5.73935 8.92863 6.05433 9.24362L9.64723 12.8365C9.84249 13.0318 10.1591 13.0318 10.3543 12.8365Z" fill="#7E7E7E" />
							</svg>
						</span>
					</button>
				</div>
				<div class="properties-count">
					<span class="properties-count-number"><?php echo esc_html($total_properties); ?></span>
					<span class="properties-count-label">items</span>
				</div>
			</div>

			<div class="properties-grid" id="properties-grid">
				<?php if ($properties_query->have_posts()) : ?>
					<?php while ($properties_query->have_posts()) : $properties_query->the_post(); ?>
						<?php get_template_part('template-parts/property-card'); ?>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<p class="properties-empty">No properties found.</p>
				<?php endif; ?>
			</div>

			<?php if ($properties_query->max_num_pages > $current_page) : ?>
				<div class="properties-load-more-wrapper">
					<button class="properties-btn properties-load-more" data-page="<?php echo esc_attr($current_page + 1); ?>">
						Load More
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>