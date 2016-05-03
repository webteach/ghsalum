<?php 
/**
 * header.php
 *
 * The header for the theme.
 */
?>

<!DOCTYPE html>
<!--[if IE 8]> <html <?php language_attributes(); ?> class="ie8"> <![endif]-->
<!--[if !IE]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<!-- Mobile Specific Meta -->
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		
		<?php wp_head(); ?>
		
	</head>
	
	<body <?php body_class(); ?>>
    	<div class="header_area">
        	<div class="header">
            	<div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="logo">
                                <h2> 
                                    <a href="<?php echo site_url();?>">
                                        <i class="fa fa-cube"></i> <?php bloginfo('name');?>
                                    </a> 
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="menu_area">
        	<div class="container">
            	<div class="row">
                	<div class="col-md-12">
                    		<div class="mainmenu">
								<?php 
									wp_nav_menu( array( 'theme_location' => 'main-menu' ) );
								?>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="body_area">
            <div class="container">
                <div class="mainbody">
                	<div class="row">