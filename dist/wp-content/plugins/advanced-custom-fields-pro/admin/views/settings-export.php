<div class="wrap acf-settings-wrap">
	
	<h2><?php _e('Import / Export', 'acf'); ?></h2>
	
	<div class="acf-box" id="acf-export-field-groups">
		<div class="title">
			<h3><?php _e('Export Field Groups', 'acf'); ?></h3>
		</div>
		<div class="inner">
			<p><?php _e('Select the field groups you would like to export and then select your export method. Use the download button to export to a .json file which you can then import to another ACF installation. Use the generate button to export to PHP code which you can place in your theme.', 'acf'); ?></p>
			
			<form method="post" action="">
			<div class="acf-hidden">
				<input type="hidden" name="_acfnonce" value="<?php echo wp_create_nonce( 'export' ); ?>" />
			</div>
			<table class="form-table">
                <tbody>
                	<tr>
                    	<th>
                    		<label><?php _e('Select Field Groups', 'acf'); ?></label>
                    	</th>
						<td>
							<?php 
							
							// vars
							$choices = array();
							$field_groups = acf_get_field_groups();
							
							
							// populate choices
							if( !empty($field_groups) )
							{
								foreach( $field_groups as $field_group )
								{
									$choices[ $field_group['key'] ] = $field_group['title'];
								}
							}
							
							
							// render field
							acf_render_field(array(
								'type'		=> 'checkbox',
								'name'		=> 'acf_export_keys',
								'prefix'	=> false,
								'value'		=> false,
								'choices'	=> $choices,
							));
							
							?>
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<input type="submit" name="download" class="acf-button blue" value="<?php _e('Download export file', 'acf'); ?>" />
							<input type="submit" name="generate" class="acf-button blue" value="<?php _e('Generate export code', 'acf'); ?>" />
						</td>
					</tr>
				</tbody>
			</table>
			</form>
            
		</div>
		<script type="text/javascript">
		(function($) {
			
			// hide on screen toggle
			var $ul = $('#acf-export-field-groups .acf-checkbox-list'),
				$li = $('<li><label><input type="checkbox" value="" name=""><?php _e("Toggle All", 'acf'); ?></label></li>');
			
			
			// event
			$li.on('change', 'input', function(){
				
				var checked = $(this).is(':checked');
				
				$ul.find('input').attr('checked', checked);
				
			});
			
			
			// add to ul
			$ul.prepend( $li );
						
		})(jQuery);	
		</script>
		
	</div>

	
	<div class="acf-box">
		<div class="title">
			<h3><?php _e('Import Field Groups', 'acf'); ?></h3>
		</div>
		<div class="inner">
			<p><?php _e('Select the Advanced Custom Fields JSON file you would like to import. When you click the import button below, ACF will import the field groups.', 'acf'); ?></p>
			
			<form method="post" action="" enctype="multipart/form-data">
			<div class="acf-hidden">
				<input type="hidden" name="_acfnonce" value="<?php echo wp_create_nonce( 'import' ); ?>" />
			</div>
			<table class="form-table">
                <tbody>
                	<tr>
                    	<th>
                    		<label><?php _e('Select File', 'acf'); ?></label>
                    	</th>
						<td>
							<input type="file" name="acf_import_file">
						</td>
					</tr>
					<tr>
						<th></th>
						<td>
							<input type="submit" class="acf-button blue" value="<?php _e('Import', 'acf'); ?>" />
						</td>
					</tr>
				</tbody>
			</table>
			</form>
			
		</div>
		
		
	</div>
	
</div>
