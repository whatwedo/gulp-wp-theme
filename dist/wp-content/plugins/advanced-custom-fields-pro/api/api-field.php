<?php

/*
*  acf_get_field_types
*
*  This function will return all available field types
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_field_types() {

	return apply_filters('acf/get_field_types', array());
	
}


/*
*  acf_get_field_type_label
*
*  This function will return the label of a field type
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array)
*/

function acf_get_field_type_label( $field_type ) {

	// vars
	$field_types = acf_get_field_types();
	
	
	// loop through categories
	foreach( $field_types as $category ) {
		
		if( isset( $category[ $field_type ] ) ) {
		
			return $category[ $field_type ];
			
		}
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_field_type_exists
*
*  This function will check if the field_type is available
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$field_type (string)
*  @return	(boolean)
*/

function acf_field_type_exists( $field_type ) {

	// vars
	$label = acf_get_field_type_label( $field_type );
	
	
	// return true if label exists
	if( !empty( $label ) ) {
		
		return true;
		
	}
		
	
	// return
	return false;
}


/*
*  acf_is_field_key
*
*  This function will return true or false for the given $field_key parameter
*
*  @type	function
*  @date	6/12/2013
*  @since	5.0.0
*
*  @param	$field_key (string)
*  @return	(boolean)
*/

function acf_is_field_key( $key = '' ) {
	
	// look for 'field_' prefix
	if( is_string($key) && substr($key, 0, 6) === 'field_' ) {
		
		return true;
		
	}
	
	
	// allow local field group key to not start with prefix
	if( acf_is_local_field($key) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_valid_field_key
*
*  This function will return a valid field key starting with 'field_'
*
*  @type	function
*  @date	2/02/2015
*  @since	5.1.5
*
*  @param	$key (string)
*  @return	$key
*/

function acf_get_valid_field_key( $key = '' ) {
	
	// test if valid
	if( !acf_is_field_key($key) ) {
		
		// empty
		if( !$key ) {
			
			$key =  uniqid();
			
		} 
		
		
		// add prefix
		$key = "field_{$key}";
		
	}
	
	
	// return
	return $key;
	
}


/*
*  acf_get_valid_field
*
*  This function will fill in any missing keys to the $field array making it valid
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	$field (array)
*/

function acf_get_valid_field( $field = false ) {
	
	// $field must be an array
	if( !is_array($field) ) {
		
		$field = array();
		
	}
	
	
	// bail ealry if already valid
	if( !empty($field['_valid']) ) {
		
		return $field;
		
	}
	
	
	// defaults
	$field = acf_parse_args($field, array(
		'ID'				=> 0,
		'key'				=> '',
		'label'				=> '',
		'name'				=> '',
		'prefix'			=> '',
		'type'				=> 'text',
		'value'				=> null,
		'menu_order'		=> 0,
		'instructions'		=> '',
		'required'			=> 0,
		'id'				=> '',
		'class'				=> '',
		'conditional_logic'	=> 0,
		'parent'			=> 0,
		'wrapper'			=> array(
			'width'				=> '',
			'class'				=> '',
			'id'				=> ''
		),
		'_name'				=> '',
		'_input'			=> '',
		'_valid'			=> 0,
	));
	
	
	// _name
	$field['_name'] = $field['name'];
	
	
	// translate
	foreach( array('label', 'instructions') as $s ) {
		
		$field[ $s ] = __($field[ $s ]);
		
	}
	
	
	// field specific defaults
	$field = apply_filters( "acf/get_valid_field", $field );
	$field = apply_filters( "acf/get_valid_field/type={$field['type']}", $field );
	
	
	// field is now valid
	$field['_valid'] = 1;
	
	
	// return
	return $field;
}


/*
*  acf_prepare_field
*
*  This function will prepare the field for input
*
*  @type	function
*  @date	12/02/2014
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	$field (array)
*/

function acf_prepare_field( $field ) {
	
	// _input
	if( !$field['_input'] ) {
		
		$field['_input'] = $field['name'];
	
	
		// _input: key overrides name
		if( $field['key'] ) {
			
			$field['_input'] = $field['key'];
			
		}
	
		
		// _input: prefix prepends name
		if( $field['prefix'] ) {
			
			$field['_input'] = "{$field['prefix']}[{$field['_input']}]";
			
		}
		
	}
	
	
	// add id (may be custom set)
	if( !$field['id'] ) {
		
		$field['id'] = str_replace(array('][', '[', ']'), array('-', '-', ''), $field['_input']);
		
	}
	
	
	// return
	return $field;
}


/*
*  acf_is_sub_field
*
*  This function will return true if the field is a sub field
*
*  @type	function
*  @date	17/05/2014
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	(boolean)
*/

function acf_is_sub_field( $field ) {
	
	// local field uses a field instead of ID
	if( acf_is_field_key($field['parent']) ) {
		
		return true;
		
	}
	
	
	// attempt to load parent field
	if( acf_get_field($field['parent']) ) {
		
		return true;
		
	}
	
	
	// return
	return false;
	
}


/*
*  acf_get_field_label
*
*  This function will return the field label with appropriate required label
*
*  @type	function
*  @date	4/11/2013
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	$label (string)
*/

function acf_get_field_label( $field ) {
	
	// vars
	$label = $field['label'];
	
	
	if( $field['required'] ) {
		
		$label .= ' <span class="acf-required">*</span>';
		
	}
	
	
	// return
	return $label;

}

function acf_the_field_label( $field ) {

	echo acf_get_field_label( $field );
	
}


/*
*  acf_render_fields
*
*  This function will render an array of fields for a given form.
*  Becasue the $field's values have not been loaded yet, this function will also load values
*
*  @type	function
*  @date	8/10/13
*  @since	5.0.0
*
*  @param	$post_id (int) the post to load values from
*  @param	$fields (array) the fields to render
*  @param	$el (string) the wrapping element type
*  @param	$instruction (int) the instructions position
*  @return	n/a
*/

function acf_render_fields( $post_id = 0, $fields, $el = 'div', $instruction = 'label' ) {
		
	if( !empty($fields) ) {
		
		foreach( $fields as $field ) {
			
			// load value
			if( $field['value'] === null ) {
				
				$field['value'] = acf_get_value( $post_id, $field );
				
			} 
			
			
			// set prefix for correct post name (prefix + key)
			$field['prefix'] = 'acf';
			
			
			// render
			acf_render_field_wrap( $field, $el, $instruction );
		}
		
	}
		
}


/*
*  acf_render_field
*
*  This function will render a field input
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	n/a
*/

function acf_render_field( $field = false ) {
	
	// get valid field
	$field = acf_get_valid_field( $field );
	
	
	// prepare field for input
	$field = acf_prepare_field( $field );
	
	
	// update $field['name']
	$field['name'] = $field['_input'];
		
	
	// create field specific html
	do_action( "acf/render_field", $field );
	do_action( "acf/render_field/type={$field['type']}", $field );
	
}


/*
*  acf_render_field_wrap
*
*  This function will render the complete HTML wrap with label & field
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array) must be a valid ACF field array
*  @param	$el (string) modifys the rendered wrapping elements. Default to 'div', but can be 'tr', 'ul', 'ol', 'dt' or custom
*  @param	$instruction (string) specifys the placement of the instructions. Default to 'label', but can be 'field'
*  @param	$atts (array) an array of custom attributes to render on the $el
*  @return	N/A
*/

function acf_render_field_wrap( $field, $el = 'div', $instruction = 'label' ) {
	
	// get valid field
	$field = acf_get_valid_field( $field );
	
	
	// prepare field for input
	$field = acf_prepare_field( $field );
	
	
	// el
	$elements = apply_filters('acf/render_field_wrap/elements', array(
		'div'	=> 'div',
		'tr'	=> 'td',
		'ul'	=> 'li',
		'ol'	=> 'li',
		'dl'	=> 'dt',
		'td'	=> 'div' // special case for sub field!
	));
	
	
	// validate $el
	if( !array_key_exists($el, $elements) ) {
		
		$el = 'div';
	
	}
	
	
	// wrapper
	$wrapper = array(
		'id'		=> '',
		'class'		=> 'acf-field',
		'width'		=> '',
		'style'		=> '',
		'data-name'	=> $field['name'],
		'data-type'	=> $field['type'],
		'data-key'	=> '',
	);
	
	
	// add required
	if( $field['required'] ) {
		
		$wrapper['data-required'] = 1;
		
	}
	
	
	// add type
	$wrapper['class'] .= " acf-field-{$field['type']}";
	
	
	// add key
	if( $field['key'] ) {
		
		$wrapper['class'] .= " acf-field-{$field['key']}";
		$wrapper['data-key'] = $field['key'];
		
	}
	
	
	// replace
	$wrapper['class'] = str_replace('_', '-', $wrapper['class']);
	$wrapper['class'] = str_replace('field-field-', 'field-', $wrapper['class']);
	
	
	// compatibility
	if( acf_get_compatibility('field_wrapper_class') ) {
		
		$wrapper['class'] .= " field_type-{$field['type']}";
		
		if( $field['key'] ) {
			
			$wrapper['class'] .= " field_key-{$field['key']}";
			
		}
		
	}
		
	
	// merge in atts
	$wrapper = acf_merge_atts( $wrapper, $field['wrapper'] );
	
	
	// add width
	$width = (int) acf_extract_var( $wrapper, 'width' );
	
	if( $el == 'tr' || $el == 'td' ) {
		
		$width = 0;
		
	} elseif( $width > 0 && $width < 100 ) {
		
		$wrapper['data-width'] = $width;
		$wrapper['style'] .= " width:{$width}%;";
		
	}
	
	
	// remove empty attributes
	foreach( $wrapper as $k => $v ) {
		
		if( $v == '' ) {
			
			unset($wrapper[$k]);
			
		}
		
	}
	
	
	// vars
	$show_label = true;
	
	if( $el == 'td' ) {
		
		$show_label = false;
		
	}
	
	
?><<?php echo $el; ?> <?php echo acf_esc_attr($wrapper); ?>>
<?php if( $show_label ): ?>
	<<?php echo $elements[ $el ]; ?> class="acf-label">
		<label for="<?php echo $field['id']; ?>"><?php echo acf_get_field_label($field); ?></label>
<?php if( $instruction == 'label' && $field['instructions'] ): ?>
		<p class="description"><?php echo $field['instructions']; ?></p>
<?php endif; ?>
	</<?php echo $elements[ $el ]; ?>>
<?php endif; ?>
	<<?php echo $elements[ $el ]; ?> class="acf-input">
		<?php acf_render_field( $field ); ?>
		
<?php if( $instruction == 'field' && $field['instructions'] ): ?>
		<p class="description"><?php echo $field['instructions']; ?></p>
<?php endif; ?>
	</<?php echo $elements[ $el ]; ?>>
<?php if( !empty($field['conditional_logic'])): ?>
	<script type="text/javascript">
		if(typeof acf !== 'undefined'){ acf.conditional_logic.add( '<?php echo $field['key']; ?>', <?php echo json_encode($field['conditional_logic']); ?>); }
	</script>
<?php endif; ?>
</<?php echo $el; ?>>
<?php
	
}


/*
*  acf_render_field_settings
*
*  This function will render the available field options using an action to trigger the field's render function
*
*  @type	function
*  @date	23/01/13
*  @since	3.6.0
*
*  @param	$field (array)
*  @return	n/a
*/

function acf_render_field_settings( $field ) {
	
	// get valid field
	$field = acf_get_valid_field( $field );
	
	
	// create field specific html
	do_action( "acf/render_field_settings", $field);
	do_action( "acf/render_field_settings/type={$field['type']}", $field);
	
}


/*
*  acf_render_field_setting
*
*  This function will render a tr element containing a label and field cell, but also setting the tr data attribute for AJAX 
*
*  @type	function
*  @date	28/09/13
*  @since	5.0.0
*
*  @param	$field (array) the origional field being edited
*  @param	$setting (array) the settings field to create
*  @return	n/a
*/

function acf_render_field_setting( $field, $setting, $global = false ) {
	
	// validate
	$setting = acf_get_valid_field( $setting );
	
	
	// if this setting is not global, add a data attribute
	if( !$global ) {
		
		$setting['wrapper']['data-setting'] = $field['type'];
		
	}
	
	
	// copy across prefix
	$setting['prefix'] = $field['prefix'];
		
		
	// copy across the $setting value
	if( isset($field[ $setting['name'] ]) ) {
		
		$setting['value'] = $field[ $setting['name'] ];
		
	}
	
	
	// render
	acf_render_field_wrap( $setting, 'tr', 'label' );
	
}


/*
*  acf_get_fields
*
*  This function will return an array of fields for the given $parent
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$parent (array) a field or field group
*  @return	(array)
*/

function acf_get_fields( $parent = false ) {
	
	// allow $parent to be a field group ID
	if( !is_array($parent) ) {
		
		$parent = acf_get_field_group( $parent );
	
	}
	
	
	// validate
	if( !$parent )
	{
		return false;
	}
	
	
	// vars
	$fields = array();
	
	
	// try JSON before DB to save query time
	if( acf_have_local_fields( $parent['key'] ) ) {
		
		$fields = acf_get_local_fields( $parent['key'] );
		
	} else {
		
		$fields = acf_get_fields_by_id( $parent['ID'] );
	
	}
	
	
	// return
	return apply_filters('acf/get_fields', $fields, $parent);
	
}


/*
*  acf_get_fields_by_id
*
*  This function will get all fields for the given parent
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$fields (array)
*/

function acf_get_fields_by_id( $id = 0 ) {
	
	// vars
	$fields = array();
	
	
	// validate
	if( empty($id) ) {
		
		return $fields;
		
	}
	
	
	// cache
	$found = false;
	$cache = wp_cache_get( 'fields/parent=' . $id, 'acf', false, $found );
	
	if( $found )
	{
		return $cache;
	}
	
	
	// args
	$args = array(
		'posts_per_page'			=> -1,
		'post_type'					=> 'acf-field',
		'orderby'					=> 'menu_order',
		'order'						=> 'ASC',
		'suppress_filters'			=> true, // DO NOT allow WPML to modify the query
		'post_parent'				=> $id,
		'post_status'				=> 'publish, trash', // 'any' won't get trashed fields
		'update_post_meta_cache'	=> false
	);
		
	
	// load fields
	$posts = get_posts( $args );
	
	if( $posts )
	{
		foreach( $posts as $post )
		{
			$fields[] = acf_get_field( $post->ID );
		}	
	}
	
	
	// set cache
	wp_cache_set( 'fields/parent=' . $id, $fields, 'acf' );
		
	
	// return
	return $fields;
	
}


/*
*  acf_get_field
*
*  This function will return a field for the given selector. 
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$selector (mixed) identifyer of field. Can be an ID, key, name or post object
*  @param	$raw (boolean) return $field in it's raw form without filters or cache
*  @return	$field (array)
*/

function acf_get_field( $selector = null, $raw = false ) {
	
	// vars
	$field = false;
	$k = 'ID';
	$v = 0;
	
	
	// $post_id or $key
	if( is_numeric($selector) ) {
		
		$v = $selector;
		
	} elseif( is_string($selector) ) {
		
		if( acf_is_field_key($selector) ) {
			
			$k = 'key';
		
		} else {
			
			$k = 'name';
				
		}
		
		$v = $selector;
		
	} elseif( is_object($selector) ) {
		
		$v = $selector->ID;
		
	} else {
		
		return false;
		
	}
	
	
	// get cache key
	$cache_key = "load_field/{$k}={$v}";
	
	
	// get cache
	$found = false;
	$cache = wp_cache_get( $cache_key, 'acf', false, $found );
	
	if( $found ) {
		
		return $cache;
		
	}
	
	
	// get field group from ID or key
	if( $k == 'ID' ) {
		
		$field = _acf_get_field_by_id( $v );
		
	} elseif( $k == 'name' ) {
		
		$field = _acf_get_field_by_name( $v );
		
	} else {
		
		$field = _acf_get_field_by_key( $v );
		
	}
	
	
	// bail ealry if no field
	if( !$field) {
		
		return false;
		
	}
	
	
	// bail early if db only value (no need to update cache)
	if( $raw ) {
		
		return $field;
		
	}
	

	// filter for 3rd party customization
	$field = apply_filters('acf/load_field', $field);
	$field = apply_filters( "acf/load_field/type={$field['type']}", $field );
	$field = apply_filters( "acf/load_field/name={$field['name']}", $field );
	$field = apply_filters( "acf/load_field/key={$field['key']}", $field );
	

	// set cache
	wp_cache_set( $cache_key, $field, 'acf' );

	
	// return
	return $field;
	
}


/*
*  _acf_get_field_by_id
*
*  This function will get a field via its ID
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$field (array)
*/

function _acf_get_field_by_id( $post_id = 0 ) {
	
	// vars
	$field = false;
	
	
	// get post
	$post = get_post( $post_id );
	
	
	// bail early if no post, or is not a field
	if( empty($post) || $post->post_type != 'acf-field' ) {
	
		return $field;
		
	}
	
	
	// unserialize
	$data = maybe_unserialize( $post->post_content );
	
	
	// update $field
	if( is_array($data) )
	{
		$field = $data;
	}
	
	
	// update attributes
	$field['ID'] = $post->ID;
	$field['key'] = $post->post_name;
	$field['label'] = $post->post_title;
	$field['name'] = $post->post_excerpt;
	$field['menu_order'] = $post->menu_order;
	$field['parent'] = $post->post_parent;
	//$field['ancestors'] = get_post_ancestors( $post );
	//$field['field_group'] = end( $field['ancestors'] );


	// override with JSON
	if( acf_is_local_field( $field['key'] ) )
	{
		// extract some args
		$backup = acf_extract_vars($field, array(
			'ID',
			'parent'
		));
		

		// load JSON field
		$field = acf_get_local_field( $field['key'] );
		
		
		// merge in backup
		$field = array_merge($field, $backup);
	}
	
	
	// validate
	$field = acf_get_valid_field( $field );
	
	
	// return
	return $field;
	
}


/*
*  _acf_get_field_by_key
*
*  This function will get a field via its key
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	$field (array)
*/

function _acf_get_field_by_key( $key = '' ) {
	
	// vars
	$field = false;	
	
	
	// try JSON before DB to save query time
	if( acf_is_local_field( $key ) ) {
		
		$field = acf_get_local_field( $key );
		
		// validate
		$field = acf_get_valid_field( $field );
	
		// return
		return $field;
		
	}
	
	
	// vars
	$args = array(
		'posts_per_page'	=> 1,
		'post_type'			=> 'acf-field',
		'orderby' 			=> 'menu_order title',
		'order'				=> 'ASC',
		'suppress_filters'	=> false,
		'acf_field_key'		=> $key
	);
	
	
	// load posts
	$posts = get_posts( $args );
	
	
	// validate
	if( empty($posts) ) {
		
		return $field;
		
	}
	
	
	// return
	return _acf_get_field_by_id( $posts[0]->ID );
	
}


/*
*  _acf_get_field_by_name
*
*  This function will get a field via its name
*
*  @type	function
*  @date	27/02/2014
*  @since	5.0.0
*
*  @param	$key (string)
*  @return	$field (array)
*/

function _acf_get_field_by_name( $name = '' ) {
	
	// vars
	$args = array(
		'posts_per_page'	=> 1,
		'post_type'			=> 'acf-field',
		'orderby' 			=> 'menu_order title',
		'order'				=> 'ASC',
		'suppress_filters'	=> false,
		'acf_field_name'	=> $name
	);
	
	
	// load posts
	$posts = get_posts( $args );
	
	
	// validate
	if( empty($posts) ) {
		
		return false;	
		
	}
	
	
	// return
	return _acf_get_field_by_id( $posts[0]->ID );
	
}


/*
*  acf_update_field
*
*  This function will update a field into the DB.
*  The returned field will always contain an ID
*
*  @type	function
*  @date	1/10/13
*  @since	5.0.0
*
*  @param	$field (array)
*  @return	$field (array)
*/

function acf_update_field( $field = false, $specific = false ) {
	
	// $field must be an array
	if( !is_array($field) ) {
	
		return false;
		
	}
	
	
	// validate
	$field = acf_get_valid_field( $field );
	
	
	// may have been posted. Remove slashes
	$field = wp_unslash( $field );
	
	
	// clean up conditional logic keys
	if( !empty($field['conditional_logic']) ) {
		
		// extract groups
		$groups = acf_extract_var( $field, 'conditional_logic' );
		
		
		// clean array
		$groups = array_filter($groups);
		$groups = array_values($groups);
		
		
		// clean rules
		foreach( array_keys($groups) as $i ) {
			
			$groups[ $i ] = array_filter($groups[ $i ]);
			$groups[ $i ] = array_values($groups[ $i ]);
			
		}
		
		
		// reset conditional logic
		$field['conditional_logic'] = $groups;
		
	}
	
	
	// filter for 3rd party customization
	$field = apply_filters( "acf/update_field", $field);
	$field = apply_filters( "acf/update_field/type={$field['type']}", $field );
	$field = apply_filters( "acf/update_field/name={$field['name']}", $field );
	$field = apply_filters( "acf/update_field/key={$field['key']}", $field );
	
	
	// store origional field for return
	$data = $field;
	
	
	// extract some args
	$extract = acf_extract_vars($data, array(
		'ID',
		'key',
		'label',
		'name',
		'prefix',
		'value',
		'menu_order',
		'id',
		'class',
		'parent',
		'_name',
		'_input',
		'_valid',
	));
	
	
	// serialize for DB
	$data = maybe_serialize( $data );
    
    
    // save
    $save = array(
    	'ID'			=> $extract['ID'],
    	'post_status'	=> 'publish',
    	'post_type'		=> 'acf-field',
    	'post_title'	=> $extract['label'],
    	'post_name'		=> $extract['key'],
    	'post_excerpt'	=> $extract['name'],
    	'post_content'	=> $data,
    	'post_parent'	=> $extract['parent'],
    	'menu_order'	=> $extract['menu_order'],
    );
    
    
    // $specific
    if( !empty($specific) )
    {
    	// prepend ID
    	array_unshift( $specific, 'ID' );
    	
    	
    	// appen data
    	foreach( $specific as $key )
    	{
	    	$_save[ $key ] = $save[ $key ];
    	}
    	
    	
    	// override save
    	$save = $_save;
    	
    	
    	// clean up
    	unset($_save);
    	
    }
    
    
    // allow fields to contain the same name
	add_filter( 'wp_unique_post_slug', 'acf_update_field_wp_unique_post_slug', 100, 6 ); 
	
	
    // update the field and update the ID
    if( $field['ID'] )
    {
	    wp_update_post( $save );
    }
    else
    {
	    $field['ID'] = wp_insert_post( $save );
    }
	
    
    // clear cache
	wp_cache_delete( "load_field/ID={$field['ID']}", 'acf' );
	wp_cache_delete( "fields/parent={$field['parent']}", 'acf' );
	
	
    // update cache
	//wp_cache_set( "load_field/ID={$field['ID']}", $field, 'acf' );
	
	
    // return
    return $field;
	
}

function acf_update_field_wp_unique_post_slug( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
		
	if( $post_type == 'acf-field' ) {
	
		$slug = $original_slug;
	
	}
	
	return $slug;
}


/*
*  acf_duplicate_fields
*
*  This function will duplicate an array of fields and update conditional logic references
*
*  @type	function
*  @date	16/06/2014
*  @since	5.0.0
*
*  @param	$fields (array)
*  @param	$new_parent (int)
*  @return	n/a
*/

function acf_duplicate_fields( $fields, $new_parent = 0 ) {
	
	// bail early if no fields
	if( empty($fields) ) {
		
		return;
		
	}
	
	
	// create new field keys (for conditional logic fixes)
	foreach( $fields as $field ) {
		
		// ensure a delay for unique ID
		usleep(1);
		
		acf_update_setting( 'duplicate_key_' . $field['key'] , uniqid('field_') );
		
	}
	
	
	// duplicate fields
	foreach( $fields as $field ) {
	
		// duplicate
		acf_duplicate_field( $field['ID'], $new_parent );
		
	}
	
}


/*
*  acf_duplicate_field
*
*  This function will duplicate a field and attach it to the given field group ID
*
*  @type	function
*  @date	17/10/13
*  @since	5.0.0
*
*  @param	$selector (int)
*  @param	$new_parent (int)
*  @return	$field (array) the new field
*/

function acf_duplicate_field( $selector = 0, $new_parent = 0 ){
	
	// disable JSON to avoid conflicts between DB and JSON
	acf_disable_local();
	
	
	// load the origional field
	$field = acf_get_field( $selector );
	
	
	// bail early if field did not load correctly
	if( empty($field) ) {
	
		return false;
		
	}
	
	
	// update ID
	$field['ID'] = false;
	
	
	// try duplicate keys
	$field['key'] = acf_get_setting( 'duplicate_key_' . $field['key'] );
	
	
	// default key
	if( empty($field['key']) ) {
		
		$field['key'] = uniqid('field_');
			
	}
	
	
	// update parent
	if( $new_parent ) {
	
		$field['parent'] = $new_parent;
		
	}
	
	
	// update conditional logic references (because field keys have changed)
	if( !empty($field['conditional_logic']) ) {
	
		// extract groups
		$groups = acf_extract_var( $field, 'conditional_logic' );
		
		
		// loop over groups
		foreach( array_keys($groups) as $g ) {
			
			// extract group
			$group = acf_extract_var( $groups, $g );
			
			
			// bail early if empty
			if( empty($group) ) {
				
				continue;
				
			}
			
			
			// loop over rules
			foreach( array_keys($group) as $r ) {
				
				// extract rule
				$rule = acf_extract_var( $group, $r );
				
				
				// vars
				$new_key = acf_get_setting( 'duplicate_key_' . $rule['field'] );
				
				
				// update rule with new key
				if( $new_key ) {
					
					$rule['field'] = $new_key;
					
				}
				
				
				// append to group
				$group[ $r ] = $rule;
				
			}
			
			
			// append to groups
			$groups[ $g ] = $group;
			
		}
		
		
		// update conditional logic
		$field['conditional_logic'] = $groups;
		
		
	}
	
	
	
	// filter for 3rd party customization
	$field = apply_filters( "acf/duplicate_field", $field);
	$field = apply_filters( "acf/duplicate_field/type={$field['type']}", $field );
	
	
	// save
	return acf_update_field( $field );
	
}


/*
*  acf_delete_field
*
*  This function will delete a field from the databse
*
*  @type	function
*  @date	2/10/13
*  @since	5.0.0
*
*  @param	$id (int)
*  @return	(boolean)
*/

function acf_delete_field( $selector = 0 ) {
	
	// disable JSON to avoid conflicts between DB and JSON
	acf_disable_local();
	
	
	// load the origional field gorup
	$field = acf_get_field( $selector );
	
	
	// bail early if field did not load correctly
	if( empty($field) ) {
		
		return false;
	
	}
	
	
	// delete field
	wp_delete_post( $field['ID'], true );
	
	
	// action for 3rd party customisation
	do_action( "acf/delete_field", $field);
	do_action( "acf/delete_field/type={$field['type']}", $field );
	
	
	// clear cache
	wp_cache_delete( "load_field/ID={$field['ID']}", 'acf' );
	wp_cache_delete( "fields/parent={$field['parent']}", 'acf' );
	
	
	// return
	return true;
}


/*
*  acf_trash_field
*
*  This function will trash a field from the databse
*
*  @type	function
*  @date	2/10/13
*  @since	5.0.0
*
*  @param	$id (int)
*  @return	(boolean)
*/

function acf_trash_field( $selector = 0 ) {
	
	// disable JSON to avoid conflicts between DB and JSON
	acf_disable_local();
	
	
	// load the origional field gorup
	$field = acf_get_field( $selector );
	
	
	// bail early if field did not load correctly
	if( empty($field) ) {
		
		return false;
	
	}
	
	
	// delete field
	wp_trash_post( $field['ID'] );
	
	
	// action for 3rd party customisation
	do_action( 'acf/trash_field', $field );
	
	
	// return
	return true;
}


/*
*  acf_untrash_field
*
*  This function will restore a field from the trash
*
*  @type	function
*  @date	2/10/13
*  @since	5.0.0
*
*  @param	$id (int)
*  @return	(boolean)
*/

function acf_untrash_field( $selector = 0 ) {
	
	// disable JSON to avoid conflicts between DB and JSON
	acf_disable_local();
	
	
	// load the origional field gorup
	$field = acf_get_field( $selector );
	
	
	// bail early if field did not load correctly
	if( empty($field) ) {
		
		return false;
	
	}
	
	
	// delete field
	wp_untrash_post( $field['ID'] );
	
	
	// action for 3rd party customisation
	do_action( 'acf/untrash_field', $field );
	
	
	// return
	return true;
}


/*
*  acf_prepare_fields_for_export
*
*  description
*
*  @type	function
*  @date	11/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_prepare_fields_for_export( $fields = false ) {
	
	// validate
	if( empty($fields) ) {
		
		return $fields;
	}
	
	
	// format
	$keys = array_keys( $fields );
	
	foreach( $keys as $key ) {
		
		// prepare
		$fields[ $key ] = acf_prepare_field_for_export( $fields[ $key ] );
				
	}
	
	
	// filter for 3rd party customization
	$fields = apply_filters('acf/prepare_fields_for_export', $fields);
	
	
	// return
	return $fields;
	
}


/*
*  acf_prepare_field_for_export
*
*  description
*
*  @type	function
*  @date	11/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_prepare_field_for_export( $field ) {
	
	// extract some args
	$extract = acf_extract_vars($field, array(
		'ID',
		'value',
		'menu_order',
		'id',
		'class',
		'parent',
		//'ancestors',
		//'field_group',
		'_name',
		'_input',
		'_valid',
	));
	
	
	// filter for 3rd party customization
	$field = apply_filters( "acf/prepare_field_for_export", $field );
	
	
	// return
	return $field;
}


/*
*  acf_prepare_fields_for_import
*
*  description
*
*  @type	function
*  @date	11/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_prepare_fields_for_import( $fields = false ) {
	
	// validate
	if( empty($fields) ) {
		
		return $fields;
	}
	
	
	// re-index array
	$fields = array_values($fields);
	
	
	// vars
	$i = 0;
	
	
	// format
	while( $i < count($fields) ) {
		
		// prepare field
		$field = acf_prepare_field_for_import( $fields[ $i ] );
		
		
		// $field may be an array of multiple fields (including sub fields)
		if( !isset($field['key']) ) {
			
			$extra = $field;
			
			$field = array_shift($extra);
			$fields = array_merge($fields, $extra);
			
		}
		
		// prepare
		$fields[ $i ] = $field;
		
		
		// $i
		$i++;	
	}
	
	
	// filter for 3rd party customization
	$fields = apply_filters('acf/prepare_fields_for_import', $fields);
	
	
	// return
	return $fields;
	
}


/*
*  acf_prepare_field_for_import
*
*  description
*
*  @type	function
*  @date	11/03/2014
*  @since	5.0.0
*
*  @param	$post_id (int)
*  @return	$post_id (int)
*/

function acf_prepare_field_for_import( $field ) {
	
	// extract some args
	$extract = acf_extract_vars($field, array(
		'value',
		'id',
		'class',
		'_name',
		'_input',
		'_valid',
	));
	
	
	// filter for 3rd party customization
	$field = apply_filters( "acf/prepare_field_for_import", $field );
	
	
	// return
	return $field;
}


/*
*  acf_get_sub_field
*
*  This function will return a field for the given selector, and $field (parent). 
*
*  @type	function
*  @date	30/09/13
*  @since	5.0.0
*
*  @param	$selector (string)
*  @param	$field (mixed)
*  @return	$field (array)
*/

function acf_get_sub_field( $selector, $field ) {
	
	// sub fields
	if( $field['type'] == 'repeater' ) {
		
		// extract sub fields
		$sub_fields = acf_extract_var( $field, 'sub_fields');
		
		if( !empty($sub_fields) ) {
		
			foreach( $sub_fields as $sub_field ) {
				
				if( $sub_field['name'] == $selector || $sub_field['key'] == $selector ) {
					
					// return
					return $sub_field;
					
				}
				// if
				
			}
			// foreach
			
		}
		// if
		
	} elseif( $field['type'] == 'flexible_content' ) {
		
		// vars
		$layouts = acf_extract_var( $field, 'layouts');
		$current = get_row_layout();
		
		
		if( !empty($layouts) ) {
			
			foreach( $layouts as $layout ) {
				
				// skip layout if the current layout key does not match
				if( $current && $current !== $layout['name'] ) {
					
					continue;
					
				} 
				
				
				// extract sub fields
				$sub_fields = acf_extract_var( $layout, 'sub_fields');
				
				if( !empty($sub_fields) ) {
					
					foreach( $sub_fields as $sub_field ) {
						
						if( $sub_field['name'] == $selector || $sub_field['key'] == $selector ) {
							
							// return
							return $sub_field;
							
						}
						// if
						
					}
					// foreach
					
				}
				// if
				
			}
			// foreach
			
		}
		// if

	}
	// if
	
	
	// return
	return false;
	
}




?>
