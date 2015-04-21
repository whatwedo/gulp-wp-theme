<?php

/*
*  ACF Repeater Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_repeater
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_repeater') ) :

class acf_field_repeater extends acf_field {
	
	
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
		$this->name = 'repeater';
		$this->label = __("Repeater",'acf');
		$this->category = 'layout';
		$this->defaults = array(
			'sub_fields'	=> array(),
			'min'			=> 0,
			'max'			=> 0,
			'layout' 		=> 'table',
			'button_label'	=> __("Add Row",'acf'),
		);
		$this->l10n = array(
			'min'			=>	__("Minimum rows reached ({min} rows)",'acf'),
			'max'			=>	__("Maximum rows reached ({max} rows)",'acf'),
		);
		
		
		// do not delete!
    	parent::__construct();
	}
		
	
	/*
	*  load_field()
	*
	*  This filter is appied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/
	
	function load_field( $field ) {
		
		$field['sub_fields'] = acf_get_fields( $field );
		
		
		// return
		return $field;
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
		
		// ensure value is an array
		if( empty($field['value']) ) {
		
			$field['value'] = array();
			
		}
		
		
		// rows
		$field['min'] = empty($field['min']) ? 0 : $field['min'];
		$field['max'] = empty($field['max']) ? 0 : $field['max'];
		
		
		// populate the empty row data (used for acfcloneindex and min setting)
		$empty_row = array();
		
		foreach( $field['sub_fields'] as $f ) {
			
			$empty_row[ $f['key'] ] = isset( $f['default_value'] ) ? $f['default_value'] : false;
			
		}
				
		
		// If there are less values than min, populate the extra values
		if( $field['min'] ) {
			
			for( $i = 0; $i < $field['min']; $i++ ) {
			
				// continue if already have a value
				if( array_key_exists($i, $field['value']) ) {
				
					continue;
					
				}
				
				
				// populate values
				$field['value'][ $i ] = $empty_row;
				
			}
			
		}
		
		
		// If there are more values than man, remove some values
		if( $field['max'] ) {
		
			for( $i = 0; $i < count($field['value']); $i++ ) {
			
				if( $i >= $field['max'] ) {
				
					unset( $field['value'][ $i ] );
					
				}
				
			}
			
		}
		
		
		// setup values for row clone
		$field['value']['acfcloneindex'] = $empty_row;
		
		
		// show columns
		$show_order = true;
		$show_add = true;
		$show_remove = true;
		
		
		if( $field['max'] ) {
		
			if( $field['max'] == 1 ) {
			
				$show_order = false;
				
			}
			
			if( $field['max'] <= $field['min'] ) {
			
				$show_remove = false;
				$show_add = false;
				
			}
			
		}
		
		
		// field wrap
		$el = 'td';
		$before_fields = '';
		$after_fields = '';
		
		if( $field['layout'] == 'row' ) {
		
			$el = 'tr';
			$before_fields = '<td class="acf-table-wrap"><table class="acf-table">';
			$after_fields = '</table></td>';
			
		} elseif( $field['layout'] == 'block' ) {
		
			$el = 'div';
			
			$before_fields = '<td class="acf-fields">';
			$after_fields = '</td>';
			
		}
		
		
		// hidden input
		acf_hidden_input(array(
			'type'	=> 'hidden',
			'name'	=> $field['name'],
		));
		
?>
<div <?php acf_esc_attr_e(array( 'class' => 'acf-repeater', 'data-min' => $field['min'], 'data-max'	=> $field['max'] )); ?>>
<table <?php acf_esc_attr_e(array( 'class' => "acf-table acf-input-table {$field['layout']}-layout" )); ?>>
	
	<?php if( $field['layout'] == 'table' ): ?>
		<thead>
			<tr>
				<?php if( $show_order ): ?>
					<th class="order"><span class="order-spacer"></span></th>
				<?php endif; ?>
				
				<?php foreach( $field['sub_fields'] as $sub_field ): 
					
					$atts = array(
						'class'		=> "acf-th acf-th-{$sub_field['name']}",
						'data-key'	=> $sub_field['key'],
					);
					
					
					// Add custom width
					if( $sub_field['wrapper']['width'] ) {
					
						$atts['data-width'] = $sub_field['wrapper']['width'];
						
					}
						
					?>
					
					<th <?php acf_esc_attr_e( $atts ); ?>>
						<?php acf_the_field_label( $sub_field ); ?>
						<?php if( $sub_field['instructions'] ): ?>
							<p class="description"><?php echo $sub_field['instructions']; ?></p>
						<?php endif; ?>
					</th>
					
				<?php endforeach; ?>

				<?php if( $show_remove ): ?>
					<th class="remove"><span class="remove-spacer"></span></th>
				<?php endif; ?>
			</tr>
		</thead>
	<?php endif; ?>
	
	<tbody>
		<?php foreach( $field['value'] as $i => $row ): ?>
			<tr class="acf-row<?php echo ($i === 'acfcloneindex') ? ' acf-clone' : ''; ?>">
				
				<?php if( $show_order ): ?>
					<td class="order" title="<?php _e('Drag to reorder','acf'); ?>"><?php echo intval($i) + 1; ?></td>
				<?php endif; ?>
				
				<?php echo $before_fields; ?>
				
				<?php foreach( $field['sub_fields'] as $sub_field ): 
					
					// prevent repeater field from creating multiple conditional logic items for each row
					if( $i !== 'acfcloneindex' ) {
					
						$sub_field['conditional_logic'] = 0;
						
					}
					
					
					// add value
					if( isset($row[ $sub_field['key'] ]) ) {
						
						// this is a normal value
						$sub_field['value'] = $row[ $sub_field['key'] ];
						
					} elseif( isset($sub_field['default_value']) ) {
						
						// no value, but this sub field has a default value
						$sub_field['value'] = $sub_field['default_value'];
						
					}
					
					
					// update prefix to allow for nested values
					$sub_field['prefix'] = "{$field['name']}[{$i}]";
					
					
					// render input
					acf_render_field_wrap( $sub_field, $el ); ?>
					
				<?php endforeach; ?>
				
				<?php echo $after_fields; ?>
				
				<?php if( $show_remove ): ?>
					<td class="remove">
						<a class="acf-icon small acf-repeater-add-row" href="#" data-before="1" title="<?php _e('Add row','acf'); ?>"><i class="acf-sprite-add"></i></a>
						<a class="acf-icon small acf-repeater-remove-row" href="#" title="<?php _e('Remove row','acf'); ?>"><i class="acf-sprite-remove"></i></a>
					</td>
				<?php endif; ?>
				
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php if( $show_add ): ?>
	
	<ul class="acf-hl acf-clearfix">
		<li class="acf-fr">
			<a href="#" class="acf-button blue acf-repeater-add-row"><?php echo $field['button_label']; ?></a>
		</li>
	</ul>
			
<?php endif; ?>
</div>
<?php
		
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
		
		// vars
		$args = array(
			'fields'	=> $field['sub_fields'],
			'layout'	=> $field['layout'],
			'parent'	=> $field['ID']
		);
		
		
		?><tr class="acf-field" data-setting="repeater" data-name="sub_fields">
			<td class="acf-label">
				<label><?php _e("Sub Fields",'acf'); ?></label>
				<p class="description"></p>		
			</td>
			<td class="acf-input">
				<?php 
				
				acf_get_view('field-group-fields', $args);
				
				?>
			</td>
		</tr>
		<?php
		
		
		// rows
		$field['min'] = empty($field['min']) ? '' : $field['min'];
		$field['max'] = empty($field['max']) ? '' : $field['max'];
		
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum Rows','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
			'placeholder'	=> '0',
		));
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum Rows','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
			'placeholder'	=> '0',
		));
		
		
		// layout
		acf_render_field_setting( $field, array(
			'label'			=> __('Layout','acf'),
			'instructions'	=> '',
			'class'			=> 'acf-repeater-layout',
			'type'			=> 'radio',
			'name'			=> 'layout',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'table'			=> __('Table','acf'),
				'block'			=> __('Block','acf'),
				'row'			=> __('Row','acf')
			)
		));
		
		
		// button_label
		acf_render_field_setting( $field, array(
			'label'			=> __('Button Label','acf'),
			'instructions'	=> '',
			'type'			=> 'text',
			'name'			=> 'button_label',
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
		
		// bail early if no value
		if( empty($value) || empty($field['sub_fields']) ) {
			
			return $value;
			
		}
		
		
		// convert to int
		$value = intval( $value );
		
		
		// vars
		$rows = array();
		
		
		// check number of rows
		if( $value > 0 ) {
			
			// loop through rows
			for( $i = 0; $i < $value; $i++ ) {
				
				// create empty array
				$rows[ $i ] = array();
				
				
				// loop through sub fields
				foreach( array_keys($field['sub_fields']) as $j ) {
					
					// get sub field
					$sub_field = $field['sub_fields'][ $j ];
					
					
					// update $sub_field name
					$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
					
					
					// get value
					$sub_value = acf_get_value( $post_id, $sub_field );
				
				
					// add value
					$rows[ $i ][ $sub_field['key'] ] = $sub_value;
					
				}
				// foreach
				
			}
			// for
			
		}
		// if
		
		
		// return
		return $rows;
		
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
		if( empty($value) || empty($field['sub_fields']) ) {
						
			return $value;
			
		}
		
		
		// loop over rows
		foreach( array_keys($value) as $i ) {
			
			// loop through sub fields
			foreach( array_keys($field['sub_fields']) as $j ) {
				
				// get sub field
				$sub_field = $field['sub_fields'][ $j ];
				
				
				// extract value
				$sub_value = acf_extract_var( $value[ $i ], $sub_field['key'] );
				
				
				// format value
				$sub_value = acf_format_value( $sub_value, $post_id, $sub_field );
				
				
				// append to $row
				$value[ $i ][ $sub_field['name'] ] = $sub_value;
				
			}
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  validate_value
	*
	*  description
	*
	*  @type	function
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_value( $valid, $value, $field, $input ){
		
		// remove acfcloneindex
		if( isset($value['acfcloneindex']) ) {
		
			unset($value['acfcloneindex']);
			
		}
		
		
		// valid
		if( $field['required'] && empty($value) ) {
		
			$valid = false;
			
		}
		
		
		// check sub fields
		if( !empty($field['sub_fields']) && !empty($value) ) {
			
			$keys = array_keys($value);
			
			foreach( $keys as $i ) {
				
				foreach( $field['sub_fields'] as $sub_field ) {
					
					// vars
					$k = $sub_field['key'];
					
					
					// test sub field exists
					if( !isset($value[ $i ][ $k ]) ) {
					
						continue;
						
					}
					
					
					// validate
					acf_validate_value( $value[ $i ][ $k ], $sub_field, "{$input}[{$i}][{$k}]" );
				}
				
			}
			
		}
		
		return $valid;
		
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
	*  @param	$field - the field array holding all the field options
	*  @param	$post_id - the $post_id of which the value will be saved
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// remove acfcloneindex
		if( isset($value['acfcloneindex']) ) {
		
			unset($value['acfcloneindex']);
			
		}
		
		
		// vars
		$total = 0;
		
		
		// update sub fields
		if( !empty($value) ) {
			
			// $i
			$i = -1;
			
			
			// loop through rows
			foreach( $value as $row ) {	
				
				// $i
				$i++;
				
				
				// increase total
				$total++;
				
				
				// loop through sub fields
				if( !empty($field['sub_fields']) ) {
				
					foreach( $field['sub_fields'] as $sub_field ) {
						
						// value
						$v = false;
						
						
						// key (backend)
						if( isset($row[ $sub_field['key'] ]) ) {
							
							$v = $row[ $sub_field['key'] ];
							
						} elseif( isset($row[ $sub_field['name'] ]) ) {
							
							$v = $row[ $sub_field['name'] ];
							
						} else {
							
							// input is not set (hidden by conditioanl logic)
							continue;
							
						}
						
						
						// modify name for save
						$sub_field['name'] = "{$field['name']}_{$i}_{$sub_field['name']}";
						
						
						// update field
						acf_update_value( $v, $post_id, $sub_field );
						
					}
					// foreach
					
				}
				// if
				
			}
			// foreach
			
		}
		// if
		
		
		// get old value (db only)
		$old_total = intval( acf_get_value( $post_id, $field, true ) );
		
		if( $old_total > $total ) {
			
			for ( $i = $total; $i < $old_total; $i++ ) {
				
				foreach( $field['sub_fields'] as $sub_field ) {
					
					acf_delete_value( $post_id, "{$field['name']}_{$i}_{$sub_field['name']}" );
				
				}
				// foreach
			
			}
			// for
			
		}
		// if

		
		// update $value and return to allow for the normal save function to run
		$value = $total;
		
		
		// return
		return $value;
	}
	
	
	/*
	*  delete_field
	*
	*  description
	*
	*  @type	function
	*  @date	4/04/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function delete_field( $field ) {
		
		// loop through sub fields
		if( !empty($field['sub_fields']) ) {
		
			foreach( $field['sub_fields'] as $sub_field ) {
			
				acf_delete_field( $sub_field['ID'] );
				
			}
			
		}
		
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
		
		// remove sub fields
		unset($field['sub_fields']);
		
				
		// return		
		return $field;
	}
	
	
	/*
	*  duplicate_field()
	*
	*  This filter is appied to the $field before it is duplicated and saved to the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the modified field
	*/

	function duplicate_field( $field ) {
		
		// get sub fields
		$sub_fields = acf_extract_var( $field, 'sub_fields' );
		
		
		// save field to get ID
		$field = acf_update_field( $field );
		
		
		// duplicate sub fields
		acf_duplicate_fields( $sub_fields, $field['ID'] );
		
						
		// return		
		return $field;
	}

}

new acf_field_repeater();

endif;

?>
