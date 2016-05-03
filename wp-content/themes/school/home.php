<?php get_header(); ?>
    <div id="kt-main" role="main">
        <div class="container">
        <div class="row" id="kt-blog-title">
            <div class="col-md-1">
            <i class="icon_pencil"></i>   
            </div>
            <div class="col-md-11">
            <h1>
                <?php
                $page_id  = get_queried_object_id();
                $title = get_the_title($page_id); 
                if(!empty($title) && $page_id != 0): echo $title;
                else: 
                echo __('Blog','school'); endif; ?>
            </h1>
            <span class="small">
            <a href="<?php echo esc_url(home_url());?>">                                         
            <?php echo __('Home','school');?></a> / 
                <?php 
                    if(!empty($title) && $page_id != 0): echo $title;
                    else: 
                    echo __('Blog','school'); endif; 
                ?>
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
                                <!-- Blog Post Title -->
                                <h1>
                                <a class="kt-article-title" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                                <?php 
                                $thetitle = get_the_title($post->ID);
                                $origpostdate = get_the_date('M d, Y', $post->post_parent);
                                if($thetitle == null):echo $origpostdate; 
                                else:
                                the_title();
                                endif;
                                ?>
                                </a>
                                </h1> 
                                <!-- Blog Post Meta -->
                                <div class="kt-article-meta">
                                <span><?php the_time(get_option('date_format')); ?></span>
                                <span><?php echo __('by','school'); ?> <?php echo get_the_author(); ?></span>
        
                                </div>
                                <!-- Blog Post Meta ends here -->   
                                <!-- Blog Post Title ends here -->
                                <a href="<?php the_permalink(); ?>">
                                    <?php if(has_post_thumbnail()): the_post_thumbnail(); endif;
                                    ?>
                                </a>
                                </div>
                                <div class="col-md-12">
                                
                                <!-- Blog Post Main Content/Excerpt -->
                                <div class="kt-article-content">
                                    <?php the_excerpt(); ?>
                                </div>
                                <!-- Blog Post Main Content/Excerpt ends -->
                                <a href="<?php the_permalink();?>" title="<?php the_title_attribute();?>" class="btn btn-primary kt-read-more-link"><?php echo __('Read More..','school');?></a>
                                </div>
                            <!-- Main Blog Post Ends -->
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