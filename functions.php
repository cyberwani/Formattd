<?php
/**
 * Formattd functions and definitions
 *
 * Sets up the theme and provides some helper functions. Some helper functions
 * are used in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * The first function, formattd_setup(), sets up the theme by registering support
 * for various features in WordPress, such as post thumbnails, navigation menus, and the like.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development and
 * http://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook. The hook can be removed by using remove_action() or
 * remove_filter() and you can attach your own function to the hook.
 *
 * We can remove the parent theme's hook only after it is attached, which means we need to
 * wait until setting up the child theme:
 *
 * <code>
 * add_action( 'after_setup_theme', 'my_child_theme_setup' );
 * function my_child_theme_setup() {
 *     // We are providing our own filter for excerpt_length (or using the unfiltered value)
 *     remove_filter( 'excerpt_length', 'formattd_excerpt_length' );
 *     ...
 * }
 * </code>
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package WordPress
 * @subpackage Formattd
 * @since Formattd 0.1
 */

/**
 * In wp-includes/vars.php we set some user-agent variables. 
 * Let's be more specific about iPad vs iPhone:
 */
$is_ios = $is_ipod = $is_ipad = false;
if ( $is_iphone && stripos($_SERVER['HTTP_USER_AGENT'], 'ipad') !== false ) {
	$is_ipad = true;
	$is_iphone = false;
}

/* ...and iPod Touch... */
if ( $is_iphone && stripos($_SERVER['HTTP_USER_AGENT'], 'ipod') !== false ) {
	$is_ipod = true;
	$is_iphone = false;
}

/* ...they're all in the iOS family... */
if ( $is_iphone || $is_ipad || $is_ipod )
  $is_ios = true;

$formattd_css_version = '0.0.23';

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * Used to set the width of images and content. Should be equal to the width the theme
 * is designed for, generally via the style.css stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 480;
        if ( $is_ios ) {
          $content_width = 250; // for ios portrait orientation
        }
}

/** Tell WordPress to run formattd_setup() when the 'after_setup_theme' hook is run. */
add_action( 'after_setup_theme', 'formattd_setup', 9 );

if ( ! function_exists( 'formattd_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * To override formattd_setup() in a child theme, add your own formattd_setup to your child theme's
 * functions.php file.
 *
 * @uses add_theme_support() To add support for post thumbnails and automatic feed links.
 * @uses register_nav_menus() To add support for navigation menus.
 * @uses add_custom_background() To add support for a custom background.
 * @uses add_editor_style() To style the visual editor.
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since Formattd 0.1
 */
function formattd_setup() {
        global $is_ios, $is_iphone, $is_ipad, $is_ipod, $formattd_css_version;
	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Post Format support. You can also use the legacy "gallery" or "asides" (note the plural) categories.
	add_theme_support( 'post-formats', array( 'aside', 'link', 'image', 'video', 'quote', 'gallery', 'status', 'chat', 'audio' ) );

	// This theme uses post thumbnails
	add_theme_support( 'post-thumbnails' );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Auto-add a float-right thumbnail featured image, when set
        if (function_exists('set_post_thumbnail_size')) {
                if ($is_iphone || $is_ipod) {
                  set_post_thumbnail_size( 120, 120, true );
                } else {
        	  set_post_thumbnail_size( 240, 240, true );
                }
	}
	
	if (function_exists('add_image_size')) {
	  add_image_size('thumbnail', 125, 125, true);
	  add_image_size('featured', 240, 240, true);
	  add_image_size('small', 125, 170);
	  add_image_size('medium', 240, 320);
	  add_image_size('large', 380, 512);
	  add_image_size('xlarge', 480, 640);
	  add_image_size('full', 9999, 9999);
	}
	
	add_filter('the_content', 'formattd_post_thumbnail');
	
  	// Make theme available for translation
	// Translations can be filed in the /languages/ directory
	load_theme_textdomain( 'formattd', TEMPLATEPATH . '/languages' );

	$locale = get_locale();
	$locale_file = TEMPLATEPATH . "/languages/$locale.php";
	if ( is_readable( $locale_file ) )
		require_once( $locale_file );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'formattd' ),
	) );

	// Add official.fm as an oembed provider:
	wp_oembed_add_provider('http://official.fm/*', 'http://official.fm/services/oembed/');

}
endif;

add_action('init', 'formattd_init', 9);

if (!function_exists('formattd_init')) :
function formattd_init() {
  global $formattd_css_version;
  
	// Dropdown menus
        wp_enqueue_script('dropdown', trailingslashit( get_template_directory_uri() ) . 'js/jquery.dropdownPlain.js', array('jquery'), '1.1');

	// iOS scaling fix
        wp_enqueue_script('ios-scaling', trailingslashit( get_template_directory_uri() ) . 'js/ios-scaling-bugfix.js', array(), '1.0');

        // Load main stylesheet
        if (! is_admin() ) {
	  wp_enqueue_style( 'formattd', get_template_directory_uri() . '/style.css', array(), $formattd_css_version );
  	}
}
endif;

// Add X-UA-Compatible header in HTTP, not in HTML
if ( ! function_exists( 'formattd_redirect' ) ) :
function formattd_redirect() {
	// Send as an HTTP header instead using meta http-equiv.
	// See: http://lists.w3.org/Archives/Public/www-validator/2010Nov/0050.html
	@header( 'X-UA-Compatible: IE=edge,chrome=1' );
}
endif;
add_action( 'template_redirect', 'formattd_redirect' );

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * To override this in a child theme, remove the filter and optionally add
 * your own function tied to the wp_page_menu_args filter hook.
 *
 * @since Formattd 0.1
 */
if (! function_exists('formattd_page_menu_args') ) :
function formattd_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
endif;
add_filter( 'wp_page_menu_args', 'formattd_page_menu_args' );

/**
 * Sets the post excerpt length to 75 words.
 *
 * To override this length in a child theme, remove the filter and add your
 * own function tied to the excerpt_length filter hook.
 *
 * @since Formattd 0.1
 * @return int
 */
if (! function_exists('formattd_excerpt_length') ) :
function formattd_excerpt_length( $length ) {
	return 75;
}
endif;
add_filter( 'excerpt_length', 'formattd_excerpt_length' );

/**
 * Returns a "Continue Reading" link for excerpts
 *
 * @since Formattd 0.1
 * @return string "Continue Reading" link
 */
if (! function_exists('formattd_continue_reading_link') ) :
function formattd_continue_reading_link() {
	return ' <a href="'. get_permalink() . '">' . __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'formattd' ) . '</a>';
}
endif;

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and formattd_continue_reading_link().
 *
 * To override this in a child theme, remove the filter and add your own
 * function tied to the excerpt_more filter hook.
 *
 * @since Formattd 0.1
 * @return string An ellipsis
 */
if (! function_exists('formattd_auto_excerpt_more') ) :
function formattd_auto_excerpt_more( $more ) {
	return ' &hellip; ' . formattd_continue_reading_link();
}
endif;
add_filter( 'excerpt_more', 'formattd_auto_excerpt_more' );

/**
 * Adds a pretty "Continue Reading" link to custom post excerpts.
 *
 * To override this link in a child theme, remove the filter and add your own
 * function tied to the get_the_excerpt filter hook.
 *
 * @since Formattd 0.1
 * @return string Excerpt with a pretty "Continue Reading" link
 */
if (! function_exists('formattd_custom_excerpt_more') ) :
function formattd_custom_excerpt_more( $output ) {
	if ( has_excerpt() && ! is_attachment() ) {
		$output .= formattd_continue_reading_link();
	}
	return $output;
}
endif;
add_filter( 'get_the_excerpt', 'formattd_custom_excerpt_more' );

/**
 * Remove inline styles printed when the gallery shortcode is used.
 *
 * Galleries are styled by the theme in Formattd's style.css. This is just
 * a simple filter call that tells WordPress to not use the default styles.
 *
 * @since Formattd 0.1
 */
add_filter( 'use_default_gallery_style', '__return_false' );

if ( ! function_exists( 'formattd_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own formattd_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Formattd 0.1
 */
function formattd_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	$GLOBALS['depth'] = $depth;
	switch ( $comment->comment_type ) :
		case '' :
	?>
	<!-- comment callback -->
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div id="comment-<?php comment_ID(); ?>">
		<div class="comment-author vcard">
			<?php echo get_avatar( $comment, 40 ); ?>
			<?php printf( __( '%s <span class="says">says:</span>', 'formattd' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
		</div><!-- .comment-author .vcard -->
		<?php if ( $comment->comment_approved == '0' ) : ?>
			<em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'formattd' ); ?></em>
			<br />
		<?php endif; ?>

		<div class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
			<?php
				/* translators: 1: date, 2: time */
				printf( __( '%1$s at %2$s', 'formattd' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'formattd' ), ' ' );
			?>
		</div><!-- .comment-meta .commentmetadata -->

		<div class="comment-body"><?php comment_text(); ?></div>

		<div class="reply">
			<?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
		</div><!-- .reply -->
	</div><!-- #comment-##  -->

	<?php
			break;
		case 'pingback'  :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'formattd' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'formattd' ), ' ' ); ?></p>
	<?php
			break;
	endswitch;
}
endif;

/**
 * Register widgetized areas, including two sidebars and four widget-ready columns in the footer.
 *
 * To override formattd_widgets_init() in a child theme, remove the action hook and add your own
 * function tied to the init hook.
 *
 * @since Formattd 0.1
 * @uses register_sidebar
 */
if (! function_exists('formattd_widgets_init') ) :
function formattd_widgets_init() {
	// Header widget area
	register_sidebar( array(
		'name' => __( 'Above Header Widget Area', 'formattd' ),
		'id' => 'above-header',
		'description' => __( 'In the header, before site name', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Primary sidebar, located at the top of the sidebar.
	register_sidebar( array(
		'name' => __( 'Primary Widget Area', 'formattd' ),
		'id' => 'primary-aside',
		'description' => __( 'The primary widget area', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Secondary sidebar, located below the Primary Widget Area in the sidebar. Empty by default.
	register_sidebar( array(
		'name' => __( 'Secondary Widget Area', 'formattd' ),
		'id' => 'secondary-aside',
		'description' => __( 'The secondary widget area', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Top of the content area. Empty by default.
	register_sidebar( array(
		'name' => __( 'Index Top Widget Area', 'formattd' ),
		'id' => 'index-top',
		'description' => __( 'Appears between header and content on index and single post pages.', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Top of the content area. Empty by default.
	register_sidebar( array(
		'name' => __( 'Index Insert Widget Area', 'formattd' ),
		'id' => 'index-insert',
		'description' => __( 'Appears between 1st and 2nd post on index page.', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Top of the content area. Empty by default.
	register_sidebar( array(
		'name' => __( 'Index Bottom Widget Area', 'formattd' ),
		'id' => 'index-bottom',
		'description' => __( 'Appears below posts on index page.', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Top of the content area. Empty by default.
	register_sidebar( array(
		'name' => __( 'Single Top Widget Area', 'formattd' ),
		'id' => 'single-top',
		'description' => __( 'Appears between header and content on single post pages.', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Top of the content area. Empty by default.
	register_sidebar( array(
		'name' => __( 'Single Bottom Widget Area', 'formattd' ),
		'id' => 'single-bottom',
		'description' => __( 'Appears below posts on single pages.', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Top of the content area. Empty by default.
	register_sidebar( array(
		'name' => __( 'Page Top Widget Area', 'formattd' ),
		'id' => 'page-top',
		'description' => __( 'Appears between header and content on pages.', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Top of the content area. Empty by default.
	register_sidebar( array(
		'name' => __( 'Page Bottom Widget Area', 'formattd' ),
		'id' => 'Page-bottom',
		'description' => __( 'Appears below posts on pages.', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Footer 1, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'First Footer Widget Area', 'formattd' ),
		'id' => 'first-footer-widget-area',
		'description' => __( 'The first footer widget area', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Footer 2, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Second Footer Widget Area', 'formattd' ),
		'id' => 'second-footer-widget-area',
		'description' => __( 'The second footer widget area', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Footer 3, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Third Footer Widget Area', 'formattd' ),
		'id' => 'third-footer-widget-area',
		'description' => __( 'The third footer widget area', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// Footer 4, located in the footer. Empty by default.
	register_sidebar( array(
		'name' => __( 'Fourth Footer Widget Area', 'formattd' ),
		'id' => 'fourth-footer-widget-area',
		'description' => __( 'The fourth footer widget area', 'formattd' ),
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
}
endif;
/** Register sidebars by running formattd_widgets_init() on the widgets_init hook. */
add_action( 'widgets_init', 'formattd_widgets_init' );

/**
 * Removes the default styles that are packaged with the Recent Comments widget.
 *
 * To override this in a child theme, remove the filter and optionally add
 * your own function tied to the widgets_init action hook.
 *
 * This function uses a filter (show_recent_comments_widget_style) new in
 * WordPress 3.1 to remove the default style.  Using Formattd 1.2 in
 * WordPress 3.0 will show the styles, but they won't have any effect on the
 * widget in default Formattd styling.
 *
 * @since Formattd 0.1
 */
if (! function_exists('formattd_remove_recent_comments_style') ) :
function formattd_remove_recent_comments_style() {
	add_filter( 'show_recent_comments_widget_style', '__return_false' );
}
endif;
add_action( 'widgets_init', 'formattd_remove_recent_comments_style' );

if ( ! function_exists( 'formattd_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 *
 * @since Formattd 0.1
 */
function formattd_posted_on() {
	printf( __( '<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', 'formattd' ),
		'meta-prep meta-prep-author',
		sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>',
			get_permalink(),
			esc_attr( get_the_time() ),
			get_the_date()
		),
		sprintf( '<span class="author vcard"><a class="url fn n" rel="author" href="%1$s" title="%2$s">%3$s</a></span>',
			get_author_posts_url( get_the_author_meta( 'ID' ) ),
			sprintf( esc_attr__( 'View all posts by %s', 'formattd' ), get_the_author() ),
			get_the_author()
		)
	);
}
endif;

if ( ! function_exists( 'formattd_posted_in' ) ) :
/**
 * Prints HTML with meta information for the current post (category, tags and permalink).
 *
 * @since Formattd 0.1
 */
function formattd_posted_in() {
	// Retrieves tag list of current post, separated by commas.
	$tag_list = get_the_tag_list( '', ', ' );
	if ( $tag_list ) {
		$posted_in = __( 'This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'formattd' );
	} elseif ( is_object_in_taxonomy( get_post_type(), 'category' ) ) {
		$posted_in = __( 'This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'formattd' );
	} else {
		$posted_in = __( 'Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'formattd' );
	}
	// Prints the string, replacing the placeholders.
	printf(
		$posted_in,
		get_the_category_list( ', ' ),
		$tag_list,
		get_permalink(),
		the_title_attribute( 'echo=0' )
	);
}
endif;

if ( ! function_exists( 'formattd_post_date' ) ) :
/**
 * Generates the calendar-style dates which hang in the left margin.
 */
function formattd_post_date() {
	$mon = get_the_time('M');
	$day = get_the_time('d');
	$year = get_the_time('Y');
	
	printf('<div class="post-date"><span class="month">%s</span><span class="day">%s</span><span class="year">%s</span></div>', $mon, $day, $year);
}
endif;

if (! function_exists('formattd_post_thumbnail') ) :
/**
 * Utility filter to inject post thumbnails into posts.
 */
function formattd_post_thumbnail($text) {
  if (function_exists('has_post_thumbnail') && has_post_thumbnail() /* && (is_home() || is_singular()) */) {
    $text = '<div class="featured-image align-right" style="float: right;">' . get_the_post_thumbnail() . '</div>' . $text;
  }
  
  return $text;
}
endif;

if (! function_exists('formattd_extract_first_link') ) :
/**
 * Extract the title and url for a post object for a 'link' post format.
 */
function formattd_extract_first_link($post) {
  $str = $post->post_content;
  if ( preg_match('%^(https?://[^\s]*)$%', trim($str), $matches) ) {
    $url = $matches[1];
    if ($post->post_title) {
      $title = $post->post_title;
    } else {
      $title = $url;
    }
    return array('url' => $url, 'title' => $title );
  }
  
  preg_match('%<a\s*+(.*?)href=(["\']?)(.*?)\2(.*?)>(.*?)</a>%', trim($str), $matches);
  $url = $matches[3];
  $title = $post->post_title ? $post->post_title : $matches[5];
  
  return array( 'url' => $url, 'title' => $title );
}
endif;

if (! function_exists('formattd_time_ago') ) :
function  formattd_time_ago($timestamp=0, $granularity=2, $format='Y-m-d H:i:s') {
        if ( 0 === $timestamp ) {
          // fetch the post time in UTC
          $timestamp = get_post_time('U', true);
        }

        $difference = time() - $timestamp;
        if($difference < 0) return sprintf( '<span class="timestamp updated" title="%s">%s</span>', esc_attr(date('c', $timestamp)), 'Not yet...') ;
        elseif($difference < 31536000){
                $periods = array('mon' => 2592000, 'wk' => 604800,'day' => 86400,'hr' => 3600,'min' => 60 );
                $output = '';
                foreach($periods as $key => $value){
                        if($difference >= $value){
                                $time = round($difference / $value);
                                $difference %= $value;
                                $output .= ($output ? ' ' : '').$time.' ';
                                $output .= (($time > 1 /* && $key == 'day' */) ? $key.'s' : $key);
                                $granularity--;
                        }
                        if($granularity == 0) break;
                }
                $output = ($output ? $output : 'Just now').' ago';
                return sprintf('<span class="timestamp updated" title="%s">%s</span>', esc_attr(date('c',$timestamp)) ,$output);
        }
        else return sprintf( '<span class="timestamp updated" title="%s">%s</span>', esc_attr(date('c', $timestamp)), date($format, $timestamp) );
}
endif;

if (! function_exists('formattd_process_chat') ) :
/**
 * Auto-bold names in chat posts
 */
function formattd_process_chat( $content ) {
  if (has_post_format('chat')) {
    $content = preg_replace('%<p>\s*([^:]+):(\s.*)</p>%e', '\'<p class="chat"><span class="person person-\'.sanitize_title(\'\\1\').\'">\\1:</span>\\2</p>\'', $content);
  }
  return $content; 
}
endif;
// Run after WP html formatting
add_filter('the_content', 'formattd_process_chat', 15);


if (! function_exists('formattd_post_format_detect') ) :
/**
 * If a post comes from XML-RPC or APP, try to detect and set the post
 * format. There is currently not a good single filter or action hook
 * to make this easy. So we cheat. We use the wp_insert_post_data filter
 * to detect the format, and also to strip out the sentinel string. At
 * that time, we set a global flag containing the detected format.
 *
 * Then, we tell the wp_insert_post action hook to use that flag to
 * actually set the post format accordingly.
 */
function formattd_post_format_detect( $data, $postarr ) {
  global $dc_formattd_post_format;
  if ( defined('XMLRPC_REQUEST') || defined('APP_REQUEST') ) {
    /* Look for an image at the beginning of a post. Optionally preceded
     * by <br> or <p> tags. Optionally linked with an <a> tag.
     */
		if ( preg_match('%^(((<p[^>]*?>)?)((<br ?/?>)*?))*?(<a\s+[^>]+>)?<img\s+[^>]+>%', $post->post_content) ) {
		  $dc_formattd_post_format = 'image';
    }
    
    /* This is insufficient. And transcoding video is a real pain.  I think
     * the best way to handle this is to upload videos to a dedicated
     * service (YouTube, Vimeo, Flickr, etc), and use plugins to import them
     * as posts from there. Maybe one day there will be a universal codec
     * and container format shared by all browsers and mobile devices. Yeah,
     * right.
     */
    /*
		if ( preg_match('%^(<br ?/?>)*<video\s+[^>]+>%', $post->post_content) ) {
		  $dc_formattd_post_format = 'video';
    }
    */
    
    /**
     * Look for a :FORMAT: sentinel string in the first 30 chars. If we see
     * it, use that as the post format.  E.g., '<p>:status: Hanging with my
     * buds</p>' would become a 'format-status' post.  The format specifier
     * is not case-sensitive.
     */
    $count = preg_match('%:([A-Za-z]+):%', substr($data['post_content'], 0, 30), $matches);
    if ( $count ) {
      // Strip our :FORMAT: sentinel string from the content
      $data['post_content'] = preg_replace('%:'.$matches[1].':\s*%i', '', $data['post_content'], 1);
      $dc_formattd_post_format = $matches[1];
    }

    /**
     * Look for [gallery] in the post. If we see it, set the gallery post
     * format.
     */
    if ( false !== strpos('[gallery]', $data['post_content']) ) {
      $dc_formattd_post_format = 'gallery';
    }

    if ( $dc_formattd_post_format ) {
      add_action( 'wp_insert_post', 'formattd_post_format_set', 10, 2 );
    }
  }
  
  return $data;
}
endif;

if (! function_exists('formattd_post_format_set') ) :
function formattd_post_format_set( $postid, $post ) {
  global $dc_formattd_post_format;
  // Validate format
  $dc_formattd_post_format = sanitize_key($dc_formattd_post_format);
  
  if ( !array_key_exists( $dc_formattd_post_format, get_post_format_strings() ) ) {
    // not a valid post format. do nothing.
    return;
  }
  set_post_format( $postid, $dc_formattd_post_format );
}
endif;

add_filter('wp_insert_post_data', 'formattd_post_format_detect', 10, 2);

