<?php 
/**
* Template Name: Full Width
*/
get_header();?>
    <div id="kt-main" role="main">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-md-12">
                <?php if(have_posts()):while(have_posts()):the_post(); ?>
                    <div <?php post_class('kt-article'); ?>>
                        <div class="row">
                            <!-- Main Blog Post -->
                                <div class="col-md-12">
                                <?php if(has_post_thumbnail()): the_post_thumbnail(); endif; ?>
                                <!-- Blog Post Title -->
                                <a class="kt-article-title" href="<?php the_permalink();?>" title="<?php the_title_attribute(); ?>">
                                <?php 
                                if(get_post_meta(get_the_ID(), 'casualblog_disable-title',true) == 'No'):
                                    $thetitle = get_the_title($post->ID);
                                    $origpostdate = get_the_date('M d, Y', $post->post_parent);
                                    if($thetitle == null):echo $origpostdate; 
                                    else:
                                    the_title();
                                    endif;
                                endif;
                                ?>
                                </a>    
                                <!-- Blog Post Title ends here -->
                                <!-- Blog Post Main Content/Excerpt -->
                                <div class="kt-article-content">
                                    <?php the_content(); ?>
                                </div>
                                <?php wp_link_pages( array( 'before' => '<div class="page-link"><span>'. __( 'Pages:', 'school' ) . '</span>', 'after' => '</div>')); ?>
                                <div class="clearfix"></div>
                                <!-- Blog Post Main Content/Excerpt ends -->
                                </div>
                            <!-- Main Blog Post Ends -->
                        </div>
                        <div class="row">
                        <div class="col-md-12">
                            <div id="kt-comments">
                                <?php comments_template( '', true ) ; ?>
                            </div>
                        </div>
                    </div>  
                    </div>
                <?php endwhile; endif;?>
                </div>
                <!-- Sidebar -->
                
            </div>
        </div>
    </div>
<?php get_footer(); ?>