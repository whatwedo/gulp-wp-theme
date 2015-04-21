<table class="acf-table">
	<tbody>
		<tr class="acf-field">
			<td class="acf-label">
				<label><?php _e("Rules",'acf'); ?></label>
				<p><?php _e("Create a set of rules to determine which edit screens will use these advanced custom fields",'acf'); ?></p>
			</td>
			<td class="acf-input">
				<div class="location-groups">
					
					<?php foreach( $field_group['location'] as $group_id => $group ): 
						
						// $group_id must be completely different to $rule_id to avoid JS issues
						$group_id = "group_{$group_id}";
						
						?>
					
						<div class="location-group" data-id="<?php echo $group_id; ?>">
						
							<?php if( $group_id == 'group_0' ): ?>
								<h4><?php _e("Show this field group if",'acf'); ?></h4>
							<?php else: ?>
								<h4><?php _e("or",'acf'); ?></h4>
							<?php endif; ?>
				
							<?php if( is_array($group) ): ?>
							
							<table class="acf-table acf-clear-table">
								<tbody>
									<?php foreach( $group as $rule_id => $rule ): 
																			
										// $group_id must be completely different to $rule_id to avoid JS issues
										$rule_id = "rule_{$rule_id}";
										
										?>
										
										<tr data-id="<?php echo $rule_id; ?>">
										<td class="param"><?php 
											
											$choices = array(
												__("Post",'acf') => array(
													'post_type'		=>	__("Post Type",'acf'),
													'post_status'	=>	__("Post Status",'acf'),
													'post_format'	=>	__("Post Format",'acf'),
													'post_category'	=>	__("Post Category",'acf'),
													'post_taxonomy'	=>	__("Post Taxonomy",'acf'),
													'post'			=>	__("Post",'acf')
												),
												__("Page",'acf') => array(
													'page_template'	=>	__("Page Template",'acf'),
													'page_type'		=>	__("Page Type",'acf'),
													'page_parent'	=>	__("Page Parent",'acf'),
													'page'			=>	__("Page",'acf')
												),
												__("User",'acf') => array(
													'current_user'		=>	__("Current User",'acf'),
													'current_user_role'	=>	__("Current User Role",'acf'),
													'user_form'			=>	__("User Form",'acf'),
													'user_role'			=>	__("User Role",'acf')
												),
												__("Forms",'acf') => array(
													'attachment'	=>	__("Attachment",'acf'),
													'taxonomy'		=>	__("Taxonomy Term",'acf'),
													'comment'		=>	__("Comment",'acf'),
													'widget'		=>	__("Widget",'acf')
												)
											);
													
											
											// allow custom location rules
											$choices = apply_filters( 'acf/location/rule_types', $choices );
											
											
											// create field
											acf_render_field(array(
												'type'		=> 'select',
												'prefix'	=> "acf_field_group[location][{$group_id}][{$rule_id}]",
												'name'		=> 'param',
												'value'		=> $rule['param'],
												'choices'	=> $choices,
											));										
				
				
										?></td>
										<td class="operator"><?php 	
											
											$choices = array(
												'=='	=>	__("is equal to",'acf'),
												'!='	=>	__("is not equal to",'acf'),
											);
											
											
											// allow custom location rules
											$choices = apply_filters( 'acf/location/rule_operators', $choices );
											
											
											// create field
											acf_render_field(array(
												'type'		=> 'select',
												'prefix'	=> "acf_field_group[location][{$group_id}][{$rule_id}]",
												'name'		=> 'operator',
												'value'		=> $rule['operator'],
												'choices' 	=> $choices
											)); 	
											
										?></td>
										<td class="value"><?php 
											
											$this->render_location_value(array(
												'group_id'	=> $group_id,
												'rule_id'	=> $rule_id,
												'value'		=> $rule['value'],
												'param'		=> $rule['param'],
											)); 
											
										?></td>
										<td class="add">
											<a href="#" class="acf-button location-add-rule"><?php _e("and",'acf'); ?></a>
										</td>
										<td class="remove">
											<a href="#" class="acf-icon location-remove-rule"><i class="acf-sprite-remove"></i></a>
										</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					
					<h4><?php _e("or",'acf'); ?></h4>
					
					<a class="acf-button location-add-group" href="#"><?php _e("Add rule group",'acf'); ?></a>
					
				</div>
			</td>
		</tr>
	</tbody>
</table>
