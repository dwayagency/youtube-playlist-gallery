<?php
/**
 * Plugin Name: YouTube Playlist Gallery
 * Plugin URI: https://github.com/dway/youtube-playlist-gallery
 * Description: Visualizza una gallery dei video di una playlist YouTube con lightbox, cache e layout multipli.
 * Version: 2.0.0
 * Author: DWAY AGENCY
 * Author URI: https://dway.agency
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: youtube-playlist-gallery
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('YPG_VERSION', '2.0.0');
define('YPG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YPG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YPG_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main YouTube Playlist Gallery Class
 */
class YouTube_Playlist_Gallery {

    private $option_name = 'ypg_settings';
    private $cache_group = 'ypg_cache';
    private $cache_expiration = 3600; // 1 hour default

    public function __construct() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Frontend hooks
        add_shortcode('youtube_playlist_gallery', array($this, 'render_gallery'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // AJAX hooks
        add_action('wp_ajax_ypg_load_more', array($this, 'ajax_load_more'));
        add_action('wp_ajax_nopriv_ypg_load_more', array($this, 'ajax_load_more'));
        add_action('wp_ajax_ypg_clear_cache', array($this, 'ajax_clear_cache'));
        
        // Widget
        add_action('widgets_init', array($this, 'register_widget'));
        
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('youtube-playlist-gallery', false, dirname(YPG_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('YouTube Playlist Gallery', 'youtube-playlist-gallery'),
            __('YT Playlist Gallery', 'youtube-playlist-gallery'),
            'manage_options',
            'youtube-playlist-gallery',
            array($this, 'options_page')
        );
    }

    /**
     * Register settings
     */
    public function settings_init() {
        register_setting('ypg_plugin', $this->option_name, array($this, 'sanitize_settings'));

        // API Settings Section
        add_settings_section(
            'ypg_api_section',
            __('Impostazioni API YouTube', 'youtube-playlist-gallery'),
            array($this, 'api_section_callback'),
            'youtube_playlist_gallery'
        );

        add_settings_field(
            'api_key',
            __('YouTube API Key', 'youtube-playlist-gallery'),
            array($this, 'api_key_render'),
            'youtube_playlist_gallery',
            'ypg_api_section'
        );

        // Display Settings Section
        add_settings_section(
            'ypg_display_section',
            __('Impostazioni Visualizzazione', 'youtube-playlist-gallery'),
            null,
            'youtube_playlist_gallery'
        );

        add_settings_field(
            'default_layout',
            __('Layout Predefinito', 'youtube-playlist-gallery'),
            array($this, 'default_layout_render'),
            'youtube_playlist_gallery',
            'ypg_display_section'
        );

        add_settings_field(
            'default_columns',
            __('Colonne Predefinite', 'youtube-playlist-gallery'),
            array($this, 'default_columns_render'),
            'youtube_playlist_gallery',
            'ypg_display_section'
        );

        add_settings_field(
            'lightbox_enabled',
            __('Abilita Lightbox', 'youtube-playlist-gallery'),
            array($this, 'lightbox_enabled_render'),
            'youtube_playlist_gallery',
            'ypg_display_section'
        );

        add_settings_field(
            'show_title',
            __('Mostra Titolo Video', 'youtube-playlist-gallery'),
            array($this, 'show_title_render'),
            'youtube_playlist_gallery',
            'ypg_display_section'
        );

        add_settings_field(
            'show_description',
            __('Mostra Descrizione', 'youtube-playlist-gallery'),
            array($this, 'show_description_render'),
            'youtube_playlist_gallery',
            'ypg_display_section'
        );

        // Cache Settings Section
        add_settings_section(
            'ypg_cache_section',
            __('Impostazioni Cache', 'youtube-playlist-gallery'),
            array($this, 'cache_section_callback'),
            'youtube_playlist_gallery'
        );

        add_settings_field(
            'cache_enabled',
            __('Abilita Cache', 'youtube-playlist-gallery'),
            array($this, 'cache_enabled_render'),
            'youtube_playlist_gallery',
            'ypg_cache_section'
        );

        add_settings_field(
            'cache_duration',
            __('Durata Cache (ore)', 'youtube-playlist-gallery'),
            array($this, 'cache_duration_render'),
            'youtube_playlist_gallery',
            'ypg_cache_section'
        );
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();

        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field(trim($input['api_key']));
        }

        if (isset($input['default_layout'])) {
            $sanitized['default_layout'] = sanitize_text_field($input['default_layout']);
        }

        if (isset($input['default_columns'])) {
            $sanitized['default_columns'] = absint($input['default_columns']);
        }

        if (isset($input['lightbox_enabled'])) {
            $sanitized['lightbox_enabled'] = (bool)$input['lightbox_enabled'];
        }

        if (isset($input['show_title'])) {
            $sanitized['show_title'] = (bool)$input['show_title'];
        }

        if (isset($input['show_description'])) {
            $sanitized['show_description'] = (bool)$input['show_description'];
        }

        if (isset($input['cache_enabled'])) {
            $sanitized['cache_enabled'] = (bool)$input['cache_enabled'];
        }

        if (isset($input['cache_duration'])) {
            $sanitized['cache_duration'] = absint($input['cache_duration']);
        }

        return $sanitized;
    }

    /**
     * Section callbacks
     */
    public function api_section_callback() {
        echo '<p>' . __('Configura la tua API key di YouTube per accedere alle playlist. Ottieni la tua API key da:', 'youtube-playlist-gallery') . ' <a href="https://console.developers.google.com/" target="_blank">Google Cloud Console</a></p>';
    }

    public function cache_section_callback() {
        echo '<p>' . __('La cache riduce le chiamate API e migliora le performance.', 'youtube-playlist-gallery') . '</p>';
        echo '<button type="button" class="button" id="ypg-clear-cache">' . __('Svuota Cache', 'youtube-playlist-gallery') . '</button>';
        echo '<span id="ypg-cache-status" style="margin-left: 10px;"></span>';
    }

    /**
     * Field renders
     */
    public function api_key_render() {
        $options = get_option($this->option_name);
        $value = isset($options['api_key']) ? esc_attr($options['api_key']) : '';
        ?>
        <input type='text' 
               name='<?php echo esc_attr($this->option_name); ?>[api_key]' 
               value='<?php echo $value; ?>' 
               class="regular-text"
               placeholder="AIzaSy...">
        <p class="description"><?php _e('Inserisci la tua YouTube Data API v3 key.', 'youtube-playlist-gallery'); ?></p>
        <?php
    }

    public function default_layout_render() {
        $options = get_option($this->option_name);
        $value = isset($options['default_layout']) ? $options['default_layout'] : 'grid';
        ?>
        <select name='<?php echo esc_attr($this->option_name); ?>[default_layout]'>
            <option value='grid' <?php selected($value, 'grid'); ?>><?php _e('Griglia', 'youtube-playlist-gallery'); ?></option>
            <option value='list' <?php selected($value, 'list'); ?>><?php _e('Lista', 'youtube-playlist-gallery'); ?></option>
            <option value='masonry' <?php selected($value, 'masonry'); ?>><?php _e('Masonry', 'youtube-playlist-gallery'); ?></option>
            <option value='carousel' <?php selected($value, 'carousel'); ?>><?php _e('Carosello', 'youtube-playlist-gallery'); ?></option>
        </select>
        <?php
    }

    public function default_columns_render() {
        $options = get_option($this->option_name);
        $value = isset($options['default_columns']) ? $options['default_columns'] : 3;
        ?>
        <input type='number' 
               name='<?php echo esc_attr($this->option_name); ?>[default_columns]' 
               value='<?php echo esc_attr($value); ?>' 
               min='1' 
               max='6'
               class="small-text">
        <p class="description"><?php _e('Numero di colonne per il layout a griglia (1-6)', 'youtube-playlist-gallery'); ?></p>
        <?php
    }

    public function lightbox_enabled_render() {
        $options = get_option($this->option_name);
        $value = isset($options['lightbox_enabled']) ? $options['lightbox_enabled'] : true;
        ?>
        <label>
            <input type='checkbox' 
                   name='<?php echo esc_attr($this->option_name); ?>[lightbox_enabled]' 
                   value='1' 
                   <?php checked($value, true); ?>>
            <?php _e('Apri video in lightbox invece che in nuova finestra', 'youtube-playlist-gallery'); ?>
        </label>
        <?php
    }

    public function show_title_render() {
        $options = get_option($this->option_name);
        $value = isset($options['show_title']) ? $options['show_title'] : true;
        ?>
        <label>
            <input type='checkbox' 
                   name='<?php echo esc_attr($this->option_name); ?>[show_title]' 
                   value='1' 
                   <?php checked($value, true); ?>>
            <?php _e('Mostra il titolo del video sotto la thumbnail', 'youtube-playlist-gallery'); ?>
        </label>
        <?php
    }

    public function show_description_render() {
        $options = get_option($this->option_name);
        $value = isset($options['show_description']) ? $options['show_description'] : false;
        ?>
        <label>
            <input type='checkbox' 
                   name='<?php echo esc_attr($this->option_name); ?>[show_description]' 
                   value='1' 
                   <?php checked($value, true); ?>>
            <?php _e('Mostra la descrizione del video', 'youtube-playlist-gallery'); ?>
        </label>
        <?php
    }

    public function cache_enabled_render() {
        $options = get_option($this->option_name);
        $value = isset($options['cache_enabled']) ? $options['cache_enabled'] : true;
        ?>
        <label>
            <input type='checkbox' 
                   name='<?php echo esc_attr($this->option_name); ?>[cache_enabled]' 
                   value='1' 
                   <?php checked($value, true); ?>>
            <?php _e('Abilita il caching dei risultati API', 'youtube-playlist-gallery'); ?>
        </label>
        <?php
    }

    public function cache_duration_render() {
        $options = get_option($this->option_name);
        $value = isset($options['cache_duration']) ? $options['cache_duration'] : 1;
        ?>
        <input type='number' 
               name='<?php echo esc_attr($this->option_name); ?>[cache_duration]' 
               value='<?php echo esc_attr($value); ?>' 
               min='1' 
               max='168'
               class="small-text">
        <p class="description"><?php _e('Durata della cache in ore (1-168 ore = max 1 settimana)', 'youtube-playlist-gallery'); ?></p>
        <?php
    }

    /**
     * Options page
     */
    public function options_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="ypg-admin-wrapper">
                <div class="ypg-admin-main">
                    <form action='options.php' method='post'>
                        <?php
                        settings_fields('ypg_plugin');
                        do_settings_sections('youtube_playlist_gallery');
                        submit_button();
                        ?>
                    </form>
                </div>
                
                <div class="ypg-admin-sidebar">
                    <div class="ypg-box">
                        <h3><?php _e('Shortcode Generator', 'youtube-playlist-gallery'); ?></h3>
                        <p><?php _e('Genera uno shortcode personalizzato:', 'youtube-playlist-gallery'); ?></p>
                        
                        <label><?php _e('Playlist ID:', 'youtube-playlist-gallery'); ?></label>
                        <input type="text" id="ypg-sg-playlist" class="regular-text" placeholder="PLrAXtmErZgOeiKm4sgNOknGvNjby9efdf">
                        
                        <label><?php _e('Layout:', 'youtube-playlist-gallery'); ?></label>
                        <select id="ypg-sg-layout">
                            <option value="grid"><?php _e('Griglia', 'youtube-playlist-gallery'); ?></option>
                            <option value="list"><?php _e('Lista', 'youtube-playlist-gallery'); ?></option>
                            <option value="masonry"><?php _e('Masonry', 'youtube-playlist-gallery'); ?></option>
                            <option value="carousel"><?php _e('Carosello', 'youtube-playlist-gallery'); ?></option>
                        </select>
                        
                        <label><?php _e('Colonne:', 'youtube-playlist-gallery'); ?></label>
                        <input type="number" id="ypg-sg-columns" value="3" min="1" max="6" class="small-text">
                        
                        <label><?php _e('Max Risultati:', 'youtube-playlist-gallery'); ?></label>
                        <input type="number" id="ypg-sg-max" value="10" min="1" max="50" class="small-text">
                        
                        <label>
                            <input type="checkbox" id="ypg-sg-pagination">
                            <?php _e('Paginazione', 'youtube-playlist-gallery'); ?>
                        </label>
                        
                        <button type="button" class="button button-primary" id="ypg-generate-shortcode">
                            <?php _e('Genera Shortcode', 'youtube-playlist-gallery'); ?>
                        </button>
                        
                        <div id="ypg-shortcode-output" style="display:none; margin-top: 15px;">
                            <label><?php _e('Shortcode Generato:', 'youtube-playlist-gallery'); ?></label>
                            <input type="text" readonly class="regular-text" id="ypg-shortcode-text">
                            <button type="button" class="button" id="ypg-copy-shortcode"><?php _e('Copia', 'youtube-playlist-gallery'); ?></button>
                        </div>
                    </div>
                    
                    <div class="ypg-box">
                        <h3><?php _e('Documentazione', 'youtube-playlist-gallery'); ?></h3>
                        <p><?php _e('Esempio di utilizzo:', 'youtube-playlist-gallery'); ?></p>
                        <code>[youtube_playlist_gallery playlist_id="PLxxx..." max_results="12" layout="grid" columns="3"]</code>
                        
                        <h4><?php _e('Parametri disponibili:', 'youtube-playlist-gallery'); ?></h4>
                        <ul style="list-style: disc; padding-left: 20px;">
                            <li><strong>playlist_id</strong>: <?php _e('ID della playlist (obbligatorio)', 'youtube-playlist-gallery'); ?></li>
                            <li><strong>max_results</strong>: <?php _e('Numero massimo di video (default: 10)', 'youtube-playlist-gallery'); ?></li>
                            <li><strong>layout</strong>: <?php _e('grid, list, masonry, carousel (default: grid)', 'youtube-playlist-gallery'); ?></li>
                            <li><strong>columns</strong>: <?php _e('Numero di colonne 1-6 (default: 3)', 'youtube-playlist-gallery'); ?></li>
                            <li><strong>pagination</strong>: <?php _e('true/false (default: false)', 'youtube-playlist-gallery'); ?></li>
                            <li><strong>show_title</strong>: <?php _e('true/false (default: true)', 'youtube-playlist-gallery'); ?></li>
                            <li><strong>show_description</strong>: <?php _e('true/false (default: false)', 'youtube-playlist-gallery'); ?></li>
                        </ul>
                    </div>
                    
                    <div class="ypg-box ypg-support">
                        <h3><?php _e('Supporto', 'youtube-playlist-gallery'); ?></h3>
                        <p><?php _e('Hai bisogno di aiuto? Contatta dway per supporto.', 'youtube-playlist-gallery'); ?></p>
                        <p><strong><?php _e('Versione:', 'youtube-playlist-gallery'); ?></strong> <?php echo YPG_VERSION; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if ('settings_page_youtube-playlist-gallery' !== $hook) {
            return;
        }
        
        wp_enqueue_style('ypg-admin-styles', YPG_PLUGIN_URL . 'admin/admin-styles.css', array(), YPG_VERSION);
        wp_enqueue_script('ypg-admin-script', YPG_PLUGIN_URL . 'admin/admin-script.js', array('jquery'), YPG_VERSION, true);
        
        wp_localize_script('ypg-admin-script', 'ypgAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ypg_admin_nonce'),
            'strings' => array(
                'cache_cleared' => __('Cache svuotata con successo!', 'youtube-playlist-gallery'),
                'error' => __('Errore durante l\'operazione.', 'youtube-playlist-gallery'),
                'copied' => __('Shortcode copiato!', 'youtube-playlist-gallery'),
            )
        ));
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style('ypg-styles', YPG_PLUGIN_URL . 'css/ypg-styles.css', array(), YPG_VERSION);
        wp_enqueue_script('ypg-script', YPG_PLUGIN_URL . 'js/ypg-script.js', array('jquery'), YPG_VERSION, true);
        
        wp_localize_script('ypg-script', 'ypgData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ypg_nonce'),
        ));
    }

    /**
     * Get videos from YouTube API with caching
     */
    private function get_videos($playlist_id, $max_results = 10, $page_token = '') {
        $options = get_option($this->option_name);
        $cache_enabled = isset($options['cache_enabled']) ? $options['cache_enabled'] : true;
        $cache_duration = isset($options['cache_duration']) ? intval($options['cache_duration']) : 1;
        
        // Generate cache key
        $cache_key = 'ypg_' . md5($playlist_id . '_' . $max_results . '_' . $page_token);
        
        // Try to get from cache
        if ($cache_enabled) {
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
        }
        
        // Get API key
        if (empty($options['api_key'])) {
            return array('error' => __('API key non configurata.', 'youtube-playlist-gallery'));
        }
        $api_key = trim($options['api_key']);
        
        // Build API URL
        $api_params = array(
            'part' => 'snippet',
            'playlistId' => $playlist_id,
            'maxResults' => $max_results,
            'key' => $api_key,
        );
        
        if (!empty($page_token)) {
            $api_params['pageToken'] = $page_token;
        }
        
        $api_url = add_query_arg($api_params, 'https://www.googleapis.com/youtube/v3/playlistItems');
        
        // Make API request
        $response = wp_remote_get($api_url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return array('error' => __('Errore nel recuperare i video dalla API.', 'youtube-playlist-gallery'));
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Check for API errors
        if (isset($data['error'])) {
            $error_message = isset($data['error']['message']) ? $data['error']['message'] : __('Errore sconosciuto API', 'youtube-playlist-gallery');
            return array('error' => $error_message);
        }
        
        if (empty($data['items'])) {
            return array('error' => __('Nessun video trovato nella playlist.', 'youtube-playlist-gallery'));
        }
        
        // Cache the result
        if ($cache_enabled) {
            set_transient($cache_key, $data, $cache_duration * HOUR_IN_SECONDS);
        }
        
        return $data;
    }

    /**
     * Render gallery shortcode
     */
    public function render_gallery($atts) {
        $options = get_option($this->option_name);
        
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'playlist_id' => '',
            'max_results' => 10,
            'layout' => isset($options['default_layout']) ? $options['default_layout'] : 'grid',
            'columns' => isset($options['default_columns']) ? $options['default_columns'] : 3,
            'pagination' => false,
            'show_title' => isset($options['show_title']) ? $options['show_title'] : true,
            'show_description' => isset($options['show_description']) ? $options['show_description'] : false,
            'lightbox' => isset($options['lightbox_enabled']) ? $options['lightbox_enabled'] : true,
        ), $atts, 'youtube_playlist_gallery');
        
        // Sanitize
        $playlist_id = sanitize_text_field($atts['playlist_id']);
        $max_results = min(50, max(1, intval($atts['max_results'])));
        $layout = sanitize_text_field($atts['layout']);
        $columns = min(6, max(1, intval($atts['columns'])));
        $pagination = filter_var($atts['pagination'], FILTER_VALIDATE_BOOLEAN);
        $show_title = filter_var($atts['show_title'], FILTER_VALIDATE_BOOLEAN);
        $show_description = filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN);
        $lightbox = filter_var($atts['lightbox'], FILTER_VALIDATE_BOOLEAN);
        
        if (empty($playlist_id)) {
            return '<div class="ypg-error"><p>' . __('⚠️ Playlist ID non fornito.', 'youtube-playlist-gallery') . '</p></div>';
        }
        
        // Get videos
        $data = $this->get_videos($playlist_id, $max_results);
        
        if (isset($data['error'])) {
            return '<div class="ypg-error"><p>⚠️ ' . esc_html($data['error']) . '</p></div>';
        }
        
        // Generate unique ID for this gallery instance
        $gallery_id = 'ypg-' . uniqid();
        
        // Build HTML
        ob_start();
        ?>
        <div id="<?php echo esc_attr($gallery_id); ?>" 
             class="ypg-gallery-wrapper ypg-layout-<?php echo esc_attr($layout); ?>" 
             data-playlist="<?php echo esc_attr($playlist_id); ?>"
             data-max="<?php echo esc_attr($max_results); ?>"
             data-lightbox="<?php echo $lightbox ? '1' : '0'; ?>">
            
            <div class="ypg-gallery ypg-columns-<?php echo esc_attr($columns); ?>">
                <?php
                foreach ($data['items'] as $item) {
                    $video_id = $item['snippet']['resourceId']['videoId'];
                    $title = esc_html($item['snippet']['title']);
                    $description = isset($item['snippet']['description']) ? esc_html(wp_trim_words($item['snippet']['description'], 20)) : '';
                    $thumb = esc_url($item['snippet']['thumbnails']['medium']['url']);
                    $thumb_high = isset($item['snippet']['thumbnails']['high']['url']) ? esc_url($item['snippet']['thumbnails']['high']['url']) : $thumb;
                    
                    $link_attrs = $lightbox ? 
                        'data-video-id="' . esc_attr($video_id) . '" class="ypg-video-link"' : 
                        'href="https://www.youtube.com/watch?v=' . esc_attr($video_id) . '" target="_blank" rel="noopener"';
                    ?>
                    <div class="ypg-item">
                        <a <?php echo $link_attrs; ?>>
                            <div class="ypg-thumbnail">
                                <img src="<?php echo $thumb; ?>" 
                                     data-src-high="<?php echo $thumb_high; ?>"
                                     alt="<?php echo $title; ?>"
                                     loading="lazy">
                                <div class="ypg-play-overlay">
                                    <svg class="ypg-play-icon" viewBox="0 0 68 48" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#f00"></path>
                                        <path d="M 45,24 27,14 27,34" fill="#fff"></path>
                                    </svg>
                                </div>
                            </div>
                        </a>
                        <?php if ($show_title || $show_description): ?>
                            <div class="ypg-content">
                                <?php if ($show_title): ?>
                                    <h3 class="ypg-title"><?php echo $title; ?></h3>
                                <?php endif; ?>
                                <?php if ($show_description && !empty($description)): ?>
                                    <p class="ypg-description"><?php echo $description; ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php
                }
                ?>
            </div>
            
            <?php if ($pagination && isset($data['nextPageToken'])): ?>
                <div class="ypg-pagination">
                    <button class="ypg-load-more" data-next-token="<?php echo esc_attr($data['nextPageToken']); ?>">
                        <?php _e('Carica Altri Video', 'youtube-playlist-gallery'); ?>
                    </button>
                    <span class="ypg-loader" style="display:none;">
                        <span class="ypg-spinner"></span>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($lightbox): ?>
            <div id="ypg-lightbox" class="ypg-lightbox" style="display:none;">
                <div class="ypg-lightbox-overlay"></div>
                <div class="ypg-lightbox-content">
                    <button class="ypg-lightbox-close">&times;</button>
                    <div class="ypg-lightbox-video">
                        <iframe src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php
        
        return ob_get_clean();
    }

    /**
     * AJAX Load More
     */
    public function ajax_load_more() {
        check_ajax_referer('ypg_nonce', 'nonce');
        
        $playlist_id = isset($_POST['playlist_id']) ? sanitize_text_field($_POST['playlist_id']) : '';
        $max_results = isset($_POST['max_results']) ? intval($_POST['max_results']) : 10;
        $page_token = isset($_POST['page_token']) ? sanitize_text_field($_POST['page_token']) : '';
        
        if (empty($playlist_id)) {
            wp_send_json_error(array('message' => __('Playlist ID mancante', 'youtube-playlist-gallery')));
        }
        
        $data = $this->get_videos($playlist_id, $max_results, $page_token);
        
        if (isset($data['error'])) {
            wp_send_json_error(array('message' => $data['error']));
        }
        
        wp_send_json_success($data);
    }

    /**
     * AJAX Clear Cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer('ypg_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permessi insufficienti', 'youtube-playlist-gallery')));
        }
        
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ypg_%' OR option_name LIKE '_transient_timeout_ypg_%'");
        
        wp_send_json_success(array('message' => __('Cache svuotata con successo!', 'youtube-playlist-gallery')));
    }

    /**
     * Register widget
     */
    public function register_widget() {
        register_widget('YPG_Widget');
    }
}

/**
 * Widget Class
 */
class YPG_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'ypg_widget',
            __('YouTube Playlist Gallery', 'youtube-playlist-gallery'),
            array('description' => __('Mostra una playlist YouTube nella sidebar', 'youtube-playlist-gallery'))
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $playlist_id = !empty($instance['playlist_id']) ? $instance['playlist_id'] : '';
        $max_results = !empty($instance['max_results']) ? intval($instance['max_results']) : 5;
        
        if (!empty($playlist_id)) {
            echo do_shortcode('[youtube_playlist_gallery playlist_id="' . esc_attr($playlist_id) . '" max_results="' . esc_attr($max_results) . '" layout="list" columns="1"]');
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Video YouTube', 'youtube-playlist-gallery');
        $playlist_id = !empty($instance['playlist_id']) ? $instance['playlist_id'] : '';
        $max_results = !empty($instance['max_results']) ? $instance['max_results'] : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Titolo:', 'youtube-playlist-gallery'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('playlist_id')); ?>"><?php _e('Playlist ID:', 'youtube-playlist-gallery'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('playlist_id')); ?>" name="<?php echo esc_attr($this->get_field_name('playlist_id')); ?>" type="text" value="<?php echo esc_attr($playlist_id); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('max_results')); ?>"><?php _e('Numero Video:', 'youtube-playlist-gallery'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('max_results')); ?>" name="<?php echo esc_attr($this->get_field_name('max_results')); ?>" type="number" value="<?php echo esc_attr($max_results); ?>" min="1" max="20">
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['playlist_id'] = (!empty($new_instance['playlist_id'])) ? sanitize_text_field($new_instance['playlist_id']) : '';
        $instance['max_results'] = (!empty($new_instance['max_results'])) ? absint($new_instance['max_results']) : 5;
        return $instance;
    }
}

// Initialize the plugin
new YouTube_Playlist_Gallery();
