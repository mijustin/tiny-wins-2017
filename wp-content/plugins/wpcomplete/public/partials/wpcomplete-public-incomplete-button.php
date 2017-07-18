<a href="<?php echo admin_url( 'admin-ajax.php?action=mark_completed&button=' . $unique_button_id ); ?>" class="wpc-button wpc-button-<?php echo $this->get_button_class( $unique_button_id ); ?> wpc-button-complete wpc-complete" data-button="<?php echo $unique_button_id; ?>" data-button-text="<?php echo $completed_button_text; ?>">
  <span class="wpc-inactive"><?php echo $button_text; ?></span>
  <span class="wpc-active"><?php echo get_option($this->plugin_name . '_incomplete_active_text', 'Saving...'); ?></span>
</a>
