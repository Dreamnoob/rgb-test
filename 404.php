<?php

/**
 * The template for displaying 404 pages (not found)
 *
 * @package Start_Theme
 */

get_header(); ?>

<main class="main">
	<section class="error-404 py180">
		<div class="container">
			<div class="error-404-inner">
				<div class="error-404-content" data-animate-group="fade-up">
					<div class="error-404-title">
						<h1 class="h1-namu">404</h1>
					</div>

					<div class="error-404-subtitle">
						<p class="h4-namu">Сторінку не знайдено</p>
					</div>

					<div class="error-404-description">
						<p class="text-m">
							На жаль, ця сторінка не існує або була видалена. 
							Ви можете повернутися на головну сторінку або скористатися навігацією сайту.
						</p>
					</div>

					<a href="<?php echo home_url(); ?>" class="btn btn--secondary">
						повернутися на головну
					</a>
				</div>
			</div>
		</div>
	</section>
</main>

<?php get_footer(); ?>