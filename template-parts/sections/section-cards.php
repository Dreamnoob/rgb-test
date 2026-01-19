<?php
/**
 * Section Cards Template Part
 * Використовується для Flexible Content
 *
 * @param array $args Масив аргументів, що передаються через get_template_part()
 */

// Отримуємо дані з query var або з поля
$section_title = get_query_var('section_title') ?: get_field('title') ?: '';
$cards = get_query_var('cards') ?: get_field('cards') ?: array();

if (empty($cards)) {
	return;
}
?>

<section class="section-cards">
	<div class="container">
		<?php if ($section_title) : ?>
			<h2 class="section-cards-title title-h2"><?php echo esc_html($section_title); ?></h2>
		<?php endif; ?>
		<div class="cards-grid">
			<?php foreach ($cards as $index => $card) : 
				$card_size = $card['size'] ?? '6';
				$card_type = $card['type'] ?? 'text';
				$card_bg_color = $card['bg_color'] ?? '';
				$card_title = $card['title'] ?? '';
				$card_text = $card['text'] ?? '';
				$card_image = $card['image'] ?? null;
				$card_button = $card['button'] ?? null;
				$card_title_color = $card['title_color'] ?? '';
				$card_text_color = $card['text_color'] ?? '';
				
				// Для першої картки використовуємо title-h3, для інших title-h4
				$title_class = $index === 0 ? 'title-h3' : 'title-h4';
				
				// Функція для отримання CSS custom property з назви кольору
				$get_color_value = function($color_name) {
					if (empty($color_name)) {
						return '';
					}
					// Якщо це пресет з палітри, використовуємо CSS custom property
					$preset_colors = array(
						'Surface-White', 'Surface-Ligher', 'Surface-BG', 'Surface-Darker', 'Surface-Dark',
						'Grey-G50', 'Grey-G60', 'Grey-G75', 'Grey-G100', 'Grey-G200', 'Grey-G300', 'Grey-G400', 'Grey-G500'
					);
					if (in_array($color_name, $preset_colors)) {
						return 'var(--' . esc_attr($color_name) . ')';
					}
					// Інакше використовуємо як кастомний колір
					return esc_attr($color_name);
				};
				
				$title_style = '';
				if ($card_title_color) {
					$color_value = $get_color_value($card_title_color);
					if ($color_value) {
						$title_style = 'color: ' . $color_value . ';';
					}
				}
				
				$text_style = '';
				if ($card_text_color) {
					$color_value = $get_color_value($card_text_color);
					if ($color_value) {
						$text_style = 'color: ' . $color_value . ';';
					}
				}
				
				$card_classes = array(
					'card',
					'card--size-' . esc_attr($card_size),
					'card--type-' . esc_attr($card_type),
				);
				
				// Формуємо inline стилі для картки
				$card_style_parts = array();
				
				// Background color
				if ($card_bg_color) {
					$bg_color_value = $get_color_value($card_bg_color);
					if ($bg_color_value) {
						$card_style_parts[] = 'background-color: ' . $bg_color_value . ';';
					}
				}
				
				$card_style = !empty($card_style_parts) ? implode(' ', $card_style_parts) : '';
			?>
				<div class="<?php echo esc_attr(implode(' ', $card_classes)); ?>" <?php echo $card_style ? 'style="' . esc_attr($card_style) . '"' : ''; ?>>
					<?php if ($card_type === 'image-right' || $card_type === 'image-bottom') : ?>
						<?php if ($card_type === 'image-right') : ?>
							<div class="card-content">
								<?php if ($card_title) : ?>
									<h3 class="card-title <?php echo esc_attr($title_class); ?>" <?php echo $title_style ? 'style="' . esc_attr($title_style) . '"' : ''; ?>><?php echo esc_html($card_title); ?></h3>
								<?php endif; ?>
								<?php if ($card_text) : ?>
									<div class="card-text" <?php echo $text_style ? 'style="' . esc_attr($text_style) . '"' : ''; ?>><?php echo wp_kses_post($card_text); ?></div>
								<?php endif; ?>
								<?php if ($card_button && $card_button['url']) : ?>
									<a href="<?php echo esc_url($card_button['url']); ?>" 
									   class="card-button" 
									   <?php echo $card_button['target'] ? 'target="' . esc_attr($card_button['target']) . '"' : ''; ?>>
										<?php echo esc_html($card_button['title'] ?: 'Детальніше'); ?>
									</a>
								<?php endif; ?>
							</div>
							<?php if ($card_image) : ?>
								<div class="card-image">
									<img src="<?php echo esc_url($card_image['url']); ?>" 
										 alt="<?php echo esc_attr($card_image['alt'] ?: $card_title); ?>">
								</div>
							<?php endif; ?>
						<?php else : // image-bottom ?>
							<div class="card-content">
								<?php if ($card_title) : ?>
									<h3 class="card-title <?php echo esc_attr($title_class); ?>" <?php echo $title_style ? 'style="' . esc_attr($title_style) . '"' : ''; ?>><?php echo esc_html($card_title); ?></h3>
								<?php endif; ?>
								<?php if ($card_text) : ?>
									<div class="card-text" <?php echo $text_style ? 'style="' . esc_attr($text_style) . '"' : ''; ?>><?php echo wp_kses_post($card_text); ?></div>
								<?php endif; ?>
								<?php if ($card_button && $card_button['url']) : ?>
									<a href="<?php echo esc_url($card_button['url']); ?>" 
									   class="card-button" 
									   <?php echo $card_button['target'] ? 'target="' . esc_attr($card_button['target']) . '"' : ''; ?>>
										<?php echo esc_html($card_button['title'] ?: 'Детальніше'); ?>
									</a>
								<?php endif; ?>
							</div>
							<?php if ($card_image) : ?>
								<div class="card-image">
									<img src="<?php echo esc_url($card_image['url']); ?>" 
										 alt="<?php echo esc_attr($card_image['alt'] ?: $card_title); ?>">
								</div>
							<?php endif; ?>
						<?php endif; ?>
					<?php else : // text type ?>
						<div class="card-content">
							<?php if ($card_title) : ?>
								<h3 class="card-title <?php echo esc_attr($title_class); ?>" <?php echo $title_style ? 'style="' . esc_attr($title_style) . '"' : ''; ?>><?php echo esc_html($card_title); ?></h3>
							<?php endif; ?>
							<?php if ($card_text) : ?>
								<div class="card-text" <?php echo $text_style ? 'style="' . esc_attr($text_style) . '"' : ''; ?>><?php echo wp_kses_post($card_text); ?></div>
							<?php endif; ?>
							<?php if ($card_button && $card_button['url']) : ?>
								<a href="<?php echo esc_url($card_button['url']); ?>" 
								   class="card-button" 
								   <?php echo $card_button['target'] ? 'target="' . esc_attr($card_button['target']) . '"' : ''; ?>>
									<?php echo esc_html($card_button['title'] ?: 'Детальніше'); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
