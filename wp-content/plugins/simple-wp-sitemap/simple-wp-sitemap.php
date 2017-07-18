<?php defined('ABSPATH') or die;

/*
 * Plugin Name: Simple Wp Sitemap
 * Plugin URI: https://www.webbjocke.com/simple-wp-sitemap/
 * Description: An easy sitemap plugin that adds both an xml and an html sitemap to your site, which updates and maintains themselves so you don't have to!
 * Version: 1.1.9
 * Author: Webbjocke
 * Author URI: https://www.webbjocke.com/
 * License: GPLv3
 * Text Domain: simple-wp-sitemap
 * Domain Path: /languages
 */

/*
Simple Wp Sitemap - WordPress sitemap plugin
Copyright (C) 2017 Webbjocke

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see http://www.gnu.org/licenses/.
*/

/*
 * Simple Wp Sitemap main class
 */

class SimpleWpSitemap {
    private static $version = 18; // only changes when needed

    // Runs on plugin activation
    public static function activateSitemaps () {
        self::rewriteRules();
        flush_rewrite_rules();

        require_once 'simpleWpMapOptions.php';
        $ops = new SimpleWpMapOptions();
        $ops->migrateFromOld();

        update_option('simple_wp_sitemap_version', self::$version);
    }

    // Runs on plugin deactivation
    public static function deactivateSitemaps () {
        flush_rewrite_rules();
    }

    // Updates the plugin if needed (calls activateSitemaps)
    public static function updateCheck () {
        if (!($current = get_option('simple_wp_sitemap_version')) || $current < self::$version) {
            self::activateSitemaps();
        }
    }

    // Registers most hooks
    public static function registerHooks () {
        register_activation_hook(__FILE__, array(__CLASS__, 'activateSitemaps'));
        register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivateSitemaps'));
        add_action('plugins_loaded', array(__CLASS__, 'loadTextDomain'));
        add_action('admin_menu', array(__CLASS__, 'sitemapAdminSetup'));
        add_action('init', array(__CLASS__, 'rewriteRules'), 1);
        add_filter('query_vars', array(__CLASS__, 'addSitemapQuery'), 1);
        add_filter('template_redirect', array(__CLASS__, 'generateSitemapContent'), 1);
        add_filter("plugin_action_links_" . plugin_basename(__FILE__), array(__CLASS__, 'pluginSettingsLink'));
    }

    // Loads plugins text domain
    public static function loadTextDomain () {
        load_plugin_textdomain('simple-wp-sitemap', false, basename(dirname( __FILE__ )) . '/languages/');
    }

    // Adds a link to settings from the plugins page
    public static function pluginSettingsLink ($links) {
        return array_merge(array(sprintf('<a href="%s">%s</a>', esc_url(admin_url('options-general.php?page=simpleWpSitemapSettings')), __('Settings', 'simple-wp-sitemap'))), $links);
    }

    // Sets the option menu for admins and enqueues scripts n styles
    public static function sitemapAdminSetup () {
        add_options_page('Simple Wp Sitemap', 'Simple Wp Sitemap', 'manage_options', 'simpleWpSitemapSettings', array(__CLASS__, 'sitemapAdminArea'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'sitemapScriptsAndStyles'));
        add_action('admin_init', array(__CLASS__, 'updateCheck'));
    }

    // Rewrite rules for sitemaps
    public static function rewriteRules () {
        add_rewrite_rule('sitemap\.xml$', 'index.php?thesimplewpsitemap=xml', 'top');
        add_rewrite_rule('sitemap\.html$', 'index.php?thesimplewpsitemap=html', 'top');
    }

    // Add custom query
    public static function addSitemapQuery ($vars) {
        $vars[] = 'thesimplewpsitemap';
        return $vars;
    }

    // Generates the content
    public static function generateSitemapContent () {
        global $wp_query;

        if (isset($wp_query->query_vars['thesimplewpsitemap']) && in_array(($q = $wp_query->query_vars['thesimplewpsitemap']), array('xml', 'html'))) {
            $wp_query->is_404 = false;

            if ($q === 'html') {
                if ($htmlOpt = get_option('simple_wp_block_html')) {
                    if ($htmlOpt === '404') {
                        $wp_query->is_404 = true;
                        status_header(404);
                    }
                    return;
                }
            } else {
                header('Content-type: application/xml; charset=utf-8');
            }

            require_once 'simpleWpMapBuilder.php';

            $sitemap = new SimpleWpMapBuilder();
            $sitemap->generateSitemap($q);
            exit;
        }
    }

    // Add custom scripts and styles to the plugins customization page in admin area
    public static function sitemapScriptsAndStyles ($page) {
        if ($page === 'settings_page_simpleWpSitemapSettings') {
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_style('simple-wp-sitemap-admin-css', esc_url(plugin_dir_url( __FILE__ ) . 'css/simple-wp-sitemap-admin.css'), array(), self::$version);
            wp_enqueue_script('simple-wp-sitemap-admin-js', esc_url(plugin_dir_url( __FILE__ ) . 'js/simple-wp-sitemap-admin.js'), array('jquery'), self::$version, true);
        }
    }

    // Loads admin page and handles post request when settings are changed
    public static function sitemapAdminArea () {
        if (is_admin() && current_user_can('manage_options')) {
            require_once 'simpleWpMapOptions.php';
            $ops = new SimpleWpMapOptions();

            if (isset($_POST['simple_wp_other_urls'], $_POST['simple_wp_block_urls'], $_POST['simple_wp_home_n'], $_POST['simple_wp_posts_n'], $_POST['simple_wp_pages_n'], $_POST['simple_wp_other_n'], $_POST['simple_wp_categories_n'], $_POST['simple_wp_tags_n'], $_POST['simple_wp_authors_n'], $_POST['simple_wp_last_updated'], $_POST['simple_wp_block_html'], $_POST['simple_wp_order_by'])) {
                $order = $ops->getDefaultOrder();

                foreach ($order as $key => $val) {
                    $arr = explode('-|-', $_POST['simple_wp_' . $key . '_n']);
                    $order[$key] = array('i' => $arr[0], 'title' => isset($arr[1]) ? $arr[1] : $key);
                }
                $ops->setOptions($_POST['simple_wp_other_urls'], $_POST['simple_wp_block_urls'], (isset($_POST['simple_wp_attr_link']) ? 1 : 0), (isset($_POST['simple_wp_disp_categories']) ? 1 : 0), (isset($_POST['simple_wp_disp_tags']) ? 1 : 0), (isset($_POST['simple_wp_disp_authors']) ? 1 : 0), $order, $_POST['simple_wp_last_updated'], $_POST['simple_wp_block_html'], $_POST['simple_wp_order_by']);
            }
            require_once 'simpleWpMapAdmin.php';
        }
    }
}
SimpleWpSitemap::registerHooks();
