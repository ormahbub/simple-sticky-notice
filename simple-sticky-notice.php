<?php
/**
 * Plugin Name: Simple Sticky Notice
 * Description: High-performance fixed notice box with multi-side padding, individual corner radius, and hover effects.
 * Version: 1.5
 * Author: Mahbub
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class SimpleStickyNotice {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'create_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
        add_action( 'wp_footer', array( $this, 'render_content_box' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_footer', array( $this, 'admin_js' ) );
    }

    public function create_settings_page() {
        add_options_page('Sticky Notice Settings', 'Sticky Notice', 'manage_options', 'ssn-settings', array($this, 'settings_page_html'));
    }

    public function register_plugin_settings() {
        $settings = array(
            'ssn_text', 'ssn_url', 'ssn_side', 'ssn_vertical_pos', 'ssn_rotation', 'ssn_visible_offset',
            'ssn_bg_color', 'ssn_text_color', 'ssn_bg_hover_color', 'ssn_text_hover_color',
            'ssn_pt', 'ssn_pr', 'ssn_pb', 'ssn_pl', 'ssn_pad_lock',
            'ssn_rtl', 'ssn_rtr', 'ssn_rbr', 'ssn_rbl', 'ssn_rad_lock', // Radius settings
            'ssn_custom_css'
        );
        foreach($settings as $setting) {
            register_setting( 'ssn-group', $setting );
        }
    }

    public function settings_page_html() {
        $pad_lock = get_option('ssn_pad_lock', '0');
        $rad_lock = get_option('ssn_rad_lock', '0');
        ?>
        <div class="wrap">
            <h1>Sticky Notice Settings</h1>
            <form method="post" action="options.php" style="max-width: 850px;">
                <?php settings_fields( 'ssn-group' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Notice Text</th>
                        <td><input type="text" name="ssn_text" value="<?php echo esc_attr(get_option('ssn_text')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Link URL (Optional)</th>
                        <td><input type="url" name="ssn_url" value="<?php echo esc_attr(get_option('ssn_url')); ?>" class="regular-text" /></td>
                    </tr>

                    <tr><th colspan="2"><h3>Positioning</h3></th></tr>
                    <tr>
                        <th scope="row">Screen Side / Vertical %</th>
                        <td>
                            <select name="ssn_side">
                                <option value="left" <?php selected(get_option('ssn_side'), 'left'); ?>>Left</option>
                                <option value="right" <?php selected(get_option('ssn_side'), 'right'); ?>>Right</option>
                            </select>
                            &nbsp; Position: <input type="number" name="ssn_vertical_pos" value="<?php echo esc_attr(get_option('ssn_vertical_pos', '50')); ?>" style="width:60px" /> %
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Offset / Rotation</th>
                        <td>
                            Offset: <input type="number" name="ssn_visible_offset" value="<?php echo esc_attr(get_option('ssn_visible_offset', '20')); ?>" style="width:60px" /> px
                            &nbsp; Rotation: <input type="number" name="ssn_rotation" value="<?php echo esc_attr(get_option('ssn_rotation', '-90')); ?>" style="width:60px" /> deg
                        </td>
                    </tr>

                    <tr><th colspan="2"><h3>Padding & Corners</h3></th></tr>
                    <tr>
                        <th scope="row">Padding (T/R/B/L)</th>
                        <td>
                            <input type="number" name="ssn_pt" class="ssn-pad" value="<?php echo esc_attr(get_option('ssn_pt', '10')); ?>" style="width:50px" />
                            <input type="number" name="ssn_pr" class="ssn-pad" value="<?php echo esc_attr(get_option('ssn_pr', '20')); ?>" style="width:50px" />
                            <input type="number" name="ssn_pb" class="ssn-pad" value="<?php echo esc_attr(get_option('ssn_pb', '10')); ?>" style="width:50px" />
                            <input type="number" name="ssn_pl" class="ssn-pad" value="<?php echo esc_attr(get_option('ssn_pl', '20')); ?>" style="width:50px" />
                            <label><input type="checkbox" name="ssn_pad_lock" id="ssn_pad_lock" value="1" <?php checked($pad_lock, '1'); ?> /> <span class="dashicons dashicons-<?php echo $pad_lock ? 'lock' : 'unlock'; ?>"></span></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Border Radius (TL/TR/BR/BL)</th>
                        <td>
                            <input type="number" name="ssn_rtl" class="ssn-rad" value="<?php echo esc_attr(get_option('ssn_rtl', '4')); ?>" style="width:50px" />
                            <input type="number" name="ssn_rtr" class="ssn-rad" value="<?php echo esc_attr(get_option('ssn_rtr', '4')); ?>" style="width:50px" />
                            <input type="number" name="ssn_rbr" class="ssn-rad" value="<?php echo esc_attr(get_option('ssn_rbr', '4')); ?>" style="width:50px" />
                            <input type="number" name="ssn_rbl" class="ssn-rad" value="<?php echo esc_attr(get_option('ssn_rbl', '4')); ?>" style="width:50px" />
                            <label><input type="checkbox" name="ssn_rad_lock" id="ssn_rad_lock" value="1" <?php checked($rad_lock, '1'); ?> /> <span class="dashicons dashicons-<?php echo $rad_lock ? 'lock' : 'unlock'; ?>"></span></label>
                        </td>
                    </tr>

                    <tr><th colspan="2"><h3>Colors</h3></th></tr>
                    <tr>
                        <th scope="row">Default / Hover</th>
                        <td>
                            BG: <input type="color" name="ssn_bg_color" value="<?php echo esc_attr(get_option('ssn_bg_color', '#0073aa')); ?>" /> / <input type="color" name="ssn_bg_hover_color" value="<?php echo esc_attr(get_option('ssn_bg_hover_color', '#005177')); ?>" />
                            &nbsp; Text: <input type="color" name="ssn_text_color" value="<?php echo esc_attr(get_option('ssn_text_color', '#ffffff')); ?>" /> / <input type="color" name="ssn_text_hover_color" value="<?php echo esc_attr(get_option('ssn_text_hover_color', '#ffffff')); ?>" />
                        </td>
                    </tr>

                    <tr><th colspan="2"><h3>Advanced</h3></th></tr>
                    <tr>
                        <th scope="row">Custom CSS</th>
                        <td>
                            <textarea name="ssn_custom_css" rows="5" class="large-text"><?php echo esc_textarea(get_option('ssn_custom_css')); ?></textarea>
                            <p class="description" style="margin-top:10px; background:#f9f9f9; padding:10px; border-left:4px solid #0073aa;">
                                <strong>CSS Selector Note:</strong> Use <code>.ssn-box</code> for the container and <code>.ssn-link</code> for the text.
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function admin_js() {
        if (isset($_GET['page']) && $_GET['page'] == 'ssn-settings') {
            ?>
            <script>
                jQuery(document).ready(function($) {
                    // Padding Lock logic
                    $('.ssn-pad').on('input', function() {
                        if ($('#ssn_pad_lock').is(':checked')) { $('.ssn-pad').val($(this).val()); }
                    });
                    // Radius Lock logic
                    $('.ssn-rad').on('input', function() {
                        if ($('#ssn_rad_lock').is(':checked')) { $('.ssn-rad').val($(this).val()); }
                    });
                    // UI Lock Icons
                    $('#ssn_pad_lock, #ssn_rad_lock').change(function() {
                        $(this).next('.dashicons').toggleClass('dashicons-unlock dashicons-lock');
                    });
                });
            </script>
            <?php
        }
    }

    public function enqueue_assets() {
        wp_enqueue_style( 'ssn-base', plugins_url( '/style.css', __FILE__ ) );
        wp_enqueue_style( 'dashicons' );

        $side = get_option('ssn_side', 'left');
        $pos_css = ($side === 'left') ? "left: ".get_option('ssn_visible_offset', '20')."px;" : "right: ".get_option('ssn_visible_offset', '20')."px;";

        $dynamic_css = "
            .ssn-box {
                $pos_css
                top: ".get_option('ssn_vertical_pos', '50')."%;
                transform: rotate(".get_option('ssn_rotation', '-90')."deg);
                transform-origin: $side center;
                background-color: ".get_option('ssn_bg_color', '#0073aa').";
                color: ".get_option('ssn_text_color', '#ffffff').";
                padding: ".get_option('ssn_pt', '10')."px ".get_option('ssn_pr', '20')."px ".get_option('ssn_pb', '10')."px ".get_option('ssn_pl', '20')."px;
                border-radius: ".get_option('ssn_rtl', '4')."px ".get_option('ssn_rtr', '4')."px ".get_option('ssn_rbr', '4')."px ".get_option('ssn_rbl', '4')."px;
            }
            .ssn-box:hover { background-color: ".get_option('ssn_bg_hover_color', '#005177')." !important; color: ".get_option('ssn_text_hover_color', '#ffffff')." !important; }
            " . get_option('ssn_custom_css');
        
        wp_add_inline_style( 'ssn-base', $dynamic_css );
    }

    public function render_content_box() {
        $text = get_option('ssn_text');
        if ( empty($text) ) return;
        $url = get_option('ssn_url');
        echo '<div class="ssn-box">';
        echo !empty($url) ? '<a href="'.esc_url($url).'" class="ssn-link" style="color:inherit; text-decoration:none;">'.esc_html($text).'</a>' : esc_html($text);
        echo '</div>';
    }
}
new SimpleStickyNotice();