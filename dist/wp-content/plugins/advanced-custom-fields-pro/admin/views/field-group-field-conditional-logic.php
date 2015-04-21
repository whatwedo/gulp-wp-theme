<?php 

// vars
$field = acf_extract_var( $args, 'field');
$groups = acf_extract_var( $field, 'conditional_logic');
$disabled = empty($groups) ? 1 : 0;


// UI needs at least 1 conditional logic rule
if( empty($groups) ) {
	
	$groups = array(
		
		// group 0
		array(
			
			// rule 0
			array()
		
		)
		
	);
	
}

?>
<tr data-name="conditional_logic" class="acf-field">
	<td class="acf-label">
		<label><?php _e("Conditional Logic",'acf'); ?></label>
	</td>
	<td class="acf-input">
		<?php 
		
		acf_render_field(array(
			'type'			=> 'radio',
			'name'			=> 'conditional_logic',
			'prefix'		=> $field['prefix'],
			'value'			=> $disabled ? 0 : 1,
			'choices'		=> array(
								1	=> __("Yes",'acf'),
								0	=> __("No",'acf'),
			),
			'layout'		=> 'horizontal',
		));
		
		?>
		<div class="location-groups" <?php if($disabled): ?>style="display:none;"<?php endif; ?>>
			
			<?php foreach( $groups as $group_id => $group ): 
				
				// validate
				if( empty($group) ) {
				
					continue;
					
				}
				
				// $group_id must be completely different to $rule_id to avoid JS issues
				$group_id = "group_{$group_id}";
				
				?>
				<div class="location-group" data-id="<?php echo $group_id; ?>">
				
					<?php if( $group_id == 'group_0' ): ?>
						<h4><?php _e("Show this field if",'acf'); ?></h4>
					<?php else: ?>
						<h4><?php _e("or",'acf'); ?></h4>
					<?php endif; ?>
					
					<table class="acf-table acf-clear-table">
						<tbody>
						<?php foreach( $group as $rule_id => $rule ): 
							
							// valid rule
							$rule = wp_parse_args( $rule, array(
								'field'		=>	'',
								'operator'	=>	'==',
								'value'		=>	'',
							));
							
										
							// $group_id must be completely different to $rule_id to avoid JS issues
							$rule_id = "rule_{$rule_id}";
							$prefix = "{$field['prefix']}[conditional_logic][{$group_id}][{$rule_id}]";
							
							?>
							<tr data-id="<?php echo $rule_id; ?>">
								<td class="param">
									<?php 
									
									$choices = array();
									$choices[ $rule['field'] ] = $rule['field'];
									
									// create field
									acf_render_field(array(
										'type'		=> 'select',
										'prefix'	=> $prefix,
										'name'		=> 'field',
										'value'		=> $rule['field'],
										'choices'	=> $choices,
										'class'		=> 'conditional-logic-field',
										'disabled'	=> $disabled,
									));										
		
									?>
								</td>
								<td class="operator">
									<?php 	
									
									$choices = array(
										'=='	=>	__("is equal to",'acf'),
										'!='	=>	__("is not equal to",'acf'),
									);
									
									
									// create field
									acf_render_field(array(
										'type'		=> 'select',
										'prefix'	=> $prefix,
										'name'		=> 'operator',
										'value'		=> $rule['operator'],
										'choices' 	=> $choices,
										'class'		=> 'conditional-logic-operator',
										'disabled'	=> $disabled,
									)); 	
									
									?>
								</td>
								<td class="value">
									<?php 
									
									$choices = array();
									$choices[ $rule['value'] ] = $rule['value'];
									
									// create field
									acf_render_field(array(
										'type'		=> 'select',
										'prefix'	=> $prefix,
										'name'		=> 'value',
										'value'		=> $rule['value'],
										'choices'	=> $choices,
										'class'		=> 'conditional-logic-value',
										'disabled'	=> $disabled,
									));
									
									?>
								</td>
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
					
				</div>
			<?php endforeach; ?>
			
			<h4><?php _e("or",'acf'); ?></h4>
			
			<a class="acf-button location-add-group" href="#"><?php _e("Add rule group",'acf'); ?></a>
			
		</div>
		
	</td>
</tr>
