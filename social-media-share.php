<?php
/**
 * Plugin Name: Social Media Share
 * Description: Social Media Share plugin for WordPress, created for LibraFire test assignment.
 * Version: 1.0
 * Author: Milan Momcilovic
 **/


// Sidebar Menu - Social Media Share 
function social_media_share() {
    add_menu_page('Social Media Share', 'Social Media Share', 'manage_options', 'social-media-share', 'social_share_page', '', 200);
}
add_action('admin_menu', 'social_media_share');

// Social Media Share Page
function social_share_page() {
    $plugin_title = get_admin_page_title();
    ?>
<div class="wrap">
    <h1><?php print $plugin_title; ?></h1>
    <form method="post" action="options.php">
        <?php
            settings_fields("social_share_config_section");
            submit_button();
            ?>
    </form>
</div>
<?php
}



?>