<?php
/**
 * Plugin Name: Simple Sticky Notice
 * Description: A customizable, fixed notice box for site-wide announcements.
 * Version: 1.1
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
        add_options_page('Sticky Notice', 'Sticky Notice', 'manage_options', 'ssn-settings', array($this, 'settings_page_html'));
    }

    public function register_plugin_settings() {
        // Content
        register_setting( 'ssn-group', 'ssn_text' );
        register_setting( 'ssn-group', 'ssn_url' );
        
        // Positioning
        register_setting( 'ssn-group', 'ssn_side', array('default' => 'left') );
        register_setting( 'ssn-group', 'ssn_vertical_pos', array('default' => '50') );
        register_setting( 'ssn-group', 'ssn_rotation', array('default' => '-90') );

        // Colors & Custom CSS
        register_setting( 'ssn-group', 'ssn_bg_color', array('default' => '#0073aa') );
        register_setting( 'ssn-group', 'ssn_text_color', array('default' => '#ffffff') );
        register_setting( 'ssn-group', 'ssn_custom_css' );
    }

    public function settings_page_html() {
        ?>
        <div class="wrap">
            <h1>Sticky Notice Configuration</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'ssn-group' ); ?>
                
                <h2>Content</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Notice Text</th>
                        <td><input type="text" name="ssn_text" value="<?php echo esc_attr(get_option('ssn_text')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Link URL</th>
                        <td><input type="url" name="ssn_url" value="<?php echo esc_attr(get_option('ssn_url')); ?>" class="regular-text" /></td>
                    </tr>
                </table>

                <h2>Appearance & Position</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Screen Side</th>
                        <td>
                            <select name="ssn_side">
                                <option value="left" <?php selected(get_option('ssn_side'), 'left'); ?>>Left</option>
                                <option value="right" <?php selected(get_option('ssn_side'), 'right'); ?>>Right</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Vertical Position (%)</th>
                        <td><input type="number" name="ssn_vertical_pos" value="<?php echo esc_attr(get_option('ssn_vertical_pos')); ?>" min="0" max="100" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Rotation (deg)</th>
                        <td><input type="number" name="ssn_rotation" value="<?php echo esc_attr(get_option('ssn_rotation')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Background Color</th>
                        <td><input type="color" name="ssn_bg_color" value="<?php echo esc_attr(get_option('ssn_bg_color')); ?>" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Text Color</th>
                        <td><input type="color" name="ssn_text_color" value="<?php echo esc_attr(get_option('ssn_text_color')); ?>" /></td>
                    </tr>
                </table>

                <h2>Advanced</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">Additional Custom CSS</th>
                        <td><textarea name="ssn_custom_css" rows="5" class="large-text"><?php echo esc_textarea(get_option('ssn_custom_css')); ?></textarea></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'ssn-base-style', plugins_url( '/style.css', __FILE__ ) );

        // Generate dynamic styles from settings
        $side     = get_option('ssn_side', 'left');
        $v_pos    = get_option('ssn_vertical_pos', '50');
        $rotation = get_option('ssn_rotation', '-90');
        $bg       = get_option('ssn_bg_color', '#0073aa');
        $color    = get_option('ssn_text_color', '#ffffff');
        $custom   = get_option('ssn_custom_css');

        $dynamic_css = "
            .ssn-box {
                $side: 0;
                top: {$v_pos}%;
                transform: rotate({$rotation}deg);
                transform-origin: $side center;
                background-color: $bg;
            }
            .ssn-box, .ssn-link { color: $color; }
            $custom
        ";
        wp_add_inline_style( 'ssn-base-style', $dynamic_css );
    }

    public function render_content_box() {
        $text = get_option('ssn_text');
        if ( empty($text) ) return;

        $url = get_option('ssn_url');
        echo '<div class="ssn-box">';
        echo !empty($url) ? '<a href="'.esc_url($url).'" class="ssn-link">'.esc_html($text).'</a>' : esc_html($text);
        echo '</div>';
    }
}
new SimpleStickyNotice();