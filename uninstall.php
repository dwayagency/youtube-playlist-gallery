<?php
/**
 * Uninstall YouTube Playlist Gallery
 * 
 * Fired when the plugin is uninstalled.
 * 
 * @package YouTube_Playlist_Gallery
 * @version 2.0.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete plugin options
 */
delete_option('ypg_settings');

/**
 * Delete all transients (cache)
 */
global $wpdb;

// Delete all transients with ypg_ prefix
$wpdb->query(
    "DELETE FROM {$wpdb->options} 
    WHERE option_name LIKE '_transient_ypg_%' 
    OR option_name LIKE '_transient_timeout_ypg_%'"
);

/**
 * Delete widget settings
 * Widget settings are stored with widget_ prefix
 */
$wpdb->query(
    "DELETE FROM {$wpdb->options} 
    WHERE option_name LIKE 'widget_ypg_widget%'"
);

/**
 * Clear any cached data
 */
wp_cache_flush();

// Optional: Remove user meta if you store any user-specific data
// delete_metadata('user', 0, 'ypg_user_meta_key', '', true);

