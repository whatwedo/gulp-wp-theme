<?php 

/*
*  acf_is_field_group_key
*
*  This function will return true or false for the given $group_key parameter
*
*  @type	function
*  @date	6/12/2013
*  @since	5.0.0
*
*  @param	$group_key (string)
*  @return	(boolean)
*/

function acf_is_field_group_key( $key = '' ) {
	
	// look for 'field_' prefix
	if( is_string($key) && substr($key, 0, 6) === 'group_' ) {
		
		return true;
		
	}
	
	
	// allow local field group key to not start with prefix
	if( acf_is_local_field_group($key) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_valid_field_group_key
*
*  This function will return a valid field group key starting with 'group_'
*
*  @type	function
*  @date	2/02/2015
*  @since	5.1.5
*
*  @param	$key (string)
*  @return	$key
*/

function acf_get_valid_field_group_key( $key = '' ) {
	
	// test if valid
	if( !acf_is_field_group_key($key) ) {
		
		// empty
		if( !$key ) {
			
			$key = uniqid();
			
		} 
		
		
		// add prefix
		$key = "group_{$key}";
		
	}
	
	
	// return
	return $key;
	
}


/*
*  acf_get_valid_field_group
*
*  This function will fill in any missing keys to the $field_group array making it valid
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field_group (array)
*  @return	$field_group (array)
*/

function acf_get_valid_field_group( $field_group = false ) {
	
	// parse in defaults
	$field_group = acf_parse_args( $field_group, array(
		'ID'					=> 0,
		'key'					=> '',
		'title'					=> '',
		'fields'				=> array(),
		'location'				=> array(),
		'menu_order'			=> 0,
		'position'				=> 'normal',
		'style'					=> 'default',
		'label_placement'		=> 'top',
		'instruction_placement'	=> 'label',
		'hide_on_screen'		=> array()
	));
	
	
	// filter
	$field_group = apply_filters('acf/get_valid_field_group', $field_group);

	
	// return
	return $field_group;
}


/*
*  acf_get_field_groups
*
*  This function will return an array of field groupss for the given args. Similar to the WP get_posts function
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$args (array)
*  @return	$field_groups (array)
*/

function acf_get_field_groups( $args = false ) {
	
	// vars
	$field_groups = array();
	
	
	// cache
	$found = false;
	$cache = wp_cache_get( 'field_groups', 'acf', false, $found );
	
	if( $found )
	{
		return acf_filter_field_groups( $cache, $args );
	}
	
	
	// load from DB
	$posts = get_posts(array(
		'post_type'					=> 'acf-field-group',
		'posts_per_page'			=> -1,
		'orderby' 					=> 'menu_order title',
		'order' 					=> 'asc',
		'suppress_filters'			=> false, // allow WPML to modify the query
		'post_status'				=> 'publish',
		'update_post_meta_cache'	=> false
	));
	
	
	// loop through and load field groups
	if( $posts )
	{
		foreach( $posts as $post )
		{
			// add to return array
			$field_groups[] = acf_get_field_group( $post );
		}
	}
	
	
	// filter
	$field_groups = apply_filters('acf/get_field_groups', $field_groups);
	
	
	// set cache
	wp_cache_set( 'field_groups', $field_groups, 'acf' );
			
	
	// return		
	return acf_filter_field_groups( $field_groups, $args );
}


/*
*  acf_filter_field_groups
*
*  This function is used by acf_get_field_groups to filter out fields groups based on location rules
*
*  @type	function
*  @date	29/11/2013
*  @since	5.0.0
*
*  @param	$field_groups (array)
*  @param	$args (array)
*  @return	$field_groups (array)
*/

function acf_filter_field_groups( $field_groups, $args = false ) {
	
	// bail early if no options
	if( empty($args) )
	{
		return $field_groups;
	}
	
	
	if( !empty($field_groups) )
	{
		$keys = array_keys( $field_groups );
		
		foreach( $keys as $key )
		{
			$visibility = acf_get_field_group_visibility( $field_groups[ $key ], $args );
			
			if( !$visibility )
			{
				unset($field_groups[ $key ]);
			}
		}
		
		$field_groups = array_values( $field_groups );
	}
	

	return $field_groups;
	
}


/*
*  acf_get_field_group
*
*  This function will take either a post object, post ID or even null (for global $post), and
*  will then return a valid field group array
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$selector (mixed)
*  @return	$field_group (array)
*/

function acf_get_field_group( $selector = false, $search_trash = false ) {
	
	// vars
	$field_group = false;
	$k = 'ID';
	$v = 0;
	
	
	// $post_id or $key
	if( is_numeric($selector) )
	{
		$v = $selector;
	}
	elseif( is_string($selector) )
	{
		$k = 'key';
		$v = $selector;
	}
	elseif( is_object($selector) )
	{
		$v = $selector->ID;
	}
	else
	{
		return false;
	}
	
	
	// get cache key
	$cache_key = "get_field_group/{$k}={$v}";
	
	
	// get cache
	$found = false;
	$cache = wp_cache_get( $cache_key, 'acf', false, $found );
	
	if( $found )
	{
		return $cache;
	}
	
	
	// get field group from ID or key
	if( $k == 'ID' )
	{
		$field_group = _acf_get_field_group_by_id( $v );
	}
	else
	{
		$field_group = _acf_get_field_group_by_key( $v, $search_trash );
	}
	
	
	// filter for 3rd party customization
	$field_group = apply_filters('acf/get_field_group', $field_group);
	
	
	// set cache
	wp_cache_set( $cache_key, $field_group, 'acf' );
	
	
	// return
	return $field_group;
}


/*
*  _acf_get_field_group_by_id
*
*  This function will get a field group by its ID
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$field_group (array)
*/

function _acf_get_field_group_by_id( $post_id = 0 ) {
	
	// vars
	$field_group = false;
	
	
	// get post
	$post = get_post( $post_id );
	
	
	// bail early if no post, or is not a field group
	if( empty($post) || $post->post_type != 'acf-field-group' ) {
	
		return $field_group;
		
	}
	
	
	// unserialize
	$data = maybe_unserialize( $post->post_content );
	
	
	// update $field_group
	if( is_array($data) )
	{
		$field_group = $data;
	}
	
	
	// update attributes
	$field_group['ID'] = $post->ID;
	$field_group['title'] = $post->post_title;
	$field_group['key'] = $post->post_name;
	$field_group['menu_order'] = $post->menu_order;
	
	
	// override with JSON
	if( acf_is_local_field_group( $field_group['key'] ) )
	{
		// extract some args
		$backup = acf_extract_vars($field_group, array(
			'ID',
		));
		
		
		$field_group = acf_get_local_field_group( $field_group['key'] );
		
		
		// merge in backup
		$field_group = array_merge($field_group, $backup);
		
		
	}
	
		
	// validate
	$field_group = acf_get_valid_field_group( $field_group );

	
	// return
	return $field_group;
	
}


/*
*  _acf_get_field_group_by_key
*
*  This function will get a field group by its key
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	$field_group (array)
*/

function _acf_get_field_group_by_key( $key = '', $search_trash = false ) {
	
	// vars
	$field_group = false;
		
	
	// try JSON before DB to save query time
	if( acf_is_local_field_group( $key ) ) {
		
		$field_group = acf_get_local_field_group( $key );
		
		// validate
		$field_group = acf_get_valid_field_group( $field_group );
	
		// return
		return $field_group;
		
	}

	
	// vars
	$args = array(
		'posts_per_page'	=> 1,
		'post_type'			=> 'acf-field-group',
		'orderby' 			=> 'menu_order title',
		'order'				=> 'ASC',
		'suppress_filters'	=> false,
		'acf_group_key'		=> $key
	);
	
	
	// search trash?
	if( $search_trash ) {
		
		$args['post_status'] = 'publish, trash';
		
	}
	
	
	// load posts
	$posts = get_posts( $args );
	
	
	// validate
	if( empty($posts[0]) ) {
	
		return $field_group;
			
	}
	
	
	// load from ID
	$field_group = _acf_get_field_group_by_id( $posts[0]->ID );
	
	
	// return
	return $field_group;
	
}


/*
*  acf_update_field_group
*
*  This function will update a field group into the database.
*  The returned field group will always contain an ID
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field_group (array)
*  @return	$field_group (array)
*/

function acf_update_field_group( $field_group = array() ) {
	
	// validate
	$field_group = acf_get_valid_field_group( $field_group );
	
	
	// may have been posted. Remove slashes
	$field_group = wp_unslash( $field_group );
	
	
	// locations may contain 'uniquid' array keys
	$field_group['location'] = array_values( $field_group['location'] );
	
	foreach( $field_group['location'] as $k => $v )
	{
		$field_group['location'][ $k ] = array_values( $v );
	}
	
	
	// store origional field group for return
	$data = $field_group;
	
	
	// extract some args
	$extract = acf_extract_vars($data, array(
		'ID',
		'key',
		'title',
		'menu_order',
		'fields',
	));
	
	
	// serialize for DB
	$data = maybe_serialize( $data );
        
    
    // save
    $save = array(
    	'ID'			=> $extract['ID'],
    	'post_status'	=> 'publish',
    	'post_type'		=> 'acf-field-group',
    	'post_title'	=> $extract['title'],
    	'post_name'		=> $extract['key'],
    	'post_excerpt'	=> sanitize_title($extract['title']),
    	'post_content'	=> $data,
    	'menu_order'	=> $extract['menu_order'],
    );
    
    
    // allow field groups to contain the same name
	add_filter( 'wp_unique_post_slug', 'acf_update_field_group_wp_unique_post_slug', 100, 6 ); 
	
    
    // update the field group and update the ID
    if( $field_group['ID'] )
    {
	    wp_update_post( $save );
    }
    else
    {
	    $field_group['ID'] = wp_insert_post( $save );
    }
	
	
	// action for 3rd party customization
	do_action('acf/update_field_group', $field_group);
	
	
	// clear cache
	wp_cache_delete('field_groups', 'acf');
	
	
    // return
    return $field_group;
	
}

function acf_update_field_group_wp_unique_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		
	if( $post_type == 'acf-field-group' ) {
	
		$slug = $original_slug;
	
	}
	
	return $slug;
}


/*
*  acf_duplicate_field_group
*
*  This function will duplicate a field group into the database
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$selector (mixed)
*  @param	$new_post_id (int) allow specific ID to override (good for WPML translations)
*  @return	$field_group (array)
*/

function acf_duplicate_field_group( $selector = 0, $new_post_id = 0 ) {
	
	// disable JSON to avoid conflicts between DB and JSON
	acf_disable_local();
	
	
	// load the origional field gorup
	$field_group = acf_get_field_group( $selector );
	
	
	// bail early if field group did not load correctly
	if( empty($field_group) ) {
	
		return false;
		
	}
	
	
	// keep backup of field group
	$orig_field_group = $field_group;
	
	
	// update ID
	$field_group['ID'] = $new_post_id;
	$field_group['key'] = uniqid('group_');
	
	
	// add (copy)
	if( !$new_post_id ) {
		
		$field_group['title'] .= ' (' . __("copy", 'acf') . ')';
		
	}
	
	
	// save
	$field_group = acf_update_field_group( $field_group );
	
	
	// get fields
	$fields = acf_get_fields( $orig_field_group );
	
	
	// duplicate fields
	acf_duplicate_fields( $fields, $field_group['ID'] );
	
	
	// action for 3rd party customization
	do_action('acf/duplicate_field_group', $field_group);
	
	
	// return
	return $field_group;

}


/*
*  acf_get_field_count
*
*  This function will return the number of fields for the given field group
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	$field_group_id (int)
*  @return	(int)
*/

function acf_get_field_count( $field_group_id ) {
	
	// vars
	$args = array(
		'posts_per_page'	=> -1,
		'post_type'			=> 'acf-field',
		'orderby'			=> 'menu_order',
		'order'				=> 'ASC',
		'suppress_filters'	=> true, // DO NOT allow WPML to modify the query
		'post_parent'		=> $field_group_id,
		'fields'			=> 'ids',
		'post_status'		=> 'publish, trash' // 'any' won't get trashed fields
	);
	
	
	// load fields
	$posts = get_posts( $args );
	
	
	// return
	return apply_filters('acf/get_field_count', count( $posts ), $field_group_id);
	
}


/*
*  acf_delete_field_group
*
*  This function will delete the field group and its fields from the DB
*
*  @type	function
*  @date	5/12/2013
*  @since	5.0.0
*
*  @param	$selector (mixed)
*  @return	(boolean)
*/

function acf_delete_field_group( $selector = 0 ) {
	
	// disable JSON to avoid conflicts between DB and JSON
	acf_disable_local();
	
	
	// load the origional field gorup
	$field_group = acf_get_field_group( $selector );
	
	
	// bail early if field group did not load correctly
	if( empty($field_group) ) {
	
		return false;
	
	}
	
	
	// get fields
	$fields = acf_get_fields($field_group);
	
	
	if( !empty($fields) ) {
	
		foreach( $fields as $field ) {
			
			acf_delete_field( $field['ID'] );
		
		}
	
	}
	
	
	// delete
	wp_delete_post( $field_group['ID'] );
	
	
	// action for 3rd party customization
	do_action('acf/delete_field_group', $field_group);
	
	
	// return
	return true;
}


/*
*  acf_trash_field_group
*
*  This function will trash the field group and its fields
*
*  @type	function
*  @date	5/12/2013
*  @since	5.0.0
*
*  @param	$selector (mixed)
*  @return	(boolean)
*/

function acf_trash_field_group( $selector = 0 ) {
	
	// disable JSON to avoid conflicts between DB and JSON
	acf_disable_local();
	
	
	// load the origional field gorup
	$field_group = acf_get_field_group( $selector );
	
	
	// bail early if field group did not load correctly
	if( empty($field_group) ) {
	
		return false;
	
	}
	
	
	// get fields
	$fields = acf_get_fields($field_group);
	
	
	if( !empty($fields) ) {
	
		foreach( $fields as $field ) {
			
			acf_trash_field( $field['ID'] );
			
		}
		
	}
	
	
	// delete
	wp_trash_post( $field_group['ID'] );
	
	
	// action for 3rd party customization
	do_action('acf/trash_field_group', $field_group);
	
	
	// return
	return true;
}


/*
*  acf_untrash_field_group
*
*  This function will restore from trash the field group and its fields
*
*  @type	function
*  @date	5/12/2013
*  @since	5.0.0
*
*  @param	$selector (mixed)
*  @return	(boolean)
*/

function acf_untrash_field_group( $selector = 0 ) {
	
	// disable JSON to avoid conflicts between DB and JSON
	acf_disable_local();
	
	
	// load the origional field gorup
	$field_group = acf_get_field_group( $selector );
	
	
	// bail early if field group did not load correctly
	if( empty($field_group) ) {
	
		return false;
		
	}
	
	
	// get fields
	$fields = acf_get_fields($field_group);
	
	
	if( !empty($fields) ) {
	
		foreach( $fields as $field ) {
			
			acf_untrash_field( $field['ID'] );
		
		}
	
	}
	
	
	// delete
	wp_untrash_post( $field_group['ID'] );
	
	
	// action for 3rd party customization
	do_action('acf/untrash_field_group', $field_group);
	
	
	// return
	return true;
}



/*
*  acf_get_field_group_style
*
*  This function will render the CSS for a given field group
*
*  @type	function
*  @date	20/10/13
*  @since	5.0.0
*
*  @param	$field_group (array)
*  @return	n/a
*/

function acf_get_field_group_style( $field_group ) {
	
	// vars
	$e = '';
	
	
	// validate
	if( !is_array($field_group['hide_on_screen']) )
	{
		return $e;
	}
	
	
	// add style to html
	if( in_array('permalink',$field_group['hide_on_screen']) )
	{
		$e .= '#edit-slug-box {display: none;} ';
	}
	
	if( in_array('the_content',$field_group['hide_on_screen']) )
	{
		$e .= '#postdivrich {display: none;} ';
	}
	
	if( in_array('excerpt',$field_group['hide_on_screen']) )
	{
		$e .= '#postexcerpt, #screen-meta label[for=postexcerpt-hide] {display: none;} ';
	}
	
	if( in_array('custom_fields',$field_group['hide_on_screen']) )
	{
		$e .= '#postcustom, #screen-meta label[for=postcustom-hide] { display: none; } ';
	}
	
	if( in_array('discussion',$field_group['hide_on_screen']) )
	{
		$e .= '#commentstatusdiv, #screen-meta label[for=commentstatusdiv-hide] {display: none;} ';
	}
	
	if( in_array('comments',$field_group['hide_on_screen']) )
	{
		$e .= '#commentsdiv, #screen-meta label[for=commentsdiv-hide] {display: none;} ';
	}
	
	if( in_array('slug',$field_group['hide_on_screen']) )
	{
		$e .= '#slugdiv, #screen-meta label[for=slugdiv-hide] {display: none;} ';
	}
	
	if( in_array('author',$field_group['hide_on_screen']) )
	{
		$e .= '#authordiv, #screen-meta label[for=authordiv-hide] {display: none;} ';
	}
	
	if( in_array('format',$field_group['hide_on_screen']) )
	{
		$e .= '#formatdiv, #screen-meta label[for=formatdiv-hide] {display: none;} ';
	}
	
	if( in_array('page_attributes',$field_group['hide_on_screen']) )
	{
		$e .= '#pageparentdiv {display: none;} ';
	}

	if( in_array('featured_image',$field_group['hide_on_screen']) )
	{
		$e .= '#postimagediv, #screen-meta label[for=postimagediv-hide] {display: none;} ';
	}
	
	if( in_array('revisions',$field_group['hide_on_screen']) )
	{
		$e .= '#revisionsdiv, #screen-meta label[for=revisionsdiv-hide] {display: none;} ';
	}
	
	if( in_array('categories',$field_group['hide_on_screen']) )
	{
		$e .= '#categorydiv, #screen-meta label[for=categorydiv-hide] {display: none;} ';
	}
	
	if( in_array('tags',$field_group['hide_on_screen']) )
	{
		$e .= '#tagsdiv-post_tag, #screen-meta label[for=tagsdiv-post_tag-hide] {display: none;} ';
	}
	
	if( in_array('send-trackbacks',$field_group['hide_on_screen']) )
	{
		$e .= '#trackbacksdiv, #screen-meta label[for=trackbacksdiv-hide] {display: none;} ';
	}
	
	
	// return	
	return apply_filters('acf/get_field_group_style', $e, $field_group);
}


/*
*  acf_import_field_group
*
*  This function will import a field group from JSON into the DB
*
*  @type	function
*  @date	10/12/2014
*  @since	5.1.5
*
*  @param	$field_group (array)
*  @return	$id (int)
*/

function acf_import_field_group( $field_group ) {
	
	// vars
	$ref = array();
	$order = array();
	
	
	// extract fields
	$fields = acf_extract_var($field_group, 'fields');
	
	
	// format fields
	$fields = acf_prepare_fields_for_import( $fields );
	
	
	// remove old fields
	if( $field_group['ID'] ) {
		
		// disable local - important as to avoid 'acf_get_fields_by_id' returning fields with ID = 0
		acf_disable_local();
	
		
		// load fields
		$db_fields = acf_get_fields_by_id( $field_group['ID'] );
		$db_fields = acf_prepare_fields_for_import( $db_fields );
		
		
		// get field keys
		$keys = array();
		foreach( $fields as $field ) {
			
			$keys[] = $field['key'];
			
		}
		
		
		// loop over db fields
		foreach( $db_fields as $field ) {
			
			// add to ref
			$ref[ $field['key'] ] = $field['ID'];
			
			
			if( !in_array($field['key'], $keys) ) {
				
				acf_delete_field( $field['ID'] );
				
			}
			
		}
		
		
		// enable local - important as to allow local to find new fields and save json file
		acf_enable_local();
		
	}
	
			
	// save field group
	$field_group = acf_update_field_group( $field_group );
	
	
	// add to ref
	$ref[ $field_group['key'] ] = $field_group['ID'];
	
	
	// add to order
	$order[ $field_group['ID'] ] = 0;
	
	
	// add fields
	foreach( $fields as $field ) {
		
		// add ID
		if( !$field['ID'] && isset($ref[ $field['key'] ]) ) {
			
			$field['ID'] = $ref[ $field['key'] ];	
			
		}
		
		
		// add parent
		if( empty($field['parent']) ) {
			
			$field['parent'] = $field_group['ID'];
			
		} elseif( isset($ref[ $field['parent'] ]) ) {
			
			$field['parent'] = $ref[ $field['parent'] ];
				
		}
		
		
		// add field menu_order
		if( !isset($order[ $field['parent'] ]) ) {
			
			$order[ $field['parent'] ] = 0;
			
		}
		
		$field['menu_order'] = $order[ $field['parent'] ];
		$order[ $field['parent'] ]++;
		
		
		// save field
		$field = acf_update_field( $field );
		
		
		// add to ref
		$ref[ $field['key'] ] = $field['ID'];
		
	}
	
	
	// return new field group
	return $field_group;
	
}


?>
