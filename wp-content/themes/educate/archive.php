<?php
/**
 * The Archive template file
**/
get_header(); ?>
<section>
<!--breadcrumb start-->
<div class="site-breadcumb-bg">
  <div class="educate-container container">
    <div class="row">
      <div class="site-breadcumb col-sm-8 col-md-9">
        <?php  the_archive_title( '<h1 class="page-title">', '</h1>' );?>
        <ol class="breadcrumb breadcrumb-menubar">
          <?php if (function_exists('educate_custom_breadcrumbs')) educate_custom_breadcrumbs(); ?>
        </ol>
      </div>
      <div class="col-md-3 col-sm-4 breadcrumb-search">
      <?php get_search_form();?>
      </div>
    </div>
  </div>
</div>
<!--breadcrumb end-->
<?php get_template_part( 'content', get_post_format() );
?>
</section>
<?php
get_footer(); ?>
