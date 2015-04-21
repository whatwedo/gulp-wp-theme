<?php

/*
*  ACF Admin Field Groups Class
*
*  All the logic for editing a list of field groups
*
*  @class 		acf_admin_field_groups
*  @package		ACF
*  @subpackage	Admin
*/

if( ! class_exists('acf_admin_field_groups') ) :

class acf_admin_field_groups {
	
	// vars
	var $url = 'edit.php?post_type=acf-field-group',
		$sync = array();
		
	
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
		add_action('current_screen',		array($this, 'current_screen'));
		add_action('trashed_post',			array($this, 'trashed_post'));
		add_action('untrashed_post',		array($this, 'untrashed_post'));
		add_action('deleted_post',			array($this, 'deleted_post'));
		
	}
	
	
	/*
	*  current_screen
	*
	*  This function is fired when loading the admin page before HTML has been rendered.
	*
	*  @type	action (current_screen)
	*  @date	21/07/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function current_screen() {
		
		// validate screen
		if( !acf_is_screen('edit-acf-field-group') ) {
		
			return;
			
		}
		
		
		// check stuff
		$this->check_duplicate();
		$this->check_sync();
		
		
		// actions
		add_action('admin_footer',									array($this, 'admin_footer'));
		
		
		// columns
		add_filter('manage_edit-acf-field-group_columns',			array($this, 'field_group_columns'), 10, 1);
		add_action('manage_acf-field-group_posts_custom_column',	array($this, 'field_group_columns_html'), 10, 2);
		
	}
	
	
	/*
	*  check_duplicate
	*
	*  This function will check for any $_GET data to duplicate
	*
	*  @type	function
	*  @date	17/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function check_duplicate() {
		
		// message
		if( $ids = acf_maybe_get($_GET, 'acfduplicatecomplete') ) {
			
			// explode
			$ids = explode(',', $ids);
			$total = count($ids);
			
			if( $total == 1 ) {
				
				acf_add_admin_notice( sprintf(__('Field group duplicated. %s', 'acf'), '<a href="' . get_edit_post_link($ids[0]) . '">' . get_the_title($ids[0]) . '</a>') );
				
			} else {
				
				acf_add_admin_notice( sprintf(_n( '%s field group duplicated.', '%s field groups duplicated.', $total, 'acf' ), $total) );
				
			}
			
		}
		
		
		// import field group
		if( $id = acf_maybe_get($_GET, 'acfduplicate') ) {
			
			// validate
			check_admin_referer('bulk-posts');
			
			
			// duplicate
			$field_group = acf_duplicate_field_group( $id );
			
			
			// redirect
			wp_redirect( admin_url( $this->url . '&acfduplicatecomplete=' . $field_group['ID'] ) );
			exit;
			
		} elseif( acf_maybe_get($_GET, 'action2') === 'acfduplicate' ) {
		
			// validate
			check_admin_referer('bulk-posts');
				
			
			// get ids
			$ids = acf_maybe_get($_GET, 'post');
			
			if( !empty($ids) ) {
				
				// vars
				$new_ids = array();
				
				foreach( $ids as $id ) {
					
					// duplicate
					$field_group = acf_duplicate_field_group( $id );
					
					
					// increase counter
					$new_ids[] = $field_group['ID'];
					
				}
				
				
				// redirect
				wp_redirect( admin_url( $this->url . '&acfduplicatecomplete=' . implode(',', $new_ids)) );
				exit;
			}
		
		}
		
	}
	
	
	/*
	*  check_sync
	*
	*  This function will check for any $_GET data to sync
	*
	*  @type	function
	*  @date	9/12/2014
	*  @since	5.1.5
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function check_sync() {
		
		// message
		if( $ids = acf_maybe_get($_GET, 'acfsynccomplete') ) {
			
			// explode
			$ids = explode(',', $ids);
			$total = count($ids);
			
			if( $total == 1 ) {
				
				acf_add_admin_notice( sprintf(__('Field group synchronised. %s', 'acf'), '<a href="' . get_edit_post_link($ids[0]) . '">' . get_the_title($ids[0]) . '</a>') );
				
			} else {
				
				acf_add_admin_notice( sprintf(_n( '%s field group synchronised.', '%s field groups synchronised.', $total, 'acf' ), $total) );
				
			}
			
		}
		
		
		// vars
		$groups = acf_get_field_groups();
		
		
		// bail early if no field groups
		if( empty($groups) ) {
			
			return;
			
		}
		
		
		// find JSON field groups which have not yet been imported
		foreach( $groups as $group ) {
			
			// vars
			$local = acf_maybe_get($group, 'local', false);
			$modified = acf_maybe_get($group, 'modified', 0);
			$private = acf_maybe_get($group, 'private', false);
			
			
			// ignore DB / PHP / private field groups
			if( $local !== 'json' || $private ) {
				
				// do nothing
				
			} elseif( !$group['ID'] ) {
				
				$this->sync[ $group['key'] ] = $group;
				
			} elseif( $modified && $modified > get_post_modified_time('U', true, $group['ID'], true) ) {
				
				$this->sync[ $group['key'] ]  = $group;
				
			}
						
		}
		
		
		// bail if no sync needed
		if( empty($this->sync) ) {
			
			return;
			
		}
	
		
		// import field group
		if( $key = acf_maybe_get($_GET, 'acfsync') ) {
			
			// validate
			check_admin_referer('bulk-posts');
			
			
			// append fields
			if( acf_have_local_fields( $key ) ) {
				
				$this->sync[ $key ]['fields'] = acf_get_local_fields( $key );
				
			}
			
			
			// import
			$field_group = acf_import_field_group( $this->sync[ $key ] );
			
			
			// redirect
			wp_redirect( admin_url( $this->url . '&acfsynccomplete=' . $field_group['ID'] ) );
			exit;
			
		} elseif( acf_maybe_get($_GET, 'action2') === 'acfsync' ) {
		
			// validate
			check_admin_referer('bulk-posts');
				
			
			// get ids
			$keys = acf_maybe_get($_GET, 'post');
			
			if( !empty($keys) ) {
				
				// vars
				$new_ids = array();
				
				foreach( $keys as $key ) {
					
					// append fields
					if( acf_have_local_fields( $key ) ) {
						
						$this->sync[ $key ]['fields'] = acf_get_local_fields( $key );
						
					}
					
					
					// import
					$field_group = acf_import_field_group( $this->sync[ $key ] );
										
					
					// append
					$new_ids[] = $field_group['ID'];
					
				}
				
				
				// redirect
				wp_redirect( admin_url( $this->url . '&acfsynccomplete=' . implode(',', $new_ids)) );
				exit;
				
			}
		
		}
		
		
		// filters
		add_filter('views_edit-acf-field-group', array($this, 'list_table_views'));
		
	}
	
	
	/*
	*  list_table_views
	*
	*  This function will add an extra link for JSON in the field group list table
	*
	*  @type	function
	*  @date	3/12/2014
	*  @since	5.1.5
	*
	*  @param	$views (array)
	*  @return	$views
	*/
	
	function list_table_views( $views ) {
		
		// vars
		$class = '';
		$total = count($this->sync);
		
		// active
		if( acf_maybe_get($_GET, 'post_status') === 'sync' ) {
			
			// actions
			add_action('admin_footer', array($this, 'sync_admin_footer'));
			
			
			// set active class
			$class = ' class="current"';
			
			
			// global
			global $wp_list_table;
			
			
			// update pagination
			$wp_list_table->set_pagination_args( array(
				'total_items' => $total,
				'total_pages' => 1,
				'per_page' => $total
			));
			
		}
		
		
		// add view
		$views['json'] = '<a' . $class . ' href="' . admin_url($this->url . '&post_status=sync') . '">' . __('Sync available', 'acf') . ' <span class="count">(' . $total . ')</span></a>';
		
		
		// return
		return $views;
		
	}
	
	
	
	
	
	/*
	*  trashed_post
	*
	*  This function is run when a post object is sent to the trash
	*
	*  @type	action (trashed_post)
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function trashed_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'acf-field-group' ) {
		
			return;
		
		}
		
		
		// trash field group
		acf_trash_field_group( $post_id );
		
	}
	
	
	/*
	*  untrashed_post
	*
	*  This function is run when a post object is restored from the trash
	*
	*  @type	action (untrashed_post)
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function untrashed_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'acf-field-group' ) {
		
			return;
			
		}
		
		
		// trash field group
		acf_untrash_field_group( $post_id );
		
	}
	
	
	/*
	*  deleted_post
	*
	*  This function is run when a post object is deleted from the trash
	*
	*  @type	action (deleted_post)
	*  @date	8/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function deleted_post( $post_id ) {
		
		// validate post type
		if( get_post_type($post_id) != 'acf-field-group' ) {
		
			return;
			
		}
		
		
		// trash field group
		acf_delete_field_group( $post_id );
		
	}
	
	
	/*
	*  field_group_columns
	*
	*  This function will customize the columns for the field group table
	*
	*  @type	filter (manage_edit-acf-field-group_columns)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$columns (array)
	*  @return	$columns (array)
	*/
	
	function field_group_columns( $columns ) {
		
		$columns = array(
			'cb'	 	=> '<input type="checkbox" />',
			'title' 	=> __('Title', 'acf'),
			'fields' 	=> __('Fields', 'acf'),
		);
		
		return $columns;
	}
	
	
	/*
	*  field_group_columns_html
	*
	*  This function will render the HTML for each table cell
	*
	*  @type	action (manage_acf-field-group_posts_custom_column)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	$column (string)
	*  @param	$post_id (int)
	*  @return	n/a
	*/
	
	function field_group_columns_html( $column, $post_id ) {
		
		// vars
		if( $column == 'fields' ) {
		
            echo acf_get_field_count( $post_id );
            
	    }
	    
	}
	
	
	/*
	*  admin_footer
	*
	*  This function will render extra HTML onto the page
	*
	*  @type	action (admin_footer)
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_footer() {
		
		// vars
		$www = 'http://www.advancedcustomfields.com/resources/';
		
?><script type="text/html" id="tmpl-acf-col-side">
<div id="acf-col-side">
	<div class="acf-box">
		<div class="inner">
			<h2><?php echo acf_get_setting('name'); ?> <?php echo acf_get_setting('version'); ?></h2>

			<h3><?php _e("Changelog",'acf'); ?></h3>
			<p><?php _e("See what's new in",'acf'); ?> <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-settings-info&tab=changelog'); ?>"><?php _e("version",'acf'); ?> <?php echo acf_get_setting('version'); ?></a>
			
			<h3><?php _e("Resources",'acf'); ?></h3>
			<ul>
				<li><a href="<?php echo $www; ?>#getting-started" target="_blank"><?php _e("Getting Started",'acf'); ?></a></li>
				<li><a href="<?php echo $www; ?>#updates" target="_blank"><?php _e("Updates",'acf'); ?></a></li>
				<li><a href="<?php echo $www; ?>#field-types" target="_blank"><?php _e("Field Types",'acf'); ?></a></li>
				<li><a href="<?php echo $www; ?>#functions" target="_blank"><?php _e("Functions",'acf'); ?></a></li>
				<li><a href="<?php echo $www; ?>#actions" target="_blank"><?php _e("Actions",'acf'); ?></a></li>
				<li><a href="<?php echo $www; ?>#filters" target="_blank"><?php _e("Filters",'acf'); ?></a></li>
				<li><a href="<?php echo $www; ?>#how-to" target="_blank"><?php _e("'How to' guides",'acf'); ?></a></li>
				<li><a href="<?php echo $www; ?>#tutorials" target="_blank"><?php _e("Tutorials",'acf'); ?></a></li>
			</ul>
		</div>
		<div class="footer footer-blue">
			<ul class="acf-hl">
				<li><?php _e("Created by",'acf'); ?> Elliot Condon</li>
			</ul>
		</div>
	</div>
</div>
</script>
<script type="text/javascript">
(function($){
	
	// wrap
	$('#wpbody .wrap').attr('id', 'acf-field-group-list');
	
	
	// wrap column main
	$('#acf-field-group-list').wrapInner('<div id="acf-col-main" />');
	
	
	// add column side
	$('#acf-field-group-list').prepend( $('#tmpl-acf-col-side').html() );
	
	
	// wrap columns
	$('#acf-field-group-list').wrapInner('<div id="acf-col-wrap" class="acf-clearfix" />');
		
	
	// take out h2 + icon
	$('#acf-col-main > .icon32').insertBefore('#acf-col-wrap');
	$('#acf-col-main > h2').insertBefore('#acf-col-wrap');
	
	
	// modify row actions
	$('#acf-field-group-list .row-actions').each(function(){
		
		// vars
		var id		= $(this).closest('tr').attr('id').replace('post-', ''),
			$span	= $('<span class="acf-duplicate-field-group"><a title="<?php _e('Duplicate this item', 'acf'); ?>" href="<?php echo admin_url($this->url . '&acfduplicate='); ?>' + id + '&_wpnonce=<?php echo wp_create_nonce('bulk-posts'); ?>"><?php _e('Duplicate', 'acf'); ?></a> | </span>');
		
		$(this).find('.inline').replaceWith( $span );

		
	});
	
	
	// modify bulk actions
	$('#bulk-action-selector-bottom option[value="edit"]').attr('value','acfduplicate').text('<?php _e( 'Duplicate', 'acf' ); ?>');
	
})(jQuery);
</script><?php
		
	}
	
	
	/*
	*  sync_admin_footer
	*
	*  This function will render extra HTML onto the page
	*
	*  @type	action (admin_footer)
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function sync_admin_footer() {
		
		// vars
		$i = -1;
?>
<script type="text/html" id="tmpl-acf-json-tbody">
<?php foreach( $this->sync as $group ): $i++; ?>
	<tr <?php if($i%2 == 0): ?>class="alternate"<?php endif; ?>>
		<th class="check-column" scope="row">
			<label for="cb-select-<?php echo $group['key']; ?>" class="screen-reader-text"><?php printf( __( 'Select %s', 'acf' ), $group['title'] ); ?></label>
			<input type="checkbox" value="<?php echo $group['key']; ?>" name="post[]" id="cb-select-<?php echo $group['key']; ?>">
		</th>
		<td class="post-title page-title column-title">
			<strong><?php echo $group['title']; ?></strong>
			<div class="row-actions">
				<span class="import"><a title="<?php echo esc_attr( __('Synchronise field group', 'acf') ); ?>" href="<?php echo admin_url($this->url . '&post_status=sync&acfsync=' . $group['key'] . '&_wpnonce=' . wp_create_nonce('bulk-posts')); ?>"><?php _e( 'Sync', 'acf' ); ?></a></span>
			</div>
		</td>
		<td class="fields column-fields"><?php echo acf_count_local_fields( $group['key'] ); ?></td>
	</tr>
<?php endforeach; ?>
</script>
<script type="text/javascript">
(function($){
	
	// update table HTML
	$('#the-list').html( $('#tmpl-acf-json-tbody').html() );
	
	
	// modify bulk actions
	$('#bulk-action-selector-bottom option[value="acfduplicate"]').attr('value','acfsync').text('<?php _e( 'Sync', 'acf' ); ?>');
	$('#bulk-action-selector-bottom option[value="trash"]').remove();
		
})(jQuery);
</script>
<?php
		
	}
			
}

new acf_admin_field_groups();

endif;

?>
