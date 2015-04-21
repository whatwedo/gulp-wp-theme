<?php

/*
*  ACF Taxonomy Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_tab
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_taxonomy') ) :

class acf_field_taxonomy extends acf_field {
	
	
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
		$this->name = 'taxonomy';
		$this->label = __("Taxonomy",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'taxonomy' 			=> 'category',
			'field_type' 		=> 'checkbox',
			'multiple'			=> 0,
			'allow_null' 		=> 0,
			'load_save_terms' 	=> 0,
			'return_format'		=> 'id'
		);
		
		
		// extra
		add_action('wp_ajax_acf/fields/taxonomy/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/taxonomy/query',	array($this, 'ajax_query'));
		
		
		// do not delete!
    	parent::__construct();
    	
	}
	
	
	/*
	*  get_choices
	*
	*  This function will return an array of data formatted for use in a select2 AJAX response
	*
	*  @type	function
	*  @date	15/10/2014
	*  @since	5.0.9
	*
	*  @param	$options (array)
	*  @return	(array)
	*/
	
	function get_choices( $options = array() ) {
		
   		// defaults
   		$options = acf_parse_args($options, array(
			'post_id'		=> 0,
			's'				=> '',
			'field_key'		=> '',
		));
		
		
		// vars
   		$r = array();
		$args = array( 'hide_empty'	=> false );
		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		if( !$field ) {
		
			return false;
			
		}
		
				
		// search
		if( $options['s'] ) {
		
			$args['search'] = $options['s'];
			
		}
		
		
		// filters
		$args = apply_filters('acf/fields/taxonomy/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/taxonomy/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/taxonomy/query/key=' . $field['key'], $args, $field, $options['post_id'] );
			
		
		// get terms
		$terms = get_terms( $field['taxonomy'], $args );
		
		
		// sort into hierachial order!
		if( is_taxonomy_hierarchical( $field['taxonomy'] ) ) {
			
			// get parent
			$parent = acf_maybe_get( $args, 'parent', 0 );
			$parent = acf_maybe_get( $args, 'child_of', $parent );
			
			
			// this will fail if a search has taken place because parents wont exist
			if( empty($args['search']) ) {
			
				$terms = _get_term_children( $parent, $terms, $field['taxonomy'] );
				
			}
			
		}
		
		
		/// append to r
		foreach( $terms as $term ) {
		
			// add to json
			$r[] = array(
				'id'	=> $term->term_id,
				'text'	=> $this->get_term_title( $term, $field, $options['post_id'] )
			);
			
		}
		
		
		// return
		return $r;
			
	}
	
	
	/*
	*  ajax_query
	*
	*  description
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function ajax_query() {
		
		
		// validate
		if( empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'acf_nonce') ) {
		
			die();
			
		}
		
		
		// get choices
		$choices = $this->get_choices( $_POST );
		
		
		// validate
		if( !$choices ) {
			
			die();
			
		}
		
		
		// return JSON
		echo json_encode( $choices );
		die();
			
	}
	
	
	/*
	*  get_term_title
	*
	*  This function returns the HTML for a result
	*
	*  @type	function
	*  @date	1/11/2013
	*  @since	5.0.0
	*
	*  @param	$post (object)
	*  @param	$field (array)
	*  @param	$post_id (int) the post_id to which this value is saved to
	*  @return	(string)
	*/
	
	function get_term_title( $term, $field, $post_id = 0 ) {
		
		// get post_id
		if( !$post_id ) {
			
			$form_data = acf_get_setting('form_data');
			
			if( !empty($form_data['post_id']) ) {
				
				$post_id = $form_data['post_id'];
				
			} else {
				
				$post_id = get_the_ID();
				
			}
		}
		
		
		// vars
		$title = '';
		
		
		// ancestors
		$ancestors = get_ancestors( $term->term_id, $field['taxonomy'] );
		
		if( !empty($ancestors) ) {
		
			$title .= str_repeat('- ', count($ancestors));
			
		}
		
		
		// title
		$title .= $term->name;
				
		
		// filters
		$title = apply_filters('acf/fields/taxonomy/result', $title, $term, $field, $post_id);
		$title = apply_filters('acf/fields/taxonomy/result/name=' . $field['_name'] , $title, $term, $field, $post_id);
		$title = apply_filters('acf/fields/taxonomy/result/key=' . $field['key'], $title, $term, $field, $post_id);
		
		
		// return
		return $title;
	}
	
	
	/*
	*  get_terms
	*
	*  This function will return an array of terms for a given field value
	*
	*  @type	function
	*  @date	13/06/2014
	*  @since	5.0.0
	*
	*  @param	$value (array)
	*  @return	$value
	*/
	
	function get_terms( $value, $taxonomy = 'category' ) {
		
		// load terms in 1 query to save multiple DB calls from following code
		if( count($value) > 1 ) {
			
			$terms = get_terms($taxonomy, array(
				'hide_empty'	=> false,
				'include'		=> $value,
			));
			
		}
		
		
		// update value to include $post
		foreach( array_keys($value) as $i ) {
			
			$value[ $i ] = get_term( $value[ $i ], $taxonomy );
			
		}
		
		
		// filter out null values
		$value = array_filter($value);
		
		
		// return
		return $value;
	}
	
	
	/*
	*  load_value()
	*
	*  This filter is appied to the $value after it is loaded from the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value found in the database
	*  @param	$post_id - the $post_id from which the value was loaded from
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the value to be saved in te database
	*/
	
	function load_value( $value, $post_id, $field ) {
		
		// get valid terms
		$value = acf_get_valid_terms($value, $field['taxonomy']);
		
		
		// load/save
		if( $field['load_save_terms'] ) {
			
			// bail early if no value
			if( empty($value) ) {
				
				return $value;
				
			}
			
			
			// get current ID's
			$term_ids = wp_get_object_terms($post_id, $field['taxonomy'], array('fields' => 'ids', 'orderby' => 'none'));
			
			
			// case
			if( empty($term_ids) ) {
				
				// 1. no terms for this post
				return null;
				
			} elseif( is_array($value) ) {
				
				// 2. remove metadata terms which are no longer for this post
				$value = array_map('intval', $value);
				$value = array_intersect( $value, $term_ids );
				
			} elseif( !in_array($value, $term_ids)) {
				
				// 3. term is no longer for this post
				return null;
				
			}
			
		}
		
		
		// return
		return $value;
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
		
		// vars
		if( is_array($value) ) {
		
			$value = array_filter($value);
			
		}
		
		
		// load_save_terms
		if( $field['load_save_terms'] ) {
			
			// vars
			$taxonomy = $field['taxonomy'];
			
			
			// force value to array
			$term_ids = acf_force_type_array( $value );
			
			
			// convert to int
			$term_ids = array_map('intval', $term_ids);
			
			
			// bypass $this->set_terms if called directly from update_field
			if( !did_action('acf/save_post') ) {
				
				wp_set_object_terms( $post_id, $term_ids, $taxonomy, false );
				
				return $value;
				
			}
			
			
			// initialize
			if( empty($this->set_terms) ) {
				
				// create holder
				$this->set_terms = array();
				
				
				// add action
				add_action('acf/save_post', array($this, 'set_terms'), 15, 1);
				
			}
			
			
			// append
			if( empty($this->set_terms[ $taxonomy ]) ) {
				
				$this->set_terms[ $taxonomy ] = array();
				
			}
			
			$this->set_terms[ $taxonomy ] = array_merge($this->set_terms[ $taxonomy ], $term_ids);
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  set_terms
	*
	*  description
	*
	*  @type	function
	*  @date	26/11/2014
	*  @since	5.0.9
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function set_terms( $post_id ) {
		
		// bail ealry if no terms
		if( empty($this->set_terms) ) {
			
			return;
			
		}
		
		
		// loop over terms
		foreach( $this->set_terms as $taxonomy => $term_ids ){
			
			wp_set_object_terms( $post_id, $term_ids, $taxonomy, false );
			
		}
		
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
		
		
		// force value to array
		$value = acf_force_type_array( $value );
		
		
		// convert values to int
		$value = array_map('intval', $value);
		
		
		// load posts if needed
		if( $field['return_format'] == 'object' ) {
			
			// get posts
			$value = $this->get_terms( $value, $field["taxonomy"] );
		
		}
		
		
		// convert back from array if neccessary
		if( $field['field_type'] == 'select' || $field['field_type'] == 'radio' ) {
			
			$value = array_shift($value);
			
		}
		

		// return
		return $value;
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - an array holding all the field's data
	*/
	
	function render_field( $field ) {
		
		
		// force value to array
		$field['value'] = acf_force_type_array( $field['value'] );
		
		
		// convert values to int
		$field['value'] = array_map('intval', $field['value']);
		
		?>
<div class="acf-taxonomy-field" data-load_save="<?php echo $field['load_save_terms']; ?>">
	<?php

	if( $field['field_type'] == 'select' ) {
	
		$field['multiple'] = 0;
		
		$this->render_field_select( $field );
	
	} elseif( $field['field_type'] == 'multi_select' ) {
		
		$field['multiple'] = 1;
		
		$this->render_field_select( $field );
	
	} elseif( $field['field_type'] == 'radio' ) {
		
		$this->render_field_checkbox( $field );
		
	} elseif( $field['field_type'] == 'checkbox' ) {
	
		$this->render_field_checkbox( $field );
		
	}

	?>
</div><?php
	
		
	}
	
	
	/*
	*  render_field_select()
	*
	*  Create the HTML interface for your field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - an array holding all the field's data
	*/
	
	function render_field_select( $field ) {
		
		// Change Field into a select
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = array();
		
		
		// value
		if( !empty($field['value']) ) {
			
			// get terms
			$terms = $this->get_terms( $field['value'], $field['taxonomy'] );
			
			
			// set choices
			if( !empty($terms) ) {
				
				foreach( array_keys($terms) as $i ) {
					
					// vars
					$term = acf_extract_var( $terms, $i );
					
					
					// append to choices
					$field['choices'][ $term->term_id ] = $this->get_term_title( $term, $field );
				
				}
				
			}
			
		}
		
		
		// render select		
		acf_render_field( $field );
			
	}
	
	
	/*
	*  render_field_checkbox()
	*
	*  Create the HTML interface for your field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - an array holding all the field's data
	*/
	
	function render_field_checkbox( $field ) {
		
		// hidden input
		acf_hidden_input(array(
			'type'	=> 'hidden',
			'name'	=> $field['name'],
		));
		
		
		// checkbox saves an array
		if( $field['field_type'] == 'checkbox' ) {
		
			$field['name'] .= '[]';
			
		}
		
				
		// vars
		$args = array(
			'taxonomy'     => $field['taxonomy'],
			'hide_empty'   => false,
			'style'        => 'none',
			'walker'       => new acf_taxonomy_field_walker( $field ),
		);
		
		
		// filter for 3rd party customization
		$args = apply_filters('acf/fields/taxonomy/wp_list_categories', $args, $field );
		
		?><div class="categorychecklist-holder">
		
			<ul class="acf-checkbox-list acf-bl">
			
				<?php if( $field['field_type'] == 'radio' && $field['allow_null'] ): ?>
					<li>
						<label class="selectit">
							<input type="radio" name="<?php echo $field['name']; ?>" value="" /> <?php _e("None", 'acf'); ?>
						</label>
					</li>
				<?php endif; ?>
				
				<?php wp_list_categories( $args ); ?>
		
			</ul>
			
		</div><?php
		
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
		
		// default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Taxonomy','acf'),
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'choices'		=> acf_get_taxonomies(),
		));
		
		
		// field_type
		acf_render_field_setting( $field, array(
			'label'			=> __('Field Type','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'field_type',
			'optgroup'		=> true,
			'choices'		=> array(
				__("Multiple Values",'acf') => array(
					'checkbox' => __('Checkbox', 'acf'),
					'multi_select' => __('Multi Select', 'acf')
				),
				__("Single Value",'acf') => array(
					'radio' => __('Radio Buttons', 'acf'),
					'select' => __('Select', 'acf')
				)
			)
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
		
		
		// allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Load & Save Terms to Post','acf'),
			'instructions'	=> '',
			'type'			=> 'true_false',
			'name'			=> 'load_save_terms',
			'message'		=> __("Load value based on the post's terms and update the post's terms on save",'acf')
		));
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Value','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'choices'		=> array(
				'object'		=>	__("Term Object",'acf'),
				'id'			=>	__("Term ID",'acf')
			),
			'layout'	=>	'horizontal',
		));
		
	}
	
		
}

new acf_field_taxonomy();

endif;

if( ! class_exists('acf_taxonomy_field_walker') ) :

class acf_taxonomy_field_walker extends Walker {
	
	var $field = null,
		$tree_type = 'category',
		$db_fields = array ( 'parent' => 'parent', 'id' => 'term_id' );
	
	function __construct( $field ) {
	
		$this->field = $field;
		
	}

	function start_el( &$output, $term, $depth = 0, $args = array(), $current_object_id = 0) {
		
		// vars
		$selected = in_array( $term->term_id, $this->field['value'] );
		
		if( $this->field['field_type'] == 'checkbox' ) {
		
			$output .= '<li><label class="selectit"><input type="checkbox" name="' . $this->field['name'] . '" value="' . $term->term_id . '" ' . ($selected ? 'checked="checked"' : '') . ' /> ' . $term->name . '</label>';
			
		} elseif( $this->field['field_type'] == 'radio' ) {
			
			$output .= '<li><label class="selectit"><input type="radio" name="' . $this->field['name'] . '" value="' . $term->term_id . '" ' . ($selected ? 'checked="checkbox"' : '') . ' /> ' . $term->name . '</label>';
		
		}
				
	}
	
	function end_el( &$output, $term, $depth = 0, $args = array() ) {
	
		if( in_array($this->field['field_type'], array('checkbox', 'radio')) ) {
		
			$output .= '</li>';
			
		}
		
		$output .= "\n";
	}
	
	function start_lvl( &$output, $depth = 0, $args = array() ) {
	
		// indent
		//$output .= str_repeat( "\t", $depth);
		
		
		// wrap element
		if( in_array($this->field['field_type'], array('checkbox', 'radio')) ) {
		
			$output .= '<ul class="children acf-bl">' . "\n";
			
		}
		
	}

	function end_lvl( &$output, $depth = 0, $args = array() ) {
	
		// indent
		//$output .= str_repeat( "\t", $depth);
		
		
		// wrap element
		if( in_array($this->field['field_type'], array('checkbox', 'radio')) ) {
		
			$output .= '</ul>' . "\n";
			
		}
		
	}
	
}

endif;

?>
