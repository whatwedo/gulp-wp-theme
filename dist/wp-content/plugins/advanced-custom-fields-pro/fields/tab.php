<?php

/*
*  ACF Tab Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_tab
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_tab') ) :

class acf_field_tab extends acf_field {
	
	
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
		$this->name = 'tab';
		$this->label = __("Tab",'acf');
		$this->category = 'layout';
		$this->defaults = array(
			'value'		=> false, // prevents acf_render_fields() from attempting to load value
			'placement'	=> 'top'
		);
		
		
		// do not delete!
    	parent::__construct();
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
		$atts = array(
			'class'				=> 'acf-tab',
			'data-placement'	=> $field['placement']
		);
		
		?>
		<div <?php acf_esc_attr_e( $atts ); ?>><?php echo $field['label']; ?></div>
		<?php
		
		
	}
	
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @param	$field	- an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field_settings( $field ) {
		
		?><tr class="acf-field" data-setting="tab" data-name="warning">
			<td class="acf-label">
				<label><?php _e("Warning",'acf'); ?></label>
			</td>
			<td class="acf-input">
				<p style="margin:0;">
					<span class="acf-error-message" style="margin:0; padding:8px !important;">
					<?php _e("The tab field will display incorrectly when added to a Table style repeater field or flexible content field layout",'acf'); ?>
					</span>
				</p>
			</td>
		</tr>
		<?php
		
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Instructions','acf'),
			'instructions'	=> '',
			'type'			=> 'message',
			'message'		=>  __( 'Use "Tab Fields" to better organize your edit screen by grouping fields together.','acf') . 
							'<br /><br />' .
							   __( 'All fields following this "tab field" (or until another "tab field" is defined) will be grouped together using this field\'s label as the tab heading.','acf')
							   
		));
		
		
		// preview_size
		acf_render_field_setting( $field, array(
			'label'			=> __('Placement','acf'),
			'type'			=> 'select',
			'name'			=> 'placement',
			'choices' 		=> array(
				'top'			=>	__("Top aligned",'acf'),
				'left'			=>	__("Left Aligned",'acf'),
			)
		));
				
	}
	
}

new acf_field_tab();

endif;

?>
