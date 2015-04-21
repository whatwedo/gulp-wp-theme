<?php

/*
*  ACF Taxonomy Form Class
*
*  All the logic for adding fields to taxonomy terms
*
*  @class 		acf_form_taxonomy
*  @package		ACF
*  @subpackage	Forms
*/

if( ! class_exists('acf_form_taxonomy') ) :

class acf_form_taxonomy {
	
	var $form = '#addtag';
	
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// actions
		add_action('admin_enqueue_scripts',	array($this, 'admin_enqueue_scripts'));
		
		
		// save
		add_action('create_term',			array($this, 'save_term'), 10, 3);
		add_action('edit_term',				array($this, 'save_term'), 10, 3);
		
		
		// delete
		add_action('delete_term',			array($this, 'delete_term'), 10, 4);
		
	}
	
	
	/*
	*  validate_page
	*
	*  This function will check if the current page is for a post/page edit form
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	n/a
	*  @return	(boolean)
	*/
	
	function validate_page() {
		
		// global
		global $pagenow;
		
		
		// validate page
		if( $pagenow == 'edit-tags.php' ) {
			
			return true;
			
		}
		
		
		// return
		return false;		
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This action is run after post query but before any admin script / head actions. 
	*  It is a good place to register all actions.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @date	26/01/13
	*  @since	3.6.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_enqueue_scripts() {
		
		// validate page
		if( !$this->validate_page() ) {
			
			return;
			
		}
		
		
		// vars
		$screen = get_current_screen();
		$taxonomy = $screen->taxonomy;
		
		
		// load acf scripts
		acf_enqueue_scripts();
		
		
		// actions
		add_action('admin_footer',					array($this, 'admin_footer'), 10, 1);
		add_action("{$taxonomy}_add_form_fields", 	array($this, 'add_term'), 10, 1);
		add_action("{$taxonomy}_edit_form", 		array($this, 'edit_term'), 10, 2);
		
	}
	
	
	/*
	*  add_term
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function add_term( $taxonomy ) {
		
		// vars
		$post_id = "{$taxonomy}_0";
		$args = array(
			'taxonomy' => $taxonomy
		);
		
		
		// update vars
		$this->form = '#addtag';
		
		
		// get field groups
		$field_groups = acf_get_field_groups( $args );
		
		
		// render
		if( !empty($field_groups) ) {
			
			acf_form_data(array( 
				'post_id'	=> $post_id, 
				'nonce'		=> 'taxonomy',
			));
			
			foreach( $field_groups as $field_group ) {
				
				$fields = acf_get_fields( $field_group );

				acf_render_fields( $post_id, $fields, 'div', 'field' );
				
			}
			
		}
		
	}
	
	
	/*
	*  edit_term
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function edit_term( $term, $taxonomy ) {
		
		// vars
		$post_id = "{$taxonomy}_{$term->term_id}";
		$args = array(
			'taxonomy' => $taxonomy
		);
		
		
		// update vars
		$this->form = '#edittag';
		
		
		// get field groups
		$field_groups = acf_get_field_groups( $args );
		
		
		// render
		if( !empty($field_groups) ) {
			
			acf_form_data(array( 
				'post_id'	=> $post_id, 
				'nonce'		=> 'taxonomy' 
			));
			
			foreach( $field_groups as $field_group ) {
				
				$fields = acf_get_fields( $field_group );
				
				?>
				<?php if( $field_group['style'] == 'default' ): ?>
					<h3><?php echo $field_group['title']; ?></h3>
				<?php endif; ?>
				<table class="form-table">
					<tbody>
						<?php acf_render_fields( $post_id, $fields, 'tr', 'field' ); ?>
					</tbody>
				</table>
				<?php 
				
			}
		
		}
		
	}
	
	
	/*
	*  admin_footer
	*
	*  description
	*
	*  @type	function
	*  @date	27/03/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_footer() {
		
?>
<style type="text/css">

<?php echo $this->form; ?> p.submit .spinner {
	vertical-align: top;
	float: none;
	margin-top: 4px;
}

</style>
<script type="text/javascript">
(function($) {
	
	// vars
	var $spinner = $('<?php echo $this->form; ?> p.submit .spinner');
	
	
	// create spinner if not exists (may exist in future WP versions)
	if( !$spinner.exists() ) {
		
		// create spinner
		$spinner = $('<span class="spinner"></span>');
		
		
		// append
		$('<?php echo $this->form; ?> p.submit').append( $spinner );
		
	}
	
	
	// show spinner on submit
	$(document).on('submit', '<?php echo $this->form; ?>', function(){
		
		// show spinner
		$spinner.css('display', 'inline-block');
		
	});
	
	
	// hide spinner after validation
	acf.add_filter('validation_complete', function( json, $form ){
		
		// hide spinner
		$spinner.css('display', 'none');
		
		
		// return
		return json;
				
	});
	
	
<?php if( $this->form == '#addtag' ): ?>

	// store origional HTML
	var $orig = $('#addtag').children('.acf-field').clone();
	
	
	$(document).ready(function(){
		
		// update acf validation class
		acf.validation.error_class = 'form-invalid';
		
		
		// events
		$('#submit').on('click', function( e ){
			
			// bail early if this form does not contain ACF data
			if( ! $('#addtag').find('#acf-form-data').exists() ) {
				
				return true;
			
			}
			
			
			// ignore this submit?
			if( acf.validation.ignore == 1 ) {
			
				acf.validation.ignore = 0;
				return true;
			
			}
			
	
			// bail early if disabled
			if( acf.validation.active == 0 ) {
			
				return true;
			
			}
			
			
			// stop WP JS validation
			e.stopImmediatePropagation();
			
			
			// store submit trigger so it will be clicked if validation is passed
			acf.validation.$trigger = $(this);
			
			
			// show spinner
			$spinner.css('display', 'inline-block');
			
			
			// run validation
			acf.validation.fetch( $('#addtag') );
			
			
			// stop all other click events on this input
			return false;
			
		});
	
	});
	
	$(document).ajaxComplete(function(event, xhr, settings) {
		
		// bail early if is other ajax call
		if( settings.data.indexOf('action=add-tag') == -1 ) {
			
			return;
			
		}
		
		
		// action for 3rd party customization
		acf.do_action('remove', $('#addtag'));
		
		
		// remove old fields
		$('#addtag').find('.acf-field').remove();
		
		
		// add orig fields
		$('#acf-form-data').after( $orig.clone() );
		
		
		// action for 3rd party customization
		acf.do_action('append', $('#addtag'));
		

	});
	
<?php endif; ?>
	
})(jQuery);	
</script>
<?php
		
	}
	
	
	/*
	*  save_term
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function save_term( $term_id, $tt_id, $taxonomy ) {
		
		// verify and remove nonce
		if( ! acf_verify_nonce('taxonomy') ) {
			
			return $term_id;
		
		}
		
		
	    
	    // save data
	    if( acf_validate_save_post(true) ) {
	    
			acf_save_post("{$taxonomy}_{$term_id}");
		
		}
			
	}
	
	
	/*
	*  delete_term
	*
	*  description
	*
	*  @type	function
	*  @date	15/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function delete_term( $term, $tt_id, $taxonomy, $deleted_term ) {
		
		global $wpdb;
		
		$values = $wpdb->query($wpdb->prepare(
			"DELETE FROM $wpdb->options WHERE option_name LIKE %s",
			'%' . $taxonomy . '_' . $term . '%'
		));
		
	}
			
}

new acf_form_taxonomy();

endif;

?>
