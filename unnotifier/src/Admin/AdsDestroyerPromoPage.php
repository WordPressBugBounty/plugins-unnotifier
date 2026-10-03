<?php

declare(strict_types=1);

namespace UNNO\Admin;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Renders the AdsDestroyer feature overview.
 */
class AdsDestroyerPromoPage {
    /**
     * Render the promo landing page.
     */
    public function render(): void {
        if (!CrossPromoManager::can_promote()) {
            return;
        }

        $image_url = trailingslashit(UNNO_PLUGIN_URL) . 'assets/images/promo/';
        ?>
        <div class="unno-promo unno-promo-page plugin-card-ads-destroyer">
            <section class="unno-promo-hero">
                <div class="unno-promo-hero__eyebrow"><?php esc_html_e('A cleaner WordPress admin for you and your clients', 'unnotifier'); ?></div>
                <h2><?php esc_html_e('Turn a cluttered admin into a clean, branded workspace.', 'unnotifier'); ?></h2>
                <p>
                    <?php esc_html_e('Tired of plugin ads, upgrade prompts, crowded menus, and widgets your clients never use? AdsDestroyer lets you hide them with a click—without editing code or changing the plugins that power the site.', 'unnotifier'); ?>
                </p>
                <div class="unno-promo-hero__actions">
                    <?php CrossPromoManager::render_install_button('button-hero'); ?>
                    <a
                        href="https://wordpress.org/plugins/ads-destroyer/"
                        class="button button-secondary button-hero"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?php esc_html_e('View on WordPress.org', 'unnotifier'); ?>
                    </a>
                </div>
            </section>

            <?php
            $this->render_feature(
                __('Point, Click, Gone', 'unnotifier'),
                __('Stop hunting through settings or adding fragile snippets. Start from the admin bar, point at the distraction, and hide it. You see exactly what will disappear before you confirm.', 'unnotifier'),
                [
                    __('Remove distracting ads and upgrade prompts', 'unnotifier'),
                    __('Hide dashboard widgets nobody uses', 'unnotifier'),
                    __('Clean up unwanted blocks without writing code', 'unnotifier'),
                    __('Undo or change your choices whenever you need', 'unnotifier'),
                ],
                $image_url . 'hide-ads.jpg',
                __('A promotional banner in the WordPress admin selected and ready to be hidden.', 'unnotifier'),
                false
            );

            $this->render_feature(
                __('A Client-Friendly White-Label Dashboard', 'unnotifier'),
                __('Clients should see your service—not a wall of plugin brands, sales messages, and tools they should never touch. Create a focused admin experience that feels intentional and professional.', 'unnotifier'),
                [
                    __('Remove third-party branding and promotional blocks', 'unnotifier'),
                    __('Show clients only the tools they actually need', 'unnotifier'),
                    __('Reduce confusion, mistakes, and support questions', 'unnotifier'),
                    __('Deliver a cleaner experience under your own brand', 'unnotifier'),
                ],
                $image_url . 'white-label.jpg',
                __('Third-party branded dashboard widgets and menu items marked for removal.', 'unnotifier'),
                true
            );

            $this->render_feature(
                __('Hide It for as Long as You Need', 'unnotifier'),
                __('Some distractions should disappear forever. Others only need to stay out of the way during a launch, client handoff, or focused work session. Choose the right duration in seconds.', 'unnotifier'),
                [
                    __('Hide permanently for a lasting cleanup', 'unnotifier'),
                    __('Remove temporary messages for an hour or a day', 'unnotifier'),
                    __('Keep campaigns and upsells away for weeks or months', 'unnotifier'),
                    __('Bring anything back by editing or deleting its rule', 'unnotifier'),
                ],
                $image_url . 'hide-duration.jpg',
                __('Duration menu for choosing how long a promotional block stays hidden.', 'unnotifier'),
                false
            );

            $this->render_feature(
                __('Trim Bloated Menus and the Admin Bar', 'unnotifier'),
                __('Every plugin adds another menu item, badge, and toolbar icon until the admin becomes a maze. Cut the entries nobody opens and give the interface room to breathe—on every screen, without breaking the page.', 'unnotifier'),
                [
                    __('Remove menu items and submenus nobody opens', 'unnotifier'),
                    __('Clear leftover icons and counters from the admin bar', 'unnotifier'),
                    __('Drop plugin badges that demand attention', 'unnotifier'),
                    __('Keep the rest of the page exactly where it belongs', 'unnotifier'),
                ],
                $image_url . 'admin-bar-cleanup.jpg',
                __('Cluttered admin bar items marked for removal above a clean admin page.', 'unnotifier'),
                true
            );
            ?>

            <section class="unno-promo-final">
                <span class="dashicons dashicons-hidden" aria-hidden="true"></span>
                <div>
                    <h2><?php esc_html_e('Give clients a WordPress dashboard that feels like your product.', 'unnotifier'); ?></h2>
                    <p><?php esc_html_e('Install AdsDestroyer and remove the clutter standing between users and their work.', 'unnotifier'); ?></p>
                </div>
                <?php CrossPromoManager::render_install_button('button-hero'); ?>
            </section>
        </div>
        <?php
    }

    /**
     * Render one feature row.
     *
     * @param string[] $items Feature bullets.
     */
    private function render_feature(
        string $title,
        string $description,
        array $items,
        string $image,
        string $image_alt,
        bool $reverse
    ): void {
        $classes = 'unno-promo-feature';
        if ($reverse) {
            $classes .= ' unno-promo-feature--reverse';
        }
        if ($image === '') {
            $classes .= ' unno-promo-feature--text-only';
        }
        ?>
        <section class="<?php echo esc_attr($classes); ?>">
            <div class="unno-promo-feature__copy">
                <h2><?php echo esc_html($title); ?></h2>
                <p><?php echo esc_html($description); ?></p>
                <ul>
                    <?php foreach ($items as $item): ?>
                        <li>
                            <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
                            <?php echo esc_html($item); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php if ($image !== ''): ?>
                <figure class="unno-promo-feature__media">
                    <img
                        src="<?php echo esc_url($image); ?>"
                        alt="<?php echo esc_attr($image_alt); ?>"
                        width="1200"
                        height="800"
                        loading="lazy"
                    >
                </figure>
            <?php endif; ?>
        </section>
        <?php
    }
}
