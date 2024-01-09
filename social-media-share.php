<?php
/**
 * Plugin Name: Social Media Share
 * Description: Social Media Share plugin for WordPress, created for LibraFire test assignment.
 * Version: 1.0
 * Author: Milan Momcilovic
 **/


 
function social_media_share() {
    add_menu_page('Social Media Share', 'Social Media Share', 'manage_options', 'social-media-share', 'social_share_page', '', 200);
}
add_action('admin_menu', 'social_media_share');


?>