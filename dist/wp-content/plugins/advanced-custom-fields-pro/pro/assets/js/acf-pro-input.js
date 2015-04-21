(function($){
	
	// comon
	acf.pro = acf.model.extend({
		
		actions: {
			'refresh': 	'refresh',
		},
		
		filters: {
			'get_fields' : 'get_fields',
		},
		
		get_fields : function( $fields ){
			
			// remove clone fields
			$fields = $fields.not('.acf-clone .acf-field');
			
			// return
			return $fields;
		
		},
		
		
		/*
		*  refresh
		*
		*  This function will run when acf detects a refresh is needed on the UI
		*  Most commonly after ready / conditional logic change
		*
		*  @type	function
		*  @date	10/11/2014
		*  @since	5.0.9
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		refresh: function(){
			
			// reference
			var self = this;
			
			
			// loop over all table layouts
			$('.acf-input-table.table-layout').each(function(){
				
				// vars
				var $table = $(this);
				
				
				// loop over th
				$table.find('> thead th.acf-th').each(function(){
					
					// vars
					var $th = $(this),
						$td = $table.find('> tbody > tr > td[data-key="' + $th.attr('data-key') + '"]');
					
					
					// clear class
					$td.removeClass('appear-empty');
					$th.removeClass('hidden-by-conditional-logic');
					
					
					// remove clone if needed
					if( $td.length > 1 ) {
						
						$td = $td.not(':last');
						
					}
					
					
					// add classes
					if( $td.not('.hidden-by-conditional-logic').length == 0 ) {
						
						$th.addClass('hidden-by-conditional-logic');
						
					} else {
						
						$td.addClass('appear-empty');
						
					}
					
				});
				
				
				// render table widths
				self.render_table( $table );
				
			});
			
		},
		
		render_table : function( $table ){
			
			//console.log( 'render_table %o', $table);
			// bail early if table is row layout
			if( $table.hasClass('row-layout') ) {
			
				return;
				
			}
			
			
			// vars
			var $th = $table.find('> thead > tr > th'),
				available_width = 100;
			
			
			// clear widths
			$th.css('width', 'auto');
			
			
			// update $th
			$th = $th.not('.order, .remove, .hidden-by-conditional-logic');
			
			
			// set custom widths first
			$th.filter('[data-width]').each(function(){
				
				// vars
				var width = parseInt( $(this).attr('data-width') );
				
				
				// remove from available
				available_width -= width;
				
				
				// set width
				$(this).css('width', width + '%');
				
			});
			
			
			// update $th
			$th = $th.not('[data-width]');
			
			
			// set custom widths first
			$th.each(function(){
				
				// cal width
				var width = available_width / $th.length;
				
				
				// set width
				$(this).css('width', width + '%');
				
			});
			
		}
		
	});

})(jQuery);

(function($){
		
	acf.fields.repeater = acf.field.extend({
		
		type: 'repeater',
		$el: null,
		$input: null,
		$tbody: null,
		$clone: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'show':		'show'
		},
		
		events: {
			'click .acf-repeater-add-row': 		'add',
			'click .acf-repeater-remove-row': 	'remove',
		},
		
		focus: function(){
			
			this.$el = this.$field.find('.acf-repeater:first');
			this.$input = this.$field.find('input:first');
			this.$tbody = this.$el.find('tbody:first');
			this.$clone = this.$tbody.children('tr.acf-clone');
			
			this.o = acf.get_data( this.$el );
			
		},
		
		initialize: function(){
			
			// CSS fix
			this.$tbody.on('mouseenter', 'tr.acf-row', function( e ){
				
				// vars
				var $tr = $(this),
					$td = $tr.children('.remove'),
					$a = $td.find('.acf-repeater-add-row'),
					margin = ( $td.height() / 2 ) + 9; // 9 = padding + border
				
				
				// css
				$a.css('margin-top', '-' + margin + 'px' );
				
			});
			
			
			// sortable
			if( this.o.max != 1 ) {
				
				// reference
				var self = this,
					$tbody = this.$tbody,
					$field = this.$field;
					
				
				$tbody.one('mouseenter', 'td.order', function( e ){
					
					$tbody.unbind('sortable').sortable({
					
						items					: '> tr',
						handle					: '> td.order',
						forceHelperSize			: true,
						forcePlaceholderSize	: true,
						scroll					: true,
						
						start: function(event, ui) {
							
							// focus
							self.doFocus($field);
							
							acf.do_action('sortstart', ui.item, ui.placeholder);
							
			   			},
			   			
			   			stop: function(event, ui) {
							
							// render
							self.render();
							
							acf.do_action('sortstop', ui.item, ui.placeholder);
							
			   			},
			   			
			   			update: function(event, ui) {
				   			
				   			// trigger change
							self.$input.trigger('change');
							
				   		}
			   			
					});
				
				});
				
			}

			
			// set column widths
			// no longer needed due to refresh action in acf.pro model
			//acf.pro.render_table( this.$el.children('table') );
			
			
			// disable clone inputs
			this.$clone.find('input, textarea, select').attr('disabled', 'disabled');
						
			
			// render
			this.render();
			
		},
		
		show: function(){
			
			this.$tbody.find('.acf-field:visible').each(function(){
				
				acf.do_action('show_field', $(this));
				
			});
			
		},
		
		count: function(){
			
			return this.$tbody.children().length - 1;
			
		},
		
		render: function(){
			
			// update order numbers
			this.$tbody.children().each(function(i){
				
				$(this).children('td.order').html( i+1 );
				
			});
			
			
			// empty?
			if( this.count() == 0 ) {
			
				this.$el.addClass('empty');
				
			} else {
			
				this.$el.removeClass('empty');
				
			}
			
			
			// row limit reached
			if( this.o.max > 0 && this.count() >= this.o.max ) {
				
				this.$el.addClass('disabled');
				this.$el.find('> .acf-hl .acf-button').addClass('disabled');
				
			} else {
				
				this.$el.removeClass('disabled');
				this.$el.find('> .acf-hl .acf-button').removeClass('disabled');
				
			}
			
		},
		
		add: function( e ){
			
			// find $before
			var $before	= this.$clone;
			
			if( e && e.$el.is('.acf-icon') ) {
			
				$before	= e.$el.closest('.acf-row');
				
			}
			
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				alert( acf._e('repeater','max').replace('{max}', this.o.max) );
				return false;
				
			}
			
		
			// create and add the new field
			var new_id = acf.get_uniqid(),
				html = this.$clone.outerHTML();
				
				
			// replace acfcloneindex
			var html = html.replace(/(="[\w-\[\]]+?)(acfcloneindex)/g, '$1' + new_id),
				$html = $( html );
			
			
			// remove clone class
			$html.removeClass('acf-clone');
			
			
			// enable inputs (ignore inputs hidden by conditional logic)
			$html.find('input, textarea, select').not('.acf-clhi').removeAttr('disabled');
			
			
			// add row
			$before.before( $html );
			
			
			// trigger mouseenter on parent repeater to work out css margin on add-row button
			this.$field.parents('.acf-row').trigger('mouseenter');
			
			
			// update order
			this.render();
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
			
			// setup fields
			acf.do_action('append', $html);
			
			
			// return
			return $html;
		},
		
		remove: function( e ){
			
			// reference
			var self = this,
				$field = this.$field;
			
			
			// vars
			var $tr = e.$el.closest('.acf-row'),
				$table = $tr.closest('table');
			
			
			// validate
			if( this.count() <= this.o.min ) {
			
				alert( acf._e('repeater','min').replace('{min}', this.o.min) );
				return false;
			}
			
			
			// trigger change to allow attachment save
			this.$input.trigger('change');
				
			
			// action for 3rd party customization
			acf.do_action('remove', $tr);
			
			
			// animate out tr
			acf.remove_tr( $tr, function(){
				
				// render
				self.doFocus($field).render();
				
				
				// trigger mouseenter on parent repeater to work out css margin on add-row button
				$field.closest('.acf-row').trigger('mouseenter');
				
				
				// trigger conditional logic render
				// when removing a row, there may not be a need for some appear-empty cells
				if( $table.hasClass('table-layout') ) {
					
					acf.conditional_logic.render( $table );
					
				}
				
				
			});
			
		}
		
	});	
	
})(jQuery);

(function($){
		
	acf.fields.flexible_content = acf.field.extend({
		
		type: 'flexible_content',
		$el: null,
		$input: null,
		$values: null,
		$clones: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'show':		'show'
		},
		
		events: {
			'click .acf-fc-remove': 		'remove',
			'click .acf-fc-layout-handle':	'toggle',
			'click .acf-fc-popup li a':		'add',
			'click .acf-fc-add': 			'open_popup',
			'blur .acf-fc-popup .focus':	'close_popup'
		},
		
		focus: function(){
			
			this.$el = this.$field.find('.acf-flexible-content:first');
			this.$input = this.$field.find('input:first');
			this.$values = this.$el.children('.values');
			this.$clones = this.$el.children('.clones');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// min / max
			this.o.min = this.o.min || 0;
			this.o.max = this.o.max || 0;
			
		},
		
		count : function(){
			
			return this.$values.children('.layout').length;
			
		},
		
		initialize: function(){
			
			// sortable
			if( this.o.max != 1 ) {
				
				// reference
				var self = this,
					$values = this.$values,
					$field = this.$field;
					
				
				$values.one('mouseenter', '.acf-fc-layout-handle', function( e ){
					
					$values.unbind('sortable').sortable({
					
						items					: '> .layout',
						handle					: '> .acf-fc-layout-handle',
						forceHelperSize			: true,
						forcePlaceholderSize	: true,
						scroll					: true,
						
						start: function(event, ui) {
							
							// focus
							self.doFocus($field);
							
							acf.do_action('sortstart', ui.item, ui.placeholder);
							
			   			},
			   			
			   			stop: function(event, ui) {
							
							// render
							self.render();
							
							acf.do_action('sortstop', ui.item, ui.placeholder);
							
			   			},
			   			
			   			update: function(event, ui) {
				   			
				   			// trigger change
							self.$input.trigger('change');
							
				   		}
				   		
					});
				
				});
				
			}
			
			
			// disable clone inputs
			this.$clones.find('input, textarea, select').attr('disabled', 'disabled');
						
			
			// render
			this.render();
			
		},
		
		show: function(){
			
			this.$values.find('.acf-field:visible').each(function(){
				
				acf.do_action('show_field', $(this));
				
			});
			
		},
		
		render: function(){
			
			// update order numbers
			this.$values.children('.layout').each(function( i ){
			
				$(this).find('> .acf-fc-layout-handle .fc-layout-order').html( i+1 );
				
			});
			
			
			// empty?
			if( this.count() == 0 ) {
			
				this.$el.addClass('empty');
				
			} else {
			
				this.$el.removeClass('empty');
				
			}
			
			
			// row limit reached
			if( this.o.max > 0 && this.count() >= this.o.max ) {
				
				this.$el.addClass('disabled');
				this.$el.find('> .acf-hl .acf-button').addClass('disabled');
				
			} else {
				
				this.$el.removeClass('disabled');
				this.$el.find('> .acf-hl .acf-button').removeClass('disabled');
				
			}
			
		},
			
		validate_add : function( layout ){
			
			// vadiate max
			if( this.o.max > 0 && this.count() >= this.o.max ) {
				
				// vars
				var identifier	= ( this.o.max == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'max');
				
				
				// translate
				s = s.replace('{max}', this.o.max);
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				
				
				// alert
				alert( s );
				
				
				// return
				return false;
			}
			
			
			// vadiate max layout
			var $popup			= $( this.$el.children('.tmpl-popup').html() ),
				$a				= $popup.find('[data-layout="' + layout + '"]'),
				layout_max		= parseInt( $a.attr('data-max') ),
				layout_count	= this.$values.children('.layout[data-layout="' + layout + '"]').length;
			
			
			if( layout_max > 0 && layout_count >= layout_max ) {
				
				// vars
				var identifier	= ( layout_max == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'max_layout');
				
				
				// translate
				s = s.replace('{max}', layout_count);
				s = s.replace('{label}', '"' + $a.text() + '"');
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				
				
				// alert
				alert( s );
				
				
				// return
				return false;
			}
			
			
			// return
			return true;
			
		},
		
		validate_remove : function( layout ){
			
			// vadiate min
			if( this.o.min > 0 && this.count() <= this.o.min ) {
				
				// vars
				var identifier	= ( this.o.min == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'min') + ', ' + acf._e('flexible_content', 'remove');
				
				
				// translate
				s = s.replace('{min}', this.o.min);
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				s = s.replace('{layout}', acf._e('flexible_content', 'layout'));
				
				
				// return
				return confirm( s );

			}
			
			
			// vadiate max layout
			var $popup			= $( this.$el.children('.tmpl-popup').html() ),
				$a				= $popup.find('[data-layout="' + layout + '"]'),
				layout_min		= parseInt( $a.attr('data-min') ),
				layout_count	= this.$values.children('.layout[data-layout="' + layout + '"]').length;
			
			
			if( layout_min > 0 && layout_count <= layout_min ) {
				
				// vars
				var identifier	= ( layout_min == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'min_layout') + ', ' + acf._e('flexible_content', 'remove');
				
				
				// translate
				s = s.replace('{min}', layout_count);
				s = s.replace('{label}', '"' + $a.text() + '"');
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				s = s.replace('{layout}', acf._e('flexible_content', 'layout'));
				
				
				// return
				return confirm( s );
			}
			
			
			// return
			return true;
			
		},
		
		open_popup : function( e ){
			
			// reference
			var $values = this.$values;
			
			
			// vars
			var $popup = $( this.$el.children('.tmpl-popup').html() );
			
			
			// modify popup
			$popup.find('a').each(function(){
				
				// vars
				var min		= parseInt( $(this).attr('data-min') ),
					max		= parseInt( $(this).attr('data-max') ),
					name	= $(this).attr('data-layout'),
					label	= $(this).text(),
					count	= $values.children('.layout[data-layout="' + name + '"]').length,
					$status = $(this).children('.status');
				
				
				if( max > 0 ) {
					
					// find diff
					var available	= max - count,
						s			= acf._e('flexible_content', 'available'),
						identifier	= ( available == 1 ) ? 'layout' : 'layouts',
				
					
					// translate
					s = s.replace('{available}', available);
					s = s.replace('{max}', max);
					s = s.replace('{label}', '"' + label + '"');
					s = s.replace('{identifier}', acf._e('flexible_content', identifier));
					
					
					// show status
					$status.show().text( available ).attr('title', s);
					
					
					// limit reached?
					if( available == 0 ) {
					
						$status.addClass('warning');
						
					}
					
				}
				
				
				if( min > 0 ) {
					
					// find diff
					var required	= min - count,
						s			= acf._e('flexible_content', 'required'),
						identifier	= ( required == 1 ) ? 'layout' : 'layouts',
				
						
					// translate
					s = s.replace('{required}', required);
					s = s.replace('{min}', min);
					s = s.replace('{label}', '"' + label + '"');
					s = s.replace('{identifier}', acf._e('flexible_content', identifier));
					
					
					// limit reached?
					if( required > 0 ) {
					
						$status.addClass('warning').show().text( required ).attr('title', s);
						
					}
					
				}
				
			});
			
			
			// add popup
			e.$el.after( $popup );
			
			
			// within layout?
			if( e.$el.attr('data-before') ) {
			
				$popup.addClass('within-layout');
				$popup.closest('.layout').addClass('popup-open');
				
			}
			
			
			// vars
			$popup.css({
				'margin-top' : 0 - $popup.height() - e.$el.outerHeight() - 14,
				'margin-left' : ( e.$el.outerWidth() - $popup.width() ) / 2,
			});
			
			
			// check distance to top
			var offset = $popup.offset().top;
			
			if( offset < 30 ) {
				
				$popup.css({
					'margin-top' : 15
				});
				
				$popup.find('.bit').addClass('top');
			}
			
			
			// focus
			$popup.children('.focus').trigger('focus');
			
		},
		
		close_popup: function( e ){
			
			var $popup = e.$el.parent();
			
			
			// hide controlls?
			if( $popup.closest('.layout').exists() ) {
			
				$popup.closest('.layout').removeClass('popup-open');
				
			}
			
			
			setTimeout(function(){
				
				$popup.remove();
				
			}, 200);
			
		},
		
		add : function( e ){
						
			// vars
			var $popup = e.$el.closest('.acf-fc-popup'),
				layout = e.$el.attr('data-layout');
			
						
			// bail early if validation fails
			if( !this.validate_add(layout) ) {
			
				return;
				
			}
			
			
			// create and add the new layout
			var new_id = acf.get_uniqid(),
				html = this.$clones.children('.layout[data-layout="' + layout + '"]').outerHTML();
				
				
			// replace acfcloneindex
			var html = html.replace(/(="[\w-\[\]]+?)(acfcloneindex)/g, '$1' + new_id),
				$html = $( html );
			
			
			// enable inputs (ignore inputs hidden by conditional logic)
			$html.find('input, textarea, select').not('.acf-clhi').removeAttr('disabled');
			
							
			// hide no values message
			this.$el.children('.no-value-message').hide();
			
			
			// remove class
			$html.removeClass('acf-clone');
			
			
			// add row
			this.$values.append( $html ); 
			
			
			// move row
			if( $popup.hasClass('within-layout') ) {
			
				$popup.closest('.layout').before( $html );
			
			}
						
			
			// setup fields
			acf.do_action('append', $html);
			
			
			// update order
			this.render();
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
		},
		
		remove: function( e ){
			
			// vars
			var $layout	= e.$el.closest('.layout');
			
			
			// bail early if validation fails
			if( !this.validate_remove( $layout.attr('data-layout') ) ) {
			
				return;
				
			}
			
			
			// close field
			var end_height = 0,
				$message = this.$el.children('.no-value-message');
			
			if( $layout.siblings('.layout').length == 0 ) {
			
				end_height = $message.outerHeight();
				
			}
			
			
			// trigger change
			this.$input.trigger('change');
			
			
			// action for 3rd party customization
			acf.do_action('remove', $layout);
			
			
			// remove
			acf.remove_el( $layout, function(){
				
				if( end_height > 0 ) {
				
					$message.show();
					
				}
				
			}, end_height);
			
		},

		toggle : function( e ){
			
			// vars
			var $layout	= e.$el.closest('.layout');
			
			
			if( $layout.attr('data-toggle') == 'closed' ) {
			
				$layout.attr('data-toggle', 'open');
				$layout.children('.acf-input-table').show();
				
				// refresh layout
				acf.do_action('refresh', $layout);
				
			} else {
				
				$layout.attr('data-toggle', 'closed');
				$layout.children('.acf-input-table').hide();
				
			}
			
			
			// sync local storage (collapsed)
			this.sync();
			
		},
		
		sync : function(){
			
			// vars
			var name = 'acf_collapsed_' + acf.get_data(this.$field, 'key'),
				collapsed = [];
			
			this.$values.children('.layout').each(function( i ){
				
				if( $(this).attr('data-toggle') == 'closed' ) {
				
					collapsed.push( i );
					
				}
				
			});
			
			acf.update_cookie( name, collapsed.join('|') );	
			
		}
	});	
	

})(jQuery);

(function($){
	
	acf.fields.gallery = acf.field.extend({
		
		type: 'gallery',
		$el: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'submit':	'close_sidebar'
		},
		
		events: {
			'click .acf-gallery-attachment': 		'select_attachment',
			'click .remove-attachment':				'remove_attachment',
			'click .edit-attachment':				'edit_attachment',
			'click .update-attachment': 			'update_attachment',
			'click .add-attachment':				'add_attachment',
			'click .close-sidebar':					'close_sidebar',
			'change .acf-gallery-side input':		'update_attachment',
			'change .acf-gallery-side textarea':	'update_attachment',
			'change .acf-gallery-side select':		'update_attachment',
			'change .bulk-actions':					'sort'
		},
		
		focus: function(){
			
			this.$el = this.$field.find('.acf-gallery').first();
			this.$values = this.$el.children('.values');
			this.$clones = this.$el.children('.clones');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// min / max
			this.o.min = this.o.min || 0;
			this.o.max = this.o.max || 0;
			
		},
		
		get_attachment : function( id ){
			
			// defaults
			id = id || '';
			
			
			// vars
			var selector = '.acf-gallery-attachment';
			
			
			// update selector
			if( id === 'active' ) {
				
				selector += '.active';
				
			} else if( id ) {
				
				selector += '[data-id="' + id  + '"]';
				
			}
			
			
			// return
			return this.$el.find( selector );
			
		},
		
		count : function(){
			
			return this.get_attachment().length;
			
		},

		initialize : function(){
			
			// reference
			var self = this,
				$field = this.$field;
				
					
			// sortable
			this.$el.find('.acf-gallery-attachments').unbind('sortable').sortable({
				
				items					: '.acf-gallery-attachment',
				forceHelperSize			: true,
				forcePlaceholderSize	: true,
				scroll					: true,
				
				start : function (event, ui) {
					
					ui.placeholder.html( ui.item.html() );
					ui.placeholder.removeAttr('style');
								
					acf.do_action('sortstart', ui.item, ui.placeholder);
					
	   			},
	   			
	   			stop : function (event, ui) {
				
					acf.do_action('sortstop', ui.item, ui.placeholder);
					
	   			}
			});
			
			
			// resizable
			this.$el.unbind('resizable').resizable({
				handles : 's',
				minHeight: 200,
				stop: function(event, ui){
					
					acf.update_user_setting('gallery_height', ui.size.height);
				
				}
			});
			
			
			// resize
			$(window).on('resize', function(){
				
				self.doFocus( $field ).resize();
				
			});
			
			
			// render
			this.render();
			
			
			// resize
			this.resize();
					
		},

		render : function() {
			
			// vars
			var $select = this.$el.find('.bulk-actions'),
				$a = this.$el.find('.add-attachment');
			
			
			// disable select
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				$a.addClass('disabled');
				
			} else {
			
				$a.removeClass('disabled');
				
			}
			
		},
		
		sort: function( e ){
			
			// vars
			var sort = e.$el.val();
			
			
			// validate
			if( !sort ) {
			
				return;
				
			}
			
			
			// vars
			var data = acf.prepare_for_ajax({
				action		: 'acf/fields/gallery/get_sort_order',
				field_key	: acf.get_field_key(this.$field),
				post_id		: acf.get('post_id'),
				ids			: [],
				sort		: sort
			});
			
			
			// find and add attachment ids
			this.get_attachment().each(function(){
				
				data.ids.push( $(this).attr('data-id') );
				
			});
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'json',
				type		: 'post',
				cache		: false,
				data		: data,
				context		: this,
				success		: this.sort_success
			});
			
		},
		
		sort_success : function( json ) {
		
			// validate
			if( !acf.is_ajax_success(json) ) {
			
				return;
				
			}
			
			
			// reverse order
			json.data.reverse();
			
			
			// loop over json
			for( i in json.data ) {
				
				var id = json.data[ i ],
					$attachment = this.get_attachment(id);
				
				
				// prepend attachment
				this.$el.find('.acf-gallery-attachments').prepend( $attachment );
				
			};
			
		},
		
		clear_selection : function(){
			
			this.get_attachment().removeClass('active');
			
		},
		
		select_attachment: function( e ){
			
			// vars
			var $attachment = e.$el;
			
			
			// bail early if already active
			if( $attachment.hasClass('active') ) {
				
				return;
				
			}
			
			
			// vars
			var id = $attachment.attr('data-id');
			
			
			// clear selection
			this.clear_selection();
			
			
			// add selection
			$attachment.addClass('active');
			
			
			// fetch
			this.fetch( id );
			
			
			// open sidebar
			this.open_sidebar();
			
		},
		
		open_sidebar : function(){
			
			// add class
			this.$el.addClass('sidebar-open');
			
			
			// hide bulk actions
			this.$el.find('.bulk-actions').hide();
			
			
			// animate
			this.$el.find('.acf-gallery-main').animate({ right : 350 }, 250);
			this.$el.find('.acf-gallery-side').animate({ width : 349 }, 250);
			
		},
		
		close_sidebar : function(){
			
			// remove class
			this.$el.removeClass('sidebar-open');
			
			
			// vars
			var $select = this.$el.find('.bulk-actions');
			
			
			// deselect attachmnet
			this.clear_selection();
			
			
			// disable sidebar
			this.$el.find('.acf-gallery-side').find('input, textarea, select').attr('disabled', 'disabled');
			
			
			// animate
			this.$el.find('.acf-gallery-main').animate({ right : 0 }, 250);
			this.$el.find('.acf-gallery-side').animate({ width : 0 }, 250, function(){
				
				$select.show();
				
				$(this).find('.acf-gallery-side-data').html( '' );
				
			});
			
		},
		
		fetch : function( id ){
			
			// vars
			var data = acf.prepare_for_ajax({
				action		: 'acf/fields/gallery/get_attachment',
				field_key	: acf.get_field_key(this.$field),
				nonce		: acf.get('nonce'),
				post_id		: acf.get('post_id'),
				id			: id
			});
			
			
			// abort XHR if this field is already loading AJAX data
			if( this.$el.data('xhr') ) {
			
				this.$el.data('xhr').abort();
				
			}
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'html',
				type		: 'post',
				cache		: false,
				data		: data,
				context		: this,
				success		: this.render_fetch
			});
			
			
			// update el data
			this.$el.data('xhr', xhr);
			
		},
		
		render_fetch : function( html ){
			
			// bail early if no html
			if( !html ) {
				
				return;	
				
			}
			
			
			// vars
			var $side = this.$el.find('.acf-gallery-side-data');
			
			
			// render
			$side.html( html );
			
			
			// remove acf form data
			$side.find('.compat-field-acf-form-data').remove();
			
			
			// detach meta tr
			var $tr = $side.find('> .compat-attachment-fields > tbody > tr').detach();
			
			
			// add tr
			$side.find('> table.form-table > tbody').append( $tr );			
			
			
			// remove origional meta table
			$side.find('> .compat-attachment-fields').remove();
			
			
			// setup fields
			acf.do_action('append', $side);
			
		},
		
		update_attachment: function(){
			
			// vars
			var $a = this.$el.find('.update-attachment')
				$form = this.$el.find('.acf-gallery-side-data'),
				data = acf.serialize_form( $form );
				
				
			// validate
			if( $a.attr('disabled') ) {
			
				return false;
				
			}
			
			
			// add attr
			$a.attr('disabled', 'disabled');
			$a.before('<i class="acf-loading"></i>');
			
			
			// append AJAX action		
			data.action = 'acf/fields/gallery/update_attachment';
			
			
			// prepare for ajax
			acf.prepare_for_ajax(data);
			
			
			// ajax
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: data,
				type		: 'post',
				dataType	: 'json',
				complete	: function( json ){
					
					$a.removeAttr('disabled');
					$a.prev('.acf-loading').remove();
					
				}
			});
			
		},
		
		add : function( a ){
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				acf.validation.add_warning( this.$field, acf._e('gallery', 'max'));
				
				return;
				
			}
			
			
			// vars
			var thumb_url = a.url,
				thumb_class = 'acf-gallery-attachment acf-soh',
				filename = '',
				name = this.$el.find('[data-name="ids"]').attr('name');

			
			// title
			if( a.type !== 'image' && a.filename ) {
				
				filename = '<div class="filename">' + a.filename + '</div>';
				
			}
			
			
			// icon
			if( !thumb_url ) {
				
				thumb_url = a.icon;
				thumb_class += ' is-mime-icon';
				
			}
			
			
			// html
			var html = [
			'<div class="' + thumb_class + '" data-id="' + a.id + '">',
				'<input type="hidden" value="' + a.id + '" name="' + name + '[]">',
				'<div class="margin" title="' + a.filename + '">',
					'<div class="thumbnail">',
						'<img src="' + thumb_url + '">',
					'</div>',
					filename,
				'</div>',
				'<div class="actions acf-soh-target">',
					'<a href="#" class="acf-icon dark remove-attachment" data-id="' + a.id + '">',
						'<i class="acf-sprite-delete"></i>',
					'</a>',
				'</div>',
			'</div>'].join('');
			
			
			// append
			this.$el.find('.acf-gallery-attachments').append( html );
			
			
			// render
			this.render();
			
		},
		
		edit_attachment:function( e ){
			
			// reference
			var self = this;
			
			
			// vars
			var id = acf.get_data(e.$el, 'id');
			
			
			// popup
			var frame = acf.media.popup({
				
				title:		acf._e('image', 'edit'),
				button:		acf._e('image', 'update'),
				mode:		'edit',
				id:			id,
				select:		function( attachment ){
					
					// override url
					if( acf.isset(attachment, 'attributes', 'sizes', self.o.preview_size, 'url') ) {
			    	
				    	attachment.url = attachment.attributes.sizes[ self.o.preview_size ].url;
				    	
			    	}
			    	
			    	
			    	// update image
			    	self.get_attachment(id).find('img').attr( 'src', attachment.url );
				 	
				 	
				 	// render sidebar
					self.fetch( id );
					
				}
			});
						
		},
		
		remove_attachment: function( e ){
			
			// prevent event from triggering click on attachment
			e.stopPropagation();
			
			
			// vars
			var id = acf.get_data(e.$el, 'id');
			
			
			// deselect attachmnet
			this.clear_selection();
			
			
			// update sidebar
			this.close_sidebar();
			
			
			// remove image
			this.get_attachment(id).remove();
			
			
			// render
			this.render();
			
			
		},
		
		render_collection : function( frame ){
			
			var self = this;
			
			
			// Note: Need to find a differen 'on' event. Now that attachments load custom fields, this function can't rely on a timeout. Instead, hook into a render function foreach item
			
			// set timeout for 0, then it will always run last after the add event
			setTimeout(function(){
			
			
				// vars
				var $content	= frame.content.get().$el
					collection	= frame.content.get().collection || null;
					

				
				if( collection ) {
					
					var i = -1;
					
					collection.each(function( item ){
					
						i++;
						
						var $li = $content.find('.attachments > .attachment:eq(' + i + ')');
						
						
						// if image is already inside the gallery, disable it!
						if( self.get_attachment(item.id).exists() ) {
						
							item.off('selection:single');
							$li.addClass('acf-selected');
							
						}
						
					});
					
				}
			
			
			}, 10);

				
		},
		
		add_attachment: function( e ){
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				acf.validation.add_warning( this.$field, acf._e('gallery', 'max'));
				
				return;
				
			}
			
			
			// vars
			var preview_size = this.o.preview_size;
			
			
			// reference
			var self = this;
			
			
			// popup
			var frame = acf.media.popup({
				
				title:		acf._e('gallery', 'select'),
				mode:		'select',
				type:		'',
				field:		acf.get_field_key(this.$field),
				multiple:	'add',
				library:	this.o.library,
				mime_types: this.o.mime_types,
				
				select: function( attachment, i ) {
					
					// vars
					var atts = attachment.attributes;
					
					
					// is image already in gallery?
					if( self.get_attachment(atts.id).exists() ) {
					
						return;
						
					}
					
					//console.log( attachment );
			    	
			    	// vars
			    	var a = {
				    	id:			atts.id,
				    	type:		atts.type,
				    	icon:		atts.icon,
				    	filename:	atts.filename,
				    	url:		''
			    	};
			    	
			    	
			    	// type
			    	if( a.type === 'image' ) {
				    	
				    	a.url = acf.maybe_get(atts, 'sizes', preview_size, 'url') || atts.url;
				    	
			    	} else {
				    	
				    	a.url = acf.maybe_get(atts, 'thumb', 'src') || '';
				    	
				    }
				    
				    
			    	// add file to field
			        self.add( a );
					
				}
			});
			
			
			// modify DOM
			frame.on('content:activate:browse', function(){
				
				self.render_collection( frame );
				
				frame.content.get().collection.on( 'reset add', function(){
				    
					self.render_collection( frame );
				    
			    });
				
			});
			
		},
		
		resize : function(){
			
			// vars
			var min = 100,
				max = 175,
				columns = 4,
				width = this.$el.width();
			
			
			// get width
			for( var i = 0; i < 10; i++ ) {
			
				var w = width/i;
				
				if( min < w && w < max ) {
				
					columns = i;
					break;
					
				}
				
			}
						
			
			// update data
			this.$el.attr('data-columns', columns);
		}
		
	});
	
})(jQuery);

// @codekit-prepend "../js/acf-pro.js";
// @codekit-prepend "../js/acf-repeater.js";
// @codekit-prepend "../js/acf-flexible-content.js";
// @codekit-prepend "../js/acf-gallery.js";

