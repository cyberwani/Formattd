<?php
/**
 * The widget area between the header and content of a single post page.
 *
 * @package WordPress
 * @subpackage Formattd
 * @since Formattd 0.1
 */
?>
<?php
	if (   ! is_active_sidebar( 'single-top' ) )
		return;
	// If we get this far, we have widgets. Let's do this.
?>

			<div id="single-top" class="widget-area" role="complementary">

					<ul class="xoxo">
						<?php dynamic_sidebar( 'single-top' ); ?>
					</ul>
			</div><!-- #single-top .widget-area -->
