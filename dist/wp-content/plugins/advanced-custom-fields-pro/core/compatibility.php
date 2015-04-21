<?php 

class acf_compatibility {
	
	/*
	*  __construct
	*
	*  description
	*
	*  @type	function
	*  @date	30/04/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function __construct() {
		
		// fields
		add_filter('acf/get_valid_field',					array($this, 'get_valid_field'), 20, 1);
		add_filter('acf/get_valid_field/type=textarea',		array($this, 'get_valid_textarea_field'), 20, 1);
		add_filter('acf/get_valid_field/type=relationship',	array($this, 'get_valid_relationship_field'), 20, 1);
		add_filter('acf/get_valid_field/type=post_object',	array($this, 'get_valid_relationship_field'), 20, 1);
		add_filter('acf/get_valid_field/type=page_link',	array($this, 'get_valid_relationship_field'), 20, 1);
		add_filter('acf/get_valid_field/type=image',		array($this, 'get_valid_image_field'), 20, 1);
		add_filter('acf/get_valid_field/type=file',			array($this, 'get_valid_image_field'), 20, 1);
		add_filter('acf/get_valid_field/type=wysiwyg',		array($this, 'get_valid_wysiwyg_field'), 20, 1);
		add_filter('acf/get_valid_field/type=date_picker',	array($this, 'get_valid_date_picker_field'), 20, 1);
		
		
		// field groups
		add_filter('acf/get_valid_field_group',				array($this, 'get_valid_field_group'), 20, 1);
		
		
		// settings
		add_action('after_setup_theme',						array($this, 'after_setup_theme'), 20);
	}
	
	
	/*
	*  after_setup_theme
	*
	*  description
	*
	*  @type	function
	*  @date	19/05/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function after_setup_theme() {
		
		if( defined('ACF_LITE') && ACF_LITE ) {
			
			acf_update_setting('show_admin', false);
			
		}
			
	}
	
	
	/*
	*  get_valid_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function get_valid_field( $field ) {
		
		// conditional logic has changed
		if( isset($field['conditional_logic']['status']) ) {
			
			// extract logic
			$logic = acf_extract_var( $field, 'conditional_logic' );
			
			
			// disabled
			if( !empty($logic['status']) ) {
				
				// reset
				$field['conditional_logic'] = array();
				
				
				// vars
				$group = 0;
		 		$all_or_any = $logic['allorany'];
		 		
		 		
		 		// loop over rules
		 		if( !empty($logic['rules']) ) {
			 		
			 		foreach( $logic['rules'] as $rule ) {
				 		
					 	// sperate groups?
					 	if( $all_or_any == 'any' ) {
					 	
						 	$group++;
						 	
					 	}
					 	
					 	
					 	// add to group
					 	$field['conditional_logic'][ $group ][] = $rule;
			 	
				 	}
				 	
		 		}
			 	
			 	
			 	// reset keys
				$field['conditional_logic'] = array_values($field['conditional_logic']);
				
				
			} else {
				
				$field['conditional_logic'] = 0;
				
			}
		 	
		}
		
		
		// return
		return $field;
		
	}
	
	
	/*
	*  get_valid_relationship_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function get_valid_relationship_field( $field ) {
		
		// remove 'all' from post_type
		if( acf_in_array('all', $field['post_type']) ) {
			
			$field['post_type'] = array();
			
		}
		
		
		// remove 'all' from taxonomy
		if( acf_in_array('all', $field['taxonomy']) ) {
			
			$field['taxonomy'] = array();
			
		}
		
		
		// save_format is now return_format
		if( !empty($field['result_elements']) ) {
			
			$field['elements'] = acf_extract_var( $field, 'result_elements' );
			
		}
		
		
		
		// return
		return $field;
	}
	
	
	/*
	*  get_valid_textarea_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function get_valid_textarea_field( $field ) {
		
		// formatting has been removed
		$formatting = acf_extract_var( $field, 'formatting' );
		
		if( $formatting === 'br' ) {
			
			$field['new_lines'] = 'br';
			
		}
		
		
		// return
		return $field;
	}
	
	
	/*
	*  get_valid_image_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function get_valid_image_field( $field ) {
		
		// save_format is now return_format
		if( !empty($field['save_format']) ) {
			
			$field['return_format'] = acf_extract_var( $field, 'save_format' );
			
		}
		
		
		// object is now array
		if( $field['return_format'] == 'object' ) {
			
			$field['return_format'] = 'array';
			
		}
		
		
		// return
		return $field;
	}
	
	
	/*
	*  get_valid_wysiwyg_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function get_valid_wysiwyg_field( $field ) {
		
		// media_upload is now numeric
		if( $field['media_upload'] === 'yes' ) {
			
			$field['media_upload'] = 1;
			
		} elseif( $field['media_upload'] === 'no' ) {
			
			$field['media_upload'] = 0;
			
		}
		
		
		// return
		return $field;
	}
	
	
	/*
	*  get_valid_date_picker_field
	*
	*  This function will provide compatibility with ACF4 fields
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field (array)
	*  @return	$field
	*/
	
	function get_valid_date_picker_field( $field ) {
		
		// v4 used date_format
		if( !empty($field['date_format']) ) {
			
			// extract vars
			$date_format = acf_extract_var( $field, 'date_format' );
			$display_format = acf_extract_var( $field, 'display_format' );
			
			
			// convert from js to php
			$date_format = acf_convert_date_to_php( $date_format );
			$display_format = acf_convert_date_to_php( $display_format );
			
			
			// append settings
			$field['return_format'] = $date_format;
			$field['display_format'] = $display_format;
			
		}
		
		
		// return
		return $field;
	}
	
	
	/*
	*  get_valid_field_group
	*
	*  This function will provide compatibility with ACF4 field groups
	*
	*  @type	function
	*  @date	23/04/2014
	*  @since	5.0.0
	*
	*  @param	$field_group (array)
	*  @return	$field_group
	*/
	
	function get_valid_field_group( $field_group ) {
		
		// global
		global $wpdb;
		
		
		// vars
		$v = 5;
		
		
		// add missing 'key' (v5.0.0)
		if( empty($field_group['key']) ) {
			
			// update version
			$v = 4;
			
			
			// add missing key
			$field_group['key'] = empty($field_group['id']) ? uniqid('group_') : 'group_' . $field_group['id'];
			
		}
		
		
		// extract options (v5.0.0)
		if( !empty($field_group['options']) ) {
			
			$options = acf_extract_var($field_group, 'options');
			$field_group = array_merge($field_group, $options);
			
		}
		
		
		// location rules changed to groups (v5.0.0)
		if( !empty($field_group['location']['rules']) ) {
			
			// extract location
			$location = acf_extract_var( $field_group, 'location' );
			
			
			// reset location
			$field_group['location'] = array();
			
			
			// vars
			$group = 0;
	 		$all_or_any = $location['allorany'];
	 		
	 		
	 		// loop over rules
	 		if( !empty($location['rules']) ) {
		 		
		 		foreach( $location['rules'] as $rule ) {
			 		
				 	// sperate groups?
				 	if( $all_or_any == 'any' ) {
				 	
					 	$group++;
					 	
				 	}
				 	
				 	
				 	// add to group
				 	$field_group['location'][ $group ][] = $rule;
		 	
			 	}
			 	
	 		}
		 	
		 	
		 	// reset keys
			$field_group['location'] = array_values($field_group['location']);
		 	
		}
		
		
		// some location rules have changed (v5.0.0)
		if( !empty($field_group['location']) ) {
			
			// param changes
		 	$param_replace = array(
		 		'taxonomy'		=> 'post_taxonomy',
		 		'ef_media'		=> 'attachment',
		 		'ef_taxonomy'	=> 'taxonomy',
		 		'ef_user'		=> 'user_role',
		 		'user_type'		=> 'current_user_role' // 5.2.0
		 	);
		 	
		 	
		 	// remove conflicting param
		 	if( $v == 5 ) {
			 	
			 	unset($param_replace['taxonomy']);
			 	
		 	}
		 	
		 	
			// loop over location groups
			foreach( array_keys($field_group['location']) as $i ) {
				
				// extract group
				$group = acf_extract_var( $field_group['location'], $i );
				
				
				// bail early if group is empty
				if( empty($group) ) {
					
					continue;
					
				}
				
				
				// loop over group rules
				foreach( array_keys($group) as $j ) {
					
					// extract rule
					$rule = acf_extract_var( $group, $j );
					
					
					// migrate param
					if( isset($param_replace[ $rule['param'] ]) ) {
						
						$rule['param'] = $param_replace[ $rule['param'] ];
						
					}
					
					 	
				 	// category / taxonomy terms are saved differently
				 	if( $rule['param'] == 'post_category' || $rule['param'] == 'post_taxonomy' ) {
					 	
					 	if( is_numeric($rule['value']) ) {
						 	
						 	$term_id = $rule['value'];
						 	$taxonomy = $wpdb->get_var( $wpdb->prepare( "SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d LIMIT 1", $term_id) );
						 	$term = get_term( $term_id, $taxonomy );
						 	
						 	// update rule value
						 	$rule['value'] = "{$term->taxonomy}:{$term->slug}";
						 	
					 	}
					 	
				 	}
				 	
				 	
				 	// append rule
				 	$group[ $j ] = $rule;
				 	
				}
				// foreach
				
				
				// append group
				$field_group['location'][ $i ] = $group;
				
			}
			// foreach
			
		}
		// if
		
		
		// change layout to style (v5.0.0)
		if( !empty($field_group['layout']) ) {
		
			$field_group['style'] = acf_extract_var($field_group, 'layout');
			
		}
		
		
		// change no_box to seamless (v5.0.0)
		if( $field_group['style'] === 'no_box' ) {
		
			$field_group['style'] = 'seamless';
			
		}
		
		
		//return
		return $field_group;
		
	}
	
}

new acf_compatibility();

?>
