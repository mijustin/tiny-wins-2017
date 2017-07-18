  
  </td>
</table>
<table style="margin-top: -20px;">
  <tbody>
    <tr>
      <td>
        <div class="wpcomplete-auto-attend-container">
          <input type="hidden" name="<?php echo $this->plugin_name . '_show_widget'; ?>" value="false">
          <label>
            <input type="checkbox" name="<?php echo $this->plugin_name . '_show_widget'; ?>" id="<?php echo $this->plugin_name . '_show_widget'; ?>" value="true" <?php checked( 'true', $is_enabled ); ?>> 
            <?php echo __("Show statistics widget on dashboard.", $this->plugin_name); ?>
          </label>
        </div>
