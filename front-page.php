<?php

/**
 * The front page template file
 *
 * @package Start_Theme
 */

get_header(); ?>

<main>
	<?php
	// Отримуємо секції з Flexible Content
	$sections = get_field('sections');

	if ($sections && is_array($sections)) {
		foreach ($sections as $section) {
			$layout = $section['acf_fc_layout'] ?? '';

			switch ($layout) {
				case 'section_cards':
					// Передаємо дані секції в template part
					$section_title = $section['title'] ?? '';
					$cards = $section['cards'] ?? array();
					if (!empty($cards)) {
						set_query_var('section_title', $section_title);
						set_query_var('cards', $cards);
						get_template_part('template-parts/sections/section-cards');
					}
					break;

				case 'properties':
					// Properties секція
					$properties_title = $section['title'] ?? '';
					set_query_var('properties_title', $properties_title);
					get_template_part('template-parts/sections/properties');
					break;

				// Тут можна додати інші типи секцій
				// case 'hero':
				//     get_template_part('template-parts/sections/hero');
				//     break;
			}
		}
	}
	?>
</main>

<?php get_footer(); ?>