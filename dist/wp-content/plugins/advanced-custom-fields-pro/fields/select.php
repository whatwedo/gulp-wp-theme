<?php

/*
*  ACF Select Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_select
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_select') ) :

class acf_field_select extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// vars
		$this->name = 'select';
		$this->label = __("Select",'acf');
		$this->category = 'choice';
		$this->defaults = array(
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'choices'		=> array(),
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
			'disabled'		=> 0,
			'readonly'		=> 0,
		);
		
		
		// ajax
		add_action('wp_ajax_acf/fields/select/query',				array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/select/query',		array($this, 'ajax_query'));
		
		
		// do not delete!
    	parent::__construct();
    	
	}

	
	/*
	*  query_posts
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function ajax_query() {
		
   		// options
   		$options = acf_parse_args( $_POST, array(
			'post_id'					=>	0,
			's'							=>	'',
			'field_key'					=>	'',
			'nonce'						=>	'',
		));
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		if( !$field ) {
		
			die();
			
		}
		
		
		// vars
		$r = array();
		$s = false;
		
		
		// search
		if( $options['s'] !== '' ) {
			
			// search may be integer
			$s = strval($options['s']);
			
			
			// strip slashes
			$s = wp_unslash($s);
			
		}		
		
		
		// loop through choices
		if( !empty($field['choices']) ) {
		
			foreach( $field['choices'] as $k => $v ) {
				
				// if searching, but doesn't exist
				if( $s !== false && stripos($v, $s) === false ) {
				
					continue;
					
				}
				
				
				// append
				$r[] = array(
					'id'	=> $k,
					'text'	=> strval( $v )
				);
				
			}
			
		}
		
		
		// return JSON
		echo json_encode( $r );
		die();
			
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {

		// convert value to array
		$field['value'] = acf_force_type_array($field['value']);
		
		
		// add empty value (allows '' to be selected)
		if( empty($field['value']) ){
			
			$field['value'][''] = '';
			
		}
		
		
		// placeholder
		if( empty($field['placeholder']) ) {
		
			$field['placeholder'] = __("Select",'acf');
			
		}
		
		
		// vars
		$atts = array(
			'id'				=> $field['id'],
			'class'				=> $field['class'],
			'name'				=> $field['name'],
			'data-ui'			=> $field['ui'],
			'data-ajax'			=> $field['ajax'],
			'data-multiple'		=> $field['multiple'],
			'data-placeholder'	=> $field['placeholder'],
			'data-allow_null'	=> $field['allow_null']
		);
		
		
		// ui
		if( $field['ui'] ) {
		
			$atts['disabled'] = 'disabled';
			$atts['class'] .= ' acf-hidden';
			
		}
		
		
		// multiple
		if( $field['multiple'] ) {
		
			$atts['multiple'] = 'multiple';
			$atts['size'] = 5;
			$atts['name'] .= '[]';
			
		} 
		
		
		// special atts
		foreach( array( 'readonly', 'disabled' ) as $k ) {
		
			if( !empty($field[ $k ]) ) {
			
				$atts[ $k ] = $k;
			}
			
		}
		
		
		// vars
		$els = array();
		$choices = array();
		
		
		// loop through values and add them as options
		if( !empty($field['choices']) ) {
		
			foreach( $field['choices'] as $k => $v ) {
				
				if( is_array($v) ){
					
					// optgroup
					$els[] = array( 'type' => 'optgroup', 'label' => $k );
					
					if( !empty($v) ) {
						
						foreach( $v as $k2 => $v2 ) {
							
							$els[] = array( 'type' => 'option', 'value' => $k2, 'label' => $v2, 'selected' => in_array($k2, $field['value']) );
							
							$choices[] = $k2;
						}
						
					}
					
					$els[] = array( 'type' => '/optgroup' );
				
				} else {
					
					$els[] = array( 'type' => 'option', 'value' => $k, 'label' => $v, 'selected' => in_array($k, $field['value']) );
					
					$choices[] = $k;
					
				}
				
			}
			
		}
		
		
		// prepende orphans
		/*
		if( !empty($field['value']) ) {
			
			foreach( $field['value'] as $v ) {
				
				if( empty($v) ) {
					
					continue;
					
				}
				
				if( !in_array($v, $choices) ) {
					
					array_unshift( $els, array( 'type' => 'option', 'value' => $v, 'label' => $v, 'selected' => true ) );
					
				}
				
			}
			
		}
		*/
		
		
		// hidden input
		if( $field['ui'] ) {
			
			// find real value based on $choices and $field['value']
			$real_value = array_intersect($field['value'], $choices);
		
			acf_hidden_input(array(
				'type'	=> 'hidden',
				'id'	=> $field['id'],
				'name'	=> $field['name'],
				'value'	=> implode(',', $real_value)
			));
			
		} elseif( $field['multiple'] ) {
			
			acf_hidden_input(array(
				'type'	=> 'hidden',
				'name'	=> $field['name'],
			));
			
		}
		
		
		// null
		if( $field['allow_null'] ) {
			
			array_unshift( $els, array( 'type' => 'option', 'value' => '', 'label' => '- ' . $field['placeholder'] . ' -' ) );
			
		}		
		
		
		// html
		echo '<select ' . acf_esc_attr( $atts ) . '>';	
		
		
		// construct html
		if( !empty($els) ) {
			
			foreach( $els as $el ) {
				
				// extract type
				$type = acf_extract_var($el, 'type');
				
				
				if( $type == 'option' ) {
					
					// get label
					$label = acf_extract_var($el, 'label');
					
					
					// validate selected
					if( acf_extract_var($el, 'selected') ) {
						
						$el['selected'] = 'selected';
						
					}
					
					
					// echo
					echo '<option ' . acf_esc_attr( $el ) . '>' . $label . '</option>';
					
				} else {
					
					// echo
					echo '<' . $type . ' ' . acf_esc_attr( $el ) . '>';
					
				}
				
				
			}
			
		}
		

		echo '</select>';
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		
		
		// encode choices (convert from array)
		$field['choices'] = acf_encode_choices($field['choices']);
		$field['default_value'] = acf_encode_choices($field['default_value']);
		
		
		// choices
		acf_render_field_setting( $field, array(
			'label'			=> __('Choices','acf'),
			'instructions'	=> __('Enter each choice on a new line.','acf') . '<br /><br />' . __('For more control, you may specify both a value and label like this:','acf'). '<br /><br />' . __('red : Red','acf'),
			'type'			=> 'textarea',
			'name'			=> 'choices',
		));	
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Enter each default value on a new line','acf'),
			'type'			=> 'textarea',
			'name'			=> 'default_value',
		));
		
		
		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'allow_null',
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// multiple
		acf_render_field_setting( $field, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'multiple',
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
		
		
		// ui
		acf_render_field_setting( $field, array(
			'label'			=> __('Stylised UI','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'ui',
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
				
		
		// ajax
		acf_render_field_setting( $field, array(
			'label'			=> __('Use AJAX to lazy load choices?','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'ajax',
			'choices'		=> array(
				1				=> __("Yes",'acf'),
				0				=> __("No",'acf'),
			),
			'layout'	=>	'horizontal',
		));
			
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is applied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value found in the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*  @return	$value
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// ACF4 null
		if( $value === 'null' ) {
		
			return false;
			
		}
		
		
		// return
		return $value;
	}
	
	
	/*
	*  update_field()
	*
	*  This filter is appied to the $field before it is saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the field group ID (post_type = acf)
	*
	*  @return	$field - the modified field
	*/

	function update_field( $field ) {
		
		// decode choices (convert to array)
		$field['choices'] = acf_decode_choices($field['choices']);
		$field['default_value'] = acf_decode_choices($field['default_value']);
		
		
		// return
		return $field;
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// validate
		if( empty($value) ) {
		
			return $value;
			
		}
		
		
		// array
		if( is_array($value) ) {
			
			// save value as strings, so we can clearly search for them in SQL LIKE statements
			$value = array_map('strval', $value);
			
		}
		
		
		// return
		return $value;
	}
	
}

new acf_field_select();

endif;

?>
