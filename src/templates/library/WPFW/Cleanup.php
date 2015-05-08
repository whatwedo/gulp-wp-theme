<?php
namespace WPFW;

class Cleanup
{
    public function __construct()
    {
        // launching operation cleanup
        add_action('init', array(&$this, 'cleanupHeader'));

        // remove WP version from RSS
        add_filter('the_generator', array(&$this, 'removeRssVersion'));

        // remove pesky injected css for recent comments widget
        add_filter('wp_head', array(&$this, 'removeWpWidgetRecentCommentsStyle'), 1);

        // clean up comment styles in the head
        add_action('wp_head', array(&$this, 'removeRecentCommentsStyle'), 1);

        // clean up gallery output in wp
        add_filter('gallery_style', array(&$this, 'removeGalleryStyle'));

        // remove the p from around imgs (http://css-tricks.com/snippets/wordpress/remove-paragraph-tags-from-around-images/)
        add_filter('the_content', array(&$this, 'filterPTagOnImages'));

        // cleaning up excerpt
        add_filter('excerpt_more', array(&$this, 'excerptMore'));

    }

    function excerptMore($more) {
        global $post;
        // edit here if you like
        return '...  <a class="excerpt-read-more" href="'. get_permalink($post->ID) . '" title="' . get_the_title($post->ID) . '">Weiterlesen &raquo;</a>';
    }

    function cleanupHeader() 
    {
        // category feeds
        remove_action('wp_head', 'feed_links_extra', 3);
        
        // post and comment feeds
        remove_action('wp_head', 'feed_links', 2);
        
        // EditURI link
        remove_action('wp_head', 'rsd_link');
        
        // windows live writer
        remove_action('wp_head', 'wlwmanifest_link');
        
        // index link
        remove_action('wp_head', 'index_rel_link');
        
        // previous link
        remove_action('wp_head', 'parent_post_rel_link', 10, 0);
        
        // start link
        remove_action('wp_head', 'start_post_rel_link', 10, 0);
        
        // links for adjacent posts
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
        
        // WP version
        remove_action('wp_head', 'wp_generator');
        
        // remove WP version from css
        add_filter('style_loader_src', array(&$this, 'removeWpVersionFromLink'), 9999);
        
        // remove Wp version from scripts
        add_filter('script_loader_src', array(&$this, 'removeWpVersionFromLink'), 9999);

    }

    function removeWpVersionFromLink($src) {
        if (strpos($src, 'ver='))
            $src = remove_query_arg('ver', $src);
        return $src;
    }

    function removeWpWidgetRecentCommentsStyle() {
        if (has_filter('wp_head', 'wp_widget_recent_comments_style')) {
            remove_filter('wp_head', 'wp_widget_recent_comments_style');
        }
    }

    function removeRecentCommentsStyle() {
        global $wp_widget_factory;
        if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
            remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
        }
    }

    function removeGalleryStyle($css) {
        return preg_replace("!<style type='text/css'>(.*?)</style>!s", '', $css);
    }

    function removeRssVersion() {
        return '';
    }
    
    function filterPTagOnImages($content){
       return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
    }

}