<?php 
/**
 * content.php
 *
 * The default template for displaying content.
 */
?>

<div data-uk-scrollspy="{cls:' uk-animation-fade', delay:600}" class="single_post">
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<!-- Article header -->
	<header class="entry-header"> 
	
	<?php // If single page, display the title
		// Else, we display the title in a link
		if ( is_single() ) : ?>
			<h2><?php the_title(); ?></h2> <hr />
		<?php else : ?>
			<h2><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2> <hr />
		<?php endif; ?>
		
		
		
		
		<!-- If the post has a thumbnail and it's not password protected
		// then display the thumbnail
		// If no thumbnail, then display a default thumbnail.-->
			<div class="post_thumblain floatleft">
												
				<?php 
					if ( has_post_thumbnail() && ! post_password_required() ) {
						the_post_thumbnail('colorbox-post-image', array('class' => 'post-thumb'));
					}
					else { ?>
						<img src="<?php echo COLORBOX_IMAGES . '/wordpress.png' ?>">
						
						<?php
					}
					?>
				
				
				<div class="single_post_date"><?php echo get_the_date( 'd M, Y' ); ?></div>
			</div>

		
		<?php // If single page, display the title
		// Else, we display the title in a link
		if ( is_single() ) : ?>
			<p class="entry-meta">
				<?php 
					// Display the meta information
					colorbox_post_meta();
				?>
			</p>
		<?php else : ?>
			<?php echo "" ; ?>
		<?php endif; ?>
		
	</header> <!-- end entry-header -->

	<!-- Article content -->
	<div class="entry-content single_post_contant">
		<?php
			if ( is_single() ) {
				the_content( __( 'Continue reading &rarr;', 'colorbox' ) );
			} elseif ( is_page() ){
				the_content( __( 'Continue reading &rarr;', 'colorbox' ) );
				}
			else {
				the_excerpt();

			}
        
        
				wp_link_pages();
		?>
	</div> <!-- end entry-content -->

	<!-- Article footer -->
	 <!-- end entry-footer -->
</article>
</div>