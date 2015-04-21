<?php 

class acf_input {
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct() {
		
		add_action('acf/save_post', 							array($this, 'save_post'), 10, 1);
		add_action('acf/input/admin_enqueue_scripts', 			array($this, 'admin_enqueue_scripts'), 0, 0);
		add_action('acf/input/admin_footer', 					array($this, 'admin_footer'), 0, 0);
		
		
		// ajax
		add_action( 'wp_ajax_acf/validate_save_post',			array($this, 'ajax_validate_save_post') );
		add_action( 'wp_ajax_nopriv_acf/validate_save_post',	array($this, 'ajax_validate_save_post') );
	}
	
	
	/*
	*  save_post
	*
	*  This function will save the $_POST data
	*
	*  @type	function
	*  @date	24/10/2014
	*  @since	5.0.9
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function save_post( $post_id = 0 ) {
		
		// save $_POST data
		foreach( $_POST['acf'] as $k => $v ) {
			
			// get field
			$field = acf_get_field( $k );
			
			
			// update field
			if( $field ) {
				
				acf_update_value( $v, $post_id, $field );
				
			}
			
		}
	
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This function will enqueue all the required scripts / styles for ACF
	*
	*  @type	action (acf/input/admin_enqueue_scripts)
	*  @date	6/10/13
	*  @since	5.0.0
	*
	*  @param	n/a	
	*  @return	n/a
	*/
	
	function admin_enqueue_scripts() {

		// scripts
		wp_enqueue_script('acf-input');
		
		
		// styles
		wp_enqueue_style('acf-input');
		
	}
	

	/*
	*  admin_footer
	*
	*  description
	*
	*  @type	function
	*  @date	7/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_footer() {
		
		// vars
		$args = acf_get_setting('form_data');
		
		
		// global
		global $wp_version;
		
		
		// options
		$o = array(
			'post_id'		=> $args['post_id'],
			'nonce'			=> wp_create_nonce( 'acf_nonce' ),
			'admin_url'		=> admin_url(),
			'ajaxurl'		=> admin_url( 'admin-ajax.php' ),
			'ajax'			=> $args['ajax'],
			'validation'	=> $args['validation'],
			'wp_version'	=> $wp_version
		);
		
		
		// l10n
		$l10n = apply_filters( 'acf/input/admin_l10n', array(
			'unload'			=> __('The changes you made will be lost if you navigate away from this page','acf'),
			'expand_details' 	=> __('Expand Details','acf'),
			'collapse_details' 	=> __('Collapse Details','acf')
		));
		
		
?>
<script type="text/javascript">
/* <![CDATA[ */
if( typeof acf !== 'undefined' ) {

	acf.o = <?php echo json_encode($o); ?>;
	acf.l10n = <?php echo json_encode($l10n); ?>;
	<?php do_action('acf/input/admin_footer_js'); ?>
	
}
/* ]]> */
</script>
<?php
		
	}
	
	
	/*
	*  ajax_validate_save_post
	*
	*  This function will validate the $_POST data via AJAX
	*
	*  @type	function
	*  @date	27/10/2014
	*  @since	5.0.9
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function ajax_validate_save_post() {
		
		// validate
		if( !isset($_POST['_acfnonce']) ) {
			
			// ignore validation, this form $_POST was not correctly configured
			die();
			
		}
		
		
		// success
		if( acf_validate_save_post() ) {
			
			$json = array(
				'result'	=> 1,
				'message'	=> __('Validation successful', 'acf'),
				'errors'	=> 0
			);
			
			die( json_encode($json) );
			
		}
		
		
		// fail
		$json = array(
			'result'	=> 0,
			'message'	=> __('Validation failed', 'acf'),
			'errors'	=> acf_get_validation_errors()
		);

		
		// update message
		$i = count( $json['errors'] );
		$json['message'] .= '. ' . sprintf( _n( '1 required field below is empty', '%s required fields below are empty', $i, 'acf' ), $i );
		
		
		// return
		die( json_encode($json) );
		
	}
	
}


// initialize
new acf_input();


/*
*  listener
*
*  This class will call all the neccessary actions during the page load for acf input to function
*
*  @type	class
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

class acf_input_listener {
	
	function __construct() {
		
		// enqueue scripts
		do_action('acf/input/admin_enqueue_scripts');
		
		
		// vars
		$admin_head = 'admin_head';
		$admin_footer = 'admin_footer';
		
		
		// global
		global $pagenow;
		
		
		// determin action hooks
		if( $pagenow == 'customize.php' ) {
			
			$admin_head = 'customize_controls_print_scripts';
			$admin_footer = 'customize_controls_print_footer_scripts';
			
		} elseif( $pagenow == 'wp-login.php' ) { 
		
			$admin_head = 'login_head';
			$admin_footer = 'login_footer';
			
		} elseif( !is_admin() ) {
			
			$admin_head = 'wp_head';
			$admin_footer = 'wp_footer';
			
		}
		
		
		// add actions
		add_action($admin_head, 	array( $this, 'admin_head'), 20 );
		add_action($admin_footer, 	array( $this, 'admin_footer'), 20 );
		
	}
	
	function admin_head() {
		
		do_action('acf/input/admin_head');
	}
	
	function admin_footer() {
		
		do_action('acf/input/admin_footer');
	}
	
}


/*
*  acf_admin_init
*
*  This function is used to setup all actions / functionality for an admin page which will contain ACF inputs
*
*  @type	function
*  @date	6/10/13
*  @since	5.0.0
*
*  @param	n/a
*  @return	n/a
*/

function acf_enqueue_scripts() {
	
	// bail early if acf has already loaded
	if( acf_get_setting('enqueue_scripts') ) {
	
		return;
		
	}
	
	
	// update setting
	acf_update_setting('enqueue_scripts', 1);
	
	
	// add actions
	new acf_input_listener();
}


/*
*  acf_enqueue_uploader
*
*  This function will render a WP WYSIWYG and enqueue media
*
*  @type	function
*  @date	27/10/2014
*  @since	5.0.9
*
*  @param	n/a
*  @return	n/a
*/

function acf_enqueue_uploader() {
	
	// bail early if doing ajax
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		
		return;
		
	}
	
	
	// bail early if acf has already loaded
	if( acf_get_setting('enqueue_uploader', false) ) {
	
		return;
		
	}
	
	
	// update setting
	acf_update_setting('enqueue_uploader', 1);
	
	
	// enqueue media if user can upload
	if( current_user_can( 'upload_files' ) ) {
		
		wp_enqueue_media();
		
	}
	
	
	// create dummy editor
	?><div class="acf-hidden"><?php wp_editor( '', 'acf_content' ); ?></div><?php
	
}


/*
*  acf_form_data
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

function acf_form_data( $args = array() ) {
	
	// make sure scripts and styles have been included
	// case: front end bbPress edit user
	acf_enqueue_scripts();
	
	
	// defaults
	$args = acf_parse_args($args, array(
		'post_id'		=> 0,		// ID of current post
		'nonce'			=> 'post',	// nonce used for $_POST validation
		'validation'	=> 1,		// runs AJAX validation
		'ajax'			=> 0,		// fetches new field groups via AJAX
	));
	
	
	// save form_data for later actions
	acf_update_setting('form_data', $args);
	
	
	// enqueue uploader if page allows AJAX fields to appear
	if( $args['ajax'] ) {
		
		add_action('admin_footer', 'acf_enqueue_uploader', 1);
		//acf_enqueue_uploader();
		
	}
	
	?>
	<div id="acf-form-data" class="acf-hidden">
		<input type="hidden" name="_acfnonce" value="<?php echo wp_create_nonce( $args['nonce'] ); ?>" />
		<input type="hidden" name="_acfchanged" value="0" />
		<?php do_action('acf/input/form_data', $args); ?>
	</div>
	<?php
}


/*
*  acf_save_post
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

function acf_save_post( $post_id = 0 ) {
	
	// bail early if no acf values
	if( empty($_POST['acf']) ) {
		
		return false;
		
	}
	
	
	// hook for 3rd party customization
	do_action('acf/save_post', $post_id);
	
	
	// return
	return true;

}


/*
*  acf_validate_save_post
*
*  This function is run to validate post data
*
*  @type	function
*  @date	25/11/2013
*  @since	5.0.0
*
*  @param	$show_errors (boolean) if true, errors will be shown via a wo_die screen
*  @return	(boolean)
*/

function acf_validate_save_post( $show_errors = false ) {
	
	// validate required fields
	if( !empty($_POST['acf']) ) {
		
		$keys = array_keys($_POST['acf']);
		
		// loop through and save $_POST data
		foreach( $keys as $key ) {
			
			// get field
			$field = acf_get_field( $key );
			
			
			// validate
			acf_validate_value( $_POST['acf'][ $key ], $field, "acf[{$key}]" );
			
		}
		// foreach($fields as $key => $value)
	}
	// if($fields)
	
	
	// hook for 3rd party customization
	do_action('acf/validate_save_post');
	
	
	// check errors
	if( $errors = acf_get_validation_errors() ) {
		
		if( $show_errors ) {
			
			$message = '<h2>Validation failed</h2><ul>';
			
			foreach( $errors as $error ) {
				
				$message .= '<li>' . $error['message'] . '</li>';
				
			}
			
			$message .= '</ul>';
			
			wp_die( $message, 'Validation failed' );
			
		}
		
		return false;
		
	}
	
	
	// return
	return true;
}


/*
*  acf_validate_value
*
*  This function will validate a value for a field
*
*  @type	function
*  @date	27/10/2014
*  @since	5.0.9
*
*  @param	$value (mixed)
*  @param	$field (array)
*  @param	$input (string) name attribute of DOM elmenet
*  @return	(boolean)
*/

function acf_validate_value( $value, $field, $input ) {
	
	// vars
	$valid = true;
	$message = sprintf( __( '%s value is required', 'acf' ), $field['label'] );
	
	
	// valid
	if( $field['required'] ) {
		
		// valid is set to false if the value is empty, but allow 0 as a valid value
		if( empty($value) && !is_numeric($value) ) {
			
			$valid = false;
			
		}
		
	}
	
	
	// filter for 3rd party customization
	$valid = apply_filters( "acf/validate_value", $valid, $value, $field, $input );
	$valid = apply_filters( "acf/validate_value/type={$field['type']}", $valid, $value, $field, $input );
	$valid = apply_filters( "acf/validate_value/name={$field['name']}", $valid, $value, $field, $input );
	$valid = apply_filters( "acf/validate_value/key={$field['key']}", $valid, $value, $field, $input );
	
	
	// allow $valid to be a custom error message
	if( !empty($valid) && is_string($valid) ) {
		
		$message = $valid;
		$valid = false;
		
	}
	
	
	if( !$valid ) {
		
		acf_add_validation_error( $input, $message );
		return false;
		
	}
	
	
	// return
	return true;
	
}


/*
*  acf_add_validation_error
*
*  This function will add an error message for a field
*
*  @type	function
*  @date	25/11/2013
*  @since	5.0.0
*
*  @param	$input (string) name attribute of DOM elmenet
*  @param	$message (string) error message
*  @return	$post_id (int)
*/

function acf_add_validation_error( $input, $message = '' ) {
	
	// instantiate array if empty
	if( empty($GLOBALS['acf_validation_errors']) ) {
		
		$GLOBALS['acf_validation_errors'] = array();
		
	}
	
	
	// add to array
	$GLOBALS['acf_validation_errors'][] = array(
		'input'		=> $input,
		'message'	=> $message
	);
	
}


/*
*  acf_add_validation_error
*
*  This function will return any validation errors
*
*  @type	function
*  @date	25/11/2013
*  @since	5.0.0
*
*  @param	n/a
*  @return	(array|boolean)
*/

function acf_get_validation_errors() {
	
	if( empty($GLOBALS['acf_validation_errors']) ) {
		
		return false;
		
	}
	
	return $GLOBALS['acf_validation_errors'];
	
}

?>
