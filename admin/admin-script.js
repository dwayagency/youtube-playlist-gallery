/**
 * YouTube Playlist Gallery - Admin JavaScript
 * Version: 2.0.0
 * Author: dway
 */

(function($) {
    'use strict';

    /**
     * Admin YPG Object
     */
    const YPGAdmin = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            $('#ypg-generate-shortcode').on('click', this.generateShortcode);
            $('#ypg-copy-shortcode').on('click', this.copyShortcode);
            $('#ypg-clear-cache').on('click', this.clearCache);
        },

        /**
         * Generate shortcode
         */
        generateShortcode: function(e) {
            e.preventDefault();
            
            const playlistId = $('#ypg-sg-playlist').val().trim();
            
            if (!playlistId) {
                alert('Per favore inserisci un Playlist ID');
                return;
            }
            
            const layout = $('#ypg-sg-layout').val();
            const columns = $('#ypg-sg-columns').val();
            const maxResults = $('#ypg-sg-max').val();
            const pagination = $('#ypg-sg-pagination').is(':checked');
            
            // Build shortcode
            let shortcode = '[youtube_playlist_gallery';
            shortcode += ' playlist_id="' + playlistId + '"';
            
            if (maxResults !== '10') {
                shortcode += ' max_results="' + maxResults + '"';
            }
            
            if (layout !== 'grid') {
                shortcode += ' layout="' + layout + '"';
            }
            
            if (columns !== '3') {
                shortcode += ' columns="' + columns + '"';
            }
            
            if (pagination) {
                shortcode += ' pagination="true"';
            }
            
            shortcode += ']';
            
            // Show output
            $('#ypg-shortcode-text').val(shortcode);
            $('#ypg-shortcode-output').slideDown();
        },

        /**
         * Copy shortcode to clipboard
         */
        copyShortcode: function(e) {
            e.preventDefault();
            
            const $input = $('#ypg-shortcode-text');
            $input.select();
            
            try {
                document.execCommand('copy');
                
                const $button = $('#ypg-copy-shortcode');
                const originalText = $button.text();
                
                $button.text(ypgAdmin.strings.copied);
                
                setTimeout(function() {
                    $button.text(originalText);
                }, 2000);
                
            } catch (err) {
                alert('Errore nella copia. Seleziona e copia manualmente.');
            }
        },

        /**
         * Clear cache via AJAX
         */
        clearCache: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $status = $('#ypg-cache-status');
            
            $button.prop('disabled', true).text('Svuotamento...');
            $status.removeClass('success error').text('');
            
            $.ajax({
                url: ypgAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'ypg_clear_cache',
                    nonce: ypgAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.addClass('success').text('✓ ' + response.data.message);
                    } else {
                        $status.addClass('error').text('✗ ' + (response.data.message || ypgAdmin.strings.error));
                    }
                },
                error: function() {
                    $status.addClass('error').text('✗ ' + ypgAdmin.strings.error);
                },
                complete: function() {
                    $button.prop('disabled', false).text('Svuota Cache');
                    
                    setTimeout(function() {
                        $status.fadeOut(function() {
                            $(this).removeClass('success error').text('').show();
                        });
                    }, 3000);
                }
            });
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        YPGAdmin.init();
    });

})(jQuery);

