<?php

/*
*  ACF Date Picker Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_date_picker
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_date_picker') ) :

class acf_field_date_picker extends acf_field {
	
	
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
		$this->name = 'date_picker';
		$this->label = __("Date Picker",'acf');
		$this->category = 'jquery';
		$this->defaults = array(
			'display_format'	=> 'd/m/Y',
			'return_format'		=> 'd/m/Y',
			'first_day'			=> 1
		);
		
		
		// actions
		add_action('init', array($this, 'init'));
		
		
		// do not delete!
    	parent::__construct();
	}
	
	
	/*
	*  init
	*
	*  This function is run on the 'init' action to set the field's $l10n data. Before the init action, 
	*  access to the $wp_locale variable is not possible.
	*
	*  @type	action (init)
	*  @date	3/09/13
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function init() {
		
		global $wp_locale;
		
		$this->l10n = array(
			'closeText'         => __( 'Done', 'acf' ),
	        'currentText'       => __( 'Today', 'acf' ),
	        'monthNames'        => array_values( $wp_locale->month ),
	        'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
	        'monthStatus'       => __( 'Show a different month', 'acf' ),
	        'dayNames'          => array_values( $wp_locale->weekday ),
	        'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
	        'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),
	        'isRTL'             => isset($wp_locale->is_rtl) ? $wp_locale->is_rtl : false,
		);
		
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
		
		// vars
		$e = '';
		$div = array(
			'class'					=> 'acf-date_picker acf-input-wrap',
			'data-display_format'	=> acf_convert_date_to_js($field['display_format']),
			'data-first_day'		=> $field['first_day'],
		);
		$input = array(
			'id'					=> $field['id'],
			'class' 				=> 'input-alt',
			'type'					=> 'hidden',
			'name'					=> $field['name'],
			'value'					=> $field['value'],
		);
			

		// html
		$e .= '<div ' . acf_esc_attr($div) . '>';
			$e .= '<input ' . acf_esc_attr($input). '/>';
			$e .= '<input type="text" value="" class="input" />';
		$e .= '</div>';
		
		
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
		
		// global
		global $wp_locale;
		
		
		// display_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Display format','acf'),
			'instructions'	=> __('The format displayed when editing a post','acf'),
			'type'			=> 'radio',
			'name'			=> 'display_format',
			'other_choice'	=> 1,
			'choices'		=> array(
				'd/m/Y'			=> date('d/m/Y'),
				'm/d/Y'			=> date('m/d/Y'),
				'F j, Y'		=> date('F j, Y'),
			)
		));
				
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return format','acf'),
			'instructions'	=> __('The format returned via template functions','acf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'other_choice'	=> 1,
			'choices'		=> array(
				'd/m/Y'			=> date('d/m/Y'),
				'm/d/Y'			=> date('m/d/Y'),
				'F j, Y'		=> date('F j, Y'),
				'Ymd'			=> date('Ymd'),
			)
		));
		
		
		// first_day
		acf_render_field_setting( $field, array(
			'label'			=> __('Week Starts On','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'first_day',
			'choices'		=> array_values( $wp_locale->weekday )
		));
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) ) {
			
			return $value;
		
		}
		
		
		// get time
		$unixtimestamp = strtotime( $value );
 
		
		// bail early if timestamp is not correct
		if( !$unixtimestamp ) {
			
			return $value;
			
		}
		
		
		// translate
		$value = date_i18n($field['return_format'], $unixtimestamp);
		
		
		// return
		return $value;
		
	}
	
}

new acf_field_date_picker();

endif;

?>
