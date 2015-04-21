<?php 

class acf_media {
	
	
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
		
		// actions
		add_action('acf/save_post', 				array($this, 'save_files'), 5, 1);
		add_action('acf/input/admin_footer_js', 	array($this, 'admin_footer_js'));
		
		// filters
		add_filter('wp_handle_upload_prefilter', 	array($this, 'handle_upload_prefilter'), 10, 1);
		
	}
	
	
	/*
	*  handle_upload_prefilter
	*
	*  description
	*
	*  @type	function
	*  @date	16/02/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function handle_upload_prefilter( $file ) {
		
		// bail early if no acf field
		if( empty($_POST['_acfuploader']) ) {
		
			return $file;
			
		}
		
		
		// load field
		$field = acf_get_field( $_POST['_acfuploader'] );
		
		if( !$field ) {
		
			return $file;
			
		}
		
		
		// vars
		$errors = array();
		
		
		// image
		if( strpos($file['type'], 'image') !== false ) {
			
			// vars
			$size = getimagesize($file['tmp_name']);
			$width = acf_maybe_get($size, 0);
			$height = acf_maybe_get($size, 1);
			
			
			// width
			// * test if width exists to allow .svg to fail gracefully
			if( $width ) {
				
				if( !empty($field['min_width']) && $width < $field['min_width'] ) {
					
					// min width
					$errors['min_width'] = sprintf(__('Image width must be at least %dpx.', 'acf'), $field['min_width'] );
					
				} elseif( !empty($field['max_width']) && $width > $field['max_width'] ) {
					
					// min width
					$errors['max_width'] = sprintf(__('Image width must not exceed %dpx.', 'acf'), $field['max_width'] );
					
				}
				
				
			}
			
			
			
			// height
			if( $height ) {
				
				if( !empty($field['min_height']) && $height < $field['min_height'] ) {
					
					// min height
					$errors['min_height'] = sprintf(__('Image height must be at least %dpx.', 'acf'), $field['min_height'] );
					
				}  elseif( !empty($field['max_height']) && $height > $field['max_height'] ) {
					
					// min height
					$errors['max_height'] = sprintf(__('Image height must not exceed %dpx.', 'acf'), $field['max_height'] );
					
				}
				
			}
			
						
		}
		
		
		// file size
		$filesize = filesize($file['tmp_name']);
		
		if( !empty($field['min_size']) && $filesize < acf_get_filesize($field['min_size']) ) {
				
			// min width
			$errors['min_size'] = sprintf(__('File size must be at least %s.', 'acf'), acf_format_filesize($field['min_size']) );
			
		} elseif( !empty($field['max_size']) && $filesize > acf_get_filesize($field['max_size']) ) {
				
			// min width
			$errors['max_size'] = sprintf(__('File size must must not exceed %s.', 'acf'), acf_format_filesize($field['max_size']) );
			
		}
		
		
		// file type
		if( !empty($field['mime_types']) ) {
			
			$types = str_replace(array(' ', '.'), '', $field['mime_types']);
			$types = explode(',', $types);
			
			
			if( !in_array(pathinfo($file['name'], PATHINFO_EXTENSION), $types) ) {
				
				// glue together last 2 types
				if( count($types) > 1 ) {
					
					$last1 = array_pop($types);
					$last2 = array_pop($types);
					
					$types[] = $last2 . ' ' . __('or', 'acf') . ' ' . $last1;
					
				}
				
				$errors['mime_types'] = sprintf(__('File type must be %s.', 'acf'), implode(', ', $types) );
				
			}
					
		}
		
		
		// filter for 3rd party customization
		$errors = apply_filters("acf/upload_prefilter", $errors, $file, $field);
		$errors = apply_filters("acf/upload_prefilter/type={$field['type']}", $errors, $file, $field );
		$errors = apply_filters("acf/upload_prefilter/name={$field['name']}", $errors, $file, $field );
		$errors = apply_filters("acf/upload_prefilter/key={$field['key']}", $errors, $file, $field );
		
		
		// append error
		if( !empty($errors) ) {
			
			$file['error'] = implode("\n", $errors);
			
		}
		
		
		// return
		return $file;
		
	}

	
	/*
	*  save_files
	*
	*  This function will save the $_FILES data
	*
	*  @type	function
	*  @date	24/10/2014
	*  @since	5.0.9
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function save_files( $post_id = 0 ) {
		
		// bail early if no $_FILES data
		if( empty($_FILES['acf']['name']) ) {
			
			return;
			
		}
		
		
		// upload files
		acf_upload_files();
	
	}
	
	
	/*
	*  admin_footer_js
	*
	*  description
	*
	*  @type	function
	*  @date	19/02/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_footer_js() {
		
		?>acf.media.mime_types = <?php echo json_encode( get_allowed_mime_types() ); ?>;
	<?php
		
	}
}


// initialize
new acf_media();

?>
