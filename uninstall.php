<?php
/**
 * Clean up plugin data on uninstall.
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'kidi_ap_settings' );
