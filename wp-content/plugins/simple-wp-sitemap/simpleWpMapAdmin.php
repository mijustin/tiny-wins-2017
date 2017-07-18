<?php defined('ABSPATH') or die;

/*
 * Simple Wp Sitemap admin interface
 */

?>
<div class="wrap">

    <h2 id="simple-wp-sitemap-h2">
        <img src="<?php echo esc_url(plugins_url('sign.png', __FILE__)); ?>" alt="<?php esc_attr_e('Simple Wp Sitemap logo', 'simple-wp-sitemap'); ?>" width="40" height="40">
        <span><?php _e('Simple Wp Sitemap settings', 'simple-wp-sitemap'); ?></span>
    </h2>

    <p><?php _e('Your two sitemaps are active! Here you can change and customize them.', 'simple-wp-sitemap'); ?></p>
    <p><b><?php _e('Links to your xml and html sitemap:', 'simple-wp-sitemap'); ?></b></p>

    <ul>
        <li><?php printf('%1$s <a href="%2$s">%2$s</a>', __('Xml sitemap:', 'simple-wp-sitemap'), $ops->getSitemapUrl('xml')); ?></li>
        <li>
            <?php _e('Html sitemap:', 'simple-wp-sitemap'); ?>
            <?php echo get_option('simple_wp_block_html') ? __('(disabled)', 'simple-wp-sitemap') : sprintf('<a href="%1$s">%1$s</a>', $ops->getSitemapUrl('html')); ?>
        </li>
    </ul>

    <noscript><?php _e('(Please enable javascript to edit options)', 'simple-wp-sitemap'); ?></noscript>

    <form method="post" action="<?php $ops->printSubmitUrl(); ?>" id="simple-wp-sitemap-form">

        <ul id="sitemap-settings">
            <li><a href="#sitemap-normal"><?php _e('General', 'simple-wp-sitemap'); ?></a></li>
            <li><a href="#sitemap-advanced"><?php _e('Order', 'simple-wp-sitemap'); ?></a></li>
            <li><a href="#sitemap-premium"><?php _e('Premium', 'simple-wp-sitemap'); ?></a></li>
        </ul>

        <table id="sitemap-normal" class="widefat form-table">
            <tr>
                <td>
                    <b><?php _e('Add pages', 'simple-wp-sitemap'); ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Add pages to the sitemaps in addition to your normal WordPress ones. Just paste "full" urls in the textarea like: <b>http://www.example.com/a-page/</b>. Each link on a new row (this will affect both your xml and html sitemap).', 'simple-wp-sitemap'); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <textarea rows="7" name="simple_wp_other_urls" placeholder="<?php echo esc_url('http://www.example.com/a-page/'); ?>" class="large-text code" id="swsp-add-pages-textarea"><?php $ops->printUrls('simple_wp_other_urls'); ?></textarea>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr>
                <td>
                    <b><?php _e('Block pages', 'simple-wp-sitemap'); ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Add pages you want to block from showing up in the sitemaps. Same as above, just paste every link on a new row. (Tip: copy paste links from one of your sitemaps to get correct urls).', 'simple-wp-sitemap'); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <textarea rows="7" name="simple_wp_block_urls" placeholder="<?php echo esc_url('http://www.example.com/block-this-page/'); ?>" class="large-text code"><?php $ops->printUrls('simple_wp_block_urls'); ?></textarea>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr>
                <td>
                    <b><?php _e('Extra sitemap includes', 'simple-wp-sitemap'); ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Check if you want to include categories, tags and/or author pages in the sitemaps.', 'simple-wp-sitemap'); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <p><input type="checkbox" name="simple_wp_disp_categories" id="simple_wp_cat" <?php checked(get_option('simple_wp_disp_categories')); ?>><label for="simple_wp_cat"> <?php _e('Include categories', 'simple-wp-sitemap'); ?></label></p>
                    <p><input type="checkbox" name="simple_wp_disp_tags" id="simple_wp_tags" <?php checked(get_option('simple_wp_disp_tags')); ?>><label for="simple_wp_tags"> <?php _e('Include tags', 'simple-wp-sitemap'); ?></label></p>
                    <p><input type="checkbox" name="simple_wp_disp_authors" id="simple_wp_authors" <?php checked(get_option('simple_wp_disp_authors')); ?>><label for="simple_wp_authors"> <?php _e('Include authors', 'simple-wp-sitemap'); ?></label></p>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr>
                <td>
                    <b><?php _e('Html sitemap', 'simple-wp-sitemap'); ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Enable or disable your html sitemap. This will not effect your xml sitemap.', 'simple-wp-sitemap'); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <select name="simple_wp_block_html" id="simple_wp_block_html">
                        <?php foreach (array('' => __('Enable', 'simple-wp-sitemap'), '1' => __('Disable', 'simple-wp-sitemap'), '404' => __('Disable and set to 404', 'simple-wp-sitemap')) as $key => $val) { ?>
                            <option value="<?php echo $key; ?>" <?php selected(get_option('simple_wp_block_html'), $key); ?>><?php echo $val; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>

            <tr><td><hr></td></tr>

            <tr>
                <td>
                    <b><?php _e('Like the plugin?', 'simple-wp-sitemap'); ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('Show your support by rating the plugin at wordpress.org, and/or by adding an attribution link to the sitemap.html file :)', 'simple-wp-sitemap'); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="checkbox" name="simple_wp_attr_link" id="simple_wp_check" <?php checked(get_option('simple_wp_attr_link')); ?>><label for="simple_wp_check"> <?php _e('Add "Generated by Simple Wp Sitemap" link at bottom of sitemap.html', 'simple-wp-sitemap'); ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('A donation is also always welcome!', 'simple-wp-sitemap'); ?>
                    <a href="<?php echo esc_url('https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=UH6ANJA7M8DNS'); ?>" id="simple-wp-sitemap-donate" class="button-secondary" target="_blank"><?php _e('Donate', 'simple-wp-sitemap'); ?></a>
                </td>
            </tr>
        </table><!-- sitemap-normal -->

        <table id="sitemap-advanced" class="widefat form-table">
            <tr>
                <td>
                    <b><?php _e('Display order and titles', 'simple-wp-sitemap'); ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('If you want to change the display order in your sitemaps, click the arrows to move sections up or down. They will be displayed as ordered below (highest up is displayed first and lowest down last)', 'simple-wp-sitemap'); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <ul id="sitemap-display-order">
                        <?php if (!($orderArray = $ops->checkOrder(get_option('simple_wp_disp_sitemap_order')))) {
                            $orderArray = $ops->getDefaultOrder();
                        }
                        foreach ($orderArray as $key => $val) {
                            printf(
                                '<li><input type="text" class="swp-name" data-name="%1$s" value="%2$s"><span class="sitemap-down" title="move down"></span><span class="sitemap-up" title="move up"></span>' .
                                '<input type="hidden" name="simple_wp_%1$s_n" value="%3$d"></li>',
                                esc_attr($key), esc_attr($val['title']), esc_attr($val['i'])
                            );
                        } ?>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>
                    <b><?php _e('Last updated text:', 'simple-wp-sitemap'); ?></b>
                    <input type="text" name="simple_wp_last_updated" placeholder="<?php esc_attr_e('Last updated', 'simple-wp-sitemap'); ?>" value="<?php echo esc_attr(get_option('simple_wp_last_updated')); ?>" id="simple_wp_last_updated">
                </td>
            </tr>
            <tr>
                <td>
                    <b><?php _e('Sort posts and pages:', 'simple-wp-sitemap'); ?></b>
                    <select id="simple_wp_order_by" name="simple_wp_order_by">
                        <?php foreach (array('' => __('Posted date', 'simple-wp-sitemap'), 'modified' => __('Last updated date', 'simple-wp-sitemap'), 'name' => __('Alphabetical', 'simple-wp-sitemap'), 'rand' => __('Random', 'simple-wp-sitemap'), 'comment_count' => __('Comments', 'simple-wp-sitemap'), 'parent' => __('Parents', 'simple-wp-sitemap')) as $key => $val) { ?>
                            <option value="<?php echo $key; ?>" <?php selected(get_option('simple_wp_order_by'), $key); ?>><?php echo $val; ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="button" id="sitemap-defaults" class="button-secondary" value="<?php esc_attr_e('Restore default order', 'simple-wp-sitemap'); ?>">
                </td>
            </tr>
        </table><!-- sitemap-advanced -->

        <table id="sitemap-premium" class="widefat form-table">
            <tr>
                <td>
                    <b><?php _e('Simple Wp Sitemap Premium', 'simple-wp-sitemap'); ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    <?php _e('There\'s a premium version of Simple Wp Sitemap available which includes:', 'simple-wp-sitemap'); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <ul class="simple-wp-sitemap-includes">
                        <li><?php _e('Split sitemaps', 'simple-wp-sitemap'); ?></li>
                        <li><?php _e('Image sitemaps', 'simple-wp-sitemap'); ?></li>
                        <li><?php _e('Display with shortcode', 'simple-wp-sitemap'); ?></li>
                        <li><?php _e('Exclude directories', 'simple-wp-sitemap'); ?></li>
                        <li><?php _e('And much more!', 'simple-wp-sitemap'); ?></li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>
                    <?php printf(__('Available at: <a target="_blank" href="%s">webbjocke.com/downloads/simple-wp-sitemap-premium</a>', 'simple-wp-sitemap'), esc_url('https://www.webbjocke.com/downloads/simple-wp-sitemap-premium/')); ?>
                </td>
            </tr>
        </table><!-- sitemap-premium -->

        <p class="submit"><input type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'simple-wp-sitemap'); ?>"></p>

        <p><i><?php _e('(If you have a caching plugin, you might have to clear cache before changes will be shown in the sitemaps)', 'simple-wp-sitemap'); ?></i></p>

    </form>

</div><!-- wrap -->
