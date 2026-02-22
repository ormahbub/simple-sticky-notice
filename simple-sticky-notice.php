<?php
/**
 * Plugin Name: Simple Sticky Notice
 * Description: Fully customizable fixed notice box with positioning and styling controls.
 * Version: 1.2
 * Author: Mahbub
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SimpleStickyNotice {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'create_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
        add_action( 'wp_footer', array( $this, 'render_content_box' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function create_settings_page() {
        add_options_page('Sticky Notice Settings', 'Sticky Notice', 'manage_options', 'ssn-settings', array($this, 'settings_page_html'));
    }

    public function register_plugin_settings() {
        $settings = array(
            'ssn_text', 'ssn_url', 'ssn_side', 'ssn_vertical_pos', 
            'ssn_rotation', 'ssn_bg_color', 'ssn_text_color', 
            'ssn_border_radius', 'ssn_side_offset', 'ssn_visible_offset', 'ssn_custom_css'
        );
        foreach($settings as $setting) {
            register_setting( 'ssn-group', $setting );
        }
    }

    public function settings_page_html() {
        ?>
        <div class="wrap">
            <h1>Sticky Notice Settings</h1>
            <form method="post" action="options.php" style="max-width: 800px;">
                <?php settings_fields( 'ssn-group' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Notice Text</th>
                        <td><input type="text" name="ssn_text" value="<?php echo esc_attr(get_option('ssn_text')); ?>" class="regular-text" placeholder="Limited Offer!" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Link URL (Optional)</th>
                        <td><input type="url" name="ssn_url" value="<?php echo esc_attr(get_option('ssn_url')); ?>" class="regular-text" placeholder="https://..." /></td>
                    </tr>
                    <tr><td colspan="2"><hr></td></tr>
                    <tr>
                        <th scope="row">Screen Side</th>
                        <td>
                            <select name="ssn_side">
                                <option value="left" <?php selected(get_option('ssn_side'), 'left'); ?>>Left Side</option>
                                <option value="right" <?php selected(get_option('ssn_side'), 'right'); ?>>Right Side</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Vertical Position (%)</th>
                        <td><input type="number" name="ssn_vertical_pos" value="<?php echo esc_attr(get_option('ssn_vertical_pos', '50')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Visible Offset (px)</th>
                        <td>
                            <input type="number" name="ssn_visible_offset" value="<?php echo esc_attr(get_option('ssn_visible_offset', '20')); ?>" />
                            <p class="description">How far it sticks out from the edge (preventing it from being hidden).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Rotation (deg)</th>
                        <td><input type="number" name="ssn_rotation" value="<?php echo esc_attr(get_option('ssn_rotation', '-90')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Border Radius (px)</th>
                        <td><input type="number" name="ssn_border_radius" value="<?php echo esc_attr(get_option('ssn_border_radius', '4')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Background Color</th>
                        <td><input type="color" name="ssn_bg_color" value="<?php echo esc_attr(get_option('ssn_bg_color', '#0073aa')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Text Color</th>
                        <td><input type="color" name="ssn_text_color" value="<?php echo esc_attr(get_option('ssn_text_color', '#ffffff')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Custom CSS</th>
                        <td><textarea name="ssn_custom_css" rows="4" class="large-text"><?php echo esc_textarea(get_option('ssn_custom_css')); ?></textarea></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'ssn-base', plugins_url( '/style.css', __FILE__ ) );

        $side     = get_option('ssn_side', 'left');
        $v_pos    = get_option('ssn_vertical_pos', '50');
        $vis_off  = get_option('ssn_visible_offset', '20');
        $rot      = get_option('ssn_rotation', '-90');
        $radius   = get_option('ssn_border_radius', '4');
        $bg       = get_option('ssn_bg_color', '#0073aa');
        $color    = get_option('ssn_text_color', '#ffffff');
        $custom   = get_option('ssn_custom_css');

        // Logic to push it out from the edge based on user input
        $pos_css = ($side === 'left') ? "left: {$vis_off}px;" : "right: {$vis_off}px;";

        $dynamic_css = "
            .ssn-box {
                $pos_css
                top: {$v_pos}%;
                transform: rotate({$rot}deg);
                transform-origin: $side center;
                background-color: $bg;
                border-radius: {$radius}px;
            }
            .ssn-box, .ssn-link { color: $color; }
            $custom
        ";
        wp_add_inline_style( 'ssn-base', $dynamic_css );
    }

    public function render_content_box() {
        $text = get_option('ssn_text');
        if ( empty($text) ) return;

        $url = get_option('ssn_url');
        echo '<div class="ssn-box">';
        if ( ! empty($url) ) {
            echo '<a href="'.esc_url($url).'" class="ssn-link">'.esc_html($text).'</a>';
        } else {
            echo esc_html($text);
        }
        echo '</div>';
    }
}
new SimpleStickyNotice();