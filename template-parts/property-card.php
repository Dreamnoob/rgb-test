<?php
/**
 * Property Card Template Part
 *
 * Використовується для відображення картки property в різних контекстах
 */

// Отримуємо дані property
$price = get_field('price');
$price_per_m2 = get_field('price_per_m2');
$address = get_field('address');
$bedrooms = get_field('bedrooms');
$bathrooms = get_field('bathrooms');
$floors = get_field('floors');
$description = get_field('description');
$featured_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
?>

<div class="properties-card" data-price="<?php echo esc_attr($price); ?>">
	<?php if ($featured_image) : ?>
		<div class="properties-card-image">
			<img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
		</div>
	<?php endif; ?>
	<div class="properties-card-content">
		<div class="properties-card-head">
			<?php if ($price) : ?>
				<div class="properties-card-prices">
					<div class="properties-card-price">$<?php echo number_format($price, 0, '.', ','); ?></div>
					<?php if ($price_per_m2) : ?>
						<div class="properties-card-m2">$<?php echo esc_html($price_per_m2); ?> for м2</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ($address) : ?>
				<div class="properties-card-address"><?php echo esc_html($address); ?></div>
			<?php endif; ?>
		</div>
		<div class="properties-card-details">
			<?php if ($bedrooms) : ?>
				<div class="properties-card-detail properties-card-detail--area">
					<span><?php echo esc_html($bedrooms); ?> </span>
				</div>
			<?php endif; ?>
			<?php if ($bathrooms) : ?>
				<div class="properties-card-detail properties-card-detail--rooms">
					<span><?php echo esc_html($bathrooms); ?> </span>
				</div>
			<?php endif; ?>
			<?php if ($floors) : ?>
				<div class="properties-card-detail properties-card-detail--floors">
					<span><?php echo esc_html($floors); ?> </span>
				</div>
			<?php endif; ?>
		</div>
		<?php if ($description) : ?>
			<div class="properties-card-description"><?php echo esc_html($description); ?></div>
		<?php endif; ?>
		<a href="<?php echo esc_url(get_permalink()); ?>" class="properties-card-btn">
			More details
		</a>
	</div>
</div>
