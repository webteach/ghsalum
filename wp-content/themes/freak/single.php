<?php
/**
 * The template for displaying all single posts.
 *
 * @package freak
 */

get_header(); ?>
	</div></div><!--.mega-container-->
	
	<header class="entry-header freak-single-entry-header">
	<div class="entry-header-bg" style="background-image:url(<?php $im = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' ); echo $im[0]; ?>)"></div>
	<div class="layer">
		<div class="container">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			
			
			<div class="entry-meta">
			<?php if( have_posts() ) the_post(); ?>
				<?php freak_posted_on(); ?>
			<?php rewind_posts(); ?>	
			</div><!-- .entry-meta -->
		</div>
	</div>	
	</header><!-- .entry-header -->
	<div class="mega-container content">
		<div class="container content">
	<div id="primary-mono" class="content-area <?php do_action('freak_primary-width') ?>">
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<?php get_template_part( 'content', 'single' ); ?>

			<?php //freak_post_nav(); ?>

			<?php
				// If comments are open or we have at least one comment, load up the comment template
				if ( comments_open() || get_comments_number() ) :
					comments_template();
				endif;
			?>

		<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
