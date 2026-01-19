<?php

/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Start_Theme
 */

get_header();
?>

<div class="content">
	<div class="container">

		<?php
		while (have_posts()) :
			the_post();
		?>

			<article id="post-<?php the_ID(); ?>">
				<header class="entry-header content-header">
					<?php the_title('<h1 class="uppercase">', '</h1>'); ?>
				</header>

				<div class="entry-content content-wrapper">
					<?php
					the_content();

					wp_link_pages(
						array(
							'before' => '<div class="page-links">' . esc_html__('Pages:', 'start-theme'),
							'after'  => '</div>',
						)
					);
					?>
				</div>
			</article>
		<?php
		endwhile; // End of the loop.
		?>
	</div>
</div>

<?php
get_footer();
