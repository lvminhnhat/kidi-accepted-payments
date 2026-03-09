<?php
/**
 * Customizer integration for Accepted Payments.
 *
 * Adds a panel at Appearance → Customize → Accepted Payments
 * with toggle controls for each payment method and a label field.
 */

defined( 'ABSPATH' ) || exit;

class Kidi_AP_Customizer {

    /** Option key storing all settings as a single associative array. */
    const OPTION = 'kidi_ap_settings';

    /**
     * Hook into the Customizer.
     */
    public static function register() {
        add_action( 'customize_register', array( __CLASS__, 'register_controls' ) );
        add_action( 'customize_preview_init', array( __CLASS__, 'enqueue_preview_js' ) );
    }

    /* ── Settings & Controls ───────────────────────────────── */

    /**
     * Register Customizer section, settings, and controls.
     *
     * @param WP_Customize_Manager $wp_customize Customizer manager.
     */
    public static function register_controls( $wp_customize ) {

        // ── Section ───────────────────────────────────────────
        $wp_customize->add_section( 'kidi_ap_section', array(
            'title'    => __( 'Accepted Payments', 'kidi-accepted-payments' ),
            'priority' => 200,
        ) );

        // ── Label setting ─────────────────────────────────────
        $wp_customize->add_setting( self::OPTION . '[label]', array(
            'type'              => 'option',
            'default'           => 'We Accept',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'postMessage',
        ) );

        $wp_customize->add_control( self::OPTION . '[label]', array(
            'label'   => __( 'Label Text', 'kidi-accepted-payments' ),
            'description' => __( 'Text shown before the icons. Leave empty to hide.', 'kidi-accepted-payments' ),
            'section' => 'kidi_ap_section',
            'type'    => 'text',
        ) );

        // ── Toggle for each payment method ────────────────────
        $labels = Kidi_AP_Icons::labels();
        $priority = 20;

        foreach ( $labels as $key => $name ) {
            $setting_id = self::OPTION . '[' . $key . ']';

            $wp_customize->add_setting( $setting_id, array(
                'type'              => 'option',
                'default'           => true,
                'sanitize_callback' => array( __CLASS__, 'sanitize_boolean' ),
                'transport'         => 'postMessage',
            ) );

            $wp_customize->add_control( $setting_id, array(
                'label'    => sprintf( /* translators: %s: payment method name */ __( 'Show %s', 'kidi-accepted-payments' ), $name ),
                'section'  => 'kidi_ap_section',
                'type'     => 'checkbox',
                'priority' => $priority,
            ) );

            $priority += 10;
        }
    }

    /* ── Live Preview JS ───────────────────────────────────── */

    /**
     * Enqueue the Customizer live-preview script.
     */
    public static function enqueue_preview_js() {
        wp_enqueue_script(
            'kidi-ap-customizer-preview',
            KIDI_AP_URL . 'assets/js/customizer-preview.js',
            array( 'customize-preview', 'jquery' ),
            KIDI_AP_VERSION,
            true
        );
    }

    /* ── Helpers ───────────────────────────────────────────── */

    /**
     * Get all current settings with defaults applied.
     *
     * @return array
     */
    public static function get_settings() {
        $defaults = array(
            'label'      => 'We Accept',
            'visa'       => true,
            'mastercard' => true,
            'amex'       => true,
            'paypal'     => true,
            'applepay'   => true,
            'googlepay'  => true,
        );

        $saved = get_option( self::OPTION, array() );

        return wp_parse_args( $saved, $defaults );
    }

    /**
     * Sanitize a boolean value from the Customizer.
     *
     * @param mixed $value Value to sanitize.
     * @return bool
     */
    public static function sanitize_boolean( $value ) {
        return (bool) $value;
    }
}
