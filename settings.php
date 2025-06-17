<?php
// Add these settings to the $settings array in admin/settings.php
$settings['maintenance_mode'] = isset($settings['maintenance_mode']) ? $settings['maintenance_mode'] : 0;
$settings['maintenance_message'] = isset($settings['maintenance_message']) ? $settings['maintenance_message'] : 'We are currently performing maintenance. Please check back soon.';

// Add these to the $newSettings array in the form submission handler
$newSettings['maintenance_mode'] = isset($_POST['maintenance_mode']) ? 1 : 0;
$newSettings['maintenance_message'] = clean($_POST['maintenance_message']);

// Add this HTML to the settings form, before the closing </form> tag
?>

<!-- Maintenance Settings -->
<div class="card settings-card">
    <div class="card-header">
        <h5 class="mb-0">Maintenance Mode</h5>
    </div>
    <div class="card-body">
        <div class="form-group">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="maintenance_mode" name="maintenance_mode" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                <label class="custom-control-label" for="maintenance_mode">Enable Maintenance Mode</label>
            </div>
            <small class="form-text text-muted">When enabled, only administrators can access the site. All other visitors will see a maintenance message.</small>
        </div>
        
        <div class="form-group">
            <label for="maintenance_message">Maintenance Message</label>
            <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="3"><?php echo $settings['maintenance_message']; ?></textarea>
            <small class="form-text text-muted">Message to display to visitors during maintenance.</small>
        </div>
    </div>
</div>
