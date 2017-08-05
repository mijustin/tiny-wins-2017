<?php
/**
 * Template part for displaying page content in page.php.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package levels
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="header header--wp header--centered header--dark">
		<?php the_title( '<h1 class="header__title header--centered header--bright">', '</h1>' ); ?>
		<?php
		/*
		 	* We're going to render the WPcomplete shortcodes in the header.
			* The WPcomplete plugin must be activated in order for the shortcodes to render.
			*/ ?>
		<p>You've completed <?php echo do_shortcode("[progress_ratio]"); ?> of the tactics so far. <?php echo do_shortcode("[progress_bar]"); ?></p>
	</header><!-- .entry-header -->

	<div id="inner">

	<div class="entry-content">
		<?php
			the_content();

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'levels' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php
			// edit_post_link(
			// 	sprintf(
			// 		/* translators: %s: Name of current post */
			// 		esc_html__( 'Edit %s', 'levels' ),
			// 		the_title( '<span class="screen-reader-text">"', '"</span>', false )
			// 	),
			// 	'<span class="edit-link">',
			// 	'</span>'
			// );
		?>
	</footer><!-- .entry-footer -->

	</div><!-- #inner -->
</article><!-- #post-## -->
