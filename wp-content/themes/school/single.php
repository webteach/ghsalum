<?php get_header(); ?>
    <div id="kt-main" role="main">
        <div class="container">
        <div class="row" id="kt-blog-title">  
            <div class="col-md-1">
            <i class="icon_pencil"></i>   
            </div>
            <div class="col-md-11">
            <h1><?php echo school_get_title(get_the_ID())?></h1>
            <span class="small">  
            <a href="<?php echo esc_url(home_url());?>"> 
                                                    
            <?php echo __('Home','school');?></a> / <?php echo school_get_title(get_the_ID()); ?></span> - <span><?php the_time(get_option('date_format')); ?></span>
            <span>
            <?php echo __(', by','school'); ?> 
            <?php 
            global $post;
            $author_id=$post->post_author; 
            echo the_author_meta( 'user_nicename', $author_id );?>
            </span>
            </div>
        </div>
            <div class="row">
                <!-- Main Content -->
                <div class="col-md-7">
                <?php if(have_posts()):while(have_posts()):the_post(); ?>
                    <div <?php post_class('kt-article'); ?>>
                        <div class="row">
                            <!-- Main Blog Post -->
                                <div class="col-md-12">
                        
              
                                <!-- Blog Post Title ends here -->
                                <a href="<?php the_permalink(); ?>">
                                    <?php if(has_post_thumbnail()): the_post_thumbnail(); endif;
                                    ?>
                                </a>
                                </div>
                                <div class="col-md-12">
                                
                                <!-- Blog Post Main Content/Excerpt -->
                                <div class="kt-article-content">
                                    <?php the_content(); ?>
                                </div>
                                <?php if(has_tag()): ?>
                                <div class="kt-article-tags">
                               
                                    <?php
                                        echo get_the_tag_list('<p><i class="icon_tag_alt"></i> Tags: ',', ','</p>'); ?>
                                </div>
                                <?php endif; ?>
                                </div>
                            <!-- Main Blog Post Ends -->
                        </div>
                        <div class="row">
                        <div class="col-md-12">
                            <div id="kt-comments">
                                <?php comments_template( '', true ); ?>
                            </div>  
                        </div>
                        </div>
                    </div>
                <?php endwhile; endif;?>
                <div id="kt-pagination">
                <?php echo school_numeric_posts_nav();?>
            </div>
                </div>
                <!-- Sidebar -->
                <div class="col-md-offset-1 col-md-4">
                    <?php get_sidebar(); ?>
                </div>
            </div>
        </div>
    </div>
<?php get_footer(); ?>