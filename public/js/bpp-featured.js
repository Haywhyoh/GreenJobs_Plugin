/**
 * JavaScript for handling the featured professionals carousel
 *
 * @since      1.0.0
 * @package    Black_Potential_Pipeline
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize carousel if present on the page
        if ($('.bpp-carousel-track').length) {
            initCarousel();
        }
        
        /**
         * Initialize carousel functionality
         */
        function initCarousel() {
            const $track = $('.bpp-carousel-track');
            const $slides = $('.bpp-carousel-slide');
            const $wrapper = $('.bpp-carousel-wrapper');
            const $prevBtn = $('.bpp-carousel-prev');
            const $nextBtn = $('.bpp-carousel-next');
            
            // If no slides, exit
            if (!$slides.length) return;
            
            // Calculate values
            const slideWidth = $slides.first().outerWidth(true);
            const slideCount = $slides.length;
            let currentIndex = 0;
            let slidesPerView = calculateSlidesPerView();
            
            // Set initial track width
            updateTrackWidth();
            
            // Update button states initially
            updateButtonStates();
            
            // Previous button click
            $prevBtn.on('click', function() {
                if (currentIndex > 0) {
                    currentIndex--;
                    updatePosition();
                }
            });
            
            // Next button click
            $nextBtn.on('click', function() {
                if (currentIndex < slideCount - slidesPerView) {
                    currentIndex++;
                    updatePosition();
                }
            });
            
            // Window resize handler
            $(window).on('resize', function() {
                const newSlidesPerView = calculateSlidesPerView();
                
                if (newSlidesPerView !== slidesPerView) {
                    slidesPerView = newSlidesPerView;
                    currentIndex = Math.min(currentIndex, slideCount - slidesPerView);
                    updateTrackWidth();
                    updatePosition();
                }
            });
            
            /**
             * Calculate how many slides should be visible
             * @return {number} Number of slides per view
             */
            function calculateSlidesPerView() {
                const wrapperWidth = $wrapper.width();
                
                if (wrapperWidth < 576) {
                    return 1; // Mobile: 1 slide
                } else if (wrapperWidth < 768) {
                    return 2; // Tablet: 2 slides
                } else if (wrapperWidth < 992) {
                    return 3; // Small desktop: 3 slides
                } else {
                    return 4; // Large desktop: 4 slides
                }
            }
            
            /**
             * Update the track width based on slides
             */
            function updateTrackWidth() {
                const slideWidth = 100 / slidesPerView;
                
                // Set width for track and slides
                $slides.css({
                    'flex-basis': `${slideWidth}%`,
                    'max-width': `${slideWidth}%`
                });
            }
            
            /**
             * Update carousel position
             */
            function updatePosition() {
                const translateX = -currentIndex * (100 / slidesPerView);
                $track.css('transform', `translateX(${translateX}%)`);
                
                // Update button states
                updateButtonStates();
            }
            
            /**
             * Update navigation button states
             */
            function updateButtonStates() {
                $prevBtn.prop('disabled', currentIndex === 0);
                $nextBtn.prop('disabled', currentIndex >= slideCount - slidesPerView);
                
                // Add visual feedback
                if (currentIndex === 0) {
                    $prevBtn.addClass('bpp-button-disabled');
                } else {
                    $prevBtn.removeClass('bpp-button-disabled');
                }
                
                if (currentIndex >= slideCount - slidesPerView) {
                    $nextBtn.addClass('bpp-button-disabled');
                } else {
                    $nextBtn.removeClass('bpp-button-disabled');
                }
            }
        }
    });
    
})(jQuery); 