<?php defined('ABSPATH') or die;

/*
 * Simple Wp Sitemap builder
 */

class SimpleWpMapBuilder {
    private $home = null;
    private $xml = false;
    private $html = false;
    private $posts = '';
    private $pages = '';
    private $blockedUrls = null;
    private $url;
    private $tags;
    private $other;
    private $homeUrl;
    private $authors;
    private $orderby;
    private $pattern;
    private $categories;
    private $lastUpdated;

    // Constructor
    public function __construct () {
        $this->homeUrl = esc_url(home_url('/'));
        $this->url = esc_url(plugins_url() . '/simple-wp-sitemap/');
        $this->categories = get_option('simple_wp_disp_categories') ? array(0 => 0) : false;
        $this->tags = get_option('simple_wp_disp_tags') ? array(0 => 0) : false;
        $this->authors = get_option('simple_wp_disp_authors') ? array(0 => 0) : false;
        $this->orderby = get_option('simple_wp_order_by');
        @date_default_timezone_set(get_option('timezone_string'));
    }

    // Generates an xml or html sitemap
    public function generateSitemap ($type) {
        if ($type === 'xml' || $type === 'html') {
            $this->$type = true;
            $this->pattern = ($this->xml ? 'Y-m-d\TH:i:sP' : 'Y-m-d H:i');
            $this->getOtherPages();
            $this->setUpBlockedUrls();
            $this->setLastUpdated();
            $this->generateContent();
            $this->printOutput();
        }
    }

    // Gets custom urls user has added
    public function getOtherPages () {
        $xml = array();
        if ($others = get_option('simple_wp_other_urls')) {
            if ($this->orderby === 'modified') {
                uasort($others, array($this, 'sortDate'));
            }
            foreach ($others as $other) {
                if ($other && is_array($other)) {
                    $xml[] = $this->getXml(esc_url($other['url']), date($this->pattern, is_int($other['date']) ? $other['date'] : strtotime($other['date'])));
                }
            }
        }
        $this->other = $this->sortToString($xml);
    }

    // Sets up blocked urls into an array
    public function setUpBlockedUrls () {
        if (($blocked = get_option('simple_wp_block_urls')) && is_array($blocked)) {
            $this->blockedUrls = array();
            foreach ($blocked as $block) {
                $this->blockedUrls[$block['url']] = true;
            }
        }
    }

    // Sets the "last updated" text
    public function setLastUpdated () {
        $this->lastUpdated = ($updated = get_option('simple_wp_last_updated')) ? esc_html($updated) : __('Last updated', 'simple-wp-sitemap');
    }

    // Matches url against blocked ones that shouldn't be displayed
    public function isBlockedUrl($url) {
        return $this->blockedUrls && isset($this->blockedUrls[$url]);
    }

    // Returns xml or html
    public function getXml ($link, $date) {
        return $this->xml ? "<url>\n\t<loc>$link</loc>\n\t<lastmod>$date</lastmod>\n</url>\n" : "<li><a href=\"$link\"><span class=\"link\">$link</span><span class=\"date\">$date</span></a></li>";
    }

    // Querys the database and gets the actual sitemaps content
    public function generateContent () {
        $q = new WP_Query(array(
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => 50000,
            'has_password' => false,
            'orderby' => $this->orderby ? ($this->orderby === 'parent' ? array('type' => 'DESC', 'parent' => 'DESC') : sanitize_text_field($this->orderby)) : 'date',
            'order' => $this->orderby === 'name' ? 'ASC' : 'DESC'
        ));

        if ($q->have_posts()) {
            while ($q->have_posts()) {
                $q->the_post();

                $link = esc_url(get_permalink());
                $date = get_the_modified_date($this->pattern);
                $this->getCategoriesTagsAndAuthor($date);

                if (!$this->isBlockedUrl($link)) {
                    if (!$this->home && $link === $this->homeUrl) {
                        $this->home = $this->getXml($link, $date);

                    } elseif (get_post_type() === 'page') {
                        $this->pages .= $this->getXml($link, $date);

                    } else { // posts (also all custom post types are added here)
                        $this->posts .= $this->getXml($link, $date);
                    }
                }
            }
        }
        wp_reset_postdata();
    }

    // Gets a posts categories, tags and author, and compares for last modified date
    public function getCategoriesTagsAndAuthor ($date) {
        if ($this->categories && ($postCategories = get_the_category())) {
            foreach ($postCategories as $category) {
                if (!isset($this->categories[($id = $category->term_id)]) || $this->categories[$id] < $date) {
                    $this->categories[$id] = $date;
                }
            }
        }
        if ($this->tags && ($postTags = get_the_tags())) {
            foreach ($postTags as $tag) {
                if (!isset($this->tags[($id = $tag->term_id)]) || $this->tags[$id] < $date) {
                    $this->tags[$id] = $date;
                }
            }
        }
        if ($this->authors && ($id = get_the_author_meta('ID'))) {
            if (!isset($this->authors[$id]) || $this->authors[$id] < $date) {
                $this->authors[$id] = $date;
            }
        }
    }

    // Prints all output
    public function printOutput () {
        if ($this->xml) {
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<?xml-stylesheet type=\"text/css\" href=\"", $this->url, "css/xml.css\"?>\n<urlset xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9\n\thttp://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\" xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
            $this->sortAndPrintSections();
            echo '</urlset>';

        } else {
            $blogName = esc_html(get_bloginfo('name'));
            echo '<!doctype html><html lang="', get_locale(), '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>', $blogName, ' ', __('Html Sitemap', 'simple-wp-sitemap'), '</title><link rel="stylesheet" href="', $this->url, 'css/html.css"></head><body><div id="wrapper"><h1><a href="', $this->homeUrl, '">', $blogName, '</a> ', __('Html Sitemap', 'simple-wp-sitemap'), '</h1>';
            $this->sortAndPrintSections();
            echo '</div></body></html>';
        }
    }

    // Sorts and prints the content sections
    public function sortAndPrintSections () {
        $orderArray = get_option('simple_wp_disp_sitemap_order');

        if (!$orderArray || !isset($orderArray['home'])) { // Fix for old versions
            require_once 'simpleWpMapOptions.php';
            $ops = new SimpleWpMapOptions();
            $orderArray = $ops->migrateFromOld();
        }
        if (!$this->home) {
            $this->home = $this->getXml($this->homeUrl, date($this->pattern));
        }

        array_walk($orderArray, array($this, 'printSection'));
        $this->attributionLink();
    }

    // Prints a sections xml/html
    public function printSection ($arr, $title) {
        if ($this->$title) {
            $xml = $this->$title;
            unset($this->$title);

            if (in_array($title, array('categories', 'tags', 'authors'))) { // Categories, tags or authors
                $urls = array();
                foreach ($xml as $id => $date) {
                    if ($date) {
                        switch ($title) {
                            case 'tags': $link = esc_url(get_tag_link($id)); break;
                            case 'categories': $link = esc_url(get_category_link($id)); break;
                            default: $link = esc_url(get_author_posts_url($id)); // Authors
                        }
                        if (!$this->isBlockedUrl($link)) {
                            $urls[] = $this->getXml($link, $date);
                        }
                    }
                }
                $xml = $this->sortToString($urls);
            }
            if ($xml) {
                if ($this->html) {
                    echo '<div class="header"><p class="header-txt">', esc_html($arr['title']), '</p><p class="header-date">', $this->lastUpdated, '</p></div><ul>', $xml, '</ul>';
                } else {
                    echo $xml;
                }
            }
        }
    }

    // Displays attribution link if user has checked the checkbox
    public function attributionLink () {
        if ($this->html && get_option('simple_wp_attr_link')) {
            echo '<p id="attr"><a id="attr-a" href="', esc_url('https://www.webbjocke.com/simple-wp-sitemap/'), '" target="_blank">', __('Generated by: Simple Wp Sitemap', 'simple-wp-sitemap'), '</a></p>';
        }
    }

    // Sorts or shuffles array and returns as string
    public function sortToString ($urls) {
        switch ($this->orderby) {
            case 'name': sort($urls); break;
            case 'rand': shuffle($urls); break;
        }
        return implode('', $urls);
    }

    // Sort function for uasort
    public function sortDate ($a, $b) {
        return $b['date'] - $a['date'];
    }
}
