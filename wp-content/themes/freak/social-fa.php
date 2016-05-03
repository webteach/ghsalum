<?php
/*
** Template to Render Social Icons on Top Bar
*/

for ($i = 1; $i < 8; $i++) : 
	$social = get_theme_mod('freak_social_'.$i);
	if ( ($social != 'none') && ($social != '') ) : ?>
	<a class="hvr-bounce-to-top" href="<?php echo esc_url( get_theme_mod('freak_social_url'.$i) ); ?>"><i class="fa fa-<?php echo $social; ?>"></i></a>
	<?php endif;

endfor; ?>