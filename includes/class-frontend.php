<?php
/**
 * Frontend rendering for the accepted payments badge.
 *
 * Outputs the payment icons in the site footer via wp_footer hook.
 */

defined( 'ABSPATH' ) || exit;

class Kidi_AP_Frontend {

    /**
     * Hook into WordPress.
     */
    public static function register() {
        add_action( 'wp_footer', array( __CLASS__, 'render' ), 50 );
        add_action( 'wp_head', array( __CLASS__, 'inline_css' ), 90 );
    }

    /* ── CSS ───────────────────────────────────────────────── */

    /**
     * Output inline CSS in <head> (avoid FOUC).
     */
    public static function inline_css() {
        ?>
        <style id="kidi-ap-css">
        .kidi-ap { display:flex; align-items:center; justify-content:center; gap:10px; flex-wrap:wrap; margin-top:12px; }
        .kidi-ap__label { font-size:12px; color:#888; text-transform:uppercase; letter-spacing:.5px; font-weight:500; margin-right:2px; }
        .kidi-ap__icon { display:inline-flex; align-items:center; justify-content:center; width:50px; height:32px; border-radius:4px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); transition:transform .2s,box-shadow .2s; }
        .kidi-ap__icon:hover { transform:translateY(-1px); box-shadow:0 2px 6px rgba(0,0,0,.14); }
        .kidi-ap__icon svg { width:100%; height:100%; display:block; }
        </style>
        <?php
    }

    /* ── Render ─────────────────────────────────────────────── */

    /**
     * Output the payment badge HTML at wp_footer.
     */
    public static function render() {
        $settings = Kidi_AP_Customizer::get_settings();
        $methods  = Kidi_AP_Icons::labels();
        $label    = $settings['label'] ?? '';

        // Determine which icons to show.
        $active = array();

        if ( ! empty( $settings['auto_sync'] ) && Kidi_AP_Woo_Sync::is_woo_active() ) {
            // Auto-sync mode: icons from active WooCommerce gateways.
            $detected = Kidi_AP_Woo_Sync::detect();
            foreach ( $detected as $key ) {
                if ( isset( $methods[ $key ] ) ) {
                    $active[ $key ] = $methods[ $key ];
                }
            }
        } else {
            // Manual mode: use Customizer toggles.
            foreach ( $methods as $key => $name ) {
                if ( ! empty( $settings[ $key ] ) ) {
                    $active[ $key ] = $name;
                }
            }
        }

        // Nothing to show.
        if ( empty( $active ) ) {
            return;
        }

        // Render hidden source that JS will inject into footer.
        ?>
        <div id="kidi-ap-source" style="display:none;">
            <div class="kidi-ap" data-kidi-ap>
                <?php if ( $label ) : ?>
                    <span class="kidi-ap__label"><?php echo esc_html( $label ); ?></span>
                <?php endif; ?>
                <?php foreach ( $active as $key => $name ) : ?>
                    <span class="kidi-ap__icon" data-method="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr( $name ); ?>">
                        <?php echo Kidi_AP_Icons::get( $key ); // SVGs are hardcoded, safe output. ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        <script data-no-optimize="1">
        (function(){
            var src = document.getElementById('kidi-ap-source');
            if (!src) return;
            var badge = src.querySelector('[data-kidi-ap]');
            if (!badge) return;
            /* Try known Elementor footer containers, then generic footer. */
            var target =
                document.querySelector('.elementor-element-b60957c') ||
                (function(){
                    var eds = document.querySelectorAll('[data-elementor-type="footer"] .elementor-widget-text-editor, footer .elementor-widget-text-editor');
                    return eds.length ? eds[eds.length-1].closest('.e-con,.elementor-container') : null;
                })() ||
                document.querySelector('[data-elementor-type="footer"]') ||
                document.querySelector('footer');
            if (target) {
                target.appendChild(badge);
            } else {
                /* Absolute fallback: show in place. */
                src.style.display = '';
                return;
            }
            src.remove();
        })();
        </script>
        <?php
    }
}
