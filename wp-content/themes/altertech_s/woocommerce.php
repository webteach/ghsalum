<?php
/**
 * The template for displaying woocommerce shop page.
 *
 *
 * @package Altertech_S
 */

get_header(); ?>



				<?php if ( function_exists('woocommerce_content') ) {
  get_template_part( 'content', 'woo' ); 
                                } ?>

<?php  get_sidebar('sidebar-1'); ?>
<?php get_footer(); ?>
