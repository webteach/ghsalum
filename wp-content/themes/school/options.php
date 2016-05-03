<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 *
 */

function optionsframework_option_name() {

	// This gets the theme name from the stylesheet (lowercase and without spaces)
	$themename = get_option( 'stylesheet' );
	$themename = preg_replace("/\W/", "_", strtolower($themename) );

	$optionsframework_settings = get_option('optionsframework');
	$optionsframework_settings['id'] = $themename;
	update_option('optionsframework', $optionsframework_settings);

	// echo $themename;
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *
 */

function optionsframework_options() {

	$options = array();

	$options[] = array(
		'name' => __('Basic Options', 'school'),
		'type' => 'heading');
        
    
    $options[] = array(
        'name' => __('Premium Features', 'school'),
        'desc' => __('<ul>
        <li>Upload Logo</li>
        <li>Slider (enable/disable title & description)</li>
        <li>Testimonials</li>
        <li>Google Fonts</li>
        <li>Color Picker</li>
        <li>Opening Hours</li>
        <li>1-4 Columns Widgetized Footer Sidebar</li>
        </ul>
        <p>
        <a rel="nofollow" href="'.esc_url( __( 'http://www.ketchupthemes.com/school/', 'school')).'" style="background:red; padding:10px 20px; color:#ffffff; margin-top:10px; text-decoration:none;">Update to Premium</a></p>'),
        'type' => 'info');

	$options[] = array(
		'name' => __('Favicon Upload', 'school'),
		'desc' => __('Upload Your Favicon icon here. Please upload a 16x16 icon.', 'school'),
		'id' => 'favicon_upload',
		'type' => 'upload');
        
    $options[] = array(
        'name' => __('Footer Sidebars', 'school'),
        'desc' => __('Select Footer Sidebars Number.', 'school'),
        'id' => 'footer_sidebars_number',
        'std' => '1',
        'type' => 'radio',
        'options' => array('1'=>__('1','school'),
                           '2'=>__('2','school')
                           ));
    
	return $options;
}