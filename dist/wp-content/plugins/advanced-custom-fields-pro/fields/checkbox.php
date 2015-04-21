<?php

/*
*  ACF Checkbox Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_checkbox
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_checkbox') ) :

class acf_field_checkbox extends acf_field {
	
	
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
		$this->name = 'checkbox';
		$this->label = __("Checkbox",'acf');
		$this->category = 'choice';
		$this->defaults = array(
			'layout'		=> 'vertical',
			'choices'		=> array(),
			'default_value'	=> '',
		);
		
		
		// do not delete!
    	parent::__construct();
	}
		
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field (array) the $field being rendered
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field (array) the $field being edited
	*  @return	n/a
	*/
	
	function render_field( $field ) {
		
		// decode value (convert to array)
		$field['value'] = acf_force_type_array($field['value']);
		
		
		// hiden input
		acf_hidden_input(array(
			'type'	=> 'hidden',
			'name'	=> $field['name'],
		));
		
		
		// vars
		$i = 0;
		
		
		// class
		$field['class'] .= ' acf-checkbox-list';
		$field['class'] .= ($field['layout'] == 'horizontal') ? ' acf-hl' : ' acf-bl';

		
		// e
		$e = '<ul ' . acf_esc_attr(array( 'class' => $field['class'] )) . '>';
		
		
		// checkbox saves an array
		$field['name'] .= '[]';
		
		
		// foreach choices
		if( !empty($field['choices']) ) {
			
			foreach( $field['choices'] as $value => $label ) {
				
				// increase counter
				$i++;
				
				
				// vars
				$atts = array(
					'type'	=> 'checkbox',
					'id'	=> $field['id'], 
					'name'	=> $field['name'],
					'value'	=> $value,
				);
				
				
				if( in_array($value, $field['value']) ) {
					
					$atts['checked'] = 'checked';
					
				}
				
				
				if( isset($field['disabled']) && acf_in_array($value, $field['disabled']) ) {
				
					$atts['disabled'] = 'disabled';
					
				}
				
				
				// each input ID is generated with the $key, however, the first input must not use $key so that it matches the field's label for attribute
				if( $i > 1 ) {
				
					$atts['id'] .= '-' . $value;
					
				}
				
				$e .= '<li><label><input ' . acf_esc_attr( $atts ) . '/>' . $label . '</label></li>';
			}
		
		}
		
		$e .= '</ul>';
		
		
		// return
		echo $e;
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
		
		
		// layout
		acf_render_field_setting( $field, array(
			'label'			=> __('Layout','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'layout',
			'layout'		=> 'horizontal', 
			'choices'		=> array(
				'vertical'		=> __("Vertical",'acf'), 
				'horizontal'	=> __("Horizontal",'acf')
			)
		));
		
		
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

new acf_field_checkbox();

endif;

?>
