<?php
/**
 * Frontend rendering for the accepted payments badge.
 *
 * Primary strategy: inject badge directly into the Elementor footer via
 * the `elementor/widget/render_content` filter (server-side, no JS).
 * Fallback: render at wp_footer for non-Elementor themes.
 */

defined( 'ABSPATH' ) || exit;

class Kidi_AP_Frontend {

	/** @var bool Whether the badge has already been rendered. */
	private static $rendered = false;

	/**
	 * Hook into WordPress.
	 */
	public static function register() {
		add_action( 'wp_head', array( __CLASS__, 'inline_css' ), 90 );

		// Primary: server-side injection into Elementor footer widget.
		add_filter( 'elementor/widget/render_content', array( __CLASS__, 'inject_into_footer_widget' ), 10, 2 );

		// Fallback: wp_footer for non-Elementor themes or if the filter never fires.
		add_action( 'wp_footer', array( __CLASS__, 'render_fallback' ), 50 );
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

	/* ── Badge HTML builder ─────────────────────────────────── */

	/**
	 * Build the badge HTML string.
	 *
	 * @return string Empty string if nothing to show.
	 */
	public static function get_badge_html() {
		$settings = Kidi_AP_Customizer::get_settings();
		$methods  = Kidi_AP_Icons::labels();
		$label    = $settings['label'] ?? '';

		// Determine which icons to show.
		$active = array();

		if ( ! empty( $settings['auto_sync'] ) && Kidi_AP_Woo_Sync::is_woo_active() ) {
			$detected = Kidi_AP_Woo_Sync::detect();
			foreach ( $detected as $key ) {
				if ( isset( $methods[ $key ] ) ) {
					$active[ $key ] = $methods[ $key ];
				}
			}
		} else {
			foreach ( $methods as $key => $name ) {
				if ( ! empty( $settings[ $key ] ) ) {
					$active[ $key ] = $name;
				}
			}
		}

		if ( empty( $active ) ) {
			return '';
		}

		$html  = '<div class="kidi-ap" data-kidi-ap>';
		if ( $label ) {
			$html .= '<span class="kidi-ap__label">' . esc_html( $label ) . '</span>';
		}
		foreach ( $active as $key => $name ) {
			$html .= '<span class="kidi-ap__icon" data-method="' . esc_attr( $key ) . '" title="' . esc_attr( $name ) . '">';
			$html .= Kidi_AP_Icons::get( $key ); // SVGs are hardcoded, safe output.
			$html .= '</span>';
		}
		$html .= '</div>';

		return $html;
	}

	/* ── Primary: Elementor server-side injection ───────────── */

	/**
	 * Filter callback: append badge to the copyright text widget
	 * inside the Elementor footer template.
	 *
	 * Target: widget ID 32001c9 within footer template 3698.
	 * Falls through harmlessly for any other widget.
	 *
	 * @param string              $content Widget HTML content.
	 * @param \Elementor\Widget_Base $widget  Widget instance.
	 * @return string
	 */
	public static function inject_into_footer_widget( $content, $widget ) {
		// Only once.
		if ( self::$rendered ) {
			return $content;
		}

		// Only on the frontend (not editor/preview).
		if ( defined( 'ELEMENTOR_VERSION' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return $content;
		}

		// Target the copyright text widget by element ID.
		if ( $widget->get_id() !== '32001c9' ) {
			return $content;
		}

		$badge = self::get_badge_html();
		if ( empty( $badge ) ) {
			return $content;
		}

		self::$rendered = true;

		return $content . $badge;
	}

	/* ── Fallback: wp_footer (non-Elementor) ────────────────── */

	/**
	 * Render badge at wp_footer if Elementor injection did not fire.
	 */
	public static function render_fallback() {
		if ( self::$rendered ) {
			return;
		}

		$badge = self::get_badge_html();
		if ( empty( $badge ) ) {
			return;
		}

		self::$rendered = true;

		echo '<div style="text-align:center; padding:10px 0; background:#1a1a1a;">' . $badge . '</div>';
	}
}
