<?php
/**
 * Plugin Name: Social Media Share
 * Description: This is just an example plugin
 * Version: 1.0
 * Author: Milan Momcilovic
 **/

function enqueue_social_share_styles() {
    wp_enqueue_script('jquery');
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    $css_file = plugin_dir_path(__FILE__) . 'style.css';
    wp_enqueue_style('social-share-styles', plugins_url('style.css', __FILE__), array(), filemtime($css_file));
}

add_action('wp_enqueue_scripts', 'enqueue_social_share_styles');

function social_media_share() {
    add_menu_page('Social Media Share', 'Social Media Share', 'manage_options', 'social-media-share', 'social_share_page', '', 200);
}

add_action('admin_menu', 'social_media_share');
function social_share_page() {
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
    $plugin_title = get_admin_page_title();
    ?>
<div class="wrap">
    <h1><?php echo esc_html($plugin_title); ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="?page=social-media-share&tab=general"
            class="nav-tab <?php echo ($active_tab === 'general') ? 'nav-tab-active' : ''; ?>">General</a>
        <a href="?page=social-media-share&tab=style"
            class="nav-tab <?php echo ($active_tab === 'style') ? 'nav-tab-active' : ''; ?>">Style</a>
        <a href="?page=social-media-share&tab=display"
            class="nav-tab <?php echo ($active_tab === 'display') ? 'nav-tab-active' : ''; ?>">Display</a>
    </h2>

    <form method="post" action="options.php">
        <?php
            if ($active_tab === 'general') {
                settings_fields("social_share_config_section_general");
                do_settings_sections("social-share-general");
            } elseif ($active_tab === 'style') {
                settings_fields("social_share_config_section_style");
                do_settings_sections("social-share-style");
            } elseif ($active_tab === 'display') {
                settings_fields("social_share_config_section_display");
                do_settings_sections("social-share-display");
            }
            submit_button();
            ?>
    </form>
</div>
<?php
}
function social_share_settings() {
    add_settings_section("social_share_config_section_general", "General Settings", null, "social-share-general");

    $social_networks = array(
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'google' => 'Google+',
        'pinterest' => 'Pinterest',
        'linkedin' => 'LinkedIn',
        'whatsapp' => 'WhatsApp (Mobile only)'
    );

    foreach ($social_networks as $network_key => $network_label) {
        add_settings_field(
            "social-share-{$network_key}",
            $network_label,
            "social_share_checkbox",
            "social-share-general",
            "social_share_config_section_general",
            $network_key
        );
        register_setting("social_share_config_section_general", "social-share-{$network_key}");
    }

    add_settings_section("social_share_button_size_section", "Button Sizes", null, "social-share-style");
    add_settings_field("social-share-button-size", "Button Size", "social_share_button_size_dropdown", "social-share-style", "social_share_button_size_section");
    register_setting("social_share_config_section_style", "social-share-button-size");

    add_settings_section("social_share_button_color_section", "Button Colors", null, "social-share-style");
    register_setting('social_share_config_section_style', 'social_share_button_colors');

    $social_networks = array(
        'facebook' => 'Facebook',
        'twitter' => 'Twitter',
        'google' => 'Google+',
        'pinterest' => 'Pinterest',
        'linkedin' => 'LinkedIn',
        'whatsapp' => 'WhatsApp (Mobile only)'
    );

    foreach ($social_networks as $network_key => $network_label) {
        add_settings_field(
            "social-share-button-color-$network_key",
            "$network_label Color",
            "display_button_color_field",
            "social-share-style",
            "social_share_button_color_section",
            array('network_key' => $network_key)
        );
        register_setting("social_share_config_section_style", "social-share-button-color-$network_key");
    }

    add_settings_section("social_share_display_position_section", "Display Position", null, "social-share-display");
    add_settings_field("social-share-display-position", "Select Display Position", "social_share_display_position_dropdown", "social-share-display", "social_share_display_position_section");
    register_setting("social_share_config_section_display", "social-share-display-position");

    add_settings_section("social_share_display_on_section", "Display On", null, "social-share-display");

    add_settings_field("social-share-display-post", "Posts", "social_share_display_checkbox", "social-share-display", "social_share_display_on_section", 'display-post');
    register_setting("social_share_config_section_display", "social-share-display-post");

    add_settings_field("social-share-display-page", "Pages", "social_share_display_checkbox", "social-share-display", "social_share_display_on_section", 'display-page');
    register_setting("social_share_config_section_display", "social-share-display-page");

    $custom_post_types = get_post_types(array('_builtin' => false));
    if ($custom_post_types) {
        foreach ($custom_post_types as $post_type) {
            add_settings_field("social-share-display-{$post_type}", ucfirst($post_type), "social_share_display_checkbox", "social-share-display", "social_share_display_on_section", "display-{$post_type}");
            register_setting("social_share_config_section_display", "social-share-display-{$post_type}");
        }
    }
}

add_action("admin_init", "social_share_settings");

function social_share_icons_shortcode() {
    global $post;

    $url = esc_url(get_permalink($post->ID));
    $social_media_links = generate_social_share_links($url);

    return $social_media_links;
}

add_shortcode('social_share_icons', 'social_share_icons_shortcode');

function display_button_color_field($args) {
    $network_key = $args['network_key'];
    $button_colors = get_option("social_share_button_colors");
    $original_checked = isset($button_colors[$network_key]['original']) ? checked(1, $button_colors[$network_key]['original'], false) : '';
    $color_value = isset($button_colors[$network_key]['color']) ? esc_attr($button_colors[$network_key]['color']) : '';
    ?>
<label>
    <input type='checkbox' name='social_share_button_colors[<?php echo esc_attr($network_key); ?>][original]' value='1'
        <?php echo $original_checked; ?> />
    Original
</label>
<label>
    <input type='color' class='social-share-color-picker'
        name='social_share_button_colors[<?php echo esc_attr($network_key); ?>][color]'
        value='<?php echo esc_attr($color_value); ?>' />
    Choose color
</label>
<?php
}

function social_share_button_size_dropdown() {
    $option_name = "social-share-button-size";
    $selected = get_option($option_name);
    ?>
<select name="<?php echo esc_attr($option_name); ?>">
    <option value="small" <?php selected('small', $selected); ?>>Small</option>
    <option value="medium" <?php selected('medium', $selected); ?>>Medium</option>
    <option value="large" <?php selected('large', $selected); ?>>Large</option>
</select>
<?php
}

function social_share_checkbox($args) {
    $option_name = "social-share-{$args}";
    ?>
<input type="checkbox" name="<?php echo esc_attr($option_name); ?>" value="1"
    <?php checked(1, get_option($option_name), true); ?> />
<?php
}

function social_share_display_checkbox($args) {
    $option_name = "social-share-{$args}";
    ?>
<input type="checkbox" name="<?php echo esc_attr($option_name); ?>" value="1"
    <?php checked(1, get_option($option_name), true); ?> />
<?php
}

function social_share_display_position_dropdown() {
    $option_name = "social-share-display-position";
    $selected = get_option($option_name);
    ?>
<select name="<?php echo esc_attr($option_name); ?>">
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
    $button_colors = get_option("social_share_button_colors");
    $social_networks = array(
        'facebook' => 'http://www.facebook.com/sharer.php?url=',
        'twitter' => 'https://twitter.com/share?url=',
        'google' => 'https://plus.google.com/share?url=',
        'pinterest' => 'http://pinterest.com/pin/create/button/?url=',
        'linkedin' => 'http://www.linkedin.com/shareArticle?url=',
        'whatsapp' => 'https://api.whatsapp.com/send?text='
    );

    $html = "<div class='social-share-wrapper'>";
    foreach ($social_networks as $network_key => $network_url) {
        $button_color = '';

         if ($network_key === 'whatsapp' && !wp_is_mobile()) {
            continue;
        }

        if (get_option("social-share-{$network_key}") == 1) {
            if (isset($button_colors[$network_key]['original']) && $button_colors[$network_key]['original'] == 1) {
                $button_color = '';
            } elseif (isset($button_colors[$network_key]['color'])) {
                $button_color = "style='background-color: {$button_colors[$network_key]['color']}'";
            }

            $html .= "<div class='{$network_key}'><a class=' {$button_size}' target='_blank' href='{$network_url}{$url}' {$button_color}>" . ucfirst($network_key) . "</a></div>";
        }
    }

    $html .= "<div class='clear'></div></div>";

    return $html;
}

function add_social_share_icons($content) {
    global $post;

    $url = esc_url(get_permalink($post->ID));
    $social_media_links = generate_social_share_links($url);

    $displayed_content = ''; 

    $display_on_posts = get_option('social-share-display-post');
    $display_on_pages = get_option('social-share-display-page');
    
    if (is_singular('post') && $display_on_posts) {
        $displayed_content .= $social_media_links;
    } elseif (is_singular('page') && $display_on_pages) {
        $displayed_content .= $social_media_links;
    } elseif (is_singular()) {
        $custom_post_types = get_post_types(['_builtin' => false]);
        if (isset($custom_post_types) && is_array($custom_post_types)) {
            foreach ($custom_post_types as $post_type) {
                $display_custom = get_option("social-share-display-{$post_type}");
                if (is_singular($post_type) && $display_custom) {
                    $displayed_content .= $social_media_links;
                    break; 
                }
            }
        }
    }
    
    $display_position = get_option('social-share-display-position');

    switch ($display_position) {
        case 'below-title':
            $content = $displayed_content . $content;
            break;
        case 'after-content':
            $content .= $displayed_content;
            break;
            case 'floating-left':
                $content .= $displayed_content . '<script>
                                jQuery(document).ready(function($) {
                                    $(".social-share-wrapper").css({
                                        "position": "fixed",
                                        "left": "0",
                                        "top": "50%",
                                        "transform": "translateY(-50%)",
                                        "display": "flex",
                                        "flex-direction": "column",
                                        "align-items": "center",
                                    });
                                });
                            </script>';
                break;
                case 'inside-featured-image':
                    $content .= $displayed_content . '<script>
                                    jQuery(document).ready(function($) {
                                        $("img.wp-post-image").css({
                                            "position": "relative",
                                        });
                                        $(".social-share-wrapper").css({
                                            "position": "absolute",
                                            "top": "50%",
                                            "opacity": "0.5",
                                            "left": "50%",
                                            "transform": "translate(-50%, -50%)",
                                        });
                                    });
                                </script>';
                    break;
        default:
            break;
    }

        if (strpos($content, '[social_share_icons]') !== false) {
            $content = str_replace('[social_share_icons]', social_share_icons_shortcode(), $content);
        }

    return $content;
}

add_filter("the_content", "add_social_share_icons");

function initialize_color_picker_script() {
    ?>
<script>
jQuery(document).ready(function($) {
    $('.social-share-color-picker').wpColorPicker();
});
</script>
<?php
}

add_action('admin_footer', 'initialize_color_picker_script');
?>