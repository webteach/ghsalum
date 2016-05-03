<?php
/**
 * freak Theme Customizer
 *
 * @package freak
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function freak_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	
	
	
	//Logo Settings
	$wp_customize->add_section( 'title_tagline' , array(
	    'title'      => __( 'Title, Tagline & Logo', 'freak' ),
	    'priority'   => 30,
	) );
	
	$wp_customize->add_setting( 'freak_logo' , array(
	    'default'     => '',
	    'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control(
	    new WP_Customize_Image_Control(
	        $wp_customize,
	        'freak_logo',
	        array(
	            'label' => 'Upload Logo',
	            'section' => 'title_tagline',
	            'settings' => 'freak_logo',
	            'priority' => 5,
	        )
		)
	);
	
	$wp_customize->add_setting( 'freak_logo_resize' , array(
	    'default'     => 100,
	    'sanitize_callback' => 'freak_sanitize_positive_number',
	) );
	$wp_customize->add_control(
	        'freak_logo_resize',
	        array(
	            'label' => __('Resize & Adjust Logo','freak'),
	            'section' => 'title_tagline',
	            'settings' => 'freak_logo_resize',
	            'priority' => 6,
	            'type' => 'range',
	            'active_callback' => 'freak_logo_enabled',
	            'input_attrs' => array(
			        'min'   => 30,
			        'max'   => 200,
			        'step'  => 5,
			    ),
	        )
	);
	
	function freak_logo_enabled($control) {
		$option = $control->manager->get_setting('freak_logo');
		return $option->value() == true;
	}
	
	
	
	//Replace Header Text Color with, separate colors for Title and Description
	//Override freak_site_titlecolor
	$wp_customize->remove_control('display_header_text');
	$wp_customize->remove_setting('header_textcolor');
	$wp_customize->add_setting('freak_site_titlecolor', array(
	    'default'     => '#FFFFFF',
	    'sanitize_callback' => 'sanitize_hex_color',
	));
	
	$wp_customize->add_control(new WP_Customize_Color_Control( 
		$wp_customize, 
		'freak_site_titlecolor', array(
			'label' => __('Site Title Color','freak'),
			'section' => 'colors',
			'settings' => 'freak_site_titlecolor',
			'type' => 'color'
		) ) 
	);
	
	$wp_customize->add_setting('freak_header_desccolor', array(
	    'default'     => '#c4c4c4',
	    'sanitize_callback' => 'sanitize_hex_color',
	));
	
	$wp_customize->add_control(new WP_Customize_Color_Control( 
		$wp_customize, 
		'freak_header_desccolor', array(
			'label' => __('Site Tagline Color','freak'),
			'section' => 'colors',
			'settings' => 'freak_header_desccolor',
			'type' => 'color'
		) ) 
	);
	
	//Header Settings
	
	$wp_customize->add_panel( 'freak_header_panel', array(
	    'priority'       => 35,
	    'capability'     => 'edit_theme_options',
	    'theme_supports' => '',
	    'title'          => 'Header Settings',
	) );
	
	
	$wp_customize->add_section( 'header_image' , array(
	    'title'      => __( 'Header Image', 'freak' ),
	    'panel' => 'freak_header_panel',
	    'priority'   => 30,
	) );
	
	
	//Parallax Settings
	$wp_customize->add_section( 'freak_header_parallax' , array(
	    'title'      => __( 'Parallax Settings', 'freak' ),
	    'panel' => 'freak_header_panel',
	    'priority'   => 30,
	) );
	
	$wp_customize->add_setting( 'freak_parallax_disable' , array(
	    'default'     => false,
	    'sanitize_callback' => 'freak_sanitize_checkbox'
	) );
	
	$wp_customize->add_control(
	'freak_parallax_disable', array(
		'label' => __('Disable Parallax Effect.','freak'),
		'section' => 'freak_header_parallax',
		'settings' => 'freak_parallax_disable',
		'type' => 'checkbox'
	) );
	
	//Callback Functions to Check if Parallax is Enabled or Disabled.
	function freak_parallax_enabled($control) {
	    $option = $control->manager->get_setting('freak_parallax_disable');
	    return $option->value() == false ;
	}
	
	function freak_parallax_disabled($control) {
	    $option = $control->manager->get_setting('freak_parallax_disable');
	    return $option->value() == true ;
	}
	
	$wp_customize->add_setting( 'freak_parallax_strength' , array(
	    'default'     => 0.2,
	    'sanitize_callback' => 'freak_sanitize_positive_number',
	) );
	$wp_customize->add_control(
	        'freak_parallax_strength',
	        array(
	            'label' => __('Parallax Effect Strength','freak'),
	            'description' => __('Min: 0.05, Max: 1, Default: 0.2','freak'),
	            'section' => 'freak_header_parallax',
	            'settings' => 'freak_parallax_strength',
	            'priority' => 6,
	            'type' => 'range',
	            'active_callback' => 'freak_parallax_enabled',
	            'input_attrs' => array(
			        'min'   => 0.05,
			        'max'   => 1,
			        'step'  => 0.05,
			    ),
	        )
	);
	
	//General Settings
	$wp_customize->add_section( 'freak_header_basic' , array(
	    'title'      => __( 'General Settings', 'freak' ),
	    'panel' => 'freak_header_panel',
	    'priority'   => 30,
	) );
	
	$wp_customize->add_setting( 'freak_himg_align' , array(
	    'default'     => true,
	    'sanitize_callback' => 'freak_sanitize_himg_align'
	) );
	
	/* Sanitization Function */
	function freak_sanitize_himg_align( $input ) {
		if (in_array( $input, array('center','left','right') ) )
			return $input;
		else
			return '';	
	}
	
	$wp_customize->add_control(
	'freak_himg_align', array(
		'label' => __('Header Image Alignment','freak'),
		'section' => 'freak_header_basic',
		'settings' => 'freak_himg_align',
		'active_callback' => 'freak_parallax_disabled',
		'type' => 'select',
		'choices' => array(
				'center' => __('Center','freak'),
				'left' => __('Left','freak'),
				'right' => __('Right','freak'),
			)
	) );
	
	//Filter Enabled By Default
	$wp_customize->add_setting( 'freak_himg_darkbg' , array(
	    'default'     => true,
	    'sanitize_callback' => 'freak_sanitize_checkbox'
	) );
	
	$wp_customize->add_control(
	'freak_himg_darkbg', array(
		'label' => __('Add a Dark Filter to make the text Above the Image More Clear and Easy to Read.','freak'),
		'section' => 'freak_header_basic',
		'settings' => 'freak_himg_darkbg',
		'type' => 'checkbox'
		
	) );
	
	
	//Resize Header
	$wp_customize->add_setting( 'freak_header_size' , array(
	    'default'     => 3,
	    'sanitize_callback' => 'freak_sanitize_positive_number'
	) );
	
	$wp_customize->add_control(
	        'freak_header_size',
	        array(
	            'label' => __('Header Size for Home Page.','freak'),
	            'section' => 'freak_header_basic',
	            'settings' => 'freak_header_size',
	            'priority' => 5,
	            'type' => 'range',
	            'input_attrs' => array(
			        'min'   => 1,
			        'max'   => 3,
			        'step'  => 1,
			    ),
	        )
	);
	
	$wp_customize->add_setting( 'freak_header_size_other' , array(
	    'default'     => 3,
	    'sanitize_callback' => 'freak_sanitize_positive_number'
	) );
	
	$wp_customize->add_control(
	        'freak_header_size_other',
	        array(
	            'label' => __('Header Size for Posts,pages & Archives','freak'),
	            'description' => __('Use this option if you want a Different Header Size for All Pages, except the Home Page.','freak'),
	            'section' => 'freak_header_basic',
	            'settings' => 'freak_header_size_other',
	            'priority' => 5,
	            'type' => 'range',
	            'input_attrs' => array(
			        'min'   => 1,
			        'max'   => 3,
			        'step'  => 1,
			    ),
	        )
	);
	
	$wp_customize->add_setting( 'freak_topsearch_disable' , array(
	    'default'     => false,
	    'sanitize_callback' => 'freak_sanitize_checkbox'
	) );
	
	$wp_customize->add_control(
	'freak_topsearch_disable', array(
		'label' => __('Hide Search Bar.','freak'),
		'section' => 'freak_header_basic',
		'settings' => 'freak_topsearch_disable',
		'type' => 'checkbox'
	) );
 
	
	
	//Settings For Logo Area
	
	$wp_customize->add_setting(
		'freak_hide_title_tagline',
		array( 'sanitize_callback' => 'freak_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'freak_hide_title_tagline', array(
		    'settings' => 'freak_hide_title_tagline',
		    'label'    => __( 'Hide Title and Tagline.', 'freak' ),
		    'section'  => 'title_tagline',
		    'type'     => 'checkbox',
		)
	);
	
	function freak_title_visible( $control ) {
		$option = $control->manager->get_setting('freak_hide_title_tagline');
	    return $option->value() == false ;
	}
		
	// SLIDER PANEL
	$wp_customize->add_panel( 'freak_slider_panel', array(
	    'priority'       => 35,
	    'capability'     => 'edit_theme_options',
	    'theme_supports' => '',
	    'title'          => 'Main Slider',
	) );
	
	$wp_customize->add_section(
	    'freak_sec_slider_options',
	    array(
	        'title'     => __('Enable/Disable','freak'),
	        'priority'  => 0,
	        'panel'     => 'freak_slider_panel'
	    )
	);
	
	
	$wp_customize->add_setting(
		'freak_main_slider_enable',
		array( 'sanitize_callback' => 'freak_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'freak_main_slider_enable', array(
		    'settings' => 'freak_main_slider_enable',
		    'label'    => __( 'Enable Slider.', 'freak' ),
		    'section'  => 'freak_sec_slider_options',
		    'type'     => 'checkbox',
		)
	);
	
	$wp_customize->add_setting(
		'freak_main_slider_count',
			array(
				'default' => '0',
				'sanitize_callback' => 'freak_sanitize_positive_number'
			)
	);
	
	// Select How Many Slides the User wants, and Reload the Page.
	$wp_customize->add_control(
			'freak_main_slider_count', array(
		    'settings' => 'freak_main_slider_count',
		    'label'    => __( 'No. of Slides(Min:0, Max: 10)' ,'freak'),
		    'section'  => 'freak_sec_slider_options',
		    'type'     => 'number',
		    'description' => __('Save the Settings, and Reload this page to Configure the Slides.','freak'),
		    
		)
	);
	
		
	
	if ( get_theme_mod('freak_main_slider_count') > 0 ) :
		$slides = get_theme_mod('freak_main_slider_count');
		
		for ( $i = 1 ; $i <= $slides ; $i++ ) :
			
			//Create the settings Once, and Loop through it.
			
			$wp_customize->add_setting(
				'freak_slide_img'.$i,
				array( 'sanitize_callback' => 'esc_url_raw' )
			);
			
			$wp_customize->add_control(
			    new WP_Customize_Image_Control(
			        $wp_customize,
			        'freak_slide_img'.$i,
			        array(
			            'label' => '',
			            'section' => 'freak_slide_sec'.$i,
			            'settings' => 'freak_slide_img'.$i,			       
			        )
				)
			);
			
			
			$wp_customize->add_section(
			    'freak_slide_sec'.$i,
			    array(
			        'title'     => __('Slide ','freak').$i,
			        'priority'  => $i,
			        'panel'     => 'freak_slider_panel'
			    )
			);
			
			$wp_customize->add_setting(
				'freak_slide_title'.$i,
				array( 'sanitize_callback' => 'sanitize_text_field' )
			);
			
			$wp_customize->add_control(
					'freak_slide_title'.$i, array(
				    'settings' => 'freak_slide_title'.$i,
				    'label'    => __( 'Slide Title','freak' ),
				    'section'  => 'freak_slide_sec'.$i,
				    'type'     => 'text',
				)
			);
			
			$wp_customize->add_setting(
				'freak_slide_desc'.$i,
				array( 'sanitize_callback' => 'sanitize_text_field' )
			);
			
			$wp_customize->add_control(
					'freak_slide_desc'.$i, array(
				    'settings' => 'freak_slide_desc'.$i,
				    'label'    => __( 'Slide Description','freak' ),
				    'section'  => 'freak_slide_sec'.$i,
				    'type'     => 'text',
				)
			);
			
			
			$wp_customize->add_setting(
				'freak_slide_url'.$i,
				array( 'sanitize_callback' => 'esc_url_raw' )
			);
			
			$wp_customize->add_control(
					'freak_slide_url'.$i, array(
				    'settings' => 'freak_slide_url'.$i,
				    'label'    => __( 'Target URL','freak' ),
				    'section'  => 'freak_slide_sec'.$i,
				    'type'     => 'url',
				)
			);
			
		endfor;
	
	
	endif;
	
	
	//IMAGE GRID
	
	$wp_customize->add_section(
	    'freak_fc_grid',
	    array(
	        'title'     => __('Featured Posts','freak'),
	        'priority'  => 36,
	    )
	);
	
	$wp_customize->add_setting(
		'freak_grid_enable',
		array( 'sanitize_callback' => 'freak_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'freak_grid_enable', array(
		    'settings' => 'freak_grid_enable',
		    'label'    => __( 'Enable', 'freak' ),
		    'section'  => 'freak_fc_grid',
		    'type'     => 'checkbox',
		)
	);
	
	
	$wp_customize->add_setting(
		'freak_grid_title',
		array( 'sanitize_callback' => 'sanitize_text_field' )
	);
	
	$wp_customize->add_control(
			'freak_grid_title', array(
		    'settings' => 'freak_grid_title',
		    'label'    => __( 'Title for the Grid', 'freak' ),
		    'section'  => 'freak_fc_grid',
		    'type'     => 'text',
		)
	);
	
	
	
	$wp_customize->add_setting(
		    'freak_grid_cat',
		    array( 'sanitize_callback' => 'freak_sanitize_category' )
		);
	
		
	$wp_customize->add_control(
	    new WP_Customize_Category_Control(
	        $wp_customize,
	        'freak_grid_cat',
	        array(
	            'label'    => __('Category For Image Grid','freak'),
	            'settings' => 'freak_grid_cat',
	            'section'  => 'freak_fc_grid'
	        )
	    )
	);
	
	$wp_customize->add_setting(
		'freak_grid_rows',
		array( 'sanitize_callback' => 'freak_sanitize_positive_number' )
	);
	
	$wp_customize->add_control(
			'freak_grid_rows', array(
		    'settings' => 'freak_grid_rows',
		    'label'    => __( 'Max No. of Posts in the Grid. Enter 0 to Disable the Grid.', 'freak' ),
		    'section'  => 'freak_fc_grid',
		    'type'     => 'number',
		    'default'  => '0'
		)
	);
	
		
	// Layout and Design
	$wp_customize->add_panel( 'freak_design_panel', array(
	    'priority'       => 40,
	    'capability'     => 'edit_theme_options',
	    'theme_supports' => '',
	    'title'          => __('Design & Layout','freak'),
	) );
	
	$wp_customize->add_section(
	    'freak_static_bar_options',
	    array(
	        'title'     => __('Static Bar','freak'),
	        'priority'  => 0,
	        'panel'     => 'freak_design_panel'
	    )
	);
	
	$wp_customize->add_setting(
		'freak_disable_static_bar',
		array( 'sanitize_callback' => 'freak_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'freak_disable_static_bar', array(
		    'settings' => 'freak_disable_static_bar',
		    'label'    => __( 'Disable Static Bar.','freak' ),
		    'section'  => 'freak_static_bar_options',
		    'type'     => 'checkbox',
		    'default'  => false
		)
	);
	
	$wp_customize->add_setting(
		'freak_disable_static_bar_mobile',
		array( 'sanitize_callback' => 'freak_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'freak_disable_static_bar_mobile', array(
		    'settings' => 'freak_disable_static_bar_mobile',
		    'label'    => __( 'Disable Static Menu on Mobiles.','freak' ),
		    'section'  => 'freak_static_bar_options',
		    'type'     => 'checkbox',
		    'description' => __('Desktop Static Bar Converts to a Sliding Responsive Menu on Phones','freak'),
		    'default'  => false
		)
	);
	
	
	
	$wp_customize->add_section(
	    'freak_design_options',
	    array(
	        'title'     => __('Blog Layout','freak'),
	        'priority'  => 0,
	        'panel'     => 'freak_design_panel'
	    )
	);
	
	
	$wp_customize->add_setting(
		'freak_blog_layout',
		array( 'sanitize_callback' => 'freak_sanitize_blog_layout' )
	);
	
	function freak_sanitize_blog_layout( $input ) {
		if ( in_array($input, array('grid','freak') ) )
			return $input;
		else 
			return '';	
	}
	
	$wp_customize->add_control(
		'freak_blog_layout',array(
				'label' => __('Select Layout','freak'),
				'settings' => 'freak_blog_layout',
				'section'  => 'freak_design_options',
				'type' => 'select',
				'choices' => array(
						'freak' => __('Freak Layout','freak'),
						'grid' => __('Basic Blog Layout','freak'),
					)
			)
	);
	
	$wp_customize->add_section(
	    'freak_sidebar_options',
	    array(
	        'title'     => __('Sidebar Layout','freak'),
	        'priority'  => 0,
	        'panel'     => 'freak_design_panel'
	    )
	);
	
	$wp_customize->add_setting(
		'freak_disable_sidebar',
		array( 'sanitize_callback' => 'freak_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'freak_disable_sidebar', array(
		    'settings' => 'freak_disable_sidebar',
		    'label'    => __( 'Disable Sidebar Everywhere.','freak' ),
		    'section'  => 'freak_sidebar_options',
		    'type'     => 'checkbox',
		    'default'  => false
		)
	);
	
	$wp_customize->add_setting(
		'freak_disable_sidebar_home',
		array( 'sanitize_callback' => 'freak_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'freak_disable_sidebar_home', array(
		    'settings' => 'freak_disable_sidebar_home',
		    'label'    => __( 'Disable Sidebar on Home/Blog.','freak' ),
		    'section'  => 'freak_sidebar_options',
		    'type'     => 'checkbox',
		    'active_callback' => 'freak_show_sidebar_options',
		    'default'  => false
		)
	);
	
	$wp_customize->add_setting(
		'freak_disable_sidebar_front',
		array( 'sanitize_callback' => 'freak_sanitize_checkbox' )
	);
	
	$wp_customize->add_control(
			'freak_disable_sidebar_front', array(
		    'settings' => 'freak_disable_sidebar_front',
		    'label'    => __( 'Disable Sidebar on Front Page.','freak' ),
		    'section'  => 'freak_sidebar_options',
		    'type'     => 'checkbox',
		    'active_callback' => 'freak_show_sidebar_options',
		    'default'  => false
		)
	);
	
	
	$wp_customize->add_setting(
		'freak_sidebar_width',
		array(
			'default' => 4,
		    'sanitize_callback' => 'freak_sanitize_positive_number' )
	);
	
	$wp_customize->add_control(
			'freak_sidebar_width', array(
		    'settings' => 'freak_sidebar_width',
		    'label'    => __( 'Sidebar Width','freak' ),
		    'description' => __('Min: 25%, Default: 33%, Max: 40%','freak'),
		    'section'  => 'freak_sidebar_options',
		    'type'     => 'range',
		    'active_callback' => 'freak_show_sidebar_options',
		    'input_attrs' => array(
		        'min'   => 3,
		        'max'   => 5,
		        'step'  => 1,
		        'class' => 'sidebar-width-range',
		        'style' => 'color: #0a0',
		    ),
		)
	);
	
	/* Active Callback Function */
	function freak_show_sidebar_options($control) {
	   
	    $option = $control->manager->get_setting('freak_disable_sidebar');
	    return $option->value() == false ;
	    
	}
	
	class Freak_Custom_CSS_Control extends WP_Customize_Control {
	    public $type = 'textarea';
	 
	    public function render_content() {
	        ?>
	            <label>
	                <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
	                <textarea rows="8" style="width:100%;" <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
	            </label>
	        <?php
	    }
	}
	
	$wp_customize-> add_section(
    'freak_custom_codes',
    array(
    	'title'			=> __('Custom CSS','freak'),
    	'description'	=> __('Enter your Custom CSS to Modify design.','freak'),
    	'priority'		=> 11,
    	'panel'			=> 'freak_design_panel'
    	)
    );
    
	$wp_customize->add_setting(
	'freak_custom_css',
	array(
		'default'		=> '',
		'sanitize_callback'	=> 'freak_sanitize_text'
		)
	);
	
	$wp_customize->add_control(
	    new Freak_Custom_CSS_Control(
	        $wp_customize,
	        'freak_custom_css',
	        array(
	            'section' => 'freak_custom_codes',
	            'settings' => 'freak_custom_css'
	        )
	    )
	);
	
	function freak_sanitize_text( $input ) {
	    return wp_kses_post( force_balance_tags( $input ) );
	}
	
	$wp_customize-> add_section(
    'freak_custom_footer',
    array(
    	'title'			=> __('Custom Footer Text','freak'),
    	'description'	=> __('Enter your Own Copyright Text.','freak'),
    	'priority'		=> 11,
    	'panel'			=> 'freak_design_panel'
    	)
    );
    
	$wp_customize->add_setting(
	'freak_footer_text',
	array(
		'default'		=> '',
		'sanitize_callback'	=> 'sanitize_text_field'
		)
	);
	
	$wp_customize->add_control(	 
	       'freak_footer_text',
	        array(
	            'section' => 'freak_custom_footer',
	            'settings' => 'freak_footer_text',
	            'type' => 'text'
	        )
	);	
	
	$wp_customize->add_section(
	    'freak_typo_options',
	    array(
	        'title'     => __('Google Web Fonts','freak'),
	        'priority'  => 41,
	    )
	);
	
	$font_array = array('Roboto Slab','Bitter','Raleway','Khula','Open Sans','Droid Sans','Droid Serif','Roboto','Roboto Condensed','Lato','Bree Serif','Oswald','Slabo','Lora','Source Sans Pro','PT Sans','Ubuntu','Lobster','Arimo','Bitter','Noto Sans');
	$fonts = array_combine($font_array, $font_array);
	
	$wp_customize->add_setting(
		'freak_title_font',
		array(
			'default'=> 'Bitter',
			'sanitize_callback' => 'freak_sanitize_gfont' 
			)
	);
	
	function freak_sanitize_gfont( $input ) {
		if ( in_array($input, array('Roboto Slab','Bitter','Raleway','Khula','Open Sans','Droid Sans','Droid Serif','Roboto','Roboto Condensed','Lato','Bree Serif','Oswald','Slabo','Lora','Source Sans Pro','PT Sans','Ubuntu','Lobster','Arimo','Bitter','Noto Sans') ) )
			return $input;
		else
			return '';	
	}
	
	$wp_customize->add_control(
		'freak_title_font',array(
				'label' => __('Title','freak'),
				'settings' => 'freak_title_font',
				'section'  => 'freak_typo_options',
				'type' => 'select',
				'choices' => $fonts,
			)
	);
	
	$wp_customize->add_setting(
		'freak_body_font',
			array(	'default'=> 'Roboto Slab',
					'sanitize_callback' => 'freak_sanitize_gfont' )
	);
	
	$wp_customize->add_control(
		'freak_body_font',array(
				'label' => __('Body','freak'),
				'settings' => 'freak_body_font',
				'section'  => 'freak_typo_options',
				'type' => 'select',
				'choices' => $fonts
			)
	);
	
	// Social Icons
	$wp_customize->add_section('freak_social_section', array(
			'title' => __('Social Icons','freak'),
			'priority' => 44 ,
	));
	
	$social_networks = array( //Redefinied in Sanitization Function.
					'none' => __('-','freak'),
					'facebook' => __('Facebook','freak'),
					'twitter' => __('Twitter','freak'),
					'google-plus' => __('Google Plus','freak'),
					'instagram' => __('Instagram','freak'),
					'rss' => __('RSS Feeds','freak'),
					'vimeo' => __('Vimeo','freak'),
					'youtube' => __('Youtube','freak'),
				);
				
	$social_count = count($social_networks);
				
	for ($x = 1 ; $x <= ($social_count - 3) ; $x++) :
			
		$wp_customize->add_setting(
			'freak_social_'.$x, array(
				'sanitize_callback' => 'freak_sanitize_social',
				'default' => 'none'
			));

		$wp_customize->add_control( 'freak_social_'.$x, array(
					'settings' => 'freak_social_'.$x,
					'label' => __('Icon ','freak').$x,
					'section' => 'freak_social_section',
					'type' => 'select',
					'choices' => $social_networks,			
		));
		
		$wp_customize->add_setting(
			'freak_social_url'.$x, array(
				'sanitize_callback' => 'esc_url_raw'
			));

		$wp_customize->add_control( 'freak_social_url'.$x, array(
					'settings' => 'freak_social_url'.$x,
					'description' => __('Icon ','freak').$x.__(' Url','freak'),
					'section' => 'freak_social_section',
					'type' => 'url',
					'choices' => $social_networks,			
		));
		
	endfor;
	
	function freak_sanitize_social( $input ) {
		$social_networks = array(
					'none' ,
					'facebook',
					'twitter',
					'google-plus',
					'instagram',
					'rss',
					'vimeo',
					'youtube',
				);
		if ( in_array($input, $social_networks) )
			return $input;
		else
			return '';	
	}
	
	$wp_customize->add_section(
	    'freak_sec_upgrade',
	    array(
	        'title'     => __('Discover freak Pro','freak'),
	        'priority'  => 35,
	    )
	);
	
	$wp_customize->add_setting(
			'freak_upgrade',
			array( 'sanitize_callback' => 'esc_textarea' )
		);
			
	$wp_customize->add_control(
	    new WP_Customize_Upgrade_Control(
	        $wp_customize,
	        'freak_upgrade',
	        array(
	            'label' => __('More of Everything','freak'),
	            'description' => __('Freak Pro has more of Everything. More New Features, More Options, More Colors, More Fonts, More Layouts, Configurable Slider, Inbuilt Advertising Options, Multiple Skins, More Widgets, and a lot more options and comes with Dedicated Support. To Know More about the Pro Version, click here: <a href="http://rohitink.com/product/freak-pro/">Upgrade to Pro</a>.','freak'),
	            'section' => 'freak_sec_upgrade',
	            'settings' => 'freak_upgrade',			       
	        )
		)
	);
	
	
	/* Sanitization Functions Common to Multiple Settings go Here, Specific Sanitization Functions are defined along with add_setting() */
	function freak_sanitize_checkbox( $input ) {
	    if ( $input == 1 ) {
	        return 1;
	    } else {
	        return '';
	    }
	}
	
	function freak_sanitize_positive_number( $input ) {
		if ( ($input >= 0) && is_numeric($input) )
			return $input;
		else
			return '';	
	}
	
	function freak_sanitize_category( $input ) {
		if ( term_exists(get_cat_name( $input ), 'category') )
			return $input;
		else 
			return '';	
	}
	
	
}
add_action( 'customize_register', 'freak_customize_register' );


/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function freak_customize_preview_js() {
	wp_enqueue_script( 'freak_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'freak_customize_preview_js' );
