<?php
/**
 * Plugin Name: YouTube Playlist Gallery
 * Plugin URI: https://github.com/dway/youtube-playlist-gallery
 * Description: Visualizza una gallery dei video di una playlist YouTube con lightbox, cache e layout multipli. Gestisci multiple playlist.
 * Version: 2.1.0
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
define('YPG_VERSION', '2.1.0');
define('YPG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YPG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YPG_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main YouTube Playlist Gallery Class
 */
class YouTube_Playlist_Gallery {

    private $option_name = 'ypg_settings';
    private $table_name;
    private $cache_group = 'ypg_cache';
    private $cache_expiration = 3600;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ypg_playlists';
        
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('admin_post_ypg_save_playlist', array($this, 'save_playlist'));
        add_action('admin_post_ypg_delete_playlist', array($this, 'delete_playlist'));
        
        // Frontend hooks
        add_shortcode('youtube_playlist_gallery', array($this, 'render_gallery'));
        add_shortcode('ypg_playlist', array($this, 'render_saved_playlist'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // AJAX hooks
        add_action('wp_ajax_ypg_load_more', array($this, 'ajax_load_more'));
        add_action('wp_ajax_nopriv_ypg_load_more', array($this, 'ajax_load_more'));
        add_action('wp_ajax_ypg_clear_cache', array($this, 'ajax_clear_cache'));
        add_action('wp_ajax_ypg_duplicate_playlist', array($this, 'ajax_duplicate_playlist'));
        
        // Widget
        add_action('widgets_init', array($this, 'register_widget'));
        
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            playlist_id varchar(255) NOT NULL,
            layout varchar(50) DEFAULT 'grid',
            columns tinyint(1) DEFAULT 3,
            max_results smallint(3) DEFAULT 10,
            show_title tinyint(1) DEFAULT 1,
            show_description tinyint(1) DEFAULT 0,
            lightbox tinyint(1) DEFAULT 1,
            pagination tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Set default options
        if (!get_option($this->option_name)) {
            add_option($this->option_name, array(
                'api_key' => '',
                'cache_enabled' => true,
                'cache_duration' => 1,
            ));
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Cleanup if needed
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain('youtube-playlist-gallery', false, dirname(YPG_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Add admin menu - MAIN MENU (not in settings)
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            __('YT Playlists', 'youtube-playlist-gallery'),
            __('YT Playlists', 'youtube-playlist-gallery'),
            'manage_options',
            'youtube-playlist-gallery',
            array($this, 'playlists_page'),
            'dashicons-video-alt3',
            30
        );
        
        // Submenu: All Playlists
        add_submenu_page(
            'youtube-playlist-gallery',
            __('Tutte le Playlist', 'youtube-playlist-gallery'),
            __('Tutte le Playlist', 'youtube-playlist-gallery'),
            'manage_options',
            'youtube-playlist-gallery',
            array($this, 'playlists_page')
        );
        
        // Submenu: Add New
        add_submenu_page(
            'youtube-playlist-gallery',
            __('Aggiungi Nuova', 'youtube-playlist-gallery'),
            __('Aggiungi Nuova', 'youtube-playlist-gallery'),
            'manage_options',
            'youtube-playlist-gallery-add',
            array($this, 'add_playlist_page')
        );
        
        // Submenu: Settings
        add_submenu_page(
            'youtube-playlist-gallery',
            __('Impostazioni', 'youtube-playlist-gallery'),
            __('Impostazioni', 'youtube-playlist-gallery'),
            'manage_options',
            'youtube-playlist-gallery-settings',
            array($this, 'settings_page')
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
     * All Playlists page
     */
    public function playlists_page() {
        global $wpdb;
        
        // Handle edit
        $edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
        if ($edit_id) {
            $this->edit_playlist_page($edit_id);
            return;
        }
        
        $playlists = $wpdb->get_results("SELECT * FROM {$this->table_name} ORDER BY created_at DESC");
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Tutte le Playlist', 'youtube-playlist-gallery'); ?></h1>
            <a href="<?php echo admin_url('admin.php?page=youtube-playlist-gallery-add'); ?>" class="page-title-action">
                <?php _e('Aggiungi Nuova', 'youtube-playlist-gallery'); ?>
            </a>
            
            <?php if (isset($_GET['deleted']) && $_GET['deleted'] == '1'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Playlist eliminata con successo!', 'youtube-playlist-gallery'); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['duplicated']) && $_GET['duplicated'] == '1'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Playlist duplicata con successo!', 'youtube-playlist-gallery'); ?></p>
                </div>
            <?php endif; ?>
            
            <hr class="wp-header-end">
            
            <?php if (empty($playlists)): ?>
                <div class="ypg-empty-state">
                    <div class="ypg-empty-icon">📺</div>
                    <h2><?php _e('Nessuna playlist ancora', 'youtube-playlist-gallery'); ?></h2>
                    <p><?php _e('Crea la tua prima playlist YouTube per iniziare!', 'youtube-playlist-gallery'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=youtube-playlist-gallery-add'); ?>" class="button button-primary button-hero">
                        <?php _e('Crea Prima Playlist', 'youtube-playlist-gallery'); ?>
                    </a>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th width="30%"><?php _e('Nome', 'youtube-playlist-gallery'); ?></th>
                            <th width="25%"><?php _e('Playlist ID', 'youtube-playlist-gallery'); ?></th>
                            <th width="15%"><?php _e('Layout', 'youtube-playlist-gallery'); ?></th>
                            <th width="15%"><?php _e('Shortcode', 'youtube-playlist-gallery'); ?></th>
                            <th width="15%"><?php _e('Azioni', 'youtube-playlist-gallery'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($playlists as $playlist): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php echo admin_url('admin.php?page=youtube-playlist-gallery&edit=' . $playlist->id); ?>">
                                            <?php echo esc_html($playlist->name); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td><code><?php echo esc_html($playlist->playlist_id); ?></code></td>
                                <td>
                                    <span class="ypg-badge ypg-badge-<?php echo esc_attr($playlist->layout); ?>">
                                        <?php echo ucfirst($playlist->layout); ?>
                                    </span>
                                </td>
                                <td>
                                    <input type="text" 
                                           readonly 
                                           value='[ypg_playlist id="<?php echo $playlist->id; ?>"]' 
                                           class="ypg-shortcode-field"
                                           onclick="this.select()">
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=youtube-playlist-gallery&edit=' . $playlist->id); ?>" 
                                       class="button button-small">
                                        <?php _e('Modifica', 'youtube-playlist-gallery'); ?>
                                    </a>
                                    <button type="button" 
                                            class="button button-small ypg-duplicate-btn" 
                                            data-id="<?php echo $playlist->id; ?>">
                                        <?php _e('Duplica', 'youtube-playlist-gallery'); ?>
                                    </button>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=ypg_delete_playlist&id=' . $playlist->id), 'ypg_delete_' . $playlist->id); ?>" 
                                       class="button button-small button-link-delete"
                                       onclick="return confirm('<?php _e('Sei sicuro di voler eliminare questa playlist?', 'youtube-playlist-gallery'); ?>')">
                                        <?php _e('Elimina', 'youtube-playlist-gallery'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <style>
        .ypg-empty-state {
            text-align: center;
            padding: 80px 20px;
        }
        .ypg-empty-icon {
            font-size: 72px;
            margin-bottom: 20px;
        }
        .ypg-empty-state h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .ypg-empty-state p {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
        }
        .ypg-shortcode-field {
            width: 100%;
            font-size: 11px;
            padding: 4px 8px;
        }
        .ypg-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .ypg-badge-grid { background: #e3f2fd; color: #1976d2; }
        .ypg-badge-list { background: #f3e5f5; color: #7b1fa2; }
        .ypg-badge-masonry { background: #fff3e0; color: #e65100; }
        .ypg-badge-carousel { background: #e8f5e9; color: #2e7d32; }
        </style>
        <?php
    }

    /**
     * Add/Edit Playlist page
     */
    public function add_playlist_page() {
        $this->edit_playlist_page(0);
    }

    /**
     * Edit Playlist page
     */
    public function edit_playlist_page($playlist_id = 0) {
        global $wpdb;
        
        $is_edit = $playlist_id > 0;
        $playlist = null;
        
        if ($is_edit) {
            $playlist = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $playlist_id));
            if (!$playlist) {
                wp_die(__('Playlist non trovata', 'youtube-playlist-gallery'));
            }
        }
        
        $name = $is_edit ? $playlist->name : '';
        $playlist_id_value = $is_edit ? $playlist->playlist_id : '';
        $layout = $is_edit ? $playlist->layout : 'grid';
        $columns = $is_edit ? $playlist->columns : 3;
        $max_results = $is_edit ? $playlist->max_results : 10;
        $show_title = $is_edit ? $playlist->show_title : 1;
        $show_description = $is_edit ? $playlist->show_description : 0;
        $lightbox = $is_edit ? $playlist->lightbox : 1;
        $pagination = $is_edit ? $playlist->pagination : 0;
        
        ?>
        <div class="wrap">
            <h1><?php echo $is_edit ? __('Modifica Playlist', 'youtube-playlist-gallery') : __('Aggiungi Nuova Playlist', 'youtube-playlist-gallery'); ?></h1>
            
            <?php if (isset($_GET['saved']) && $_GET['saved'] == '1'): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e('Playlist salvata con successo!', 'youtube-playlist-gallery'); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="ypg-form">
                <input type="hidden" name="action" value="ypg_save_playlist">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="playlist_id" value="<?php echo $playlist->id; ?>">
                <?php endif; ?>
                <?php wp_nonce_field('ypg_save_playlist', 'ypg_nonce'); ?>
                
                <div class="ypg-form-row">
                    <div class="ypg-form-main">
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2><?php _e('Informazioni Playlist', 'youtube-playlist-gallery'); ?></h2>
                            </div>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="ypg_name"><?php _e('Nome Playlist', 'youtube-playlist-gallery'); ?> *</label>
                                        </th>
                                        <td>
                                            <input type="text" 
                                                   name="name" 
                                                   id="ypg_name" 
                                                   value="<?php echo esc_attr($name); ?>" 
                                                   class="regular-text" 
                                                   required>
                                            <p class="description"><?php _e('Un nome descrittivo per identificare questa playlist (es. "Video Tutorial", "Recensioni Prodotti")', 'youtube-playlist-gallery'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="ypg_playlist_id"><?php _e('YouTube Playlist ID', 'youtube-playlist-gallery'); ?> *</label>
                                        </th>
                                        <td>
                                            <input type="text" 
                                                   name="playlist_id" 
                                                   id="ypg_playlist_id" 
                                                   value="<?php echo esc_attr($playlist_id_value); ?>" 
                                                   class="regular-text" 
                                                   required>
                                            <p class="description">
                                                <?php _e('L\'ID della playlist YouTube. Si trova nell\'URL dopo "list=". Esempio:', 'youtube-playlist-gallery'); ?>
                                                <br><code>https://www.youtube.com/playlist?list=<strong>PLrAXtmErZgOeiKm4...</strong></code>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2><?php _e('Impostazioni Visualizzazione', 'youtube-playlist-gallery'); ?></h2>
                            </div>
                            <div class="inside">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="ypg_layout"><?php _e('Layout', 'youtube-playlist-gallery'); ?></label>
                                        </th>
                                        <td>
                                            <select name="layout" id="ypg_layout">
                                                <option value="grid" <?php selected($layout, 'grid'); ?>><?php _e('Griglia', 'youtube-playlist-gallery'); ?></option>
                                                <option value="list" <?php selected($layout, 'list'); ?>><?php _e('Lista', 'youtube-playlist-gallery'); ?></option>
                                                <option value="masonry" <?php selected($layout, 'masonry'); ?>><?php _e('Masonry', 'youtube-playlist-gallery'); ?></option>
                                                <option value="carousel" <?php selected($layout, 'carousel'); ?>><?php _e('Carosello', 'youtube-playlist-gallery'); ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="ypg_columns"><?php _e('Colonne', 'youtube-playlist-gallery'); ?></label>
                                        </th>
                                        <td>
                                            <input type="number" 
                                                   name="columns" 
                                                   id="ypg_columns" 
                                                   value="<?php echo esc_attr($columns); ?>" 
                                                   min="1" 
                                                   max="6" 
                                                   class="small-text">
                                            <p class="description"><?php _e('Numero di colonne per il layout a griglia (1-6)', 'youtube-playlist-gallery'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="ypg_max_results"><?php _e('Numero Video', 'youtube-playlist-gallery'); ?></label>
                                        </th>
                                        <td>
                                            <input type="number" 
                                                   name="max_results" 
                                                   id="ypg_max_results" 
                                                   value="<?php echo esc_attr($max_results); ?>" 
                                                   min="1" 
                                                   max="50" 
                                                   class="small-text">
                                            <p class="description"><?php _e('Numero massimo di video da visualizzare (1-50)', 'youtube-playlist-gallery'); ?></p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row"><?php _e('Opzioni', 'youtube-playlist-gallery'); ?></th>
                                        <td>
                                            <fieldset>
                                                <label>
                                                    <input type="checkbox" name="show_title" value="1" <?php checked($show_title, 1); ?>>
                                                    <?php _e('Mostra titolo video', 'youtube-playlist-gallery'); ?>
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="show_description" value="1" <?php checked($show_description, 1); ?>>
                                                    <?php _e('Mostra descrizione video', 'youtube-playlist-gallery'); ?>
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="lightbox" value="1" <?php checked($lightbox, 1); ?>>
                                                    <?php _e('Abilita lightbox (apri video in overlay)', 'youtube-playlist-gallery'); ?>
                                                </label><br>
                                                <label>
                                                    <input type="checkbox" name="pagination" value="1" <?php checked($pagination, 1); ?>>
                                                    <?php _e('Abilita paginazione (bottone "Carica altri")', 'youtube-playlist-gallery'); ?>
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="ypg-form-sidebar">
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2><?php _e('Pubblica', 'youtube-playlist-gallery'); ?></h2>
                            </div>
                            <div class="inside">
                                <div class="submitbox">
                                    <div class="major-publishing-actions">
                                        <div class="publishing-action">
                                            <input type="submit" 
                                                   name="submit" 
                                                   class="button button-primary button-large" 
                                                   value="<?php echo $is_edit ? __('Aggiorna Playlist', 'youtube-playlist-gallery') : __('Crea Playlist', 'youtube-playlist-gallery'); ?>">
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($is_edit): ?>
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2><?php _e('Shortcode', 'youtube-playlist-gallery'); ?></h2>
                            </div>
                            <div class="inside">
                                <p><?php _e('Usa questo shortcode per visualizzare la playlist:', 'youtube-playlist-gallery'); ?></p>
                                <input type="text" 
                                       readonly 
                                       value='[ypg_playlist id="<?php echo $playlist->id; ?>"]' 
                                       class="widefat"
                                       onclick="this.select()">
                                <p class="description" style="margin-top: 10px;">
                                    <?php _e('Copia e incolla questo shortcode in qualsiasi pagina o post.', 'youtube-playlist-gallery'); ?>
                                </p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2><?php _e('Aiuto', 'youtube-playlist-gallery'); ?></h2>
                            </div>
                            <div class="inside">
                                <h4><?php _e('Come trovare l\'ID Playlist?', 'youtube-playlist-gallery'); ?></h4>
                                <ol style="padding-left: 20px;">
                                    <li><?php _e('Vai su YouTube', 'youtube-playlist-gallery'); ?></li>
                                    <li><?php _e('Apri la playlist desiderata', 'youtube-playlist-gallery'); ?></li>
                                    <li><?php _e('Copia l\'ID dall\'URL dopo "list="', 'youtube-playlist-gallery'); ?></li>
                                </ol>
                                
                                <h4 style="margin-top: 15px;"><?php _e('Documentazione', 'youtube-playlist-gallery'); ?></h4>
                                <p>
                                    <a href="<?php echo YPG_PLUGIN_URL; ?>README.md" target="_blank">
                                        <?php _e('Leggi la guida completa', 'youtube-playlist-gallery'); ?>
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <style>
        .ypg-form-row {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            margin-top: 20px;
        }
        .ypg-form-main .postbox {
            margin-bottom: 20px;
        }
        .ypg-form-sidebar .postbox {
            margin-bottom: 20px;
        }
        @media (max-width: 1200px) {
            .ypg-form-row {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <?php
    }

    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form action='options.php' method='post'>
                <?php
                settings_fields('ypg_plugin');
                do_settings_sections('youtube_playlist_gallery');
                submit_button();
                ?>
            </form>
            
            <div class="ypg-settings-help">
                <h2><?php _e('Documentazione', 'youtube-playlist-gallery'); ?></h2>
                <p><?php _e('Per maggiori informazioni, consulta la documentazione completa del plugin.', 'youtube-playlist-gallery'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Save playlist
     */
    public function save_playlist() {
        if (!isset($_POST['ypg_nonce']) || !wp_verify_nonce($_POST['ypg_nonce'], 'ypg_save_playlist')) {
            wp_die(__('Verifica di sicurezza fallita', 'youtube-playlist-gallery'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Permessi insufficienti', 'youtube-playlist-gallery'));
        }
        
        global $wpdb;
        
        $playlist_id = isset($_POST['playlist_id']) ? intval($_POST['playlist_id']) : 0;
        $name = sanitize_text_field($_POST['name']);
        $yt_playlist_id = sanitize_text_field($_POST['playlist_id']);
        $layout = sanitize_text_field($_POST['layout']);
        $columns = intval($_POST['columns']);
        $max_results = intval($_POST['max_results']);
        $show_title = isset($_POST['show_title']) ? 1 : 0;
        $show_description = isset($_POST['show_description']) ? 1 : 0;
        $lightbox = isset($_POST['lightbox']) ? 1 : 0;
        $pagination = isset($_POST['pagination']) ? 1 : 0;
        
        $data = array(
            'name' => $name,
            'playlist_id' => $yt_playlist_id,
            'layout' => $layout,
            'columns' => $columns,
            'max_results' => $max_results,
            'show_title' => $show_title,
            'show_description' => $show_description,
            'lightbox' => $lightbox,
            'pagination' => $pagination,
        );
        
        if ($playlist_id > 0) {
            // Update
            $wpdb->update(
                $this->table_name,
                $data,
                array('id' => $playlist_id),
                array('%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d'),
                array('%d')
            );
            
            wp_redirect(admin_url('admin.php?page=youtube-playlist-gallery&edit=' . $playlist_id . '&saved=1'));
        } else {
            // Insert
            $wpdb->insert(
                $this->table_name,
                $data,
                array('%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d')
            );
            
            $new_id = $wpdb->insert_id;
            wp_redirect(admin_url('admin.php?page=youtube-playlist-gallery&edit=' . $new_id . '&saved=1'));
        }
        
        exit;
    }

    /**
     * Delete playlist
     */
    public function delete_playlist() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        if (!$id || !wp_verify_nonce($_GET['_wpnonce'], 'ypg_delete_' . $id)) {
            wp_die(__('Verifica di sicurezza fallita', 'youtube-playlist-gallery'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Permessi insufficienti', 'youtube-playlist-gallery'));
        }
        
        global $wpdb;
        $wpdb->delete($this->table_name, array('id' => $id), array('%d'));
        
        wp_redirect(admin_url('admin.php?page=youtube-playlist-gallery&deleted=1'));
        exit;
    }

    /**
     * AJAX: Duplicate playlist
     */
    public function ajax_duplicate_playlist() {
        check_ajax_referer('ypg_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permessi insufficienti', 'youtube-playlist-gallery')));
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (!$id) {
            wp_send_json_error(array('message' => __('ID non valido', 'youtube-playlist-gallery')));
        }
        
        global $wpdb;
        $playlist = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id), ARRAY_A);
        
        if (!$playlist) {
            wp_send_json_error(array('message' => __('Playlist non trovata', 'youtube-playlist-gallery')));
        }
        
        unset($playlist['id']);
        unset($playlist['created_at']);
        unset($playlist['updated_at']);
        $playlist['name'] = $playlist['name'] . ' (Copia)';
        
        $wpdb->insert($this->table_name, $playlist);
        
        wp_send_json_success(array(
            'message' => __('Playlist duplicata con successo!', 'youtube-playlist-gallery'),
            'redirect' => admin_url('admin.php?page=youtube-playlist-gallery&duplicated=1')
        ));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'youtube-playlist-gallery') === false) {
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
                'confirm_delete' => __('Sei sicuro di voler eliminare questa playlist?', 'youtube-playlist-gallery'),
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
     * Render saved playlist shortcode
     */
    public function render_saved_playlist($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);
        
        $id = intval($atts['id']);
        
        if (!$id) {
            return '<div class="ypg-error"><p>' . __('⚠️ ID playlist non valido.', 'youtube-playlist-gallery') . '</p></div>';
        }
        
        global $wpdb;
        $playlist = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id));
        
        if (!$playlist) {
            return '<div class="ypg-error"><p>' . __('⚠️ Playlist non trovata.', 'youtube-playlist-gallery') . '</p></div>';
        }
        
        // Render using the saved settings
        return $this->render_gallery(array(
            'playlist_id' => $playlist->playlist_id,
            'layout' => $playlist->layout,
            'columns' => $playlist->columns,
            'max_results' => $playlist->max_results,
            'show_title' => $playlist->show_title,
            'show_description' => $playlist->show_description,
            'lightbox' => $playlist->lightbox,
            'pagination' => $playlist->pagination,
        ));
    }

    /**
     * Render gallery shortcode (original)
     */
    public function render_gallery($atts) {
        $options = get_option($this->option_name);
        
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'playlist_id' => '',
            'max_results' => 10,
            'layout' => 'grid',
            'columns' => 3,
            'pagination' => false,
            'show_title' => true,
            'show_description' => false,
            'lightbox' => true,
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
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypg_playlists';
        
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $playlist_id = !empty($instance['saved_playlist']) ? intval($instance['saved_playlist']) : 0;
        
        if ($playlist_id) {
            echo do_shortcode('[ypg_playlist id="' . $playlist_id . '"]');
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypg_playlists';
        
        $title = !empty($instance['title']) ? $instance['title'] : __('Video YouTube', 'youtube-playlist-gallery');
        $saved_playlist = !empty($instance['saved_playlist']) ? $instance['saved_playlist'] : '';
        
        $playlists = $wpdb->get_results("SELECT id, name FROM $table_name ORDER BY name ASC");
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Titolo:', 'youtube-playlist-gallery'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('saved_playlist')); ?>"><?php _e('Playlist:', 'youtube-playlist-gallery'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('saved_playlist')); ?>" name="<?php echo esc_attr($this->get_field_name('saved_playlist')); ?>">
                <option value=""><?php _e('-- Seleziona Playlist --', 'youtube-playlist-gallery'); ?></option>
                <?php foreach ($playlists as $playlist): ?>
                    <option value="<?php echo $playlist->id; ?>" <?php selected($saved_playlist, $playlist->id); ?>>
                        <?php echo esc_html($playlist->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['saved_playlist'] = (!empty($new_instance['saved_playlist'])) ? absint($new_instance['saved_playlist']) : '';
        return $instance;
    }
}

// Initialize the plugin
new YouTube_Playlist_Gallery();
