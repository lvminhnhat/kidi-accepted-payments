<?php
/**
 * Plugin Name: KIDI Accepted Payments
 * Plugin URI:  https://customizedpajamas.com
 * Description: Display accepted payment method icons (Visa, Mastercard, Amex, PayPal, Apple Pay, Google Pay) in the site footer. Fully configurable via Appearance → Customize → Accepted Payments.
 * Version:     1.0.0
 * Author:      KIDI CUSTOM LLC
 * Author URI:  https://customizedpajamas.com
 * License:     GPL-2.0-or-later
 * Text Domain: kidi-accepted-payments
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'KIDI_AP_VERSION', '1.0.0' );
define( 'KIDI_AP_FILE', __FILE__ );
define( 'KIDI_AP_DIR', plugin_dir_path( __FILE__ ) );
define( 'KIDI_AP_URL', plugin_dir_url( __FILE__ ) );

/* ── Bootstrap ─────────────────────────────────────────────── */

/**
 * Load plugin classes on plugins_loaded (avoids side-effects at file-load time).
 */
add_action( 'plugins_loaded', 'kidi_ap_init' );

function kidi_ap_init() {
    require_once KIDI_AP_DIR . 'includes/class-icons.php';
    require_once KIDI_AP_DIR . 'includes/class-customizer.php';
    require_once KIDI_AP_DIR . 'includes/class-frontend.php';

    Kidi_AP_Customizer::register();
    Kidi_AP_Frontend::register();
}

/* ── Activation ────────────────────────────────────────────── */

register_activation_hook( __FILE__, 'kidi_ap_activate' );

function kidi_ap_activate() {
    // Set sensible defaults on first activation.
    if ( false === get_option( 'kidi_ap_settings' ) ) {
        update_option( 'kidi_ap_settings', array(
            'label'      => 'We Accept',
            'visa'       => true,
            'mastercard' => true,
            'amex'       => true,
            'paypal'     => true,
            'applepay'   => true,
            'googlepay'  => true,
        ) );
    }
}

/* ── Uninstall ─────────────────────────────────────────────── */
// Handled via uninstall.php for safety.
