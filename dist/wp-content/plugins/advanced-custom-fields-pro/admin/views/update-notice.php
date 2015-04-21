<?php 

// extract args
extract( $args );

$nonce1 = wp_create_nonce( 'upgrade-plugin_' . $basename );
$nonce2 = wp_create_nonce( 'rollback-acf_' . $rollback );

?>
<div id="acf-upgrade-notice" class="acf-cf">
	
	<div class="inner">
		
		<div class="acf-icon logo">
			<i class="acf-sprite-logo"></i>
		</div>
		
		<div class="content">
			
		<?php if( !$pro && !empty($addons) ): ?>
				
			<h2><?php _e("ACF PRO Required",'acf'); ?></h2>
			
			<p><?php printf(__("Thank you for updating to %s v%s!", 'acf'), acf_get_setting('name'), $version ); ?><br />
			 
			<?php printf(__("We have detected an issue which requires your attention: This website makes use of premium add-ons (%s) which are no longer compatible with ACF.", 'acf'), implode(', ', $addons) ); ?></p>
			
			<p><?php printf(__("Don't panic, you can simply roll back the plugin and continue using ACF as you know it!", 'acf') ); ?></p>
			
			<p><a id="acf-notice-action" href="<?php echo admin_url('update.php?action=upgrade-plugin&plugin=' . $basename . '&_wpnonce=' . $nonce1 . '&_acfrollback=' . $nonce2 ); ?>" class="acf-button blue"><?php printf(__("Roll back to ACF v%s", 'acf'), $rollback ); ?></a> <a id="acf-notice-action" href="#" class="acf-button"><?php _e("Learn why ACF PRO is required for my site", 'acf'); ?></a></p>
			
		<?php else: ?>	
				
			<h2><?php _e("Database Upgrade Required",'acf'); ?></h2>
			
			<p><?php printf(__("Thank you for updating to %s v%s!", 'acf'), acf_get_setting('name'), $version ); ?><br /><?php _e("Before you start using the new awesome features, please update your database to the newest version.", 'acf'); ?></p>
			
			<p><a id="acf-notice-action" href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-upgrade'); ?>" class="acf-button blue"><?php _e("Update Database", 'acf'); ?></a></p>	
			
			<script type="text/javascript">
			(function($) {
				
				$("#acf-notice-action").on("click", function(){
			
					var answer = confirm("<?php _e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'acf' ); ?>");
					return answer;
			
				});
				
			})(jQuery);
			</script>
			
		<?php endif; ?>
			
		</div>
	</div>
	
</div>
