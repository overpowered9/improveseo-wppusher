/**
 * ImproveSEO Modal System
 * Professional modal and notification system
 * Version: 1.0.0
 */

(function($) {
    'use strict';

    // Modal System
    window.ImproveSEOModal = {
        
        /**
         * Show Credit Exhausted Modal
         */
        showCreditModal: function(options) {
            const defaults = {
                creditsNeeded: 5,
                creditsRemaining: 0,
                dashboardUrl: 'https://dashboard.improveseoplugin.com'
            };
            
            const settings = $.extend({}, defaults, options);
            
            const html = `
                <div class="improveseo-modal-overlay active" id="improveseo-credit-modal" data-dismissible="true">
                    <div class="improveseo-modal">
                        <button class="improveseo-modal-close" onclick="ImproveSEOModal.closeModal('improveseo-credit-modal')"></button>
                        <div class="improveseo-modal-header">
                            <div class="improveseo-modal-icon">
                                <img src="${improveseModalVars.logoUrl}" alt="ImproveSEO">
                                <span class="icon-badge warning">⚠️</span>
                            </div>
                            <h2>Credits Exhausted</h2>
                        </div>
                        <div class="improveseo-modal-body">
                            <p>You need <strong>${settings.creditsNeeded} credits</strong> to complete this action.</p>
                            <div class="credit-balance">
                                Current Balance: <span>${settings.creditsRemaining} credits</span>
                            </div>
                            <p style="text-align: center; color: #666;">Purchase more credits to continue generating amazing AI content.</p>
                        </div>
                        <div class="improveseo-modal-actions">
                            <a href="${settings.dashboardUrl}" target="_blank" class="improveseo-btn improveseo-btn-primary">
                                Buy More Credits
                            </a>
                            <button class="improveseo-btn improveseo-btn-secondary" onclick="ImproveSEOModal.closeModal('improveseo-credit-modal')">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(html);
        },

        /**
         * Show Success Modal
         */
        showSuccessModal: function(message, options) {
            const defaults = {
                title: 'Success!',
                actionText: 'Continue',
                actionUrl: null,
                onClose: null
            };
            
            const settings = $.extend({}, defaults, options);
            
            const actionButton = settings.actionUrl 
                ? `<a href="${settings.actionUrl}" class="improveseo-btn improveseo-btn-success">${settings.actionText}</a>`
                : `<button class="improveseo-btn improveseo-btn-success" onclick="ImproveSEOModal.closeModal('improveseo-success-modal')">${settings.actionText}</button>`;
            
            const html = `
                <div class="improveseo-modal-overlay active" id="improveseo-success-modal" data-dismissible="true">
                    <div class="improveseo-modal">
                        <button class="improveseo-modal-close" onclick="ImproveSEOModal.closeModal('improveseo-success-modal')"></button>
                        <div class="improveseo-modal-header">
                            <div class="improveseo-modal-icon success">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                </svg>
                            </div>
                            <h2>${settings.title}</h2>
                        </div>
                        <div class="improveseo-modal-body">
                            <p style="text-align: center;">${message}</p>
                        </div>
                        <div class="improveseo-modal-actions">
                            ${actionButton}
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(html);
            
            if (settings.onClose) {
                $('#improveseo-success-modal').on('remove', settings.onClose);
            }
        },

        /**
         * Show Error Modal
         */
        showErrorModal: function(message, options) {
            const defaults = {
                title: 'Error Occurred',
                details: null,
                showRetry: false,
                onRetry: null
            };
            
            const settings = $.extend({}, defaults, options);
            
            const detailsHtml = settings.details ? `
                <div class="error-details">
                    <strong>Error Details:</strong>
                    ${settings.details}
                </div>
            ` : '';
            
            const retryButton = settings.showRetry ? `
                <button class="improveseo-btn improveseo-btn-primary" onclick="ImproveSEOModal.handleRetry('improveseo-error-modal')">
                    Try Again
                </button>
            ` : '';
            
            const html = `
                <div class="improveseo-modal-overlay active" id="improveseo-error-modal" data-dismissible="true">
                    <div class="improveseo-modal">
                        <button class="improveseo-modal-close" onclick="ImproveSEOModal.closeModal('improveseo-error-modal')"></button>
                        <div class="improveseo-modal-header">
                            <div class="improveseo-modal-icon error">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                                </svg>
                            </div>
                            <h2>${settings.title}</h2>
                        </div>
                        <div class="improveseo-modal-body">
                            <p style="text-align: center;">${message}</p>
                            ${detailsHtml}
                        </div>
                        <div class="improveseo-modal-actions">
                            ${retryButton}
                            <button class="improveseo-btn improveseo-btn-secondary" onclick="ImproveSEOModal.closeModal('improveseo-error-modal')">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(html);
            
            if (settings.onRetry) {
                window.improveseoRetryCallback = settings.onRetry;
            }
        },

        /**
         * Show Loading Modal
         */
        showLoadingModal: function(title, options) {
            const defaults = {
                stages: [],
                currentStage: 0,
                percentage: 0,
                tip: 'This may take 2-3 minutes.'
            };
            
            const settings = $.extend({}, defaults, options);
            
            const stageText = settings.stages.length > 0 
                ? `Step ${settings.currentStage + 1} of ${settings.stages.length}: ${settings.stages[settings.currentStage]}`
                : 'Processing...';
            
            const html = `
                <div class="improveseo-modal-overlay active" id="improveseo-loading-modal" data-dismissible="false">
                    <div class="improveseo-modal">
                        <div class="improveseo-modal-header">
                            <div class="loader-animation">
                                <div class="loader-spinner"></div>
                            </div>
                            <h2 id="loading-title">${title}</h2>
                        </div>
                        <div class="improveseo-modal-body">
                            <div class="improveseo-progress-bar">
                                <div class="improveseo-progress-fill" id="loading-progress" style="width: ${settings.percentage}%"></div>
                            </div>
                            <p class="loading-stage" id="loading-stage">${stageText}</p>
                            <p class="loading-tip">${settings.tip}</p>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing loading modal if any
            $('#improveseo-loading-modal').remove();
            $('body').append(html);
        },

        /**
         * Update Loading Progress
         */
        updateLoadingProgress: function(stage, totalStages, percentage, stageText) {
            $('#loading-progress').css('width', percentage + '%');
            $('#loading-stage').text(`Step ${stage} of ${totalStages}: ${stageText}`);
        },

        /**
         * Show Confirmation Modal
         */
        showConfirmationModal: function(title, message, onConfirm, options) {
            const defaults = {
                confirmText: 'Confirm',
                cancelText: 'Cancel',
                isDangerous: false
            };
            
            const settings = $.extend({}, defaults, options);
            
            const confirmClass = settings.isDangerous ? 'improveseo-btn-danger' : 'improveseo-btn-primary';
            
            const html = `
                <div class="improveseo-modal-overlay active" id="improveseo-confirmation-modal" data-dismissible="true">
                    <div class="improveseo-modal">
                        <button class="improveseo-modal-close" onclick="ImproveSEOModal.closeModal('improveseo-confirmation-modal')"></button>
                        <div class="improveseo-modal-header">
                            <div class="improveseo-modal-icon">
                                <img src="${improveseModalVars.logoUrl}" alt="ImproveSEO">
                                <span class="icon-badge warning">⚠️</span>
                            </div>
                            <h2>${title}</h2>
                        </div>
                        <div class="improveseo-modal-body">
                            <p class="confirmation-message">${message}</p>
                        </div>
                        <div class="improveseo-modal-actions">
                            <button class="improveseo-btn ${confirmClass}" onclick="ImproveSEOModal.handleConfirm('improveseo-confirmation-modal')">
                                ${settings.confirmText}
                            </button>
                            <button class="improveseo-btn improveseo-btn-secondary" onclick="ImproveSEOModal.closeModal('improveseo-confirmation-modal')">
                                ${settings.cancelText}
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(html);
            
            window.improveseoConfirmCallback = onConfirm;
        },

        /**
         * Close Modal
         */
        closeModal: function(modalId) {
            const $modal = $('#' + modalId);
            $modal.find('.improveseo-modal').css('animation', 'slideUp 0.3s ease reverse');
            $modal.css('animation', 'fadeIn 0.3s ease reverse');
            
            setTimeout(function() {
                $modal.remove();
            }, 300);
        },

        /**
         * Handle Confirmation
         */
        handleConfirm: function(modalId) {
            if (typeof window.improveseoConfirmCallback === 'function') {
                window.improveseoConfirmCallback();
                window.improveseoConfirmCallback = null;
            }
            this.closeModal(modalId);
        },

        /**
         * Handle Retry
         */
        handleRetry: function(modalId) {
            if (typeof window.improveseoRetryCallback === 'function') {
                window.improveseoRetryCallback();
                window.improveseoRetryCallback = null;
            }
            this.closeModal(modalId);
        },

        /**
         * Show Toast Notification
         */
        showToast: function(message, type, duration) {
            type = type || 'success';
            duration = duration || 4000;
            
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };
            
            const titles = {
                success: 'Success',
                error: 'Error',
                warning: 'Warning',
                info: 'Info'
            };
            
            const html = `
                <div class="improveseo-toast ${type}" id="improveseo-toast-${Date.now()}">
                    <div class="improveseo-toast-icon">${icons[type]}</div>
                    <div class="improveseo-toast-content">
                        <div class="improveseo-toast-title">${titles[type]}</div>
                        <div class="improveseo-toast-message">${message}</div>
                    </div>
                    <button class="improveseo-toast-close" onclick="this.parentElement.classList.add('hiding'); setTimeout(() => this.parentElement.remove(), 300)">×</button>
                </div>
            `;
            
            const $toast = $(html);
            $('body').append($toast);
            
            setTimeout(function() {
                $toast.addClass('hiding');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            }, duration);
        }
    };

    // Close modal on overlay click
    $(document).on('click', '.improveseo-modal-overlay[data-dismissible="true"]', function(e) {
        if ($(e.target).hasClass('improveseo-modal-overlay')) {
            const modalId = $(this).attr('id');
            ImproveSEOModal.closeModal(modalId);
        }
    });

    // Close modal on ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            const $modal = $('.improveseo-modal-overlay[data-dismissible="true"]:last');
            if ($modal.length) {
                const modalId = $modal.attr('id');
                ImproveSEOModal.closeModal(modalId);
            }
        }
    });

})(jQuery);
