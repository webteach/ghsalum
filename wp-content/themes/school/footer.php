<?php if(is_active_sidebar('footer-sidebar-1') && is_active_sidebar('footer-sidebar-2')): ?>
<div id="kt-footer">
     <div class="container">
        <div class="row">
        <?php $fsn = esc_html(of_get_option('footer_sidebars_number','1')); 
              if($fsn == 1):
        ?>
               <div class="col-md-12 kt-sidebar">
                    <?php if (!dynamic_sidebar( 'footer-sidebar-1')): ?>
                        <div class="pre-widget">
                            <h3><?php _e('Widgetized Sidebar', 'school'); ?></h3>
                            <p><?php _e('This panel is active and ready for you to add 
                            some widgets via the WP Admin', 'school'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>    
        <?php elseif($fsn == 2): ?>
                <div class="col-md-6 kt-sidebar">
                    <?php if (!dynamic_sidebar( 'footer-sidebar-1')): ?>
                        <div class="pre-widget">
                            <h3><?php _e('Widgetized Sidebar', 'school'); ?></h3>
                            <p><?php _e('This panel is active and ready for you to add 
                            some widgets via the WP Admin', 'school'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 kt-sidebar">
                    <?php if (!dynamic_sidebar( 'footer-sidebar-2')): ?>
                        <div class="pre-widget">
                            <h3><?php _e('Widgetized Sidebar', 'school'); ?></h3>
                            <p><?php _e('This panel is active and ready for you to add 
                            some widgets via the WP Admin', 'school'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
        <?php else: ?>
               <div class="col-md-12 kt-sidebar">
                    <?php if (!dynamic_sidebar( 'footer-sidebar-1')): ?>
                        <div class="pre-widget">
                            <h3><?php _e('Widgetized Sidebar', 'school'); ?></h3>
                            <p><?php _e('This panel is active and ready for you to add 
                            some widgets via the WP Admin', 'school'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>    
        <?php endif ;?>    
        </div>
    </div>
</div>
<?php endif; ?>
<div id="kt-copyright">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p><a rel="nofollow" href="<?php echo esc_url( __( 'http://ketchupthemes.com/school-theme', 'school')); ?>">
                    <?php printf( __( 'School', 'school' )); ?></a>, 
                    <?php echo __('&copy;','school'); ?> 
                    <?php echo date('Y'); ?>
                    <?php echo get_bloginfo('name'); ?></p>
                </div>
            </div>
        </div>
    </div>
<?php wp_footer();?>
</body>
</html>