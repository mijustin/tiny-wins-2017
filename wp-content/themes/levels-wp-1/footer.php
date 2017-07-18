<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package levels
 */

?>

	</div><!-- #content -->

	<footer id="colophon" class="footer" role="contentinfo">
    <div class="container container--grid">
  		<div class="footer__bottom">
        <p class="footer__bottom-right"><?php echo get_option('footer_content_right') ?></p>
        <p><?php echo get_option('footer_content_left') ?></p>
      </div>
    </div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>
</body>
</html>
