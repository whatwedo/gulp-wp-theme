(function($){        
	
	acf.field_group_pro = {
		
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
	    	
	    	
	    	// actions
	    	acf.add_action('open_field sortstop', function( $el ){
			    
			    self.update_field_parent( $el );
				
		    });
		    
		    acf.add_action('duplicate_field', function( $el ){
			    
			    self.duplicate_field( $el );
				
		    });
		    
		    acf.add_action('delete_field', function( $el ){
			    
			    self.delete_field( $el );
				
		    });
		    
			acf.add_action('change_field_type', function( $el ){
			    
			    self.change_field_type( $el );
				
		    });
		    
		    
		    // modules
		    this.repeater.init();
	    	this.flexible_content.init();
    	},
    	
    	
    	/*
    	*  fix_conditional_logic
    	*
    	*  This function will update sub field conditional logic rules after duplication
    	*
    	*  @type	function
    	*  @date	10/06/2014
    	*  @since	5.0.0
    	*
    	*  @param	$fields (jquery selection)
    	*  @return	n/a
    	*/
    	
    	fix_conditional_logic : function( $fields ){
	    	
	    	// build refernce
			var ref = {};
			
			$fields.each(function(){
				
				ref[ $(this).attr('data-orig') ] = $(this).attr('data-key');
				
			});
			
	    	$fields.find('.conditional-logic-field').each(function(){
		    	
		    	// vars
		    	var key = $(this).val();
		    	
		    	
		    	// bail early if val is not a ref key
		    	if( !(key in ref) ) {
			    	
			    	return;
			    	
		    	}
		    	
		    	
		    	// add option if doesn't yet exist
		    	if( ! $(this).find('option[value="' + ref[key] + '"]').exists() ) {
			    	
			    	$(this).append('<option value="' + ref[key] + '">' + ref[key] + '</option>');
			    	
		    	}
		    	
		    	
		    	// set new val
		    	$(this).val( ref[key] );
		    	//console.log('setting new value to ' + ref[key]);
		    	
	    	});
	    	
    	},
    	
    	
    	/*
    	*  update_field_parent
    	*
    	*  This function will update field meta such as parent
    	*
    	*  @type	function
    	*  @date	8/04/2014
    	*  @since	5.0.0
    	*
    	*  @param	$el
    	*  @return	n/a
    	*/
    	
    	update_field_parent : function( $el ){
	    	
	    	// bail early if not div.field (flexible content tr)
	    	if( ! $el.hasClass('acf-field-object') ) {
		    	
		    	return;
		    	
	    	}
	    	
	    	
	    	// vars
	    	var $parent = $el.parent().closest('.acf-field-object'),
		    	val = 0;
		    
		    
		    // find parent
			if( $parent.exists() ) {
				
				// vars
				var id = acf.get_data($parent, 'id'),
					key = acf.get_data($parent, 'key');
					
				
				// set val
				val = key;
				
				
				// if field has an ID, use that
				if( id ) {
					
					val = id;
					
				}
				
			}
			
			
			// update parent
			acf.field_group.update_field_meta( $el, 'parent', val );
	    	
	    	
	    	// action for 3rd party customization
			acf.do_action('update_field_parent', $el, $parent);
			
    	},
    	
    	
    	/*
    	*  duplicate_field
    	*
    	*  This function is triggered when duplicating a field
    	*
    	*  @type	function
    	*  @date	8/04/2014
    	*  @since	5.0.0
    	*
    	*  @param	$el
    	*  @return	n/a
    	*/
    	
    	duplicate_field : function( $el ) {
	    	
	    	// vars
			var $fields = $el.find('.acf-field-object').not('[data-key="acfcloneindex"]');
				
			
			// bail early if $fields are empty
			if( !$fields.exists() ) {
				
				return;
				
			}
			
			
			// loop over sub fields
	    	$fields.each(function(){
		    	
		    	// vars
		    	var $parent = $(this).parent().closest('.acf-field-object'),
		    		key = acf.field_group.get_field_meta( $parent, 'key');
		    		
		    	
		    	// wipe field
		    	acf.field_group.wipe_field( $(this) );
		    	
		    	
		    	// update parent
		    	acf.field_group.update_field_meta( $(this), 'parent', key );
		    	
		    	
		    	// save field
		    	acf.field_group.save_field( $(this) );
		    	
		    	
	    	});
	    	
	    	
	    	// fix conditional logic rules
	    	this.fix_conditional_logic( $fields );
	    	
    	},
    	
    	
    	/*
    	*  delete_field
    	*
    	*  This function is triggered when deleting a field
    	*
    	*  @type	function
    	*  @date	8/04/2014
    	*  @since	5.0.0
    	*
    	*  @param	$el
    	*  @return	n/a
    	*/
    	
    	delete_field : function( $el ){
	    	
	    	$el.find('.acf-field-object').each(function(){
		    	
		    	acf.field_group.delete_field( $(this), false );
		    	
	    	});
	    	
    	},
    	
    	
    	/*
    	*  change_field_type
    	*
    	*  This function is triggered when changing a field type
    	*
    	*  @type	function
    	*  @date	7/06/2014
    	*  @since	5.0.0
    	*
    	*  @param	$post_id (int)
    	*  @return	$post_id (int)
    	*/
		
		change_field_type : function( $el ) {
			
			$el.find('.acf-field-object').each(function(){
		    	
		    	acf.field_group.delete_field( $(this), false );
		    	
	    	});
			
		}
		
	};
	
	acf.field_group_pro.repeater = {
	
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
			var _this = this;
			
			
			// actions
			acf.add_action('open_field change_field_type', function( $el ){
			    
			    _this.render_field( $el );
				
		    });
		    
			
			// events	
			$(document).on('change', '.acf-repeater-layout input', function(){
				
				_this.render_field( $(this).closest('.acf-field-object') );
				
			});
			
		},
		
		
		/*
		*  render_field
		*
		*  This function is triggered when a repeater field is visible
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		render_field : function( $el ){
			
			// bail early if not repeater
			if( $el.attr('data-type') != 'repeater' ) {
				
				return;
				
			}
			
			
			// vars
			var $tbody = $el.find('> .settings > table > tbody'),
				$field_list = $tbody.find('> [data-name="sub_fields"] .acf-field-list:first'),
				layout = $tbody.find('> [data-name="layout"] input:checked').val();
				
			
			
			// add class
			$field_list.removeClass('layout-row layout-table').addClass( 'layout-' + layout );
			
			
			// sortable
			if( ! $field_list.hasClass('ui-sortable') ) {
			
				acf.field_group.sort_fields( $field_list );
				
			}
			
		}
		
	}
	
	
	acf.field_group_pro.flexible_content = {
		
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
			var _this = this;
			
			
			// actions
			acf.add_action('open_field change_field_type', function( $el ){
			    
			    _this.render_field( $el );
				
		    });
		    
		    acf.add_action('update_field_parent', function( $el, $parent ){
			    
			    _this.update_field_parent( $el, $parent );
				
		    });
			
						
			// events
			$(document).on('click', '[data-name="acf-fc-add"]', function( e ){
				
				e.preventDefault();
				
				_this.add_layout( $(this).closest('.acf-field') );
				
			});
			
			$(document).on('click', '[data-name="acf-fc-duplicate"]', function( e ){
				
				e.preventDefault();
				
				_this.duplicate_layout( $(this).closest('.acf-field') );
				
			});
			
			$(document).on('click', '[data-name="acf-fc-delete"]', function( e ){
				
				e.preventDefault();
				
				_this.delete_layout( $(this).closest('.acf-field') );
				
			});
			
			$(document).on('blur', '.acf-fc-meta-label input', function( e ){
				
				_this.change_layout_label( $(this) );
				
			});
			
			$(document).on('change', '.acf-fc-meta-display select', function( e ){
				
				_this.render_layout( $(this).closest('.acf-field') );
				
			});	
			
		},
		
		
		/*
		*  add_layout
		*
		*  This function will add another fc layout after the given $tr
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		add_layout : function( $tr ){
			
			// vars
			var $new_tr = $tr.clone( false );
		
		
			// remove sub fields
			$new_tr.find('.acf-field-object').not('[data-key="acfcloneindex"]').remove();
	
			
			// show add new message
			$new_tr.find('.no-fields-message').show();
			
			
			// reset layout meta values
			$new_tr.find('.acf-fc-meta input').val('');
			
			
			// wipe layout
			this.wipe_layout( $new_tr );
			
			
			// add new tr
			$tr.after( $new_tr );
			
			
			// make sortbale
			$new_tr.find('.acf-field-list').each(function(){
				
				acf.field_group.sort_fields( $(this) );
				
			});
			
			
			// display
			$new_tr.find('.acf-fc-meta select').val('row').trigger('change');
			
		},
		
		
		/*
		*  wipe_layout
		*
		*  This function will prepare a new fc layout
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		wipe_layout : function( $tr ){
			
			// vars
			var old_key = $tr.attr('data-key'),
				new_key = acf.get_uniqid();
			
			
			// give field a new id
			$tr.attr('data-key', new_key);
			$tr.find('> .acf-input > .acf-hidden [data-name="layout-key"]').val(new_key);
			
			
			// update attributes
			$tr.find('[id*="' + old_key + '"]').each(function(){	
			
				$(this).attr('id', $(this).attr('id').replace('-layouts-' + old_key + '-','-layouts-' + new_key + '-') );
				
			});
			
			$tr.find('[name*="' + old_key + '"]').each(function(){	
			
				$(this).attr('name', $(this).attr('name').replace('[layouts][' + old_key + ']','[layouts][' + new_key + ']') );
				
			});
						
		},
		
		
		/*
		*  duplicate_layout
		*
		*  This function will duplicate a fc layout
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		duplicate_layout : function( $tr ){
			
			// allow acf to modify DOM
			acf.do_action('before_duplicate', $tr);
			
			
			// vars
			var $new_tr = $tr.clone( false );
			
			
			// wipe layout
			this.wipe_layout( $new_tr );
			
			
			// vars
			var new_parent_layout = $new_tr.attr('data-key'),
				$fields = $new_tr.find('.acf-field-object').not('[data-key="acfcloneindex"]');
				
			$fields.each(function(){
				
				// wipe
				acf.field_group.wipe_field( $(this) );
				
				
				// update layput key
				acf.field_group.update_field_meta( $(this), 'parent_layout', new_parent_layout );
				
				
				// save
				acf.field_group.save_field( $(this) );
				
			});
			
			
			// add new tr
			$tr.after( $new_tr );
			
			
			// allow acf to modify DOM
			acf.do_action('after_duplicate', $tr, $new_tr);
			
			
			// fix conditional logic rules
			acf.field_group_pro.fix_conditional_logic( $fields );
			
			
			// make sortbale
			$new_tr.find('.acf-field-list').each(function(){
				
				acf.field_group.sort_fields( $(this) );
				
			});
				
			
			// focus on new label
			$new_tr.find('.acf-fc-meta-label input').focus();
			
		},

		
		/*
		*  delete_layout
		*
		*  This function will delete a fc layout
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$tr
		*  @return	n/a
		*/
		
		delete_layout : function( $tr ){
			
			// validate
			if( $tr.siblings('tr[data-name="fc_layout"]').length == 0 ) {
			
				alert( acf._e('flexible_content','layout_warning') );
				
				return false;
				
			}
			
			
			// delete fields
			$tr.find('.acf-field-object').not('[data-key="acfcloneindex"]').each(function(){
				
				// dlete without animation
				acf.field_group.delete_field( $(this), false );
				
			});
			
			
			// save field
			acf.field_group.save_field( $tr.closest('.acf-field-object') );
			
			
			// remove tr
			acf.remove_tr( $tr );
						
		},
		
		
		/*
		*  change_layout_label
		*
		*  This function is triggered when changing the layout's label
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$input 
		*  @return	n/a
		*/
		
		change_layout_label : function( $label ){
			
			var $name = $label.closest('.acf-fc-meta').find('.acf-fc-meta-name input');
			
			// only if name is empty
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
			
		},
		
		
		/*
		*  render_field
		*
		*  This function is triggered when a fc field is visible
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		render_field : function( $el ){
			
			// bail early if not flexible_content
			if( $el.attr('data-type') != 'flexible_content' ) {
				
				return;
				
			}
			
			// reference
			var _this = this;
			
			
			// vars
			var $tbody = $el.find('> .settings > table > tbody');
			
			
			// validate
			if( ! $tbody.hasClass('ui-sortable') ) {
				
				// add sortable
				$tbody.sortable({
					items					: '> tr[data-name="fc_layout"]',
					handle					: '[data-name="acf-fc-reorder"]',
					forceHelperSize			: true,
					forcePlaceholderSize	: true,
					scroll					: true,
					start : function (event, ui) {
						
						acf.do_action('sortstart', ui.item, ui.placeholder);
						
		   			},
		   			
		   			stop : function (event, ui) {
					
						acf.do_action('sortstop', ui.item, ui.placeholder);
						
						// save
						acf.field_group.save_field( $el );
						
						
		   			}
				});
				
			}
			
			
			// layouts
			$tbody.children('tr[data-name="fc_layout"]').each(function(){
				
				_this.render_layout( $(this) );
					
			});

			
		},
		
		
		/*
		*  render_layout
		*
		*  This function will update the field list class
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$field_list
		*  @return	n/a
		*/
		
		render_layout : function( $tr ){
			
			// vars
			var layout = $tr.find('.acf-fc-meta:first .acf-fc-meta-display select').val(),
				$field_list = $tr.find('.acf-field-list:first');
			
			
			// add class
			$field_list.removeClass('layout-row layout-table').addClass( 'layout-' + layout );
			
			
			// sortable
			if( ! $field_list.hasClass('ui-sortable') ) {
			
				acf.field_group.sort_fields( $field_list );
				
			}
			
			
			// append parent_layout input
			var layout_key = $tr.attr('data-key');
			
			$field_list.children('.acf-field-object').each(function(){
				
				acf.field_group.update_field_meta( $(this), 'parent_layout', layout_key );
				
			});
			
		},
		
		
		/*
		*  update_field_parent
		*
		*  This function is triggered when a field's parent is being updated
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$el
		*  @return	n/a
		*/
		
		update_field_parent : function( $el, $parent ){			
			
			// remove parent_layout if not a sub field
			if( !$parent.exists() ) {
				
				acf.field_group.delete_field_meta( $el, 'parent_layout' );
				return;
				
			}
			
			
			// vars
			var $tr = $el.closest('tr.acf-field');
			
			
			// bail early if $tr is not a fc layout
			if( $tr.attr('data-name') != 'fc_layout' ) {
				
				return;
				
			}
			
			
			// vars
			var parent_layout = acf.field_group.get_field_meta( $el, 'parent_layout' ),
				new_parent_layout = $tr.attr('data-key');
			
			
			// bail early if no change
			if( parent_layout == new_parent_layout ) {
				
				return;
				
			}
			
			
			// update meta
			acf.field_group.update_field_meta( $el, 'parent_layout', new_parent_layout );
			
			
			// save
			acf.field_group.save_field( $el );
			
		}
		
	}
	
	/*
	*  ready
	*
	*  This function is riggered on document ready and will initialize the fiedl group object
	*
	*  @type	function
	*  @date	8/04/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	acf.add_action('ready', function(){
	 	
		acf.field_group_pro.init();
	 	
	});

})(jQuery);

// @codekit-prepend "../js/field-group.js";

