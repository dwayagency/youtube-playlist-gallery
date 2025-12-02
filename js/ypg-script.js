/**
 * YouTube Playlist Gallery - Frontend JavaScript
 * Version: 2.0.0
 * Author: dway
 */

(function($) {
    'use strict';

    /**
     * Main YPG Object
     */
    const YPG = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initCarousel();
            this.initFeaturedLayout();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Lightbox
            $(document).on('click', '.ypg-video-link', this.openLightbox);
            $(document).on('click', '.ypg-lightbox-close, .ypg-lightbox-overlay', this.closeLightbox);
            $(document).on('keydown', this.handleKeyboard);
            
            // Load more
            $(document).on('click', '.ypg-load-more', this.loadMore);
            
            // Featured layout - click on thumbnail
            $(document).on('click', '.ypg-layout-featured .ypg-thumb-item', this.switchFeaturedVideo);
            
            // Share
            $(document).on('click', '.ypg-share-btn', this.openShareModal);
            $(document).on('click', '.ypg-share-close, .ypg-share-overlay', this.closeShareModal);
            $(document).on('click', '#ypg-share-copy', this.copyShareUrl);
            
            // Lazy load high-res thumbnails
            this.lazyLoadThumbnails();
        },
        
        /**
         * Initialize featured layout
         */
        initFeaturedLayout: function() {
            // Check if there's a video hash in URL
            const hash = window.location.hash;
            if (hash && hash.startsWith('#video=')) {
                const videoId = hash.replace('#video=', '');
                const $thumb = $('.ypg-layout-featured .ypg-thumb-item[data-video-id="' + videoId + '"]');
                
                if ($thumb.length) {
                    // Switch to this video
                    setTimeout(function() {
                        $thumb.trigger('click');
                    }, 500);
                    return;
                }
            }
            
            // Mark first thumb as active (default)
            $('.ypg-layout-featured .ypg-thumb-item').first().addClass('active');
        },
        
        /**
         * Switch featured video
         */
        switchFeaturedVideo: function(e) {
            e.preventDefault();
            
            const $thumb = $(this);
            const $wrapper = $thumb.closest('.ypg-gallery-wrapper');
            const $featured = $wrapper.find('.ypg-featured-video');
            const lightbox = $wrapper.data('lightbox') === 1;
            
            // Get video data from thumbnail
            const videoId = $thumb.data('video-id');
            const title = $thumb.data('title');
            const description = $thumb.data('description');
            const thumbHigh = $thumb.data('thumb-high');
            
            // Update active state
            $wrapper.find('.ypg-thumb-item').removeClass('active');
            $thumb.addClass('active');
            
            // Update featured video with fade effect
            $featured.fadeOut(200, function() {
                // Update video ID
                $featured.data('video-id', videoId);
                
                // Update link
                const $link = $featured.find('a');
                if (lightbox) {
                    $link.attr('data-video-id', videoId);
                } else {
                    $link.attr('href', 'https://www.youtube.com/watch?v=' + videoId);
                }
                
                // Update image
                $featured.find('.ypg-thumbnail img').attr('src', thumbHigh).attr('alt', title);
                
                // Update title
                const $titleEl = $featured.find('.ypg-featured-title');
                if ($titleEl.length) {
                    $titleEl.text(title);
                }
                
                // Update description
                const $descEl = $featured.find('.ypg-featured-description');
                if ($descEl.length && description) {
                    $descEl.text(description);
                }
                
                // Fade back in
                $featured.fadeIn(200);
                
                // Scroll to featured video (smooth)
                $('html, body').animate({
                    scrollTop: $featured.offset().top - 100
                }, 400);
                
                // Update URL hash (without page reload)
                if (history.pushState) {
                    history.pushState(null, null, '#video=' + videoId);
                } else {
                    window.location.hash = '#video=' + videoId;
                }
                
                // Update share button
                const $shareBtn = $wrapper.find('.ypg-share-btn');
                if ($shareBtn.length) {
                    $shareBtn.attr('data-video-id', videoId);
                    $shareBtn.attr('data-video-title', title);
                }
            });
        },
        
        /**
         * Open share modal
         */
        openShareModal: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $wrapper = $button.closest('.ypg-gallery-wrapper');
            const $featuredVideo = $wrapper.find('.ypg-featured-video');
            
            // Get video ID from the currently displayed featured video
            const videoId = $featuredVideo.data('video-id');
            const videoTitle = $wrapper.find('.ypg-featured-title').text();
            
            // Generate share URL
            const baseUrl = window.location.href.split('#')[0];
            const shareUrl = baseUrl + '#video=' + videoId;
            
            // Set URL in modal
            $('#ypg-share-url-input').val(shareUrl);
            
            // Update social share links
            const encodedUrl = encodeURIComponent(shareUrl);
            const encodedTitle = encodeURIComponent(videoTitle);
            
            $('.ypg-share-facebook').attr('href', 'https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl);
            $('.ypg-share-twitter').attr('href', 'https://twitter.com/intent/tweet?url=' + encodedUrl + '&text=' + encodedTitle);
            $('.ypg-share-whatsapp').attr('href', 'https://wa.me/?text=' + encodedTitle + '%20' + encodedUrl);
            
            // Show modal
            $('#ypg-share-modal').fadeIn(300);
            
            // Select URL for easy copying
            setTimeout(function() {
                $('#ypg-share-url-input').select();
            }, 100);
        },
        
        /**
         * Close share modal
         */
        closeShareModal: function(e) {
            e.preventDefault();
            $('#ypg-share-modal').fadeOut(300);
        },
        
        /**
         * Copy share URL
         */
        copyShareUrl: function(e) {
            e.preventDefault();
            
            const $input = $('#ypg-share-url-input');
            const $button = $(this);
            const originalText = $button.html();
            
            // Select and copy
            $input.select();
            document.execCommand('copy');
            
            // Visual feedback
            $button.addClass('copied').html('<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Copiato!');
            
            setTimeout(function() {
                $button.removeClass('copied').html(originalText);
            }, 2000);
        },

        /**
         * Open lightbox with YouTube video
         */
        openLightbox: function(e) {
            const $link = $(this);
            const videoId = $link.data('video-id');
            
            if (!videoId) {
                return true; // Let default link behavior happen
            }
            
            e.preventDefault();
            
            const embedUrl = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0';
            const $lightbox = $('#ypg-lightbox');
            
            if ($lightbox.length === 0) {
                // Create lightbox if it doesn't exist
                $('body').append(`
                    <div id="ypg-lightbox" class="ypg-lightbox">
                        <div class="ypg-lightbox-overlay"></div>
                        <div class="ypg-lightbox-content">
                            <button class="ypg-lightbox-close" aria-label="Close">&times;</button>
                            <div class="ypg-lightbox-video">
                                <iframe src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                `);
            }
            
            // Set iframe src and show lightbox
            $('#ypg-lightbox iframe').attr('src', embedUrl);
            $('#ypg-lightbox').fadeIn(300);
            $('body').addClass('ypg-lightbox-open').css('overflow', 'hidden');
        },

        /**
         * Close lightbox
         */
        closeLightbox: function(e) {
            e.preventDefault();
            
            const $lightbox = $('#ypg-lightbox');
            
            $lightbox.fadeOut(300, function() {
                $lightbox.find('iframe').attr('src', '');
            });
            
            $('body').removeClass('ypg-lightbox-open').css('overflow', '');
        },

        /**
         * Handle keyboard navigation
         */
        handleKeyboard: function(e) {
            // Close lightbox on ESC key
            if (e.keyCode === 27 && $('#ypg-lightbox').is(':visible')) {
                YPG.closeLightbox(e);
            }
        },

        /**
         * Load more videos via AJAX
         */
        loadMore: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $wrapper = $button.closest('.ypg-gallery-wrapper');
            const $gallery = $wrapper.find('.ypg-gallery');
            const $loader = $button.siblings('.ypg-loader');
            
            const playlistId = $wrapper.data('playlist');
            const maxResults = $wrapper.data('max');
            const nextToken = $button.data('next-token');
            const lightbox = $wrapper.data('lightbox') === 1;
            
            // Disable button and show loader
            $button.prop('disabled', true);
            $loader.show();
            
            $.ajax({
                url: ypgData.ajax_url,
                type: 'POST',
                data: {
                    action: 'ypg_load_more',
                    nonce: ypgData.nonce,
                    playlist_id: playlistId,
                    max_results: maxResults,
                    page_token: nextToken
                },
                success: function(response) {
                    if (response.success && response.data.items) {
                        // Append new videos
                        response.data.items.forEach(function(item) {
                            const videoId = item.snippet.resourceId.videoId;
                            const title = $('<div>').text(item.snippet.title).html();
                            const thumb = item.snippet.thumbnails.medium.url;
                            const thumbHigh = item.snippet.thumbnails.high ? item.snippet.thumbnails.high.url : thumb;
                            
                            const linkAttrs = lightbox ? 
                                `data-video-id="${videoId}" class="ypg-video-link"` : 
                                `href="https://www.youtube.com/watch?v=${videoId}" target="_blank" rel="noopener"`;
                            
                            const html = `
                                <div class="ypg-item">
                                    <a ${linkAttrs}>
                                        <div class="ypg-thumbnail">
                                            <img src="${thumb}" 
                                                 data-src-high="${thumbHigh}"
                                                 alt="${title}"
                                                 loading="lazy">
                                            <div class="ypg-play-overlay">
                                                <svg class="ypg-play-icon" viewBox="0 0 68 48" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#f00"></path>
                                                    <path d="M 45,24 27,14 27,34" fill="#fff"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </a>
                                    <div class="ypg-content">
                                        <h3 class="ypg-title">${title}</h3>
                                    </div>
                                </div>
                            `;
                            
                            $gallery.append(html);
                        });
                        
                        // Update or hide load more button
                        if (response.data.nextPageToken) {
                            $button.data('next-token', response.data.nextPageToken);
                            $button.prop('disabled', false);
                        } else {
                            $button.parent().fadeOut();
                        }
                        
                        // Lazy load new thumbnails
                        YPG.lazyLoadThumbnails();
                    } else {
                        alert('Errore nel caricamento dei video.');
                    }
                },
                error: function() {
                    alert('Errore di connessione.');
                    $button.prop('disabled', false);
                },
                complete: function() {
                    $loader.hide();
                }
            });
        },

        /**
         * Initialize carousel navigation
         */
        initCarousel: function() {
            $('.ypg-layout-carousel .ypg-gallery').each(function() {
                const $carousel = $(this);
                
                // Add touch/swipe support
                let isDown = false;
                let startX;
                let scrollLeft;
                
                $carousel.on('mousedown', function(e) {
                    isDown = true;
                    $carousel.addClass('ypg-grabbing');
                    startX = e.pageX - $carousel.offset().left;
                    scrollLeft = $carousel.scrollLeft();
                });
                
                $carousel.on('mouseleave mouseup', function() {
                    isDown = false;
                    $carousel.removeClass('ypg-grabbing');
                });
                
                $carousel.on('mousemove', function(e) {
                    if (!isDown) return;
                    e.preventDefault();
                    const x = e.pageX - $carousel.offset().left;
                    const walk = (x - startX) * 2;
                    $carousel.scrollLeft(scrollLeft - walk);
                });
            });
        },

        /**
         * Lazy load high-resolution thumbnails
         */
        lazyLoadThumbnails: function() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver(function(entries, observer) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            const highResSrc = img.getAttribute('data-src-high');
                            
                            if (highResSrc && highResSrc !== img.src) {
                                const tempImg = new Image();
                                tempImg.onload = function() {
                                    img.src = highResSrc;
                                };
                                tempImg.src = highResSrc;
                            }
                            
                            observer.unobserve(img);
                        }
                    });
                });
                
                document.querySelectorAll('.ypg-thumbnail img[data-src-high]').forEach(function(img) {
                    imageObserver.observe(img);
                });
            }
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        YPG.init();
    });

})(jQuery);

