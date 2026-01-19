/**
 * Properties Section - Sorting and Load More
 */
(function($) {
	'use strict';

	const PropertiesSection = {
		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			// Sort button click
			$(document).on('click', '.properties-sort-btn', this.handleSort.bind(this));
			
			// Load More button click
			$(document).on('click', '.properties-load-more', this.handleLoadMore.bind(this));
		},

		handleSort: function(e) {
			e.preventDefault();
			const $button = $(e.currentTarget);
			const $section = $button.closest('.properties');
			const currentPage = parseInt($section.data('current-page')) || 1;
			
			// Determine new sort order based on current button state
			let newOrder;
			if ($button.hasClass('properties-sort-btn--high')) {
				$button.removeClass('properties-sort-btn--high').addClass('properties-sort-btn--low');
				newOrder = 'ASC';
			} else {
				$button.removeClass('properties-sort-btn--low').addClass('properties-sort-btn--high');
				newOrder = 'DESC';
			}
			
			// Update section data
			$section.data('sort-order', newOrder);
			
			// Update icon data-order
			const $icon = $button.find('.properties-sort-icon');
			$icon.attr('data-order', newOrder);

			// Load properties with new sort order
			// Keep the same number of items visible
			const perPage = currentPage * 3;

			this.loadProperties($section, {
				sort_order: newOrder,
				page: 1,
				replace: true,
				per_page: perPage,
				setCurrentPage: currentPage
			});
		},

		handleLoadMore: function(e) {
			e.preventDefault();
			const $button = $(e.currentTarget);
			const $section = $button.closest('.properties');
			const currentPage = parseInt($section.data('current-page')) || 1;
			const sortOrder = $section.data('sort-order') || 'DESC';

			// Disable button
			$button.prop('disabled', true).text('Loading...');

			// Load more properties
			this.loadProperties($section, {
				sort_order: sortOrder,
				page: currentPage + 1,
				replace: false
			});
		},

		loadProperties: function($section, options) {
			const defaults = {
				sort_order: 'DESC',
				page: 1,
				replace: true,
				per_page: 3
			};
			const params = $.extend({}, defaults, options);

			$.ajax({
				url: propertiesAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'load_properties',
					nonce: propertiesAjax.nonce,
					sort_order: params.sort_order,
					page: params.page,
					per_page: params.per_page
				},
				beforeSend: function() {
					if (params.replace) {
						$section.find('.properties-grid').addClass('loading');
					}
				},
				success: function(response) {
					if (response.success) {
						const data = response.data;
						
						if (params.replace) {
							// Replace existing properties
							$section.find('.properties-grid').html(data.html);
							$section.data('current-page', params.setCurrentPage || params.page);
						} else {
							// Append new properties
							$section.find('.properties-grid').append(data.html);
							$section.data('current-page', params.page);
							
							// Update or remove Load More button
							if (data.has_more) {
								$section.find('.properties-load-more')
									.prop('disabled', false)
									.text('Load More')
									.attr('data-page', params.page + 1);
							} else {
								$section.find('.properties-load-more-wrapper').remove();
							}
						}

						// Update count
						if (data.total !== undefined) {
							$section.find('.properties-count-number').text(data.total);
							$section.data('total', data.total);
						}
					} else {
						alert('Error loading properties: ' + (response.data || 'Unknown error'));
					}
				},
				error: function(xhr, status, error) {
					console.error('AJAX Error:', error);
					alert('Error loading properties. Please try again.');
				},
				complete: function() {
					$section.find('.properties-grid').removeClass('loading');
					$section.find('.properties-load-more').prop('disabled', false).text('Load More');
				}
			});
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		PropertiesSection.init();
	});

})(jQuery);
