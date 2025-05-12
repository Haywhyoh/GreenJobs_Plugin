/**
 * Admin JavaScript for the Black Potential Pipeline plugin
 *
 * Handles admin interactions, approval/rejection workflows, and dashboard functionality
 *
 * @since      1.0.0
 * @package    Black_Potential_Pipeline
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        /**
         * Initialize tabs on settings page
         */
        function initSettingsTabs() {
            const $tabLinks = $('.bpp-settings-tabs a');
            const $tabContents = $('.bpp-settings-tab-content');
            
            // Set the first tab as active by default
            if ($tabLinks.length > 0 && $tabContents.length > 0) {
                $tabLinks.first().addClass('active');
                $tabContents.first().addClass('active');
                
                // Handle tab clicks
                $tabLinks.on('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = $(this).attr('href');
                    
                    // Update active states
                    $tabLinks.removeClass('active');
                    $tabContents.removeClass('active');
                    
                    $(this).addClass('active');
                    $(targetId).addClass('active');
                    
                    // Update URL hash
                    history.pushState(null, null, targetId);
                });
                
                // Check for hash in URL
                if (window.location.hash) {
                    const $targetTab = $tabLinks.filter('[href="' + window.location.hash + '"]');
                    if ($targetTab.length) {
                        $targetTab.trigger('click');
                    }
                }
            }
        }
        
        /**
         * Initialize the application approval/rejection functionality
         */
        function initApplicationActions() {
            // Approve button click handler (if not already initialized in the page)
            if (typeof window.bppApproveInitialized === 'undefined') {
                $(document).on('click', '.bpp-approve-button', function() {
                    if (confirm(bpp_admin_obj.i18n.approve_confirm)) {
                        const applicantId = $(this).data('id');
                        const $card = $(this).closest('.bpp-application-card');
                        
                        $.ajax({
                            url: bpp_admin_obj.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'bpp_approve_applicant',
                                applicant_id: applicantId,
                                nonce: bpp_admin_obj.nonce
                            },
                            beforeSend: function() {
                                $card.addClass('bpp-loading');
                            },
                            success: function(response) {
                                if (response.success) {
                                    $card.fadeOut(400, function() {
                                        $card.remove();
                                        
                                        // Show message if no more applications
                                        if ($('.bpp-application-card').length === 0) {
                                            $('.bpp-application-list').html('<div class="bpp-no-applications"><p>' + 
                                                bpp_admin_obj.i18n.no_applications + '</p></div>');
                                        }
                                    });
                                } else {
                                    alert(response.data || bpp_admin_obj.i18n.error);
                                }
                            },
                            error: function() {
                                alert(bpp_admin_obj.i18n.error);
                            },
                            complete: function() {
                                $card.removeClass('bpp-loading');
                            }
                        });
                    }
                });
                
                window.bppApproveInitialized = true;
            }
            
            // Reject button and modal functionality (if not already initialized)
            if (typeof window.bppRejectInitialized === 'undefined') {
                const $rejectModal = $('#bpp-rejection-modal');
                
                if ($rejectModal.length) {
                    // Reject button click handler
                    $(document).on('click', '.bpp-reject-button', function() {
                        const applicantId = $(this).data('id');
                        $('#bpp-rejection-applicant-id').val(applicantId);
                        $('#bpp-rejection-reason').val('');
                        $rejectModal.show();
                    });
                    
                    // Modal close button
                    $('.bpp-modal-close, #bpp-cancel-reject').on('click', function() {
                        $rejectModal.hide();
                    });
                    
                    // Confirm rejection
                    $('#bpp-confirm-reject').on('click', function() {
                        const applicantId = $('#bpp-rejection-applicant-id').val();
                        const reason = $('#bpp-rejection-reason').val();
                        const $card = $('.bpp-application-card[data-id="' + applicantId + '"]');
                        
                        $.ajax({
                            url: bpp_admin_obj.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'bpp_reject_applicant',
                                applicant_id: applicantId,
                                reason: reason,
                                nonce: bpp_admin_obj.nonce
                            },
                            beforeSend: function() {
                                $card.addClass('bpp-loading');
                            },
                            success: function(response) {
                                if (response.success) {
                                    $rejectModal.hide();
                                    $card.fadeOut(400, function() {
                                        $card.remove();
                                        
                                        // Show message if no more applications
                                        if ($('.bpp-application-card').length === 0) {
                                            $('.bpp-application-list').html('<div class="bpp-no-applications"><p>' + 
                                                bpp_admin_obj.i18n.no_applications + '</p></div>');
                                        }
                                    });
                                } else {
                                    alert(response.data || bpp_admin_obj.i18n.error);
                                }
                            },
                            error: function() {
                                alert(bpp_admin_obj.i18n.error);
                            },
                            complete: function() {
                                $card.removeClass('bpp-loading');
                            }
                        });
                    });
                    
                    // Close modal if clicked outside
                    $(window).on('click', function(event) {
                        if ($(event.target).is($rejectModal)) {
                            $rejectModal.hide();
                        }
                    });
                    
                    window.bppRejectInitialized = true;
                }
            }
        }
        
        /**
         * Initialize dashboard charts and statistics
         */
        function initDashboardCharts() {
            const $statsContainer = $('.bpp-admin-dashboard .bpp-stats-grid');
            
            if ($statsContainer.length) {
                // Add animation to stats numbers
                $statsContainer.find('.bpp-stat-number').each(function() {
                    const $this = $(this);
                    const value = parseInt($this.text(), 10);
                    
                    if (!isNaN(value)) {
                        $this.prop('Counter', 0).animate({
                            Counter: value
                        }, {
                            duration: 1000,
                            easing: 'swing',
                            step: function(now) {
                                $this.text(Math.ceil(now));
                            }
                        });
                    }
                });
            }
        }
        
        /**
         * Initialize form field toggles in settings
         */
        function initFormFieldToggles() {
            const $formFieldsContainer = $('.bpp-form-fields-container');
            
            if ($formFieldsContainer.length) {
                // Toggle required status
                $('.bpp-toggle-required').on('change', function() {
                    const $this = $(this);
                    const fieldId = $this.data('field');
                    const $statusLabel = $('#' + fieldId + '-required-status');
                    
                    if ($this.is(':checked')) {
                        $statusLabel.text(bpp_admin_obj.i18n.required);
                        $statusLabel.addClass('bpp-required');
                    } else {
                        $statusLabel.text(bpp_admin_obj.i18n.optional);
                        $statusLabel.removeClass('bpp-required');
                    }
                });
                
                // Toggle field visibility
                $('.bpp-toggle-field').on('change', function() {
                    const $this = $(this);
                    const fieldId = $this.data('field');
                    const $fieldRow = $('#' + fieldId + '-row');
                    const $requiredToggle = $('#' + fieldId + '-required');
                    
                    if ($this.is(':checked')) {
                        $fieldRow.removeClass('bpp-field-disabled');
                        $requiredToggle.prop('disabled', false);
                    } else {
                        $fieldRow.addClass('bpp-field-disabled');
                        $requiredToggle.prop('disabled', true).prop('checked', false).trigger('change');
                    }
                });
                
                // Sortable fields
                if ($.fn.sortable) {
                    $formFieldsContainer.sortable({
                        items: '.bpp-form-field-row',
                        handle: '.bpp-drag-handle',
                        axis: 'y',
                        update: function() {
                            // Update field order in hidden input
                            const fieldOrder = [];
                            $formFieldsContainer.find('.bpp-form-field-row').each(function() {
                                fieldOrder.push($(this).data('field-id'));
                            });
                            $('#bpp-field-order').val(fieldOrder.join(','));
                        }
                    });
                }
            }
        }
        
        // Initialize all admin functionality
        function initAdmin() {
            initSettingsTabs();
            initApplicationActions();
            initDashboardCharts();
            initFormFieldToggles();
            
            // Initialize any media uploaders
            $('.bpp-media-upload').each(function() {
                const $button = $(this);
                const $previewContainer = $button.siblings('.bpp-media-preview');
                const $idInput = $button.siblings('input[type="hidden"]');
                
                $button.on('click', function(e) {
                    e.preventDefault();
                    
                    // If WordPress media uploader exists
                    if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                        const frame = wp.media({
                            title: bpp_admin_obj.i18n.select_image,
                            multiple: false,
                            library: { type: 'image' },
                            button: { text: bpp_admin_obj.i18n.use_image }
                        });
                        
                        frame.on('select', function() {
                            const attachment = frame.state().get('selection').first().toJSON();
                            $idInput.val(attachment.id);
                            $previewContainer.html('<img src="' + attachment.url + '" alt="" />');
                            $button.text(bpp_admin_obj.i18n.change_image);
                        });
                        
                        frame.open();
                    }
                });
            });
        }
        
        // Initialize admin functionality
        initAdmin();
    });

})(jQuery); 