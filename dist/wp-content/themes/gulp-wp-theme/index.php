<?php
get_header(); 

if ( have_posts() ) :
	while ( have_posts() ) : the_post();
		// Post
	endwhile;
else :
	// keine Posts vorhanden
endif;

get_sidebar();
get_footer();
