<?php get_header();?>
                    	<div class="col-md-8">
                        	<div class="contant_area">
								<?php if ( have_posts() ) : while( have_posts() ) : the_post(); ?>
									<?php get_template_part( 'content' ); ?>
								<?php endwhile; ?>

								<?php colorbox_paging_nav(); ?>
									
								<?php else : ?>
									<?php get_template_part( 'content', 'none' ); ?>
								<?php endif; ?>
                            </div>
                        </div>
                        <?php get_sidebar();?>
<?php get_footer();?>