<div class="wrap">
<?php screen_icon(); ?>
    <h2>Octoprint for Wordpress</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('octoprint-settings-group'); ?>
        <?php @do_settings_fields('octoprint-settings-group'); ?>

        <?php do_settings_sections('octoprint'); ?>

        <?php @submit_button(); ?>
    </form>
</div>
