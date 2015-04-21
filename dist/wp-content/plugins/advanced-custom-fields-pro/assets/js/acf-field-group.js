(function($){
	
	acf.field_group = {
		
		$fields: null,
		$locations: null,
		$options: null,
		
		fields: {},
		conditions: {},
		locations: {},
		options: {},
		
		
		focus: function( $el ){
			
			return $el.closest('.acf-field-object');
			
		},
		
		
		/*
		*  init
		*
		*  This function will run on document ready and initialize the module
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		init : function(){
			
			// reference
			var self = this;
			
			
			// $el
			this.$fields = $('#acf-field-group-fields');
			this.$locations = $('#acf-field-group-locations');
			this.$options = $('#acf-field-group-options');
			
			
			// update classes
			this.$fields.addClass('acf-postbox seamless');
			this.$locations.addClass('acf-postbox');
			this.$options.addClass('acf-postbox');
		
			
			// sortable
			this.sort_fields( $('.acf-field-list:first') );
			
			
			// events
			$(document).on('submit', '#post', function(){
				
				return self.submit();
				
			});
			
			$(document).on('click', '#submitdiv .submitdelete', function(){
					
				return self.trash();
				
			});
			
			$(document).on('change', '#adv-settings input[name="show_field_keys"]', function(){
				
				self.toggle_field_keys( $(this).val() );
				
			});
			
			
			// field events
			this.$fields.on('click', '.edit-field', function( e ){
				
				e.preventDefault();
				
				self.edit_field( self.focus( $(this) ) );
				
			});
			
			this.$fields.on('click', '.duplicate-field', function( e ){
				
				e.preventDefault();
				
				self.duplicate_field( self.focus( $(this) ) );
				
			});
			
			this.$fields.on('click', '.move-field', function( e ){
				
				e.preventDefault();
				
				self.move_field( self.focus( $(this) ) );
				
			});
			
			this.$fields.on('click', '.delete-field', function( e ){
				
				e.preventDefault();
				
				self.delete_field( self.focus( $(this) ) );
				
			});
			
			this.$fields.on('click', '.acf-add-field', function( e ){
				
				e.preventDefault();
				
				self.add_field( $(this).closest('.acf-field-list-wrap').children('.acf-field-list') );
				
			});
			
			this.$fields.on('change', 'tr[data-name="type"] select', function(){
				
				self.change_field_type( $(this) );
				
			});
			
			this.$fields.on('blur', 'tr[data-name="label"] input', function( e ){
				
				self.change_field_label( self.focus( $(this) ) );
				
			});
			
			this.$fields.on('blur', 'tr[data-name="name"] input', function( e ){
				
				self.change_field_name( self.focus( $(this) ) );
				
			});
			
			this.$fields.on('keyup', 'tr[data-name="label"] input, tr[data-name="name"] input', function( e ){
				
				self.render_field( self.focus( $(this) ) );
				
			});
			
			this.$fields.on('change', 'input, textarea, select', function( e ){
				
				self.save_field( self.focus( $(this) ) );
				
			});
			
			
			// filter for new_field
			acf.add_filter('get_fields', function( $fields ){
			 	
			 	$fields = $fields.not('.acf-field-object[data-key="acfcloneindex"] .acf-field');
			 	
				
				// return
				return $fields;
			    
		    }, 99);
		    
			
			// modules
			this.conditions.init();
			this.locations.init();
			this.options.init();
			
			
			// render
			this.render();
		},
		
		
		/*
		*  render
		*
		*  description
		*
		*  @type	function
		*  @date	15/07/2014
		*  @since	5.0.0
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		render : function(){
			
			// vars
			var options = acf.serialize_form( $('#adv-settings') );
			
			
			// convert types
			options.show_field_keys = parseInt(options.show_field_keys);
			
			
			// show field keys	
			if( options.show_field_keys ) {
			
				this.$fields.addClass('show-field-keys');
			
			} else {
				
				this.$fields.removeClass('show-field-keys');
				
			}
			
		},
		
		
		/*
		*  toggle_field_keys
		*
		*  description
		*
		*  @type	function
		*  @date	15/07/2014
		*  @since	5.0.0
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		toggle_field_keys : function( val ){
			
			// vars
			val = parseInt(val);
			
			
			// update user setting
			acf.update_user_setting('show_field_keys', val);
			
			
			// toggle class
			if( val ) {
			
				this.$fields.addClass('show-field-keys');
			
			} else {
				
				this.$fields.removeClass('show-field-keys');
				
			}
			
		},
		
		
		/*
		*  get_field_meta
		*
		*  This function will return an input value for a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	name
		*  @return	(string)
		*/
		
		get_field_meta : function( $el, name ){
		
			// vars
	    	var $input = $el.children('.meta').children('.input-' + name);
	    	
	    	
	    	// return
			if( $input.exists() ) {
			
				return $input.val();
				
			}
			
			
			// return
			return false;
			
		},
		
		
		/*
		*  update_field_meta
		*
		*  This function will update an input value for a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	name
		*  @param	value
		*  @return	n/a
		*/
		
		update_field_meta : function( $el, name, value ){
			
			//console.log( 'update_field_meta(%o, %o, %o)', $el, name, value );
			// vars
	    	var $input = $el.children('.meta').children('.input-' + name);
	    	
	    	
	    	// create hidden input if doesn't exist
			if( !$input.exists() ) {
				
				var html = $el.children('.meta').children('.input-ID').outerHTML().replace(/ID/g, name);
				
				
				// update $input
				$input = $(html);
				
				
				// reset value
				$input.val( value );
				
				
				// append
				$el.children('.meta').append( $input );
				
			}
			
			
			// bail early if no change
			if( $input.val() == value ) {
				
				//console.log('update_field_meta: no value change %o', $input);
				return;
			}
			
			
			// update value
			$input.val( value );
			
			
			// bail early if updating save
			if( name == 'save' ) {
				
				//console.log('update_field_meta: name = save %o', $input);
				return;
				
			}
			
			
			// meta has changed, update save
			this.save_field( $el, 'meta' );
			
		},
		
		
		/*
		*  delete_field_meta
		*
		*  This function will return an input value for a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	name
		*  @return	(string)
		*/
		
		delete_field_meta : function( $el, name ){
		
			// vars
	    	var $input = $el.children('.meta').children('.input-' + name);
	    	
	    	
	    	// return
			if( $input.exists() ) {
			
				$input.remove();
				
			}
			
			
			// meta has changed, update save
			this.save_field( $el, 'meta' );
			
		},
		
		
		/*
		*  save_field
		*
		*  This function will update the changed input for a given field making sure it is saved on submit
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		save_field : function( $el, type ){
			
			//console.log('save_field: %o %o', $el, type);
			
			// defaults
			type = type || 'settings';
			
			
			// vars
			var value = this.get_field_meta( $el, 'save' );
			
			
			// bail early if already 'settings'
			if( value == 'settings' ) {
				
				return;
				
			}
			
			
			// bail early if no change
			if( value == type ) {
				
				return;
				
			}
			
			
			// update meta
			this.update_field_meta( $el, 'save', type );
			
			
			// action for 3rd party customization
			acf.do_action('save_field', $el, type);
			
		},
		
		
		/*
		*  submit
		*
		*  This function is triggered when submitting the form and provides validation prior to posting the data
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	(boolean)
		*/
		
		submit : function(){
			
			// reference
			var self = this;
			
			
			// vars
			var $title = $('#titlewrap #title');
			
			
			// title empty
			if( !$title.val() ) {
				
				// hide ajax stuff on submit button
				if( $('#submitdiv').exists() ) {
					
					// remove disabled classes
					$('#submitdiv').find('.disabled').removeClass('disabled');
					$('#submitdiv').find('.button-disabled').removeClass('button-disabled');
					$('#submitdiv').find('.button-primary-disabled').removeClass('button-primary-disabled');
					
					
					// remove spinner
					$('#submitdiv .spinner').hide();
					
				}
				
				
				// alert
				alert( acf._e('title_is_required') );
				
				
				// focus
				$title.focus();
				
				
				// return
				return false;
			}
			
			
			// close / delete fields
			self.$fields.find('.acf-field-object').each(function(){
				
				// vars
				var save = self.get_field_meta( $(this), 'save'),
					ID = self.get_field_meta( $(this), 'ID'),
					open = $(this).hasClass('open');
				
				
				// clone
				if( ID == 'acfcloneindex' ) {
					
					$(this).remove();
					return;
					
				}
				
				
				// close
				if( open ) {
					
					self.close_field( $(this) );
					
				}
				
				
				// remove unnecessary inputs
				if( save == 'settings' ) {
					
					// allow all settings to save (new field, changed field)
					
				} else if( save == 'meta' ) {
					
					$(this).children('.settings').find('[name^="acf_fields[' + ID + ']"]').remove();
					
				} else {
					
					$(this).find('[name^="acf_fields[' + ID + ']"]').remove();
					
				}
				
			});
			
			
			// return
			return true;
		},
		
		
		/*
		*  trash
		*
		*  This function is triggered when moving the field group to trash
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	(boolean)
		*/
		
		trash : function(){
			
			return confirm( acf._e('move_to_trash') );
			
		},
		
		
		/*
		*  sort_fields
		*
		*  This function will add sortable to a field list
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		sort_fields : function( $el ){
			
			$el.sortable({
				connectWith: '.acf-field-list',
				update: function(event, ui){
					
					// vars
					var $el = ui.item;
					
					
					// render
					acf.field_group.render_fields();
					
					
					// actions
					acf.do_action('sortstop', $el);
					
				},
				handle: '.acf-icon'
			});
			
		},
		
		
		/*
		*  render_fields
		*
		*  This function is triggered by a change in field order, and will update the field icon number
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		render_fields : function(){
			
			// reference
			var self = this;
			
			
			// update order numbers
			this.$fields.find('.acf-field-list').each(function(){
				
				// vars
				var $fields = $(this).children('.acf-field-object').not('[data-id="acfcloneindex"]');
				
				
				// loop over fields
				$fields.each(function( i ){
					
					// update meta
					self.update_field_meta( $(this), 'menu_order', i );
					
					
					// update icon number
					$(this).children('.handle').find('.acf-icon').html( i+1 );
					
				});
				
				
				// show no fields message
				if( ! $fields.exists() ){
					
					$(this).children('.no-fields-message').show();
					
				} else {
					
					$(this).children('.no-fields-message').hide();
					
				}
				
			});
			
		},
		
		
		/*
		*  render_field
		*
		*  This function will update the field's info
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		render_field : function( $el ){
			
			// vars
			var label = $el.find('tr[data-name="label"]:first input').val(),
				name = $el.find('tr[data-name="name"]:first input').val(),
				type = $el.find('tr[data-name="type"]:first select option:selected').text();
			
			
			// update label
			$el.find('> .handle .li-field-label strong a').text( label );
			
			
			// update name
			$el.find('> .handle .li-field-name').text( name );
			
			
			// update type
			$el.find('> .handle .li-field-type').text( type );
			
		},
		
		
		/*
		*  edit_field
		*
		*  This function is triggered when clicking on a field. It will open / close a fields settings
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		edit_field : function( $el ){
			
			if( $el.hasClass('open') ) {
			
				this.close_field( $el );
				
			} else {
			
				this.open_field( $el );
				
			}
			
		},
		
		
		/*
		*  open_field
		*
		*  This function will open a fields settings
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		open_field : function( $el ){
			
			// bail early if already open
			if( $el.hasClass('open') ) {
			
				return false;
				
			}
			
			
			// add class
			$el.addClass('open');
			
			
			// action for 3rd party customization
			acf.do_action('open_field', $el);
			
			
			// animate toggle
			$el.children('.settings').animate({ 'height' : 'toggle' }, 250 );
			
		},
		
		
		/*
		*  close_field
		*
		*  This function will open a fields settings
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		close_field : function( $el ){
			
			// bail early if already closed
			if( !$el.hasClass('open') ) {
			
				return false;
				
			}
			
			
			// remove class
			$el.removeClass('open');
			
			
			// action for 3rd party customization
			acf.do_action('close_field', $el);
			
			
			// animate toggle
			$el.children('.settings').animate({ 'height' : 'toggle' }, 250 );
			
		},
		
		
		/*
		*  wipe_field
		*
		*  This function will prepare a new field by updating the input names
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		wipe_field : function( $el ){
			
			// vars
			var old_id = $el.attr('data-id'), // use data attr for better compatibility with new fields
				old_key = $el.attr('data-key'),
				new_id = acf.get_uniqid('field_');
			
			
			// give field a new id
			$el.attr('data-orig', old_key);
			$el.attr('data-key', new_id);
			$el.attr('data-id', new_id);
			
			
			// update hidden inputs
			this.update_field_meta( $el, 'ID', '' );
			this.update_field_meta( $el, 'key', new_id );
			
			
			// update attributes
			$el.find('[id*="' + old_id + '"]').each(function(){	
			
				$(this).attr('id', $(this).attr('id').replace(old_id, new_id) );
				
			});
			
			$el.find('[name*="' + old_id + '"]').each(function(){	
			
				$(this).attr('name', $(this).attr('name').replace(old_id, new_id) );
				
			});
			
			
			// update key
			$el.find('> .handle .pre-field-key').text( new_id );
			
			
			// remove sortable classes
			$el.find('.ui-sortable').removeClass('ui-sortable');
			
			
			// action for 3rd party customization
			acf.do_action('wipe_field', $el);
			
		},
		
		
		/*
		*  duplicate_field
		*
		*  This function will duplicate a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$field
		*  @return	n/a
		*/
		
		duplicate_field : function( $field ){
			
			// allow acf to modify DOM
			acf.do_action('before_duplicate', $field);
			
			
			// vars
			var $el = $field.clone(),
				$field_list	= $field.closest('.acf-field-list');
			
			
			// remove JS functionality
			acf.do_action('remove', $el);
			
			
			// update names
			this.wipe_field( $el );
			
			
			// append to table
			$field.after( $el );
			
			
			// allow acf to modify DOM
			acf.do_action('after_duplicate', $field, $el);
			
			
			// focus after form has dropped down
			setTimeout(function(){
			
	        	$el.find('tr[data-name="label"]:first input').focus();
	        	
	        }, 251);
	        
			
			// update order numbers
			this.render_fields();
			
			
			// trigger append
			acf.do_action('append', $el);
			
			
			// open up form
			if( $field.hasClass('open') ) {
			
				this.close_field( $field );
				
			} else {
			
				this.open_field( $el );
				
			}
			
			
			// update new_field label / name
			var $label = $el.find('tr[data-name="label"]:first input'),
				$name = $el.find('tr[data-name="name"]:first input');
					
			
			$label.val( $label.val() + ' (' + acf._e('copy') + ')' );
			$name.val( $name.val() + '_' + acf._e('copy') );
			
			
			// save field
			this.save_field( $el );
			
			
			// render field
			this.render_field( $el );
			
			
			// action for 3rd party customization
			acf.do_action('duplicate_field', $el);
		},
		
		
		/*
		*  move_field
		*
		*  This function will launch a popup to move a field to another field group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$field
		*  @return	n/a
		*/
		
		move_field : function( $field ){
			
			// reference
			var self = this;
			
			
			// AJAX data
			var ajax_data = acf.prepare_for_ajax({
				'action'	: 'acf/field_group/move_field',
				'field_id'	: $field.data('id')
			});
			
			
			// vars
			var warning = false;



			// validate
			if( !ajax_data.field_id ) {
				
				// Case: field not saved to DB
				warning = true;
				
			} else if( this.get_field_meta( $field, 'save' ) == 'settings' ) {
				
				// Case: field's settings have changed
				warning = true;
				
			} else {
				
				// Case: sub field's settings have changed
				$field.find('.acf-field-object').not('[data-id="acfcloneindex"]').each(function(){
					
					if( !$(this).data('id') ) {
						
						// Case: field not saved to DB
						warning = true;
						return false;
						
					} else if( self.get_field_meta( $(this), 'save' ) == 'settings' ) {
						
						// Case: field's settings have changed
						warning = true;
						
					}
					
				});
				
			}
			
			
			if( warning ) {
				
				alert( acf._e('move_field_warning') );
				return;
				
			}
			
			
			// open popup
			acf.open_popup({
				title	: acf._e('move_field'),
				loading	: true,
				height	: 220
			});
			
			
			// get HTML
			$.ajax({
				url: acf.get('ajaxurl'),
				data: ajax_data,
				type: 'post',
				dataType: 'html',
				success: function(html){
				
					self.move_field_confirm( $field, html );
					
				}
			});
			
		},
		
		
		/*
		*  move_field_confirm
		*
		*  This function will move a field to another field group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		move_field_confirm : function( $field, html ){
			
			// reference
			var self = this;
			
			
			// update popup
			acf.update_popup({
				content : html
			});
			
			
			// AJAX data
			var ajax_data = {
				'action'			: 'acf/field_group/move_field',
				'nonce'				: acf.get('nonce'),
				'field_id'			: this.get_field_meta($field, 'ID'),
				'field_group_id'	: 0
			};
			
			
			// submit form
			$('#acf-move-field-form').on('submit', function(){

				ajax_data.field_group_id = $(this).find('select').val();
				
				
				// get HTML
				$.ajax({
					url: acf.get('ajaxurl'),
					data: ajax_data,
					type: 'post',
					dataType: 'html',
					success: function(html){
					
						acf.update_popup({
							content : html
						});
						
						
						// remove the field without actually deleting it
						self.remove_field( $field );
						
					}
				});
				
				return false;
				
			});
			
		},
		
		
		/*
		*  delete_field
		*
		*  This function will delete a field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @param	animation
		*  @return	n/a
		*/
		
		delete_field : function( $el, animation ){
			
			// defaults
			animation = animation || true;
			
			
			// vars
			var id = this.get_field_meta($el, 'ID');
			
			
			// bail early if cloneindex
			if( id == 'acfcloneindex' ) {
				
				return;
				
			}
			
			
			// add to remove list
			if( id ) {
			
				$('#input-delete-fields').val( $('#input-delete-fields').val() + '|' + id );	
				
			}
			
			
			// action for 3rd party customization
			acf.do_action('delete_field', $el);
			
			
			// bail early if no animation
			if( animation ) {
				
				this.remove_field( $el );
				
			}
						
		},
		
		
		/*
		*  remove_field
		*
		*  This function will visualy remove a field
		*
		*  @type	function
		*  @date	24/10/2014
		*  @since	5.0.9
		*
		*  @param	$el
		*  @param	animation
		*  @return	n/a
		*/
		
		remove_field : function( $el ){
			
			// reference
			var self = this;
			
			
			// vars
			var $field_list	= $el.closest('.acf-field-list');
			
			
			// set layout
			$el.css({
				height		: $el.height(),
				width		: $el.width(),
				position	: 'absolute'
			});
			
			
			// wrap field
			$el.wrap( '<div class="temp-field-wrap" style="height:' + $el.height() + 'px"></div>' );
			
			
			// fade $el
			$el.animate({ opacity : 0 }, 250);
			
			
			// close field
			var end_height = 0,
				$show = false;
			
			
			if( $field_list.children('.acf-field-object').length == 1 ) {
			
				$show = $field_list.children('.no-fields-message');
				end_height = $show.outerHeight();
				
			}
			
			$el.parent('.temp-field-wrap').animate({ height : end_height }, 250, function(){
				
				// show another element
				if( $show ) {
				
					$show.show();
					
				}
				
				
				// action for 3rd party customization 
				acf.do_action('remove', $(this));
				
				
				// remove $el
				$(this).remove();
				
				
				// render fields becuase they have changed
				self.render_fields();
				
			});
						
		},
		
		
		/*
		*  add_field
		*
		*  This function will add a new field to a field list
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$field_list
		*  @return	n/a
		*/
		
		add_field : function( $field_list ){
			
			// clone tr
			var $clone = $field_list.children('.acf-field-object[data-key="acfcloneindex"]'),
				$el = $clone.clone();
			
			
			// update names
			this.wipe_field( $el );
			
			
			// show
			$el.show();
			
			
			// append to table
			$clone.before( $el );
			
			
			// remove no fields message
			$field_list.children('.no-fields-message').hide();
			
			
			// clear name
			$el.find('.settings input[type="text"]').val('');
			
			
			// focus after form has dropped down
			// - this prevents a strange rendering bug in Firefox
			setTimeout(function(){
			
	        	$el.find('input[type="text"]:first').focus();
	        	
	        }, 251);
			
			
			// update order numbers
			this.render_fields();
			
			
			// trigger append
			acf.do_action('append', $el);
			
			
			// open up form
			this.edit_field( $el );
			
			
			// action for 3rd party customization
			acf.do_action('add_field', $el);
			
		},
		
		
		/*
		*  change_field_type
		*
		*  This function will update the field's settings based on the new field type
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$select
		*  @return	n/a
		*/
		
		change_field_type : function( $select ){
			
			// vars
			var $tbody		= $select.closest('tbody'),
				$el			= $tbody.closest('.acf-field-object'),
				$parent		= $el.parent().closest('.acf-field-object'),
				
				key			= $el.attr('data-key'),
				old_type	= $el.attr('data-type'),
				new_type	= $select.val();
				
			
			// update class
			$el.removeClass('acf-field-object-' + old_type.replace('_', '-'));
			$el.addClass('acf-field-object-' + new_type.replace('_', '-'));
			
			
			// update atts
			$el.attr('data-type', new_type);
			
			
			// abort XHR if this field is already loading AJAX data
			if( $el.data('xhr') ) {
			
				$el.data('xhr').abort();
				
			}
			
			
			// get settings
			var $settings = $tbody.children('tr[data-setting="' + old_type + '"]'),
				html = '';
			
			
			// populate settings html
			$settings.each(function(){
				
				html += $(this).outerHTML();
				
			});
			
			
			// remove settings
			$settings.remove();
			
			
			// save field settings html
			acf.update( key + '_settings_' + old_type, html );
			
			
			// render field
			this.render_field( $el );
			
			
			// show field options if they already exist
			html = acf.get( key + '_settings_' + new_type );
			
			if( html ) {
				
				// append settings
				$tbody.children('.acf-field[data-name="conditional_logic"]').before( html );
				
				
				// remove field settings html
				acf.update( key + '_settings_' + new_type, '' );
				
				
				// trigger event
				acf.do_action('change_field_type', $el);
				
				
				// return
				return;
			}
			
			
			// add loading
			var $tr = $('<tr class="acf-field"><td class="acf-label"></td><td class="acf-input"><div class="acf-loading"></div></td></tr>');
			
			
			// add $tr
			$tbody.children('.acf-field[data-name="conditional_logic"]').before( $tr );
			
			
			var ajax_data = {
				action		: 'acf/field_group/render_field_settings',
				nonce		: acf.o.nonce,
				parent		: acf.o.post_id,
				field_group	: acf.o.post_id,
				prefix		: $select.attr('name').replace('[type]', ''),
				type		: new_type,
			};
			
			
			// parent
			if( $parent.exists() ) {
				
				ajax_data.parent = this.get_field_meta( $parent, 'ID' );
				
			}
			
			
			// ajax
			var xhr = $.ajax({
				url: acf.o.ajaxurl,
				data: ajax_data,
				type: 'post',
				dataType: 'html',
				success: function( html ){
					
					// bail early if no html
					if( !html ) {
					
						return;
						
					}
					
					
					// vars
					var $new_tr = $(html);
					
					
					// replace
					$tr.after( $new_tr );
					
					
					// trigger event
					acf.do_action('append', $new_tr);
					acf.do_action('change_field_type', $el);

					
				},
				complete : function(){
					
					// this function will also be triggered by $el.data('xhr').abort();
					$tr.remove();
					
				}
			});
			
			
			// update el data
			$el.data('xhr', xhr);
			
		},
		
		/*
		*  change_field_label
		*
		*  This function is triggered when changing the field's label
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		change_field_label : function( $el ) {
			
			// vars
			var $label = $el.find('tr[data-name="label"]:first input'),
				$name = $el.find('tr[data-name="name"]:first input'),
				type = $el.attr('data-type');
				
			
			if( $name.val() == '' ) {
				
				// thanks to https://gist.github.com/richardsweeney/5317392 for this code!
				var val = $label.val(),
					replace = {
						'ä': 'a',
						'æ': 'a',
						'å': 'a',
						'ö': 'o',
						'ø': 'o',
						'é': 'e',
						'ë': 'e',
						'ü': 'u',
						'ó': 'o',
						'ő': 'o',
						'ú': 'u',
						'é': 'e',
						'á': 'a',
						'ű': 'u',
						'í': 'i',
						' ' : '_',
						'\'' : '',
						'\\?' : ''
					};
				
				$.each( replace, function(k, v){
					var regex = new RegExp( k, 'g' );
					val = val.replace( regex, v );
				});
				
				
				val = val.toLowerCase();
				$name.val( val ).trigger('change');
			}
			
			
			// render field
			this.render_field( $el );
			
			
			// action for 3rd party customization
			acf.do_action('change_field_label', $el);
			
		},
		
		/*
		*  change_field_name
		*
		*  This function is triggered when changing the field's name
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		change_field_name : function( $el ) {
			
			// vars
			var $name = $el.find('tr[data-name="name"]:first input');
			
			if( $name.val().substr(0, 6) === 'field_' ) {
				
				alert( acf._e('field_name_start') );
				
				setTimeout(function(){
					
					$name.focus();
					
				}, 1);
				
			}
			
		}
		
	};
	
	acf.field_group.conditions = {
		
		$el : null,
		
		
		/*
		*  init
		*
		*  This function will run on document ready and initialize the module
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		init : function(){
			
			// vars
			this.$el = acf.field_group.$fields;
			
			
			// reference
			var self = this;
			
			
			// events
			acf.add_action('open_field', function($field){
				
				// render conditions for this field
				self.render( $field );
			
			});
			
			acf.add_action('change_field_label change_field_type', function( $el ){
				
				// render conditions for all open fields
				self.$el.find('.acf-field-object.open').each(function(){
					
					self.render( $(this) );
					
				});
				
			});
			
			self.$el.on('change', 'tr[data-name="conditional_logic"] input[type="radio"]', function( e ){
				
				e.preventDefault();
				
				self.change_toggle( $(this) );
				
			});
	
			self.$el.on('change', '.conditional-logic-field', function( e ){
				
				e.preventDefault();
				
				self.change_trigger( $(this) );
				
			});
			
			
			// add rule
			self.$el.on('click', '.location-add-rule', function( e ){
				
				e.preventDefault();
				
				self.add_rule( $(this).closest('tr') );
								
			});
			
			
			// remove rule
			self.$el.on('click', '.location-remove-rule', function( e ){
					
				e.preventDefault();
						
				self.remove_rule( $(this).closest('tr') );
								
			});
			
			
			// add group
			self.$el.on('click', '.location-add-group', function( e ){
				
				e.preventDefault();
							
				self.add_group( $(this).closest('.location-groups') );
								
			});
			
		},
		
		/*
		*  update_select
		*
		*  This function will update a select field with new choices
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$select
		*  @param	choices
		*  @return	n/a
		*/
		
		update_select : function( $select, choices ){
			
			// default choices
			if( !choices || choices.length == 0 ) {
				
				choices = [{
					'value' : '',
					'label' : ''
				}];
				
			}
			
			
			// vars
			var value = $select.val();
			
			
			// clear choices
			$select.html('');
			
			
			// populate choices
			$.each(choices, function( k, v ){
				
				var $optgroup = $select;
				
				if( v.group )
				{
					$optgroup = $select.find('optgroup[label="' + v.group + '"]');
					
					if( ! $optgroup.exists() )
					{
						$optgroup = $('<optgroup label="' + v.group + '"></optgroup>');
						
						$select.append( $optgroup );
					}
				}
				
				
				// append select
				$optgroup.append( '<option value="' + v.value + '">' + v.label + '</option>' );
			});
			
			
			// reset val
			if( $select.find('option[value="' + value + '"]').exists() ) {
			
				$select.val( value );
				
			}
			
		},
		
		
		/*
		*  render
		*
		*  This function will render the conditional logic fields for a given field
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$field
		*  @return	n/a
		*/
		
		render : function( $field ){
			
			// reference
			var self = this;
			
			
			// vars
			var choices		= [],
				key			= $field.attr('data-key'),
				$ancestors	= $field.parents('.acf-field-list'),
				$tr			= $field.find('> .settings > table > tbody > tr[data-name="conditional_logic"]');
				
			
			$.each( $ancestors, function( i ){
				
				var group = (i == 0) ? acf.l10n.sibling_fields : acf.l10n.parent_fields;
				
				$(this).children('.acf-field-object').each(function(){
					
					
					// vars
					var $this_field	= $(this),
						this_key	= $this_field.attr('data-key'),
						this_type	= $this_field.attr('data-type'),
						this_label	= $this_field.find('> .settings > table > tbody > tr[data-name="label"] input').val();
					
					
					// validate
					if( this_key == 'acfcloneindex' ) {
						
						return;
						
					} else if( this_key == key ) {
						
						return;
						
					}
										
					
					// add this field to available triggers
					if( this_type == 'select' || this_type == 'checkbox' || this_type == 'true_false' || this_type == 'radio' )
					{
						choices.push({
							value	: this_key,
							label	: this_label,
							group	: group
						});
					}
					
					
				});
				
			});
				
			
			// empty?
			if( choices.length == 0 ) {
				
				choices.push({
					'value' : '',
					'label' : acf.l10n.no_fields
				});
				
			}
			
			
			// create select fields
			$tr.find('.conditional-logic-field').each(function(){
				
				self.update_select( $(this), choices );
				
				self.change_trigger( $(this) );
				
			});
			
		},
		
		
		/*
		*  change_toggle
		*
		*  This function is triggered by changing the 'Conditional Logic' radio button
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$input
		*  @return	n/a
		*/
		
		change_toggle : function( $input ){
			
			// vars
			var val = $input.val(),
				$td = $input.closest('.acf-input');
				
			
			if( val == "1" ) {
				
				$td.find('.location-groups').show();
				$td.find('.location-groups').find('[name]').removeAttr('disabled');
			
			} else {
				
				$td.find('.location-groups').hide();
				$td.find('.location-groups').find('[name]').attr('disabled', 'disabled');
			
			}
			
		},
		
		
		/*
		*  change_trigger
		*
		*  This function is triggered by changing a 'Conditional Logic' trigger
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$select
		*  @return	n/a
		*/
		
		change_trigger : function( $select ){
			
			// vars
			var val			= $select.val(),
				$trigger	= this.$el.find('.acf-field-object[data-key="' + val + '"]'),
				type		= $trigger.attr('data-type'),
				$value		= $select.closest('tr').find('.conditional-logic-value'),
				choices		= [];
				
			
			// populate choices
			if( type == "true_false" )
			{
				choices = [
					{ value : 1, label : acf.l10n.checked }
				];
							
			}
			else if( type == "select" || type == "checkbox" || type == "radio" )
			{
				var field_choices = $trigger.find('tr[data-name="choices"] textarea').val().split("\n");				
				if( field_choices )
				{
					for( var i = 0; i < field_choices.length; i++ )
					{
						var choice = field_choices[i].split(':');
						
						var label = choice[0];
						if( choice[1] )
						{
							label = choice[1];
						}
						
						choices.push({
							'value' : $.trim( choice[0] ),
							'label' : $.trim( label )
						});
						
					}
				}
				
				
				// allow null
				$allow_null = $trigger.find('tr[data-name="allow_null"]');
				
				if( $allow_null.exists() ) {
					
					if( $allow_null.find('input:checked').val() == '1' ) {
						
						choices.unshift({
							'value' : '',
							'label' : acf._e('null')
						});
						
					}
					
				}
				
				
			}
			
			
			// update select
			this.update_select( $value, choices );
			
		},
		
		
		/*
		*  add_rule
		*
		*  This function will add a new rule below the specified $tr
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		add_rule : function( $tr ){
			
			// vars
			var $tr2 = $tr.clone(),
				old_id = $tr2.attr('data-id'),
				new_id = acf.get_uniqid();
			
			
			// update names
			$tr2.find('[name]').each(function(){
				
				$(this).attr('name', $(this).attr('name').replace( old_id, new_id ));
				$(this).attr('id', $(this).attr('id').replace( old_id, new_id ));
				
			});
				
				
			// update data-i
			$tr2.attr( 'data-id', new_id );
			
			
			// add tr
			$tr.after( $tr2 );
					
			
			// save field
			acf.field_group.save_field( $tr.closest('.acf-field-object') );
			
		},
		
		
		/*
		*  remove_rule
		*
		*  This function will remove the $tr and potentially the group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		remove_rule : function( $tr ){
			
			// vars
			var siblings = $tr.siblings('tr').length;

			
			// save field
			acf.field_group.save_field( $tr.closest('.acf-field-object') );
			
			
			if( siblings == 0 )
			{
				// remove group
				this.remove_group( $tr.closest('.location-group') );
			}
			else
			{
				// remove tr
				$tr.remove();
			}
			
		},
		
		
		/*
		*  add_group
		*
		*  This function will add a new rule group to the given $groups container
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		add_group : function( $groups ){
			
			// vars
			var $group = $groups.find('.location-group:last'),
				$group2 = $group.clone(),
				old_id = $group2.attr('data-id'),
				new_id = acf.get_uniqid();
			
			
			// update names
			$group2.find('[name]').each(function(){
				
				$(this).attr('name', $(this).attr('name').replace( old_id, new_id ));
				$(this).attr('id', $(this).attr('id').replace( old_id, new_id ));
				
			});
			
			
			// update data-i
			$group2.attr( 'data-id', new_id );
			
			
			// update h4
			$group2.find('h4').text( acf.l10n.or );
			
			
			// remove all tr's except the first one
			$group2.find('tr:not(:first)').remove();
			
			
			// add tr
			$group.after( $group2 );
		
			
		},
		
		
		/*
		*  remove_group
		*
		*  This function will remove a rule group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		remove_group : function( $group ){
			
			$group.remove();
			
		}
		
	};
	
	
	acf.field_group.locations = {
	
		$el : null,
		
		
		/*
		*  init
		*
		*  This function will run on document ready and initialize the module
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		init : function(){
			
			// vars
			this.$el = acf.field_group.$locations;
			
			
			// reference
			var self = this;
			
			
			// add rule
			self.$el.on('click', '.location-add-rule', function( e ){
				
				e.preventDefault();
				
				self.add_rule( $(this).closest('tr') );
								
			});
			
			
			// remove rule
			self.$el.on('click', '.location-remove-rule', function( e ){
					
				e.preventDefault();
						
				self.remove_rule( $(this).closest('tr') );
								
			});
			
			
			// add group
			self.$el.on('click', '.location-add-group', function( e ){
				
				e.preventDefault();
							
				self.add_group();
								
			});
			
			
			// change rule
			self.$el.on('change', '.param select', function(){
					
				self.change_rule( $(this) );
					
				
			});
			
		},
		
		
		/*
		*  add_rule
		*
		*  This function will add a new rule below the specified $tr
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		add_rule : function( $tr ){
			
			// vars
			var $tr2 = $tr.clone(),
				old_id = $tr2.attr('data-id'),
				new_id = acf.get_uniqid();
			
			
			// update names
			$tr2.find('[name]').each(function(){
				
				$(this).attr('name', $(this).attr('name').replace( old_id, new_id ));
				$(this).attr('id', $(this).attr('id').replace( old_id, new_id ));
				
			});
				
				
			// update data-i
			$tr2.attr( 'data-id', new_id );
			
			
			// clear selected
			$tr2.find('option[selected]').removeAttr('selected');
			
			
			// add tr
			$tr.after( $tr2 );
					
			
			return false;
			
		},
		
		
		/*
		*  remove_rule
		*
		*  This function will remove the $tr and potentially the group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		remove_rule : function( $tr ){
			
			// vars
			var siblings = $tr.siblings('tr').length;

			
			if( siblings == 0 )
			{
				// remove group
				this.remove_group( $tr.closest('.location-group') );
			}
			else
			{
				// remove tr
				$tr.remove();
			}
			
		},
		
		
		/*
		*  add_group
		*
		*  This function will add a new rule group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		add_group : function(){
			
			// vars
			var $group = this.$el.find('.location-group:last'),
				$group2 = $group.clone(),
				old_id = $group2.attr('data-id'),
				new_id = acf.get_uniqid();
			
			
			// update names
			$group2.find('[name]').each(function(){
				
				$(this).attr('name', $(this).attr('name').replace( old_id, new_id ));
				$(this).attr('id', $(this).attr('id').replace( old_id, new_id ));
				
			});
			
			
			// update data-i
			$group2.attr( 'data-id', new_id );
			
			
			// update h4
			$group2.find('h4').text( acf.l10n.or );
			
			
			// remove all tr's except the first one
			$group2.find('tr:not(:first)').remove();
			
			
			// add tr
			$group.after( $group2 );
			
		},
		
		
		/*
		*  remove_group
		*
		*  This function will remove a rule group
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$group
		*  @return	n/a
		*/
		
		remove_group : function( $group ){
			
			$group.remove();
			
		},
		
		
		/*
		*  change_rule
		*
		*  This function is triggered when changing a location rule trigger
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$select
		*  @return	n/a
		*/
		
		change_rule : function( $select ){
				
			// vars
			var $tr = $select.closest('tr'),
				rule_id = $tr.attr('data-id'),
				$group = $tr.closest('.location-group'),
				group_id = $group.attr('data-id');
			
			
			// add loading gif
			var div = $('<div class="acf-loading"></div>');
			$tr.find('td.value').html( div );
			
			
			// load location html
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: acf.prepare_for_ajax({
					'action'	: 'acf/field_group/render_location_value',
					'rule_id'	: rule_id,
					'group_id'	: group_id,
					'value'		: '',
					'param'		: $select.val(),
				}),
				type		: 'post',
				dataType	: 'html',
				success		: function(html){
	
					div.replaceWith(html);
	
				}
			});
			
		}
	};
	
	
	acf.field_group.options = {
		
		$el : null,
		
		
		/*
		*  init
		*
		*  This function will run on document ready and initialize the module
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		init : function(){
			
			// vars
			this.$el = acf.field_group.$options;
			
			
			// hide on screen toggle
			var $ul = this.$el.find('tr[data-name="hide_on_screen"] ul'),
				$li = $('<li><label><input type="checkbox" value="" name="" >' + acf._e('hide_show_all') + '</label></li>');
			
			
			// start checked?
			if( $ul.find('input:not(:checked)').length == 0 )
			{
				$li.find('input').attr('checked', 'checked');
			}
			
			
			// event
			$li.on('change', 'input', function(){
				
				var checked = $(this).is(':checked');
				
				$ul.find('input').attr('checked', checked);
				
			});
			
			
			// add to ul
			$ul.prepend( $li );
			
		}
		
	};
	
	
	/*
	*  ready
	*
	*  This function is triggered on document ready and will initialize the field group object
	*
	*  @type	function
	*  @date	8/04/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	acf.add_action('ready', function(){
	 	
		acf.field_group.init();
	 	
	});
	
	
	/*
	*  Append
	*
	*  description
	*
	*  @type	function
	*  @date	12/02/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.add_action('open_field change_field_type', function( $el ){
		
		// clear name
		$el.find('tr[data-append]').each(function(){
			
			// vars
			var append = $(this).data('append');
			
			
			// find sibling
			$sibling = $(this).siblings('[data-name="' + append + '"]');
			
			
			// bail early if no $sibling
			if( !$sibling.exists() ) {
				
				return;
				
			}
			
			
			// vars
			var $wrap = $sibling.children('.acf-input'),
				$ul = $wrap.children('.acf-hl');
			
			
			if( !$ul.exists() ) {
				
				$wrap.wrapInner('<ul class="acf-hl"><li></li></ul>');
				
				$ul = $wrap.children('.acf-hl');
			}
			
			
			// create $li
			var $li = $('<li></li>').append( $(this).children('.acf-input').children() );
			
			
			// append $li
			$ul.append( $li );
			
			
			// update cols
			$ul.attr('data-cols', $ul.children().length );
			
			
			// remove
			$(this).remove();
			
		});
			
	});
	
	
	/*
	*  Select
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function acf_render_select_field( $el ){
		
		// vars
		var ui = $el.find('[data-name="toggle-select-ui"]:checked').val();
		
		if( ui == '1' ) {
		
			$el.find('.acf-field[data-name="ajax"]').show();
			
		} else {
		
			$el.find('.acf-field[data-name="ajax"]').hide();
			
		}		
		
	}
	
	acf.add_action('open_field change_field_type', function( $el ){
		
		// bail early if not select
		if( $el.attr('data-type') != 'select' ) {
			
			return;
			
		}
		
		
		// add class to input
		$el.find('.acf-field[data-name="ui"] input[type="radio"]').attr('data-name', 'toggle-select-ui');
		
		
		// render
		acf_render_select_field( $el );
		
	});
	
	$(document).on('change', '[data-name="toggle-select-ui"]', function(){
		
		acf_render_select_field( acf.field_group.focus( $(this) ) );
		
	});
	
	
	/*
	*  Radio
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function acf_render_radio_field( $el ){
		
		// vars
		var $input = $el.find('[data-name="toggle-radio-other"]');
		
		if( $input.is(':checked') ) {
			
			$el.find('.acf-field[data-name="save_other_choice"]').show();
			
		} else {
			
			$el.find('.acf-field[data-name="save_other_choice"]').hide();
			$el.find('.acf-field[data-name="save_other_choice"] input').removeAttr('checked');
			
		}
			
	}
	
	acf.add_action('open_field change_field_type', function( $el ){
		
		// bail early if not radio
		if( $el.attr('data-type') != 'radio' ) {
			
			return;
			
		}
		
		
		// add class to input
		$el.find('.acf-field[data-name="other_choice"] input[type="checkbox"]').attr('data-name', 'toggle-radio-other');
		
		
		// render
		acf_render_radio_field( $el );
		
	});
	
	$(document).on('change', '[data-name="toggle-radio-other"]', function(){
		
		acf_render_radio_field( acf.field_group.focus( $(this) ) );
		
	});
		
	
	/*
	*  Date Picker
	*
	*  This field type requires some extra logic for its settings
	*
	*  @type	function
	*  @date	24/10/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function acf_render_date_picker_field( $el ){
		
		$.each(['display_format', 'return_format'], function(k,v){
			
			// vars
			var $radio = $el.find('.acf-field[data-name="' + v + '"] input[type="radio"]:checked'),
				$other = $el.find('.acf-field[data-name="' + v + '"] input[type="text"]');
			
			
			if( $radio.val() != 'other' ) {
			
				$other.val( $radio.val() );
				
			}
			
		});
			
	}
	
	acf.add_action('open_field change_field_type', function( $el ){
		
		// bail early if not radio
		if( $el.attr('data-type') != 'date_picker' ) {
			
			return;
			
		}
		
		
		// render
		acf_render_date_picker_field( $el );
		
	});
	
	$(document).on('change', '.acf-field-object-date-picker input[type="radio"]', function(){
		
		acf_render_date_picker_field( acf.field_group.focus( $(this) ) );
		
	});
	
	
	/*
	*  tab
	*
	*  description
	*
	*  @type	function
	*  @date	12/02/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.add_action('change_field_label change_field_type', function( $el ){
		
		// bail early if not radio
		if( $el.attr('data-type') != 'tab' ) {
			
			return;
			
		}
		
		
		// clear name
		$el.find('tr[data-name="name"]:first input').val('').trigger('change');
		
	});
	
	
	/*
	*  message
	*
	*  description
	*
	*  @type	function
	*  @date	12/02/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	acf.add_action('change_field_label change_field_type', function( $el ){
		
		// bail early if not radio
		if( $el.attr('data-type') != 'message' ) {
			
			return;
			
		}
		
		
		// clear name
		$el.find('tr[data-name="name"]:first input').val('').trigger('change');
		
	});
	
	
})(jQuery);

// @codekit-prepend "../js/field-group.js";

