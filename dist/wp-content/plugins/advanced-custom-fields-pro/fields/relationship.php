<?php

/*
*  ACF Relationship Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_relationship
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_relationship') ) :

class acf_field_relationship extends acf_field {
	
	
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
		$this->name = 'relationship';
		$this->label = __("Relationship",'acf');
		$this->category = 'relational';
		$this->defaults = array(
			'post_type'			=> array(),
			'taxonomy'			=> array(),
			'min' 				=> 0,
			'max' 				=> 0,
			'filters'			=> array('search', 'post_type', 'taxonomy'),
			'elements' 			=> array(),
			'return_format'		=> 'object'
		);
		$this->l10n = array(
			'min'		=> __("Minimum values reached ( {min} values )",'acf'),
			'max'		=> __("Maximum values reached ( {max} values )",'acf'),
			'loading'	=> __('Loading','acf'),
			'empty'		=> __('No matches found','acf'),
		);
		
		
		// extra
		add_action('wp_ajax_acf/fields/relationship/query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/relationship/query',	array($this, 'ajax_query'));
		
		
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
			'post_id'			=> 0,
			's'					=> '',
			'post_type'			=> '',
			'taxonomy'			=> '',
			'lang'				=> false,
			'field_key'			=> '',
			'paged'				=> 1
		));
		
		
		// vars
   		$r = array();
   		$args = array();
   		
   		
   		// paged
   		$args['posts_per_page'] = 20;
   		$args['paged'] = $options['paged'];
   		
		
		// load field
		$field = acf_get_field( $options['field_key'] );
		
		if( !$field ) {
		
			return false;
			
		}
		
		
		// WPML
		if( $options['lang'] ) {
			
			global $sitepress;
			
			if( !empty($sitepress) ) {
			
				$sitepress->switch_lang( $options['lang'] );
				
			}
		}
		
		
		// update $args
		if( !empty($options['post_type']) ) {
			
			$args['post_type'] = acf_force_type_array( $options['post_type'] );
		
		} elseif( !empty($field['post_type']) ) {
		
			$args['post_type'] = acf_force_type_array( $field['post_type'] );
			
		} else {
			
			$args['post_type'] = acf_get_post_types();
		}
		
		
		// update taxonomy
		$taxonomies = array();
		
		if( !empty($options['taxonomy']) ) {
			
			$term = acf_decode_taxonomy_term($options['taxonomy']);
			
			// append to $args
			$args['tax_query'] = array(
				
				array(
					'taxonomy'	=> $term['taxonomy'],
					'field'		=> 'slug',
					'terms'		=> $term['term'],
				)
				
			);
			
			
		} elseif( !empty($field['taxonomy']) ) {
			
			$taxonomies = acf_decode_taxonomy_terms( $field['taxonomy'] );
			
			// append to $args
			$args['tax_query'] = array();
			
			
			// now create the tax queries
			foreach( $taxonomies as $taxonomy => $terms ) {
			
				$args['tax_query'][] = array(
					'taxonomy'	=> $taxonomy,
					'field'		=> 'slug',
					'terms'		=> $terms,
				);
				
			}
			
		}	
		
		
		// search
		if( $options['s'] ) {
		
			$args['s'] = $options['s'];
			
		}
		
		
		// filters
		$args = apply_filters('acf/fields/relationship/query', $args, $field, $options['post_id']);
		$args = apply_filters('acf/fields/relationship/query/name=' . $field['name'], $args, $field, $options['post_id'] );
		$args = apply_filters('acf/fields/relationship/query/key=' . $field['key'], $args, $field, $options['post_id'] );
		
		
		// get posts grouped by post type
		$groups = acf_get_grouped_posts( $args );
		
		if( !empty($groups) ) {
			
			foreach( array_keys($groups) as $group_title ) {
				
				// vars
				$posts = acf_extract_var( $groups, $group_title );
				$titles = array();
				
				
				// data
				$data = array(
					'text'		=> $group_title,
					'children'	=> array()
				);
				
				
				foreach( array_keys($posts) as $post_id ) {
					
					// override data
					$posts[ $post_id ] = $this->get_post_title( $posts[ $post_id ], $field, $options['post_id'] );
					
				};
				
				
				// order by search
				if( !empty($args['s']) ) {
					
					$posts = acf_order_by_search( $posts, $args['s'] );
					
				}
				
				
				// append to $data
				foreach( array_keys($posts) as $post_id ) {
					
					$data['children'][] = array(
						'id'	=> $post_id,
						'text'	=> $posts[ $post_id ]
					);
					
				}
				
				
				// append to $r
				$r[] = $data;
				
			}
			
			
			// optgroup or single
			$post_types = acf_force_type_array( $args['post_type'] );
			
			// add as optgroup or results
			if( count($post_types) == 1 ) {
				
				$r = $r[0]['children'];
				
			}
			
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
		
		
		// get posts
		$posts = $this->get_choices( $_POST );
		
		
		// validate
		if( !$posts ) {
			
			die();
			
		}
		
		
		// return JSON
		echo json_encode( $posts );
		die();
			
	}
	
	
	/*
	*  get_post_title
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
	
	function get_post_title( $post, $field, $post_id = 0 ) {
		
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
		$title = acf_get_post_title( $post );
		
		
		// elements
		if( !empty($field['elements']) ) {
			
			if( in_array('featured_image', $field['elements']) ) {
				
				$image = '';
				
				if( $post->post_type == 'attachment' ) {
					
					$image = wp_get_attachment_image( $post->ID, array(17, 17) );
					
				} else {
					
					$image = get_the_post_thumbnail( $post->ID, array(17, 17) );
					
				}
				
				
				$title = '<div class="thumbnail">' . $image . '</div>' . $title;
			}
			
		}
		
		
		// filters
		$title = apply_filters('acf/fields/relationship/result', $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/relationship/result/name=' . $field['_name'], $title, $post, $field, $post_id);
		$title = apply_filters('acf/fields/relationship/result/key=' . $field['key'], $title, $post, $field, $post_id);
		
		
		// return
		return $title;
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
		$values = array();
		$atts = array(
			'id'				=> $field['id'],
			'class'				=> "acf-relationship {$field['class']}",
			'data-min'			=> $field['min'],
			'data-max'			=> $field['max'],
			'data-s'			=> '',
			'data-post_type'	=> '',
			'data-taxonomy'		=> '',
			'data-paged'		=> 1,
		);
		
		
		// Lang
		if( defined('ICL_LANGUAGE_CODE') ) {
		
			$atts['data-lang'] = ICL_LANGUAGE_CODE;
			
		}
		
		
		// data types
		$field['post_type'] = acf_force_type_array( $field['post_type'] );
		$field['taxonomy'] = acf_force_type_array( $field['taxonomy'] );
		
		
		// post_types
		$post_types = array();
		
		if( !empty($field['post_type']) ) {
		
			$post_types = $field['post_type'];


		} else {
			
			$post_types = acf_get_post_types();
			
		}
		
		$post_types = acf_get_pretty_post_types($post_types);
		
		
		// taxonomies
		$taxonomies = array();
		
		if( !empty($field['taxonomy']) ) {
			
			// get the field's terms
			$term_groups = acf_force_type_array( $field['taxonomy'] );
			$term_groups = acf_decode_taxonomy_terms( $term_groups );
			
			
			// update taxonomies
			$taxonomies = array_keys($term_groups);
		
		} elseif( !empty($field['post_type']) ) {
			
			// loop over post types and find connected taxonomies
			foreach( $field['post_type'] as $post_type ) {
				
				$post_taxonomies = get_object_taxonomies( $post_type );
				
				// bail early if no taxonomies
				if( empty($post_taxonomies) ) {
					
					continue;
					
				}
					
				foreach( $post_taxonomies as $post_taxonomy ) {
					
					if( !in_array($post_taxonomy, $taxonomies) ) {
						
						$taxonomies[] = $post_taxonomy;
						
					}
					
				}
							
			}
			
		} else {
			
			$taxonomies = acf_get_taxonomies();
			
		}
		
		
		// terms
		$term_groups = acf_get_taxonomy_terms( $taxonomies );
		
		
		// update $term_groups with specific terms
		if( !empty($field['taxonomy']) ) {
			
			foreach( array_keys($term_groups) as $taxonomy ) {
				
				foreach( array_keys($term_groups[ $taxonomy ]) as $term ) {
					
					if( ! in_array($term, $field['taxonomy']) ) {
						
						unset($term_groups[ $taxonomy ][ $term ]);
						
					}
					
				}
				
			}
			
		}
		
		// width for select filters
		$width = array(
			'search'	=> 0,
			'post_type'	=> 0,
			'taxonomy'	=> 0
		);
		
		if( !empty($field['filters']) ) {
			
			$width = array(
				'search'	=> 50,
				'post_type'	=> 25,
				'taxonomy'	=> 25
			);
			
			foreach( array_keys($width) as $k ) {
				
				if( ! in_array($k, $field['filters']) ) {
				
					$width[ $k ] = 0;
					
				}
				
			}
			
			
			// search
			if( $width['search'] == 0 ) {
			
				$width['post_type'] = ( $width['post_type'] == 0 ) ? 0 : 50;
				$width['taxonomy'] = ( $width['taxonomy'] == 0 ) ? 0 : 50;
				
			}
			
			// post_type
			if( $width['post_type'] == 0 ) {
			
				$width['taxonomy'] = ( $width['taxonomy'] == 0 ) ? 0 : 50;
				
			}
			
			
			// taxonomy
			if( $width['taxonomy'] == 0 ) {
			
				$width['post_type'] = ( $width['post_type'] == 0 ) ? 0 : 50;
				
			}
			
			
			// search
			if( $width['post_type'] == 0 && $width['taxonomy'] == 0 ) {
			
				$width['search'] = ( $width['search'] == 0 ) ? 0 : 100;
				
			}
		}
			
		?>
<div <?php acf_esc_attr_e($atts); ?>>
	
	<div class="acf-hidden">
		<input type="hidden" name="<?php echo $field['name']; ?>" value="" />
	</div>
	
	<?php if( $width['search'] > 0 || $width['post_type'] > 0 || $width['taxonomy'] > 0 ): ?>
	<div class="filters">
		
		<ul class="acf-hl">
		
			<?php if( $width['search'] > 0 ): ?>
			<li style="width:<?php echo $width['search']; ?>%;">
				<div class="inner">
				<input class="filter" data-filter="s" placeholder="<?php _e("Search...",'acf'); ?>" type="text" />
				</div>
			</li>
			<?php endif; ?>
			
			<?php if( $width['post_type'] > 0 ): ?>
			<li style="width:<?php echo $width['post_type']; ?>%;">
				<div class="inner">
				<select class="filter" data-filter="post_type">
					<option value=""><?php _e('Select post type','acf'); ?></option>
					<?php foreach( $post_types as $k => $v ): ?>
						<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
					<?php endforeach; ?>
				</select>
				</div>
			</li>
			<?php endif; ?>
			
			<?php if( $width['taxonomy'] > 0 ): ?>
			<li style="width:<?php echo $width['taxonomy']; ?>%;">
				<div class="inner">
				<select class="filter" data-filter="taxonomy">
					<option value=""><?php _e('Select taxonomy','acf'); ?></option>
					<?php foreach( $term_groups as $k_opt => $v_opt ): ?>
						<optgroup label="<?php echo $k_opt; ?>">
							<?php foreach( $v_opt as $k => $v ): ?>
								<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
							<?php endforeach; ?>
						</optgroup>
					<?php endforeach; ?>
				</select>
				</div>
			</li>
			<?php endif; ?>
		</ul>
		
	</div>
	<?php endif; ?>
	
	<div class="selection acf-cf">
	
		<div class="choices">
		
			<ul class="acf-bl list"></ul>
			
		</div>
		
		<div class="values">
		
			<ul class="acf-bl list">
			
				<?php if( !empty($field['value']) ): 
					
					// get posts
					$posts = acf_get_posts(array(
						'post__in' => $field['value'],
					));
					
					
					// set choices
					if( !empty($posts) ):
						
						foreach( array_keys($posts) as $i ):
							
							// vars
							$post = acf_extract_var( $posts, $i );
							
							
							?><li>
								<input type="hidden" name="<?php echo $field['name']; ?>[]" value="<?php echo $post->ID; ?>" />
								<span data-id="<?php echo $post->ID; ?>" class="acf-rel-item">
									<?php echo $this->get_post_title( $post, $field ); ?>
									<a href="#" class="acf-icon small dark" data-name="remove_item"><i class="acf-sprite-remove"></i></a>
								</span>
							</li><?php
							
						endforeach;
						
					endif;
				
				endif; ?>
				
			</ul>
			
		</div>
		
	</div>
	
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
		$field['min'] = empty($field['min']) ? '' : $field['min'];
		$field['max'] = empty($field['max']) ? '' : $field['max'];
		
		
		// post_type
		acf_render_field_setting( $field, array(
			'label'			=> __('Filter by Post Type','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'post_type',
			'choices'		=> acf_get_pretty_post_types(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All post types",'acf'),
		));
		
		
		// taxonomy
		acf_render_field_setting( $field, array(
			'label'			=> __('Filter by Taxonomy','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'choices'		=> acf_get_taxonomy_terms(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All taxonomies",'acf'),
		));
		
		
		// filters
		acf_render_field_setting( $field, array(
			'label'			=> __('Filters','acf'),
			'instructions'	=> '',
			'type'			=> 'checkbox',
			'name'			=> 'filters',
			'choices'		=> array(
				'search'		=> __("Search",'acf'),
				'post_type'		=> __("Post Type",'acf'),
				'taxonomy'		=> __("Taxonomy",'acf'),
			),
		));
		
		
		// filters
		acf_render_field_setting( $field, array(
			'label'			=> __('Elements','acf'),
			'instructions'	=> __('Selected elements will be displayed in each result','acf'),
			'type'			=> 'checkbox',
			'name'			=> 'elements',
			'choices'		=> array(
				'featured_image'	=> __("Featured Image",'acf'),
			),
		));
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum posts','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'min',
		));
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum posts','acf'),
			'instructions'	=> '',
			'type'			=> 'number',
			'name'			=> 'max',
		));
		
		
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Format','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'choices'		=> array(
				'object'		=> __("Post Object",'acf'),
				'id'			=> __("Post ID",'acf'),
			),
			'layout'	=>	'horizontal',
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
		
		
		// force value to array
		$value = acf_force_type_array( $value );
		
		
		// convert to int
		$value = array_map('intval', $value);
		
		
		// load posts if needed
		if( $field['return_format'] == 'object' ) {
			
			// get posts
			$value = acf_get_posts(array(
				'post__in' => $value,
			));
			
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
		
		
		// force value to array
		$value = acf_force_type_array( $value );
		
					
		// array
		foreach( $value as $k => $v ){
		
			// object?
			if( is_object($v) && isset($v->ID) ) {
			
				$value[ $k ] = $v->ID;
				
			}
			
		}
		
		
		// save value as strings, so we can clearly search for them in SQL LIKE statements
		$value = array_map('strval', $value);
		
	
		// return
		return $value;
		
	}
		
}

new acf_field_relationship();

endif;

?>
