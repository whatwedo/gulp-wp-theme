<?php
namespace WPFW;

class Admin
{
    public static $rssWidgets = array(
        //'gulp-wp-theme' => 'https://github.com/whatwedo/gulp-wp-theme/commits/master.atom'
    );

    public static $changelogPath = '';

    public static $loginLogo = '';

    public function __construct()
    {
        // uncomment this to add a login logo
        //static::$loginLogo = get_bloginfo('template_directory') . '/images/wordpress/login-logo.png';
        
        static::$changelogPath = get_stylesheet_directory() . '/CHANGELOG.html';

        // removes Dashboard Widgets 
        add_action('admin_menu', array(&$this, 'removeWidgets'));

        // adds RSS Widget(s)
        add_action('wp_dashboard_setup', array(&$this, 'addRssWidgets'));

        // changes the Logo-URL to Blog-URL
        add_filter('login_headerurl', array(&$this, 'changeLoginUrl'));

        // changes the Login-Title to Blog Name
        add_filter('login_headertitle', array(&$this, 'changeLoginTitle'));

        // changes the Login Logo
        add_action('login_enqueue_scripts', array(&$this, 'changeLoginLogo'));

        // set WP SEO by Yoast Plugin Metabox Position
        apply_filters( 'wpseo_metabox_prio', 'low' );

        // Hide Site Analysis of WP SEO because we're mostly using Advanced Custom Fields
        add_filter('wpseo_use_page_analysis', '__return_false');
    }

    public function removeWidgets()
    {
        // WordPress Core Widgets
        remove_meta_box('dashboard_right_now', 'dashboard', 'core');       // Right Now Widget
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'core'); // Comments Widget
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'core');  // Incoming Links Widget
        remove_meta_box('dashboard_plugins', 'dashboard', 'core');         // Plugins Widget

        remove_meta_box('dashboard_quick_press', 'dashboard', 'core');      // Quick Press Widget
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'core');    // Recent Drafts Widget
        remove_meta_box('dashboard_primary', 'dashboard', 'core');          //
        remove_meta_box('dashboard_secondary', 'dashboard', 'core');        //

        // WordPress Plugin Widgets
        remove_meta_box('yoast_db_widget', 'dashboard', 'normal');          // Yoast's SEO Plugin Widget

        // WordPress Welcome Screen
        remove_action( 'welcome_panel', 'wp_welcome_panel' );
    }

    public function addRssWidgets()
    {
        if (function_exists('fetch_feed')) {
            $i = 0;
            foreach (static::$rssWidgets as $title => $feed) {
                $i++;
                wp_add_dashboard_widget('wpfw_custom_rss_widget_' . $i, $title, array(&$this, 'createRssWidget'));
            }
        }

        if (file_exists(static::$changelogPath)) {
            wp_add_dashboard_widget('wpfw_custom_changelog_widget', __('Letzte Änderungen', 'gulp-wp-theme'), array(&$this, 'createChangelogWidget'));
        }
    }

    public function createRssWidget()
    {
        require_once(ABSPATH . WPINC . '/feed.php');

        $url = array_shift(static::$rssWidgets);
        $feed = fetch_feed($url);
        $limit = $feed->get_item_quantity(8);
        $items = $feed->get_items(0, $limit);

        if ($limit == 0) {
            echo '<div>The RSS Feed is either empty or unavailable.</div>';
            return;
        }

        foreach ($items as $item) {
            ?>
            <h4 style="margin-bottom: 0;">
                <a href="<?php echo $item->get_permalink(); ?>" title="<?php echo mysql2date('d.m.Y H:i:s', $item->get_date( 'Y-m-d H:i:s' ) ); ?>" target="_blank">
                    <?php echo $item->get_title(); ?>
                </a>
            </h4>
            <p style="margin-top: 0.5em;">
                <?php echo substr($item->get_description(), 0, 200); ?>
            </p>
            <?php
        }
    }

    public function createChangelogWidget()
    {
        echo '<div style="overflow: scroll; height: 200px;">';

        $changelog = file(static::$changelogPath);

        // we only want to see changes, not the headers
        while(count($changelog) > 0) {
            $row = array_shift($changelog);
            if (strpos($row, '<h2') !== false) {
                array_unshift($changelog, $row);
                break;
            }
        }

        $changelog = implode(PHP_EOL, $changelog);
//        $changelog = preg_replace('!<h2 id="([\w\d\-]+)">([\w\d\W\-]+)</h2>!i', '<h4 id="$1">$2</h4>', $changelog);
        //$changelog = preg_replace('!<h3 id="([\w\d\-]+)">([\w\d\W\-]+)</h3>!i', '<h5 id="$1">$2</h5>', $changelog);

        echo $changelog;

        echo '</div>';
    }

    public function changeLoginUrl()
    {
        return home_url();
    }

    public function changeLoginTitle()
    {
        return get_option('blogname');
    }

    public function changeLoginLogo()
    {
        if (static::$loginLogo == '') {
            return;
        }
?>
        <style type="text/css">
            body.login div#login h1 a {
                background-image: url(<?php echo static::$loginLogo; ?>);
                padding-bottom: 0;
                height: 150px;
                background-size: contain;
                background-position: center center;
                margin-bottom: 15px;
            }
        </style>
<?php
    }

}
