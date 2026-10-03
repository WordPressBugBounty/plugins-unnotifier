<?php

declare(strict_types=1);

namespace UNNO\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the AdsDestroyer cross-promotion.
 */
class CrossPromoManager {
    private const PLUGIN_SLUG = 'ads-destroyer';
    private const PLUGIN_FILE = 'ads-destroyer/ads-destroyer.php';
    private const NOTICE_ID = 'unno_adsdestroyer_promo';
    private const DISMISSED_META_KEY = 'unno_adsdestroyer_promo_dismissed';
    private const BANNER_DISMISSED_META_KEY = 'unno_adsdestroyer_banner_dismissed';
    private const DISMISS_ACTION = 'unno_dismiss_adsdestroyer_promo';

    /**
     * Register promo hooks.
     */
    public function init(): void {
        if (!is_admin()) {
            return;
        }

        add_action('wp_ajax_' . self::DISMISS_ACTION, [$this, 'handle_dismiss']);

        if (!self::can_promote()) {
            return;
        }

        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_footer', [$this, 'render_footer_templates']);

        if ($this->should_show_notice()) {
            // An anonymous callback prevents Unnotifier from treating this as a foreign notice.
            add_action('admin_notices', function (): void {
                $this->render_notice();
            });
        }
    }

    /**
     * Whether the current user may see the promotion.
     */
    public static function can_promote(): bool {
        if (!is_admin() || is_network_admin() || !current_user_can('install_plugins')) {
            return false;
        }

        if (defined('UNNO_PROMO_DEBUG') && UNNO_PROMO_DEBUG === true) {
            return true;
        }

        return !self::is_target_installed();
    }

    /**
     * Whether AdsDestroyer is already installed.
     */
    public static function is_target_installed(): bool {
        return file_exists(trailingslashit(WP_PLUGIN_DIR) . self::PLUGIN_FILE);
    }

    /**
     * URL of the detailed promo tab.
     */
    public static function promo_tab_url(): string {
        return add_query_arg(
            [
                'page' => 'unnotifier',
                'tab' => 'adsdestroyer',
            ],
            admin_url('options-general.php')
        );
    }

    /**
     * Enqueue native installer support and promo assets.
     */
    public function enqueue_assets(string $hook): void {
        if ($hook !== 'settings_page_unnotifier' && !$this->should_show_notice()) {
            return;
        }

        wp_enqueue_style(
            'unnotifier-promo',
            UNNO_PLUGIN_URL . 'assets/css/promo.css',
            [],
            UNNO_VERSION
        );

        wp_enqueue_script(
            'unnotifier-promo',
            UNNO_PLUGIN_URL . 'assets/js/promo.js',
            ['jquery', 'updates'],
            UNNO_VERSION,
            true
        );

        wp_localize_script(
            'unnotifier-promo',
            'unnoPromo',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'dismissAction' => self::DISMISS_ACTION,
                'dismissNonce' => wp_create_nonce(self::DISMISS_ACTION),
                'installError' => __('AdsDestroyer could not be installed. Please try again.', 'unnotifier'),
            ]
        );
    }

    /**
     * Render a compact promo banner on regular settings tabs.
     */
    public static function render_banner(): void {
        if (!self::should_show_banner()) {
            return;
        }
        ?>
        <section class="unno-promo unno-promo-banner plugin-card-<?php echo esc_attr(self::PLUGIN_SLUG); ?>">
            <div class="unno-promo-banner__icon" aria-hidden="true">
                <span class="dashicons dashicons-hidden"></span>
            </div>
            <div class="unno-promo-banner__content">
                <strong><?php esc_html_e('Already using Unnotifier to hide admin notices?', 'unnotifier'); ?></strong>
                <span><?php esc_html_e('Want to go further? AdsDestroyer hides ads, menus, dashboard widgets, upsells, and any other element in WordPress admin.', 'unnotifier'); ?></span>
            </div>
            <div class="unno-promo-banner__actions">
                <a href="<?php echo esc_url(self::promo_tab_url()); ?>" class="button button-secondary">
                    <?php esc_html_e('Learn more', 'unnotifier'); ?>
                </a>
                <?php self::render_install_button(); ?>
            </div>
            <button type="button" class="notice-dismiss unno-promo-dismiss" data-promo-surface="banner">
                <span class="screen-reader-text"><?php esc_html_e('Dismiss this promotion', 'unnotifier'); ?></span>
            </button>
        </section>
        <?php
    }

    /**
     * Render the dismissible admin notice.
     */
    public function render_notice(): void {
        if (!$this->should_show_notice()) {
            return;
        }
        ?>
        <div
            id="<?php echo esc_attr(self::NOTICE_ID); ?>"
            class="unno-promo unno-promo-notice plugin-card-<?php echo esc_attr(self::PLUGIN_SLUG); ?> notice notice-info is-dismissible"
            data-unno-ignore="true"
        >
            <div class="unno-promo-notice__content">
                <span class="dashicons dashicons-hidden" aria-hidden="true"></span>
                <p>
                    <strong><?php esc_html_e('Already using Unnotifier to hide admin notices?', 'unnotifier'); ?></strong>
                    <?php esc_html_e('Want to go further? AdsDestroyer hides ads, menus, dashboard widgets, upsells, and any other element in WordPress admin.', 'unnotifier'); ?>
                </p>
                <div class="unno-promo-notice__actions">
                    <a href="<?php echo esc_url(self::promo_tab_url()); ?>">
                        <?php esc_html_e('See how it works', 'unnotifier'); ?>
                    </a>
                    <?php self::render_install_button(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render an install button compatible with WordPress updates.js.
     *
     * @param string $extra_class Additional CSS class.
     */
    public static function render_install_button(string $extra_class = ''): void {
        if (!self::can_promote()) {
            return;
        }

        $install_url = wp_nonce_url(
            self_admin_url('update.php?action=install-plugin&plugin=' . self::PLUGIN_SLUG),
            'install-plugin_' . self::PLUGIN_SLUG
        );
        ?>
        <a
            href="<?php echo esc_url($install_url); ?>"
            class="install-now button button-primary <?php echo esc_attr($extra_class); ?>"
            data-slug="<?php echo esc_attr(self::PLUGIN_SLUG); ?>"
            data-name="<?php esc_attr_e('AdsDestroyer', 'unnotifier'); ?>"
            aria-label="<?php esc_attr_e('Install AdsDestroyer now', 'unnotifier'); ?>"
        >
            <?php esc_html_e('Install AdsDestroyer', 'unnotifier'); ?>
        </a>
        <?php
    }

    /**
     * Print templates needed by the native filesystem credentials flow.
     */
    public function render_footer_templates(): void {
        static $rendered = false;

        if ($rendered || !self::can_promote() || !wp_script_is('unnotifier-promo', 'enqueued')) {
            return;
        }
        $rendered = true;

        if (!function_exists('wp_print_request_filesystem_credentials_modal')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        if (!function_exists('wp_print_admin_notice_templates')) {
            require_once ABSPATH . 'wp-admin/includes/update.php';
        }

        wp_print_request_filesystem_credentials_modal();
        wp_print_admin_notice_templates();
    }

    /**
     * Permanently dismiss the admin notice for the current user.
     */
    public function handle_dismiss(): void {
        if (!check_ajax_referer(self::DISMISS_ACTION, '_wpnonce', false)) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'unnotifier')], 403);
        }

        if (!current_user_can('install_plugins')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'unnotifier')], 403);
        }

        $surface = isset($_POST['surface']) ? sanitize_key(wp_unslash($_POST['surface'])) : 'notice';
        $meta_key = $surface === 'banner'
            ? self::BANNER_DISMISSED_META_KEY
            : self::DISMISSED_META_KEY;

        update_user_meta(get_current_user_id(), $meta_key, '1');
        wp_send_json_success(['message' => __('Promotion dismissed.', 'unnotifier')]);
    }

    /**
     * Decide whether the settings banner should be displayed.
     */
    private static function should_show_banner(): bool {
        if (!self::can_promote()) {
            return false;
        }

        return !get_user_meta(get_current_user_id(), self::BANNER_DISMISSED_META_KEY, true);
    }

    /**
     * Decide whether the global notice belongs on the current screen.
     */
    private function should_show_notice(): bool {
        if (!self::can_promote()) {
            return false;
        }

        if (get_user_meta(get_current_user_id(), self::DISMISSED_META_KEY, true)) {
            return false;
        }

        global $pagenow;
        if (in_array($pagenow, ['plugin-install.php', 'plugins.php', 'update.php', 'update-core.php'], true)) {
            return false;
        }

        $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
        return $page !== 'unnotifier';
    }
}
