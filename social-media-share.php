<?php
/**
 * Plugin Name: Social Media Share
 * Description: This is just an example plugin
 * Version: 1.0
 * Author: Milan Momcilovic
 **/


// Style and script enqueues
function enqueue_social_share_styles() {
    wp_enqueue_style('social-share-styles', plugins_url('style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'enqueue_social_share_styles');

// Sidebar menu item
function social_media_share() {
    add_menu_page('Social Media Share', 'Social Media Share', 'manage_options', 'social-media-share', 'social_share_page', '', 200);
}
add_action('admin_menu', 'social_media_share');


// Settings page
function social_share_page() {
    $plugin_title = get_admin_page_title();
    ?>
<div class="wrap">
    <h1><?php print $plugin_title; ?></h1>
    <form method="post" action="options.php">
        <?php
            settings_fields("social_share_config_section");
            do_settings_sections("social-share");
            do_settings_sections("social-share-display");
            submit_button();
            ?>
    </form>
</div>
<?php
}


// Settings page sections and fields
function social_share_settings() {
    $custom_post_types = get_post_types(array('_builtin' => false));
    $social_networks = array(
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'google' => 'Google+',
        'pinterest' => 'Pinterest',
        'linkedin' => 'LinkedIn',
        'whatsapp' => 'WhatsApp (Mobile only)'
    );

    // Social media options
    add_settings_section("social_share_config_section", "Social Share Settings", null, "social-share");
    
    foreach ($social_networks as $network_key => $network_label) {
        add_settings_field("social-share-{$network_key}", $network_label, "social_share_checkbox", "social-share", "social_share_config_section", $network_key);
        register_setting("social_share_config_section", "social-share-{$network_key}");
    }

    // Display settings
    add_settings_section("social_share_display_section", "Display", null, "social-share-display");
    
    add_settings_field("social-share-display-post", "Posts", "social_share_display_checkbox", "social-share-display", "social_share_display_section", 'display-post');
    register_setting("social_share_config_section", "social-share-display-post");

    add_settings_field("social-share-display-page", "Pages", "social_share_display_checkbox", "social-share-display", "social_share_display_section", 'display-page');
    register_setting("social_share_config_section", "social-share-display-page");

    if ($custom_post_types) {
        foreach ($custom_post_types as $post_type) {
            add_settings_field("social-share-display-{$post_type}", ucfirst($post_type), "social_share_display_checkbox", "social-share-display", "social_share_display_section", "display-{$post_type}");
            register_setting("social_share_config_section", "social-share-display-{$post_type}");
        }
    }
    
    // Button size seciton
    add_settings_section("social_share_button_size_section", "Button Sizes", null, "social-share");
    add_settings_field("social-share-button-size", "Button Size", "social_share_button_size_dropdown", "social-share", "social_share_button_size_section");
    register_setting("social_share_config_section", "social-share-button-size");


    // Display position
    add_settings_section("social_share_display_position_section", "Display Position", null, "social-share");
    add_settings_field("social-share-display-position", "Select Display Position", "social_share_display_position_dropdown", "social-share", "social_share_display_position_section");
    register_setting("social_share_config_section", "social-share-display-position");
}
add_action("admin_init", "social_share_settings");


function social_share_button_size_dropdown() {
    $option_name = "social-share-button-size";
    $selected = get_option($option_name);
    ?>
<select name="<?php echo $option_name; ?>">
    <option value="small" <?php selected('small', $selected); ?>>Small</option>
    <option value="medium" <?php selected('medium', $selected); ?>>Medium</option>
    <option value="large" <?php selected('large', $selected); ?>>Large</option>
</select>
<?php
}

function social_share_checkbox($args) {
    $option_name = "social-share-{$args}";
    ?>
<input type="checkbox" name="<?php echo $option_name; ?>" value="1"
    <?php checked(1, get_option($option_name), true); ?> />
<?php
}

function social_share_display_checkbox($args) {
    $option_name = "social-share-{$args}";
    ?>
<input type="checkbox" name="<?php echo $option_name; ?>" value="1"
    <?php checked(1, get_option($option_name), true); ?> />
<?php
}

function social_share_display_position_dropdown() {
    $option_name = "social-share-display-position";
    $selected = get_option($option_name);
    ?>
<select name="<?php echo $option_name; ?>">
    <option value="below-title" <?php selected('below-title', $selected); ?>>Below Post Title</option>
    <option value="floating-left" <?php selected('floating-left', $selected); ?>>Floating on the Left</option>
    <option value="after-content" <?php selected('after-content', $selected); ?>>After Post Content</option>
    <option value="inside-featured-image" <?php selected('inside-featured-image', $selected); ?>>Inside Featured Image
    </option>
</select>
<?php
}

function generate_social_share_links($url) {
    $button_size = get_option("social-share-button-size");

    $social_networks = array(
        'facebook' => 'http://www.facebook.com/sharer.php?url=',
        'twitter' => 'https://twitter.com/share?url=',
        'google' => 'https://plus.google.com/share?url=',
        'pinterest' => 'http://pinterest.com/pin/create/button/?url=',
        'linkedin' => 'http://www.linkedin.com/shareArticle?url=',
        'whatsapp' => 'https://api.whatsapp.com/send?text='
    );

    $html = "<div class='social-share-wrapper  {$button_size}'><div class='share-on'>Share on: </div>";

    foreach ($social_networks as $network_key => $network_url) {
        if (get_option("social-share-{$network_key}") == 1) {
            $html .= "<div class='{$network_key}'><a class='{$button_size}' target='_blank' href='{$network_url}{$url}'>" . ucfirst($network_key) . "</a></div>";
        }
    }

    $html .= "<div class='clear'></div></div>";

    return $html;
}
// function add_social_share_icons($content) {
//     global $post;

//     $url = esc_url(get_permalink($post->ID));
//     $social_media_links = generate_social_share_links($url);

//     $display_on_posts = get_option('social-share-display-post');
//     $display_on_pages = get_option('social-share-display-page');
//     $displayed_content = '';

//     // Check if it's a singular post type and display is enabled for that post type
//     if (is_singular('post') && $display_on_posts) {
//         $displayed_content .= $social_media_links;
//     } elseif (is_singular('page') && $display_on_pages) {
//         $displayed_content .= $social_media_links;
//     } elseif ($custom_post_types = get_post_types(['_builtin' => false])) {
//         // Loop through custom post types to check and display if enabled
//         foreach ($custom_post_types as $post_type) {
//             $display_custom = get_option("social-share-display-{$post_type}");
//             if (is_singular($post_type) && $display_custom) {
//                 $displayed_content .= $social_media_links;
//                 break; // Display only once for a custom post type
//             }
//         }
//     }

//     // Append the displayed content to the original content
//     if (!empty($displayed_content)) {
//         $content .= $displayed_content;
//     }

//     return $content;
// }

function add_social_share_icons($content) {
    global $post;

    $url = esc_url(get_permalink($post->ID));
    $social_media_links = generate_social_share_links($url);

    $displayed_content = ''; // Variable to store the displayed content

    $display_on_posts = get_option('social-share-display-post');
    $display_on_pages = get_option('social-share-display-page');

    if (is_singular('post') && $display_on_posts) {
        $displayed_content .= $social_media_links;
    } elseif (is_singular('page') && $display_on_pages) {
        $displayed_content .= $social_media_links;
    } elseif (is_singular() ) {
        $custom_post_types = get_post_types(['_builtin' => false]);
        if (isset($custom_post_types) && is_array($custom_post_types)) {
            // Loop through custom post types to check and display if enabled
            foreach ($custom_post_types as $post_type) {
                $display_custom = get_option("social-share-display-{$post_type}");
                if (is_singular($post_type) && $display_custom) {
                    $displayed_content .= $social_media_links;
                    break; // Display only once for a custom post type
                }
            }
        }
    }

    $display_position = get_option('social-share-display-position');

    // Append the social links based on the selected display position
    switch ($display_position) {
        case 'below-title':
            $content = $displayed_content . $content;
            break;
        case 'after-content':
            $content .= $displayed_content;
            break;
        case 'floating-left':
            $content .= $displayed_content;
            break;
        case 'inside-featured-image':
            $content = $displayed_content . $content;
            break;
        default:
            break;
    }

    return $content;
}

add_filter("the_content", "add_social_share_icons");
?>