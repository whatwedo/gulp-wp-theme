<?php 

/*
*  acf_get_setting
*
*  This function will return a value from the settings array found in the acf object
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$name (string) the setting name to return
*  @return	(mixed)
*/

function acf_get_setting( $name, $allow_filter = true ) {
	
	// vars
	$r = null;
	
	
	// load from ACF if available
	if( isset( acf()->settings[ $name ] ) ) {
		
		$r = acf()->settings[ $name ];
		
	}
	
	
	// filter for 3rd party customization
	if( $allow_filter ) {
		
		$r = apply_filters( "acf/settings/{$name}", $r );
		
	}
	
	
	// return
	return $r;
}


/*
*  acf_get_compatibility
*
*  This function will return true or false for a given compatibility setting
*
*  @type	function
*  @date	20/01/2015
*  @since	5.1.5
*
*  @param	$name (string)
*  @return	(boolean)
*/

function acf_get_compatibility( $name ) {
	
	return apply_filters( "acf/compatibility/{$name}", true );
	
}


/*
*  acf_update_setting
*
*  This function will update a value into the settings array found in the acf object
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$name (string)
*  @param	$value (mixed)
*  @return	n/a
*/

function acf_update_setting( $name, $value ) {
	
	acf()->settings[ $name ] = $value;
	
}


/*
*  acf_append_setting
*
*  This function will add a value into the settings array found in the acf object
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$name (string)
*  @param	$value (mixed)
*  @return	n/a
*/

function acf_append_setting( $name, $value ) {
	
	// createa array if needed
	if( ! isset(acf()->settings[ $name ]) ) {
		
		acf()->settings[ $name ] = array();
		
	}
	
	
	// append to array
	acf()->settings[ $name ][] = $value;
}


/*
*  acf_get_path
*
*  This function will return the path to a file within the ACF plugin folder
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$path (string) the relative path from the root of the ACF plugin folder
*  @return	(string)
*/

function acf_get_path( $path ) {
	
	return acf_get_setting('path') . $path;
	
}


/*
*  acf_get_dir
*
*  This function will return the url to a file within the ACF plugin folder
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$path (string) the relative path from the root of the ACF plugin folder
*  @return	(string)
*/

function acf_get_dir( $path ) {
	
	return acf_get_setting('dir') . $path;
	
}


/*
*  acf_include
*
*  This function will include a file
*
*  @type	function
*  @date	10/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_include( $file ) {
	
	$path = acf_get_path( $file );
	
	if( file_exists($path) ) {
		
		include_once( $path );
		
	}
	
}


/*
*  acf_parse_args
*
*  This function will merge together 2 arrays and also convert any numeric values to ints
*
*  @type	function
*  @date	18/10/13
*  @since	5.0.0
*
*  @param	$args (array)
*  @param	$defaults (array)
*  @return	$args (array)
*/

function acf_parse_args( $args, $defaults = array() ) {
	
	// $args may not be na array!
	if( !is_array($args) ) {
		
		$args = array();
		
	}
	
	
	// parse args
	$args = wp_parse_args( $args, $defaults );
	
	
	// parse types
	$args = acf_parse_types( $args );
	
	
	// return
	return $args;
	
}


/*
*  acf_parse_types
*
*  This function will convert any numeric values to int and trim strings
*
*  @type	function
*  @date	18/10/13
*  @since	5.0.0
*
*  @param	$var (mixed)
*  @return	$var (mixed)
*/

function acf_parse_types( $array ) {
	
	// some keys are restricted
	$restricted = array(
		'label',
		'name',
		'value',
		'instructions',
		'nonce'
	);
	
	
	// loop
	foreach( array_keys($array) as $k ) {
		
		// parse type if not restricted
		if( !in_array($k, $restricted, true) ) {
			
			$array[ $k ] = acf_parse_type( $array[ $k ] );
			
		}

	}
	
	// return
	return $array;
}


/*
*  acf_parse_type
*
*  description
*
*  @type	function
*  @date	11/11/2014
*  @since	5.0.9
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_parse_type( $v ) {
	
	// test for array
	if( is_array($v) ) {
		
		return acf_parse_types($v);
	}
	
	
	// bail early if not string
	if( !is_string($v) ) {
		
		return $v;
				
	}
	
	
	// trim
	$v = trim($v);
	
	
	// numbers
	if( is_numeric($v) && strval((int)$v) === $v ) {
		
		$v = intval( $v );
		
	}
	
	
	// return
	return $v;
	
}


/*
*  acf_get_view
*
*  This function will load in a file from the 'admin/views' folder and allow variables to be passed through
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$view_name (string)
*  @param	$args (array)
*  @return	n/a
*/

function acf_get_view( $view_name = '', $args = array() ) {

	// vars
	$path = acf_get_path("admin/views/{$view_name}.php");
	
	if( file_exists($path) ) {
		
		include( $path );
		
	}
	
}


/*
*  acf_merge_atts
*
*  description
*
*  @type	function
*  @date	2/11/2014
*  @since	5.0.9
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_merge_atts( $atts, $extra = array() ) {
	
	// bail ealry if no $extra
	if( empty($extra) ) {
		
		return $atts;
		
	}
	
	
	// merge in new atts
	foreach( $extra as $k => $v ) {
			
		if( $k == 'class' || $k == 'style' ) {
			
			if( $v === '' ) {
				
				continue;
				
			}
			
			$v = $atts[ $k ] . ' ' . $v;
			
		}
		
		$atts[ $k ] = $v;
		
	}
	
	
	// return
	return $atts;
	
}


/*
*  acf_esc_attr
*
*  This function will return a render of an array of attributes to be used in markup
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$atts (array)
*  @return	n/a
*/

function acf_esc_attr( $atts ) {
	
	// is string?
	if( is_string($atts) ) {
		
		$atts = trim( $atts );
		return esc_attr( $atts );
		
	}
	
	
	// validate
	if( empty($atts) ) {
		
		return '';
		
	}
	
	
	// vars
	$e = array();
	
	
	// loop through and render
	foreach( $atts as $k => $v ) {
		
		if( is_array($v) || is_object($v) || is_bool($v) ) {
			
			$v = '';
			
		}
		
		if( is_string($v) ) {
			
			$v = trim( $v );
			
		}
		
		$e[] = $k . '="' . esc_attr( $v ) . '"';
	}
	
	
	// echo
	return implode(' ', $e);
}

function acf_esc_attr_e( $atts ) {
	
	echo acf_esc_attr( $atts );
	
}


/*
*  acf_hidden_input
*
*  description
*
*  @type	function
*  @date	3/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_hidden_input( $atts ) {
	
	$atts['type'] = 'hidden';
	
	return '<input ' . acf_esc_attr( $atts ) . ' />';
	
}

function acf_hidden_input( $atts ) {
	
	echo acf_get_hidden_input( $atts );
	
}


/*
*  acf_extract_var
*
*  This function will remove the var from the array, and return the var
*
*  @type	function
*  @date	2/10/13
*  @since	5.0.0
*
*  @param	$array (array)
*  @param	$key (string)
*  @return	(mixed)
*/

function acf_extract_var( &$array, $key ) {
	
	// check if exists
	if( is_array($array) && array_key_exists($key, $array) ) {
		
		// store value
		$v = $array[ $key ];
		
		
		// unset
		unset( $array[ $key ] );
		
		
		// return
		return $v;
		
	}
	
	
	// return
	return null;
}


/*
*  acf_extract_vars
*
*  This function will remove the vars from the array, and return the vars
*
*  @type	function
*  @date	8/10/13
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_extract_vars( &$array, $keys ) {
	
	$r = array();
	
	foreach( $keys as $key ) {
		
		$r[ $key ] = acf_extract_var( $array, $key );
		
	}
	
	return $r;
}


/*
*  acf_get_post_types
*
*  This function will return an array of available post types
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	$exclude (array)
*  @param	$include (array)
*  @return	(array)
*/

function acf_get_post_types( $exclude = array(), $include = array() ) {
	
	// get all custom post types
	$post_types = get_post_types();
	
	
	// core exclude
	$exclude = wp_parse_args( $exclude, array( 'acf-field', 'acf-field-group', 'revision', 'nav_menu_item' ) );
	
	
	// include
	if( !empty($include) ) {
		
		foreach( array_keys($include) as $i ) {
			
			$post_type = $include[ $i ];
			
			if( post_type_exists($post_type) ) {	
									
				$post_types[ $post_type ] = $post_type;
				
			}
			
		}
		
	}
	
	
	// exclude
	foreach( array_values($exclude) as $i ) {
		
		unset( $post_types[ $i ] );
		
	}
	
	
	// return
	return $post_types;
	
}


function acf_get_pretty_post_types( $post_types = array() ) {
	
	// get post types
	if( empty($post_types) ) {
		
		// get all custom post types
		$post_types = acf_get_post_types();
		
	}
	
	
	// get labels
	$ref = array();
	$r = array();
	
	foreach( $post_types as $post_type ) {
		
		// vars
		$label = $post_type;
		
		
		// check that object exists (case exists when importing field group from another install and post type does not exist)
		if( post_type_exists($post_type) ) {
			
			$obj = get_post_type_object($post_type);
			$label = $obj->labels->singular_name;
			
		}
		
		
		// append to r
		$r[ $post_type ] = $label;
		
		
		// increase counter
		if( !isset($ref[ $label ]) ) {
			
			$ref[ $label ] = 0;
			
		}
		
		$ref[ $label ]++;
	}
	
	
	// get slugs
	foreach( array_keys($r) as $i ) {
		
		// vars
		$post_type = $r[ $i ];
		
		if( $ref[ $post_type ] > 1 ) {
			
			$r[ $i ] .= ' (' . $i . ')';
			
		}
		
	}
	
	
	// return
	return $r;
	
}


/*
*  acf_verify_nonce
*
*  This function will look at the $_POST['_acfnonce'] value and return true or false
*
*  @type	function
*  @date	15/10/13
*  @since	5.0.0
*
*  @param	$nonce (string)
*  @return	(boolean)
*/

function acf_verify_nonce( $nonce, $post_id = 0 ) {
	
	// vars
	$r = false;
	
	
	// note: don't reset _acfnonce here, only when $r is set to true. This solves an issue caused by other save_post actions using this function with a different $nonce
	
	
	// check
	if( isset($_POST['_acfnonce']) ) {

		// verify nonce 'post|user|comment|term'
		if( is_string($_POST['_acfnonce']) && wp_verify_nonce($_POST['_acfnonce'], $nonce) ) {
			
			$r = true;
			
			
			// remove potential for inifinite loops
			$_POST['_acfnonce'] = false;
			
		
			// if we are currently saving a revision, allow its parent to bypass this validation
			if( $post_id && $parent = wp_is_post_revision($post_id) ) {
				
				// revision: set parent post_id
				$_POST['_acfnonce'] = $parent;
				
			}
			
		} elseif( $_POST['_acfnonce'] === $post_id ) {
			
			$r = true;
			
			// remove potential for inifinite loops
			$_POST['_acfnonce'] = false;
			
		}
		
	}
		
	
	// return
	return $r;
	
}


/*
*  acf_add_admin_notice
*
*  This function will add the notice data to a setting in the acf object for the admin_notices action to use
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	$text (string)
*  @param	$class (string)
*  @return	(int) message ID (array position)
*/

function acf_add_admin_notice( $text, $class = '', $wrap = 'p' )
{
	// vars
	$admin_notices = acf_get_admin_notices();
	
	
	// add to array
	$admin_notices[] = array(
		'text'	=> $text,
		'class'	=> "updated {$class}",
		'wrap'	=> $wrap
	);
	
	
	// update
	acf_update_setting( 'admin_notices', $admin_notices );
	
	
	// return
	return ( count( $admin_notices ) - 1 );
	
}


/*
*  acf_get_admin_notices
*
*  This function will return an array containing any admin notices
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_admin_notices()
{
	// vars
	$admin_notices = acf_get_setting( 'admin_notices' );
	
	
	// validate
	if( !$admin_notices )
	{
		$admin_notices = array();
	}
	
	
	// return
	return $admin_notices;
}


/*
*  acf_get_image_sizes
*
*  This function will return an array of available image sizes
*
*  @type	function
*  @date	23/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_image_sizes() {
	
	// global
	global $_wp_additional_image_sizes;
	
	
	// vars
	$sizes = array(
		'thumbnail'	=>	__("Thumbnail",'acf'),
		'medium'	=>	__("Medium",'acf'),
		'large'		=>	__("Large",'acf')
	);
	
	
	// find all sizes
	$all_sizes = get_intermediate_image_sizes();
	
	
	// add extra registered sizes
	if( !empty($all_sizes) ) {
		
		foreach( $all_sizes as $size ) {
			
			// bail early if already in array
			if( isset($sizes[ $size ]) ) {
			
				continue;
				
			}
			
			
			// append to array
			$label = str_replace('-', ' ', $size);
			$label = ucwords( $label );
			$sizes[ $size ] = $label;
			
		}
		
	}
	
	
	// add sizes
	foreach( array_keys($sizes) as $s ) {
		
		// vars
		$w = isset($_wp_additional_image_sizes[$s]['width']) ? $_wp_additional_image_sizes[$s]['width'] : get_option( "{$s}_size_w" );
		$h = isset($_wp_additional_image_sizes[$s]['height']) ? $_wp_additional_image_sizes[$s]['height'] : get_option( "{$s}_size_h" );
		
		if( $w && $h ) {
			
			$sizes[ $s ] .= " ({$w} x {$h})";
			
		}
		
	}
	
	
	// add full end
	$sizes['full'] = __("Full Size",'acf');
	
	
	// filter for 3rd party customization
	$sizes = apply_filters( 'acf/get_image_sizes', $sizes );
	
	
	// return
	return $sizes;
	
}


/*
*  acf_get_taxonomies
*
*  This function will return an array of available taxonomies
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_taxonomies() {

	// get all taxonomies
	$taxonomies = get_taxonomies( false, 'objects' );
	$ignore = array( 'nav_menu', 'link_category' );
	$r = array();
	
	
	// populate $r
	foreach( $taxonomies as $taxonomy )
	{
		if( in_array($taxonomy->name, $ignore) )
		{
			continue;
		
		}
		
		$r[ $taxonomy->name ] = $taxonomy->name; //"{$taxonomy->labels->singular_name}"; // ({$taxonomy->name})
	}
	
	
	// return
	return $r;
	
}


function acf_get_pretty_taxonomies( $taxonomies = array() ) {
	
	// get post types
	if( empty($taxonomies) ) {
		
		// get all custom post types
		$taxonomies = acf_get_taxonomies();
		
	}
	
	
	// get labels
	$ref = array();
	$r = array();
	
	foreach( array_keys($taxonomies) as $i ) {
		
		// vars
		$taxonomy = acf_extract_var( $taxonomies, $i);
		$obj = get_taxonomy( $taxonomy );
		$name = $obj->labels->singular_name;
		
		
		// append to r
		$r[ $taxonomy ] = $name;
		
		
		// increase counter
		if( !isset($ref[ $name ]) ) {
			
			$ref[ $name ] = 0;
			
		}
		
		$ref[ $name ]++;
	}
	
	
	// get slugs
	foreach( array_keys($r) as $i ) {
		
		// vars
		$taxonomy = $r[ $i ];
		
		if( $ref[ $taxonomy ] > 1 ) {
			
			$r[ $i ] .= ' (' . $i . ')';
			
		}
		
	}
	
	
	// return
	return $r;
	
}


/*
*  acf_get_taxonomy_terms
*
*  This function will return an array of available taxonomy terms
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	$taxonomies (array)
*  @return	(array)
*/

function acf_get_taxonomy_terms( $taxonomies = array() ) {
	
	// force array
	$taxonomies = acf_force_type_array( $taxonomies );
	
	
	// get pretty taxonomy names
	$taxonomies = acf_get_pretty_taxonomies( $taxonomies );
	
	
	// vars
	$r = array();
	
	
	// populate $r
	foreach( array_keys($taxonomies) as $taxonomy ) {
		
		// vars
		$label = $taxonomies[ $taxonomy ];
		$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
		
		
		if( !empty($terms) ) {
			
			$r[ $label ] = array();
			
			foreach( $terms as $term ) {
			
				$k = "{$taxonomy}:{$term->slug}"; 
				$r[ $label ][ $k ] = $term->name;
				
			}
			
		}
		
	}
		
	
	// return
	return $r;
	
}


/*
*  acf_decode_taxonomy_terms
*
*  This function decodes the $taxonomy:$term strings into a nested array
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$terms (array)
*  @return	(array)
*/

function acf_decode_taxonomy_terms( $terms = false ) {
	
	// load all taxonomies if not specified in args
	if( !$terms ) {
		
		$terms = acf_get_taxonomy_terms();
		
	}
	
	
	// vars
	$r = array();
	
	
	foreach( $terms as $term ) {
		
		// vars
		$data = acf_decode_taxonomy_term( $term );
		
		
		// create empty array
		if( !array_key_exists($data['taxonomy'], $r) )
		{
			$r[ $data['taxonomy'] ] = array();
		}
		
		
		// append to taxonomy
		$r[ $data['taxonomy'] ][] = $data['term'];
		
	}
	
	
	// return
	return $r;
	
}


/*
*  acf_decode_taxonomy_term
*
*  This function will convert a term string into an array of term data
*
*  @type	function
*  @date	31/03/2014
*  @since	5.0.0
*
*  @param	$string (string)
*  @return	(array)
*/

function acf_decode_taxonomy_term( $string ) {
	
	// vars
	$r = array();
	
	
	// vars
	$data = explode(':', $string);
	$taxonomy = 'category';
	$term = '';
	
	
	// check data
	if( isset($data[1]) ) {
		
		$taxonomy = $data[0];
		$term = $data[1];
		
	}
	
	
	// add data to $r
	$r['taxonomy'] = $taxonomy;
	$r['term'] = $term;
	
	
	// return
	return $r;
	
}


/*
*  acf_cache_get
*
*  This function is a wrapper for the wp_cache_get to allow for 3rd party customization
*
*  @type	function
*  @date	4/12/2013
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

/*
function acf_cache_get( $key, &$found ) {
	
	// vars
	$group = 'acf';
	$force = false;
	
	
	// load from cache
	$cache = wp_cache_get( $key, $group, $force, $found );
	
	
	// allow 3rd party customization if cache was not found
	if( !$found )
	{
		$custom = apply_filters("acf/get_cache/{$key}", $cache);
		
		if( $custom !== $cache )
		{
			$cache = $custom;
			$found = true;
		}
	}
	
	
	// return
	return $cache;
	
}
*/


/*
*  acf_force_type_array
*
*  This function will force a variable to become an array
*
*  @type	function
*  @date	4/02/2014
*  @since	5.0.0
*
*  @param	$var (mixed)
*  @return	(array)
*/

function acf_force_type_array( $var ) {
	
	// is array?
	if( is_array($var) ) {
	
		return $var;
	
	}
	
	
	// bail early if empty
	if( empty($var) && !is_numeric($var) ) {
		
		return array();
		
	}
	
	
	// string 
	if( is_string($var) ) {
		
		return explode(',', $var);
		
	}
	
	
	// place in array
	return array( $var );
} 


/*
*  acf_get_posts
*
*  This function will return an array of posts making sure the order is correct
*
*  @type	function
*  @date	3/03/2015
*  @since	5.1.5
*
*  @param	$args (array)
*  @return	(array)
*/

function acf_get_posts( $args = array() ) {
	
	// vars
	$posts = array();
	
	
	// defaults
	// leave suppress_filters as true becuase we don't want any plugins to modify the query as we know exactly what 
	$args = acf_parse_args( $args, array(
		'posts_per_page'	=> -1,
		'post_type'			=> acf_get_post_types(),
		'post_status'		=> 'any',
	));
	
	
	// validate post__in
	if( $args['post__in'] ) {
		
		// force value to array
		$args['post__in'] = acf_force_type_array( $args['post__in'] );
		
		
		// convert to int
		$args['post__in'] = array_map('intval', $args['post__in']);
		
		
		// add filter to remove post_type
		// use 'query' filter so that 'suppress_filters' can remain true
		add_filter('query', '_acf_get_posts_query');
		
		
		// order by post__in
		$args['orderby'] = 'post__in';
		
	}
	
	
	// load posts in 1 query to save multiple DB calls from following code
	$posts = get_posts($args);
	
	
	// validate order
	if( $posts && $args['post__in'] ) {
		
		// vars
		$order = array();
		
		
		// generate sort order
		foreach( $posts as $i => $post ) {
			
			$order[ $i ] = array_search($post->ID, $args['post__in']);
			
		}
		
		
		// sort
		array_multisort($order, $posts);
			
	}
	
	
	// return
	return $posts;
	
}


/*
*  _acf_get_posts_query
*
*  This function will remove the 'wp_posts.post_type' WHERE clause completely
*  When using 'post__in', this clause is unneccessary and slow.
*
*  @type	function
*  @date	4/03/2015
*  @since	5.1.5
*
*  @param	$sql (string)
*  @return	$sql
*/

function _acf_get_posts_query( $sql ) {
	
	// get bits
	$glue = 'AND';
	$bits = explode($glue, $sql);
	
	
	// loop through $where and remove any post_type queries
	foreach( $bits as $i => $bit ) {
		
		if( strpos($bit, 'post_type') !== false ) {
			
			unset( $bits[ $i ] );
			
		}
		
	}
	
	
	// join $where back together
	$sql = implode($glue, $bits);
	
	
	// remove this filter (only once)
	remove_filter('query', '_acf_get_posts_query');
	
	
	// return
	return $sql;
	
}


/*
*  acf_get_grouped_posts
*
*  This function will return all posts grouped by post_type
*  This is handy for select settings
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$args (array)
*  @return	(array)
*/

function acf_get_grouped_posts( $args ) {
	
	// vars
	$r = array();
	
	
	// defaults
	$args = acf_parse_args( $args, array(
		'posts_per_page'			=> -1,
		'paged'						=> 0,
		'post_type'					=> 'post',
		'orderby'					=> 'menu_order title',
		'order'						=> 'ASC',
		'post_status'				=> 'any',
		'suppress_filters'			=> false,
		'update_post_meta_cache'	=> false,
	));

	
	// find array of post_type
	$post_types = acf_force_type_array( $args['post_type'] );
	$post_types_labels = acf_get_pretty_post_types($post_types);
	
	
	// attachment doesn't work if it is the only item in an array
	if( count($post_types) == 1 ) {
	
		$args['post_type'] = current($post_types);
		
	}
	
	
	// add filter to orderby post type
	add_filter('posts_orderby', '_acf_orderby_post_type', 10, 2);
	
	
	// get posts
	$posts = get_posts( $args );
	
	
	// loop
	foreach( $post_types as $post_type ) {
		
		// vars
		$this_posts = array();
		$this_group = array();
		
		
		// populate $this_posts
		foreach( array_keys($posts) as $key ) {
		
			if( $posts[ $key ]->post_type == $post_type ) {
				
				$this_posts[] = acf_extract_var( $posts, $key );
				
			}
			
		}
		
		
		// bail early if no posts for this post type
		if( empty($this_posts) ) {
		
			continue;
			
		}
		
		
		// sort into hierachial order!
		// this will fail if a search has taken place because parents wont exist
		if( is_post_type_hierarchical($post_type) && empty($args['s'])) {
			
			// vars
			$match_id = $this_posts[ 0 ]->ID;
			$offset = 0;
			$length = count($this_posts);
			$parent = acf_maybe_get( $args, 'post_parent', 0 );
			
			
			// reset $this_posts
			$this_posts = array();
			
			
			// get all posts
			$all_args = array_merge($args, array(
				'posts_per_page'	=> -1,
				'paged'				=> 0,
				'post_type'			=> $post_type
			));
			
			$all_posts = get_posts( $all_args );
			
			
			// loop over posts and find $i
			foreach( $all_posts as $offset => $p ) {
				
				if( $p->ID == $match_id ) {
					
					break;
					
				}
				
			}
			
			
			// order posts
			$all_posts = get_page_children( $parent, $all_posts );
			
			
			for( $i = $offset; $i < ($offset + $length); $i++ ) {
				
				$this_posts[] = acf_extract_var( $all_posts, $i);
				
			}			
			
		}
		
				
		// populate $this_posts
		foreach( array_keys($this_posts) as $key ) {
			
			// extract post
			$post = acf_extract_var( $this_posts, $key );
			
			
			// add to group
			$this_group[ $post->ID ] = $post;
			
		}
		
		
		// group by post type
		$post_type_name = $post_types_labels[ $post_type ];
		
		$r[ $post_type_name ] = $this_group;
					
	}
	
	
	// return
	return $r;
	
}

function _acf_orderby_post_type( $ordeby, $wp_query ) {
	
	// global
	global $wpdb;
	
	
	// get post types
	$post_types = $wp_query->get('post_type');
	
	
	// prepend SQL
	if( is_array($post_types) ) {
		
		$post_types = implode("','", $post_types);
		$ordeby = "FIELD({$wpdb->posts}.post_type,'$post_types')," . $ordeby;
		
	}
	
	
	// remove this filter (only once)
	remove_filter('posts_orderby', '_acf_orderby_post_type');
	
	
	// return
	return $ordeby;
}


function acf_get_post_title( $post = 0 ) {
	
	// load post if given an ID
	if( is_numeric($post) ) {
		
		$post = get_post($post);
		
	}
	
	
	// title
	$title = get_the_title( $post->ID );
	
	
	// empty
	if( $title === '' ) {
		
		$title = __('(no title)', 'acf');
		
	}
	
	
	// ancestors
	if( $post->post_type != 'attachment' ) {
		
		$ancestors = get_ancestors( $post->ID, $post->post_type );
		
		$title = str_repeat('- ', count($ancestors)) . $title;
		
	}
	
	
	// status
	if( get_post_status( $post->ID ) != "publish" ) {
		
		$title .= ' (' . get_post_status( $post->ID ) . ')';
		
	}
	
	
	// return
	return $title;
	
}


function acf_order_by_search( $array, $search ) {
	
	// vars
	$weights = array();
	$needle = strtolower( $search );
	
	
	// add key prefix
	foreach( array_keys($array) as $k ) {
		
		$array[ '_' . $k ] = acf_extract_var( $array, $k );
		
	}


	// add search weight
	foreach( $array as $k => $v ) {
	
		// vars
		$weight = 0;
		$haystack = strtolower( $v );
		$strpos = strpos( $haystack, $needle );
		
		
		// detect search match
		if( $strpos !== false ) {
			
			// set eright to length of match
			$weight = strlen( $search );
			
			
			// increase weight if match starts at begining of string
			if( $strpos == 0 ) {
				
				$weight++;
				
			}
			
		}
		
		
		// append to wights
		$weights[ $k ] = $weight;
		
	}
	
	
	// sort the array with menu_order ascending
	array_multisort( $weights, SORT_DESC, $array );
	
	
	// remove key prefix
	foreach( array_keys($array) as $k ) {
		
		$array[ substr($k,1) ] = acf_extract_var( $array, $k );
		
	}
		
	
	// return
	return $array;
}



/*
*  acf_json_encode
*
*  This function will return pretty JSON for all PHP versions
*
*  @type	function
*  @date	6/03/2014
*  @since	5.0.0
*
*  @param	$json (array)
*  @return	(string)
*/

function acf_json_encode( $json ) {
	
	// PHP at least 5.4
	if( version_compare(PHP_VERSION, '5.4.0', '>=') )
	{
		return json_encode($json, JSON_PRETTY_PRINT);
	}

	
	
	// PHP less than 5.4
	$json = json_encode($json);
	
	
	// http://snipplr.com/view.php?codeview&id=60559
    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = '  ';
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }
	
	
	// return
    return $result;
	
}


/*
*  acf_str_exists
*
*  This function will return true if a sub string is found
*
*  @type	function
*  @date	1/05/2014
*  @since	5.0.0
*
*  @param	$needle (string)
*  @param	$haystack (string)
*  @return	(boolean)
*/

function acf_str_exists( $needle, $haystack ) {
	
	// return true if $haystack contains the $needle
	if( is_string($haystack) && strpos($haystack, $needle) !== false ) {
		
		return true;
		
	}
	
	
	// return
	return false;
}


/*
*  acf_debug
*
*  description
*
*  @type	function
*  @date	2/05/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_debug() {
	
	// vars
	$args = func_get_args();
	$s = array_shift($args);
	$o = '';
	$nl = "\r\n";
	
	
	// start script
	$o .= '<script type="text/javascript">' . $nl;
	
	$o .= 'console.log("' . $s . '"';
	
	if( !empty($args) ) {
		
		foreach( $args as $arg ) {
			
			if( is_object($arg) || is_array($arg) ) {
				
				$arg = json_encode($arg);
				
			} elseif( is_bool($arg) ) {
				
				$arg = $arg ? 'true' : 'false';
				
			}elseif( is_string($arg) ) {
				
				$arg = '"' . $arg . '"';
				
			}
			
			$o .= ', ' . $arg;
			
		}
	}
	
	$o .= ');' . $nl;
	
	
	// end script
	$o .= '</script>' . $nl;
	
	
	// echo
	echo $o;
}

function acf_debug_start() {
	
	acf_update_setting( 'debug_start', memory_get_usage());
	
}

function acf_debug_end() {
	
	$start = acf_get_setting( 'debug_start' );
	$end = memory_get_usage();
	
	return $end - $start;
	
}


/*
*  acf_get_updates
*
*  This function will reutrn all or relevant updates for ACF
*
*  @type	function
*  @date	12/05/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_updates() {
	
	// cache
	$found = false;
	$cache = wp_cache_get( 'acf_get_updates', 'acf', false, $found );
	
	if( $found ) {
	
		return $cache;
		
	}
	
	
	// vars
	$updates = array();
	$plugin_version = acf_get_setting('version');
	$acf_version = get_option('acf_version');
	$path = acf_get_path('admin/updates');
	
	
	// check that path exists
	if( !file_exists( $path ) ) {
	
		return false;
		
	}
	
	
	$dir = opendir( $path );

    while(false !== ( $file = readdir($dir)) ) {
    
    	// only php files
    	if( substr($file, -4) !== '.php' ) {
    	
	    	continue;
	    	
    	}
    	
    	
    	// get version number
    	$update_version = substr($file, 0, -4);
    	
    	
    	// ignore if update is for a future version. May exist for testing
		if( version_compare( $update_version, $plugin_version, '>') ) {
		
			continue;
			
		}
		
		// ignore if update has already been run
		if( version_compare( $update_version, $acf_version, '<=') ) {
		
			continue;
			
		}
		
		
    	// append
        $updates[] = $update_version;
        
    }
    
    
    // set cache
	wp_cache_set( 'acf_get_updates', $updates, 'acf' );
	
    
    // return
    return $updates;
	
}


/*
*  acf_encode_choices
*
*  description
*
*  @type	function
*  @date	4/06/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_encode_choices( $array = array() ) {
	
	// bail early if not array
	if( !is_array($array) ) {
		
		return $array;
		
	}
	
	
	// vars
	$string = '';
	
	
	if( !empty($array) ) {
		
		foreach( $array as $k => $v ) { 
			
			if( $k !== $v ) {
				
				$array[ $k ] = $k . ' : ' . $v;
				
			}
			
		}
		
		$string = implode("\n", $array);
		
	}
	
	
	// return
	return $string;
	
}

function acf_decode_choices( $string = '' ) {
	
	// validate
	if( is_numeric($string) ) {
		
		// force array on single numeric values
		return array( $string );
		
	} elseif( !is_string($string) ) {
		
		// bail early if not a a string
		return $string;
		
	}
	
	
	// vars
	$array = array();
	
	
	// explode
	$lines = explode("\n", $string);
	
	
	// key => value
	foreach( $lines as $line ) {
		
		// vars
		$k = trim($line);
		$v = trim($line);
		
		
		// look for ' : '
		if( acf_str_exists(' : ', $line) ) {
		
			$line = explode(' : ', $line);
			
			$k = trim($line[0]);
			$v = trim($line[1]);
			
		}
		
		
		// append
		$array[ $k ] = $v;
		
	}
	
	
	// return
	return $array;
	
}



/*
*  acf_convert_date_to_php
*
*  This fucntion converts a date format string from JS to PHP
*
*  @type	function
*  @date	20/06/2014
*  @since	5.0.0
*
*  @param	$date (string)
*  @return	$date (string)
*/

acf_update_setting('php_to_js_date_formats', array(

	// Year
	'Y'	=> 'yy',	// Numeric, 4 digits 								1999, 2003
	'y'	=> 'y',		// Numeric, 2 digits 								99, 03
	
	
	// Month
	'm'	=> 'mm',	// Numeric, with leading zeros  					01–12
	'n'	=> 'm',		// Numeric, without leading zeros  					1–12
	'F'	=> 'MM',	// Textual full   									January – December
	'M'	=> 'M',		// Textual three letters    						Jan - Dec 
	
	
	// Weekday
	'l'	=> 'DD',	// Full name  (lowercase 'L') 						Sunday – Saturday
	'D'	=> 'D',		// Three letter name 	 							Mon – Sun 
	
	
	// Day of Month
	'd'	=> 'dd',	// Numeric, with leading zeros						01–31
	'j'	=> 'd',		// Numeric, without leading zeros 					1–31
	'S'	=> '',		// The English suffix for the day of the month  	st, nd or th in the 1st, 2nd or 15th. 

));

function acf_convert_date_to_php( $date ) {
	
	// vars
	$ignore = array();
	
	
	// conversion
	$php_to_js = acf_get_setting('php_to_js_date_formats');
	
	
	// loop over conversions
	foreach( $php_to_js as $replace => $search ) {
		
		// ignore this replace?
		if( in_array($search, $ignore) ) {
			
			continue;
			
		}
		
		
		// replace
		$date = str_replace($search, $replace, $date);
		
		
		// append to ignore
		$ignore[] = $replace;
	}
	
	
	// return
	return $date;
	
}

/*
*  acf_convert_date_to_js
*
*  This fucntion converts a date format string from PHP to JS
*
*  @type	function
*  @date	20/06/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_convert_date_to_js( $date ) {
	
	// vars
	$ignore = array();
	
	
	// conversion
	$php_to_js = acf_get_setting('php_to_js_date_formats');
	
	
	// loop over conversions
	foreach( $php_to_js as $search => $replace ) {
		
		// ignore this replace?
		if( in_array($search, $ignore) ) {
			
			continue;
			
		}
		
		
		// replace
		$date = str_replace($search, $replace, $date);
		
		
		// append to ignore
		$ignore[] = $replace;
	}
	
	
	// return
	return $date;
	
}


/*
*  acf_update_user_setting
*
*  description
*
*  @type	function
*  @date	15/07/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_update_user_setting( $name, $value ) {
	
	// get current user id
	$user_id = get_current_user_id();
	
	
	// get user settings
	$settings = get_user_meta( $user_id, 'acf_user_settings', false );
	
	
	// find settings
	if( isset($settings[0]) ) {
	
		$settings = $settings[0];
	
	} else {
		
		$settings = array();
		
	}
	
	
	// append setting
	$settings[ $name ] = $value;
	
	
	// update user data
	return update_metadata('user', $user_id, 'acf_user_settings', $settings);
	
	
}


/*
*  acf_get_user_setting
*
*  description
*
*  @type	function
*  @date	15/07/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_get_user_setting( $name = '', $default = false ) {
	
	// get current user id
	$user_id = get_current_user_id();
	
	
	// get user settings
	$settings = get_user_meta( $user_id, 'acf_user_settings', false );
	
	
	// bail arly if no settings
	if( empty($settings[0][$name]) ) {
		
		return $default;
		
	}
	
	
	// return
	return $settings[0][$name];
	
}


/*
*  acf_in_array
*
*  description
*
*  @type	function
*  @date	22/07/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_in_array( $value, $array ) {
	
	// bail early if not array
	if( !is_array($array) ) {
		
		return false;
		
	}
	
	
	// find value in array
	return in_array($value, $array);
	
}


/*
*  acf_get_valid_post_id
*
*  This function will return a valid post_id based on the current screen / parameter
*
*  @type	function
*  @date	8/12/2013
*  @since	5.0.0
*
*  @param	$post_id (mixed)
*  @return	$post_id (mixed)
*/

function acf_get_valid_post_id( $post_id = 0 ) {
	
	// set post_id to global
	if( !$post_id ) {
	
		$post_id = (int) get_the_ID();
		
	}
	
	
	// allow for option == options
	if( $post_id == 'option' ) {
	
		$post_id = 'options';
		
	}
	
	
	// $post_id may be an object
	if( is_object($post_id) ) {
		
		if( isset($post_id->roles, $post_id->ID) ) {
		
			$post_id = 'user_' . $post_id->ID;
			
		} elseif( isset($post_id->taxonomy, $post_id->term_id) ) {
		
			$post_id = $post_id->taxonomy . '_' . $post_id->term_id;
			
		} elseif( isset($post_id->comment_ID) ) {
		
			$post_id = 'comment_' . $post_id->comment_ID;
			
		} elseif( isset($post_id->ID) ) {
		
			$post_id = $post_id->ID;
			
		}
		
	}
	
	
	// append language code
	if( $post_id == 'options' ) {
		
		$dl = acf_get_setting('default_language');
		$cl = acf_get_setting('current_language');
		
		if( $cl && $cl !== $dl ) {
			
			$post_id .= '_' . $cl;
			
		}
		
	}
	
	
	/*
	*  Override for preview
	*  
	*  If the $_GET['preview_id'] is set, then the user wants to see the preview data.
	*  There is also the case of previewing a page with post_id = 1, but using get_field
	*  to load data from another post_id.
	*  In this case, we need to make sure that the autosave revision is actually related
	*  to the $post_id variable. If they match, then the autosave data will be used, otherwise, 
	*  the user wants to load data from a completely different post_id
	*/
	
	if( isset($_GET['preview_id']) ) {
	
		$autosave = wp_get_post_autosave( $_GET['preview_id'] );
		
		if( $autosave && $autosave->post_parent == $post_id ) {
		
			$post_id = (int) $autosave->ID;
			
		}
		
	}
	
	
	// return
	return $post_id;
	
}


/*
*  acf_upload_files
*
*  This function will walk througfh the $_FILES data and upload each found
*
*  @type	function
*  @date	25/10/2014
*  @since	5.0.9
*
*  @param	$ancestors (array) an internal parameter, not required
*  @return	n/a
*/
	
function acf_upload_files( $ancestors = array() ) {
	
	// vars
	$file = array(
		'name'		=> '',
		'type'		=> '',
		'tmp_name'	=> '',
		'error'		=> '',
		'size' 		=> ''
	);
	
	
	// populate with $_FILES data
	foreach( array_keys($file) as $k ) {
		
		$file[ $k ] = $_FILES['acf'][ $k ];
		
	}
	
	
	// walk through ancestors
	if( !empty($ancestors) ) {
		
		foreach( $ancestors as $a ) {
			
			foreach( array_keys($file) as $k ) {
				
				$file[ $k ] = $file[ $k ][ $a ];
				
			}
			
		}
		
	}
	
	
	// is array?
	if( is_array($file['name']) ) {
		
		foreach( array_keys($file['name']) as $k ) {
				
			$_ancestors = array_merge($ancestors, array($k));
			
			acf_upload_files( $_ancestors );
			
		}
		
		return;
		
	}
	
	
	// bail ealry if file has error (no file uploaded)
	if( $file['error'] ) {
		
		return;
		
	}
	
	
	// assign global _acfuploader for media validation
	$_POST['_acfuploader'] = end($ancestors);
	
	
	// file found!
	$attachment_id = acf_upload_file( $file );
	
	
	// update $_POST
	array_unshift($ancestors, 'acf');
	acf_update_nested_array( $_POST, $ancestors, $attachment_id );
	
}


/*
*  acf_upload_file
*
*  This function will uploade a $_FILE
*
*  @type	function
*  @date	27/10/2014
*  @since	5.0.9
*
*  @param	$uploaded_file (array) array found from $_FILE data
*  @return	$id (int) new attachment ID
*/

function acf_upload_file( $uploaded_file ) {
	
	// required
	require_once( ABSPATH . "/wp-load.php" );
	require_once( ABSPATH . "/wp-admin/includes/file.php" );
	require_once( ABSPATH . "/wp-admin/includes/image.php" );
	 
	 
	// required for wp_handle_upload() to upload the file
	$upload_overrides = array( 'test_form' => false );
	
	
	// upload
	$file = wp_handle_upload( $uploaded_file, $upload_overrides );
	
	
	// bail ealry if upload failed
	if( isset($file['error']) ) {
		
		return $file['error'];
		
	}
	
	
	// vars
	$url = $file['url'];
	$type = $file['type'];
	$file = $file['file'];
	$filename = basename($file);
	

	// Construct the object array
	$object = array(
		'post_title' => $filename,
		'post_mime_type' => $type,
		'guid' => $url,
		'context' => 'acf-upload'
	);

	// Save the data
	$id = wp_insert_attachment($object, $file);

	// Add the meta-data
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
	
	/** This action is documented in wp-admin/custom-header.php */
	do_action( 'wp_create_file_in_uploads', $file, $id ); // For replication
	
	// return new ID
	return $id;
	
}


/*
*  acf_update_nested_array
*
*  This function will update a nested array value. Useful for modifying the $_POST array
*
*  @type	function
*  @date	27/10/2014
*  @since	5.0.9
*
*  @param	$array (array) target array to be updated
*  @param	$ancestors (array) array of keys to navigate through to find the child
*  @param	$value (mixed) The new value
*  @return	(boolean)
*/

function acf_update_nested_array( &$array, $ancestors, $value ) {
	
	// if no more ancestors, update the current var
	if( empty($ancestors) ) {
		
		$array = $value;
		
		// return
		return true;
		
	}
	
	
	// shift the next ancestor from the array
	$k = array_shift( $ancestors );
	
	
	// if exists
	if( isset($array[ $k ]) ) {
		
		return acf_update_nested_array( $array[ $k ], $ancestors, $value );
		
	}
		
	
	// return 
	return false;
}


/*
*  acf_is_screen
*
*  This function will return true if all args are matched for the current screen
*
*  @type	function
*  @date	9/12/2014
*  @since	5.1.5
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_is_screen( $id = '' ) {
	
	// vars
	$current_screen = get_current_screen();
	
	
	// return
	return ($id === $current_screen->id);
	
}


/*
*  acf_maybe_get
*
*  This function will return a var if it exists in an array
*
*  @type	function
*  @date	9/12/2014
*  @since	5.1.5
*
*  @param	$array (array) the array to look within
*  @param	$key (key) the array key to look for
*  @param	$default (mixed) the value returned if not found
*  @return	$post_id (int)
*/

function acf_maybe_get( $array, $key, $default = null ) {
	
	// check if exists
	if( isset($array[ $key ]) ) {
		
		return $array[ $key ];
		
	}
	
	
	// return
	return $default;
	
}


/*
*  acf_get_attachment
*
*  This function will return an array of attachment data
*
*  @type	function
*  @date	5/01/2015
*  @since	5.1.5
*
*  @param	$post (mixed) either post ID or post object
*  @return	(array)
*/

function acf_get_attachment( $post ) {
	
	// get post
	if ( !$post = get_post( $post ) ) {
		
		return false;
		
	}
	
	
	// vars
	$thumb_id = 0;
	$id = $post->ID;
	$a = array(
		'ID'			=> $id,
		'id'			=> $id,
		'title'       	=> $post->post_title,
		'filename'    	=> wp_basename( $post->guid ),
		'url'			=> wp_get_attachment_url( $id ),
		'alt'			=> get_post_meta($id, '_wp_attachment_image_alt', true),
		'author'		=> $post->post_author,
		'description'	=> $post->post_content,
		'caption'		=> $post->post_excerpt,
		'name'			=> $post->post_name,
		'date'			=> $post->post_date_gmt,
		'modified'		=> $post->post_modified_gmt,
		'mime_type'		=> $post->post_mime_type,
		'type'			=> acf_maybe_get( explode('/', $post->post_mime_type), 0, '' ),
		'icon'			=> wp_mime_type_icon( $id )
	);
	
	
	// video may use featured image
	if( $a['type'] === 'image' ) {
		
		$thumb_id = $id;
		$src = wp_get_attachment_image_src( $id, 'full' );
		
		$a['url'] = $src[0];
		$a['width'] = $src[1];
		$a['height'] = $src[2];
		
		
	} elseif( $a['type'] === 'audio' || $a['type'] === 'video' ) {
		
		// video dimentions
		if( $a['type'] == 'video' ) {
			
			$meta = wp_get_attachment_metadata( $id );
			$a['width'] = acf_maybe_get($meta, 'width', 0);
			$a['height'] = acf_maybe_get($meta, 'height', 0);
		
		}
		
		
		// feature image
		if( $featured_id = get_post_thumbnail_id($id) ) {
		
			$thumb_id = $featured_id;
			
		}
						
	}
	
	
	// sizes
	if( $thumb_id ) {
		
		// find all image sizes
		if( $sizes = get_intermediate_image_sizes() ) {
			
			$a['sizes'] = array();
			
			foreach( $sizes as $size ) {
				
				// url
				$src = wp_get_attachment_image_src( $thumb_id, $size );
				
				// add src
				$a['sizes'][ $size ] = $src[0];
				$a['sizes'][ $size . '-width' ] = $src[1];
				$a['sizes'][ $size . '-height' ] = $src[2];
				
			}
			
		}
		
	}
	
	
	// return
	return $a;
	
}


/*
*  acf_get_truncated
*
*  This function will truncate and return a string
*
*  @type	function
*  @date	8/08/2014
*  @since	5.0.0
*
*  @param	$text (string)
*  @param	$length (int)
*  @return	(string)
*/

function acf_get_truncated( $text, $length = 64 ) {
	
	// vars
	$text = trim($text);
	$the_length = strlen( $text );
	
	
	// cut
	$return = substr( $text, 0, ($length - 3) );
	
	
	// ...
	if( $the_length > ($length - 3) ) {
	
		$return .= '...';
		
	}
	
	
	// return
	return $return;
	
}


/*
*  acf_get_current_url
*
*  This function will return the current URL
*
*  @type	function
*  @date	23/01/2015
*  @since	5.1.5
*
*  @param	n/a
*  @return	(string)
*/

function acf_get_current_url() {
	
	// vars
	$home = home_url();
	$url = home_url($_SERVER['REQUEST_URI']);
	
	
	// test
	//$home = 'http://acf5/dev/wp-admin';
	//$url = $home . '/dev/wp-admin/api-template/acf_form';
	
	
	// explode url (4th bit is the sub folder)
	$bits = explode('/', $home, 4);
	
	
	/*
	Array (
	    [0] => http:
	    [1] => 
	    [2] => acf5
	    [3] => dev
	)
	*/
	
	
	// handle sub folder
	if( !empty($bits[3]) ) {
		
		$find = '/' . $bits[3];
		$pos = strpos($url, $find);
		$length = strlen($find);
		
		if( $pos !== false ) {
			
		    $url = substr_replace($url, '', $pos, $length);
		    
		}
				
	}
	
	
	// return
	return $url;
	
}


/*
*  acf_current_user_can_admin
*
*  This function will return true if the current user can administrate the ACF field groups
*
*  @type	function
*  @date	9/02/2015
*  @since	5.1.5
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_current_user_can_admin() {
	
	if( acf_get_setting('show_admin') && current_user_can(acf_get_setting('capability')) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_filesize
*
*  This function will return a numeric value of bytes for a given filesize string
*
*  @type	function
*  @date	18/02/2015
*  @since	5.1.5
*
*  @param	$size (mixed)
*  @return	(int)
*/

function acf_get_filesize( $size = 1 ) {
	
	// vars
	$unit = 'MB';
	$units = array(
		'TB' => 4,
		'GB' => 3,
		'MB' => 2,
		'KB' => 1,
	);
	
	
	// look for $unit within the $size parameter (123 KB)
	if( is_string($size) ) {
		
		foreach( $units as $k => $v ) {
			
			if( substr($size, -2) === $k ) {
				
				$unit = $k;
				$size = substr($size, 0, -2);
					
			}
			
		}
		
	}
	
	
	// calc bytes
	$bytes = intval($size) * pow(1024, $units[$unit]); 
	
	
	// return
	return $bytes;
	
}


/*
*  acf_format_filesize
*
*  This function will return a formatted string containing the filesize and unit
*
*  @type	function
*  @date	18/02/2015
*  @since	5.1.5
*
*  @param	$size (mixed)
*  @return	(int)
*/

function acf_format_filesize( $size = 1 ) {
	
	// vars
	$unit = 'MB';
	$units = array(
		'TB' => 4,
		'GB' => 3,
		'MB' => 2,
		'KB' => 1,
	);
	
	
	// look for $unit within the $size parameter (123 KB)
	if( is_string($size) ) {
		
		foreach( $units as $k => $v ) {
			
			if( substr($size, -2) === $k ) {
				
				$unit = $k;
				$size = substr($size, 0, -2);
					
			}
			
		}
		
	}
	
	
	// return
	return $size . ' ' . $unit;
	
}


/*
*  acf_get_valid_terms
*
*  This function will replace old terms with new split term ids
*
*  @type	function
*  @date	27/02/2015
*  @since	5.1.5
*
*  @param	$terms (int|array)
*  @param	$taxonomy (string)
*  @return	$terms
*/

function acf_get_valid_terms( $terms = false, $taxonomy = 'category' ) {
	
	// bail early if function does not yet exist or
	if( !function_exists('wp_get_split_term') || empty($terms) ) {
		
		return $terms;
		
	}
	
	
	// vars
	$is_array = is_array($terms);
	
	
	// force into array
	$terms = acf_force_type_array( $terms );
	
	
	// force ints
	$terms = array_map('intval', $terms);
	
	
	// attempt to find new terms
	foreach( $terms as $i => $term_id ) {
		
		$new_term_id = wp_get_split_term($term_id, $taxonomy);
		
		if( $new_term_id ) {
			
			$terms[ $i ] = $new_term_id;
			
		}
		
	}
	
	
	// revert array if needed
	if( !$is_array ) {
		
		$terms = $terms[0];
		
	}
	
	
	// return
	return $terms;
	
}



/*
*  Hacks
*
*  description
*
*  @type	function
*  @date	17/01/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

add_filter("acf/settings/slug", '_acf_settings_slug');

function _acf_settings_slug( $v ) {
	
	$basename = acf_get_setting('basename');
    $slug = explode('/', $basename);
    $slug = current($slug);
	
	return $slug;
}

?>
