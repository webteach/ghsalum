<?php 
/**
 * functions.php
 *
 * The theme's functions and definitions.
 */

/**
 * ----------------------------------------------------------------------------------------
 * 1.0 - Define constants and as for required function files.
 * ----------------------------------------------------------------------------------------
 */
define( 'COLORBOX_THEMEROOT', get_stylesheet_directory_uri() );
define( 'COLORBOX_IMAGES', COLORBOX_THEMEROOT . '/images' );
define( 'COLORBOX_SCRIPTS', COLORBOX_THEMEROOT . '/js' );




/**
 * ----------------------------------------------------------------------------------------
 * 2.0 - Extra jQuery and other codes
 * ----------------------------------------------------------------------------------------
 */

if ( ! function_exists( 'colorbox_footer_jquery' ) ) {
	function colorbox_footer_jquery() {
        echo'<script>
			jQuery(document).ready(function(){
			  jQuery(".mainmenu ul li ul").hover(function(){
				jQuery(this).parent(".mainmenu ul li").addClass("active");
				},function(){
				jQuery(".mainmenu ul li").removeClass("active");
			  });
			});
				
		</script>';
    }
    add_action('wp_footer','colorbox_footer_jquery' );
    
}

if ( ! function_exists( 'colorbox_header_extra' ) ) {
	function colorbox_header_extra() {
        echo'
		<!--[if lt IE 9]>
			<script src="'.get_template_directory_uri().'/js/html5.js"></script>
		<![endif]-->';
    }
    add_action('wp_head','colorbox_header_extra' );
    
}


/**
 * ----------------------------------------------------------------------------------------
 * 3.0 - Set up the content width value based on the theme's design.
 * ----------------------------------------------------------------------------------------
 */
if ( ! isset( $content_width ) ) {
	$content_width = 800;
}


/**
 * ----------------------------------------------------------------------------------------
 * 4.0 - Set up theme default and register various supported features.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'colorbox_setup' ) ) {
	function colorbox_setup() {
		/**
		 * Make the theme available for translation.
		 */
		$lang_dir = COLORBOX_THEMEROOT . '/languages';
		load_theme_textdomain( 'colorbox', $lang_dir );

		/**
		 * Add support for automatic feed links.
		 */
		add_theme_support( 'automatic-feed-links' );

		/**
		 * Add support for post excerpts
		 */

			function colorbox_new_excerpt_more( $more ) {
				return ' <a class="readmore" href="'. get_permalink( get_the_ID() ) . '">' . __('Read More', 'colorbox') . '</a>';
			}
			add_filter( 'excerpt_more', 'colorbox_new_excerpt_more' );

		/**
		 * Add support for post thumbnails.
		 */
		add_theme_support( 'post-thumbnails' );
		/* 
			/* Add Support for homepage single post image sizes
		*/
		add_image_size( 'colorbox-post-image', 225, 225, true );
		
		
		/**
		 * Register nav menus.
		 */
		register_nav_menus(
			array(
				'main-menu' => __( 'Main Menu', 'colorbox' )
			)
		);
	}

	add_action( 'after_setup_theme', 'colorbox_setup' );
}


/**
 * ----------------------------------------------------------------------------------------
 * 5.0 - Display meta information for a specific post.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'colorbox_post_meta' ) ) {
	function colorbox_post_meta() {
		echo '<ul class="list-inline entry-meta">';

		if ( get_post_type() === 'post' ) {
			// If the post is sticky, mark it.
			if ( is_sticky() ) {
				echo '<li class="meta-featured-post"><i class="fa fa-thumb-tack"></i> ' . __( 'Sticky', 'colorbox' ) . ' </li>';
			}

			// Get the post author.
			printf(
				'<li class="meta-author"><a href="%1$s" rel="author">%2$s</a></li>',
				esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
				get_the_author()
			);

			// Get the date.
			echo '<li class="meta-date"> ' . get_the_date() . ' </li>';

			// The categories.
			$category_list = get_the_category_list( ', ' );
			if ( $category_list ) {
				echo '<li class="meta-categories"> ' . $category_list . ' </li>';
			}

			// The tags.
			$tag_list = get_the_tag_list( '', ', ' );
			if ( $tag_list ) {
				echo '<li class="meta-tags"> ' . $tag_list . ' </li>';
			}

			// Comments link.
			if ( comments_open() ) :
				echo '<li>';
				echo '<span class="meta-reply">';
				comments_popup_link( __( 'Leave a comment', 'colorbox' ), __( 'One comment so far', 'colorbox' ), __( 'View all % comments', 'colorbox' ) );
				echo '</span>';
				echo '</li>';
			endif;

			// Edit link.
			if ( is_user_logged_in() ) {
				echo '<li>';
				edit_post_link( __( 'Edit', 'colorbox' ), '<span class="meta-edit">', '</span>' );
				echo '</li>';
			}
		}
	}
}


/**
 * ----------------------------------------------------------------------------------------
 * 6.0 - Display navigation to the next/previous set of posts.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'colorbox_paging_nav' ) ) {
	function colorbox_paging_nav() { ?>
		<ul class="colorbox_pagination">
			<?php 
				if ( get_previous_posts_link() ) : ?>
				<li class="next">
					<?php previous_posts_link( __( 'Newer Posts &rarr;', 'colorbox' ) ); ?>
				</li>
				<?php endif;
			 ?>
			<?php 
				if ( get_next_posts_link() ) : ?>
				<li class="previous">
					<?php next_posts_link( __( '&larr; Older Posts', 'colorbox' ) ); ?>
				</li>
				<?php endif;
			 ?>
		</ul> <?php
	}
}


/**
 * ----------------------------------------------------------------------------------------
 * 7.0 - Register the widget areas.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'colorbox_widget_init' ) ) {
	function colorbox_widget_init() {
		if ( function_exists( 'register_sidebar' ) ) {
			register_sidebar(
				array(
					'name' => __( 'Main Widget Area', 'colorbox' ),
					'id' => 'sidebar-1',
					'description' => __( 'Appears on posts and pages.', 'colorbox' ),
					'before_widget' => '<div id="%1$s" class="single_sidebar %2$s">',
					'after_widget' => '</div> <!-- end widget -->',
					'before_title' => '<h2>',
					'after_title' => '</h2>',
				)
			);
		}
	}

	add_action( 'widgets_init', 'colorbox_widget_init' );
}

/**
 * ----------------------------------------------------------------------------------------
 * 8.0 - Function that validates a field's length.
 * ----------------------------------------------------------------------------------------
 */
if ( ! function_exists( 'colorbox_validate_length' ) ) {
	function colorbox_validate_length( $fieldValue, $minLength ) {
		// First, remove trailing and leading whitespace
		return ( strlen( trim( $fieldValue ) ) > $minLength );
	}
}


 
 
 
 
 
/**
 * ----------------------------------------------------------------------------------------
 * 9 - Load the custom scripts for the theme.
 * ----------------------------------------------------------------------------------------
 */
 
/* Add Editor Style */



 
if ( ! function_exists( 'colorbox_scripts' ) ) {
	function colorbox_scripts() {
		// Adds support for pages with threaded comments
		if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}

		// Register scripts
		wp_register_script( 'colorbox-bootstrap', COLORBOX_SCRIPTS . '/bootstrap.min.js', array( 'jquery' ), false, true );
		wp_register_script( 'colorbox-uikit', COLORBOX_SCRIPTS . '/uikit.min.js', array( 'jquery' ), false, true );

		// Load the custom scripts
		wp_enqueue_script( 'colorbox-bootstrap' );
		wp_enqueue_script( 'colorbox-uikit' );

		// Load the stylesheets
		
		add_editor_style();
		wp_enqueue_style( 'colorbox-lato', 'http://fonts.googleapis.com/css?family=Lato' );
		wp_enqueue_style( 'colorbox-droid', 'http://fonts.googleapis.com/css?family=Droid+Serif:700' );
		wp_enqueue_style( 'colorbox-yellowtail', 'http://fonts.googleapis.com/css?family=Yellowtail' );
		wp_enqueue_style( 'colorbox-master', COLORBOX_THEMEROOT . '/css/master.css' );
		wp_enqueue_style( 'colorbox-style', get_stylesheet_uri() );
		
		
	}

	add_action( 'wp_enqueue_scripts', 'colorbox_scripts' );
}
?>