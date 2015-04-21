<div class="acf-hidden">
	<input type="hidden" name="acf_field_group[key]" value="<?php echo $field_group['key']; ?>" />
</div>
<table class="acf-table">
	<tbody>
		<?php 
		
		// menu_order
		acf_render_field_wrap(array(
			'label'			=> __('Order No.','acf'),
			'instructions'	=> __('Field groups are created in order from lowest to highest','acf'),
			'type'			=> 'number',
			'name'			=> 'menu_order',
			'prefix'		=> 'acf_field_group',
			'value'			=> $field_group['menu_order'],
		), 'tr');
		
		
		// position
		acf_render_field_wrap(array(
			'label'			=> __('Position','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'position',
			'prefix'		=> 'acf_field_group',
			'value'			=> $field_group['position'],
			'choices' 		=> array(
				'acf_after_title'	=> __("High (after title)",'acf'),
				'normal'			=> __("Normal (after content)",'acf'),
				'side' 				=> __("Side",'acf'),
			),
			'default_value'	=> 'normal'
		), 'tr');
		
		
		// style
		acf_render_field_wrap(array(
			'label'			=> __('Style','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'style',
			'prefix'		=> 'acf_field_group',
			'value'			=> $field_group['style'],
			'choices' 		=> array(
				'default'			=>	__("Standard (WP metabox)",'acf'),
				'seamless'			=>	__("Seamless (no metabox)",'acf'),
			)
		), 'tr');
		
		
		// label_placement
		acf_render_field_wrap(array(
			'label'			=> __('Label placement','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'label_placement',
			'prefix'		=> 'acf_field_group',
			'value'			=> $field_group['label_placement'],
			'choices' 		=> array(
				'top'			=>	__("Top aligned",'acf'),
				'left'			=>	__("Left Aligned",'acf'),
			)
		), 'tr');
		
		
		// instruction_placement
		acf_render_field_wrap(array(
			'label'			=> __('Instruction placement','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'instruction_placement',
			'prefix'		=> 'acf_field_group',
			'value'			=> $field_group['instruction_placement'],
			'choices' 		=> array(
				'label'		=>	__("Below labels",'acf'),
				'field'		=>	__("Below fields",'acf'),
			)
		), 'tr');
		
		
		// hide on screen
		acf_render_field_wrap(array(
			'label'			=> __('Hide on screen','acf'),
			'instructions'	=> __('<b>Select</b> items to <b>hide</b> them from the edit screen.','acf') . '<br /><br />' . __("If multiple field groups appear on an edit screen, the first field group's options will be used (the one with the lowest order number)",'acf'),
			'type'			=> 'checkbox',
			'name'			=> 'hide_on_screen',
			'prefix'		=> 'acf_field_group',
			'value'			=> $field_group['hide_on_screen'],
			'choices' => array(
				'permalink'			=>	__("Permalink", 'acf'),
				'the_content'		=>	__("Content Editor",'acf'),
				'excerpt'			=>	__("Excerpt", 'acf'),
				'custom_fields'		=>	__("Custom Fields", 'acf'),
				'discussion'		=>	__("Discussion", 'acf'),
				'comments'			=>	__("Comments", 'acf'),
				'revisions'			=>	__("Revisions", 'acf'),
				'slug'				=>	__("Slug", 'acf'),
				'author'			=>	__("Author", 'acf'),
				'format'			=>	__("Format", 'acf'),
				'page_attributes'	=>	__("Page Attributes", 'acf'),
				'featured_image'	=>	__("Featured Image", 'acf'),
				'categories'		=>	__("Categories", 'acf'),
				'tags'				=>	__("Tags", 'acf'),
				'send-trackbacks'	=>	__("Send Trackbacks", 'acf'),
			)
		), 'tr');
		
		?>
	</tbody>
</table>
