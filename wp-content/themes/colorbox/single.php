<?php get_header();?>
                    	<div class="col-md-8">
                        	<div class="contant_area">
								<?php while( have_posts() ) : the_post(); ?>

										<?php echo get_template_part('content');?>

									<?php if ( comments_open() ) :
										comments_template();
									endif; ?>
								<?php endwhile; ?>
                            </div>
                        </div>
                        <?php get_sidebar();?>
<?php get_footer();?>