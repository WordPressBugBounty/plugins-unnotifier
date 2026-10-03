(function ($) {
    'use strict';

    var promoSelector = '.unno-promo';

    $(document).on('click', promoSelector + ' .install-now', function (event) {
        var $button = $(this);
        var slug = $button.data('slug');

        if (
            !slug ||
            $button.hasClass('updating-message') ||
            $button.hasClass('button-disabled') ||
            !window.wp ||
            !wp.updates
        ) {
            return;
        }

        event.preventDefault();
        wp.updates.maybeRequestFilesystemCredentials(event);
        wp.updates.$elToReturnFocusToFromCredentialsModal = $button;
        var request = wp.updates.installPlugin({ slug: slug });

        // Keep the native callback as the source of truth. The promise fallback
        // covers custom admin screens where WordPress may not run it itself.
        if (request && typeof request.done === 'function') {
            request.done(function (response) {
                var selector = '.plugin-card-' + response.slug + ' .install-now.updating-message';
                if ($(selector).length) {
                    wp.updates.installPluginSuccess(response);
                }
            });
        }
    });

    $(document).on('click', '.unno-promo-notice .notice-dismiss, .unno-promo-banner .notice-dismiss', function () {
        if (!window.unnoPromo) {
            return;
        }

        var $promo = $(this).closest('.unno-promo-notice, .unno-promo-banner');
        var surface = $(this).data('promo-surface') || 'notice';

        if ($promo.hasClass('unno-promo-banner')) {
            $promo.fadeOut(200, function () {
                $(this).remove();
            });
        }

        $.post(unnoPromo.ajaxUrl || window.ajaxurl, {
            action: unnoPromo.dismissAction,
            _wpnonce: unnoPromo.dismissNonce,
            surface: surface
        });
    });

    $(document).on('wp-plugin-install-error', function (event, response) {
        if (!response || response.slug !== 'ads-destroyer' || !window.unnoPromo) {
            return;
        }

        $('.plugin-card-ads-destroyer .install-now')
            .attr('title', unnoPromo.installError);
    });
})(jQuery);
