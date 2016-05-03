<?php
/*
** Template to Render Social Icons on Top Bar
*/

for ($i = 1; $i < 8; $i++) : 
	$social = get_theme_mod('freak_social_'.$i);
	if ( ($social != 'none') && ($social != '') ) : ?>
	<a href="<?php echo esc_url(get_theme_mod('freak_social_url'.$i)); ?>"><img src="<?php echo get_template_directory_uri().'/assets/social/'.$social.'.png'; ?>"></i></a>
	<?php endif;

endfor; ?>