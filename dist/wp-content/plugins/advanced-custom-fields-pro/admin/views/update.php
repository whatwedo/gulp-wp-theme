<?php 

// vars
$updates = acf_extract_var( $args, 'updates');

?>
<div id="acf-upgrade-wrap" class="wrap">
	
	<h2><?php _e("Data Upgrade",'acf'); ?></h2>
	
	<?php if( !empty($updates) ): ?>
		
		<p><?php _e('Reading upgrade tasks...', 'acf'); ?></p>
		
		<ul class="bl acf-update-list" style="display:none;">
			
			<?php foreach( $updates as $update ): ?>
				
				<li data-version="<?php echo $update; ?>"><?php printf(__('Upgrading data to version %s', 'acf'), $update); ?> <i class="acf-loading"></i></li>
				
			<?php endforeach; ?>
			
		</ul>
		
		<p class="show-on-complete"><?php _e('Data upgraded successfully.', 'acf'); ?></p>
		
		<p class="show-on-complete"><a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-settings-info'); ?>"><?php _e("See what's new",'acf'); ?></a></p>
	
	<?php else: ?>
	
		<p><?php _e('Data is at the latest version.', 'acf'); ?></p>
		
	<?php endif; ?>
	
	<style type="text/css">
		
		/* hide upgrade notice */
		.acf-update-notice {
			display: none;
		}
		
		/* hide show */
		.show-on-complete {
			display: none;
		}
		
		.acf-update-list {
			
		}
		
		.acf-update-list li {
			opacity: 0.5;
		}
		
		.acf-update-list li .acf-loading {
			visibility: hidden;
		}
		
		.acf-update-list li.active,
		.acf-update-list li.complete {
			opacity: 1;
		}
		
		.acf-update-list li.active .acf-loading {
			visibility: visible
		}
		
		
	</style>
	
	<script type="text/javascript">
	(function($) {
		
		var acf_upgrade = {
			
			$el : null,
			versions : [],
			
			init : function(){
				
				// reference
				var _this = this;
				
				
				// $el
				this.$el = $('#acf-upgrade-wrap');
				
				
				// versions
				this.$el.find('.acf-update-list li').each(function(){
					
					_this.versions.push( $(this).attr('data-version') );
					
				});
				
				
				// allow user to read message for 1 second
				setTimeout(function(){
					
					// show tasks
					_this.$el.find('.acf-update-list').show();
					
					_this.upgrade();
					
				}, 1000);
				
			},
			
			upgrade : function(){
				
				// reference
				var _this = this;
				
				
				// bail early if no versions left
				if( this.versions.length == 0 )
				{
					this.complete();
					return;
				}
				
				
				// get version
				var version = this.versions.shift();
				
				
				// $el
				var $li = this.$el.find('.acf-update-list li[data-version="' + version + '"]');
				
					
				// vars
				var data = {
					action		: 'acf/admin/data_upgrade',
					version		: version,
					nonce		: "<?php echo wp_create_nonce( 'acf_nonce' ); ?>",
				};
				
				
				// add loading
				$li.addClass('active');
				
				
				// get results
			    var xhr = $.ajax({
			    	url			: "<?php echo admin_url( 'admin-ajax.php' ); ?>",
					dataType	: 'json',
					type		: 'post',
					data		: data,
					success : function( json ){
						
						$li.removeClass('active').addClass('complete');
						
						if( json.feedback )
						{
							$li.append('<pre>' + json.feedback +  '</pre>');
						}
						
						_this.upgrade();
						
					},
				});
				
			},
			
			complete : function(){
				
				this.$el.find('.show-on-complete').show();
				
			}
			
		};
			
		
		$(document).ready(function(){
			
			acf_upgrade.init();
			
		});
		
	})(jQuery);	
	</script>
	
</div>
