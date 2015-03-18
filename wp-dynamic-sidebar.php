<?php
/**
* Plugin Name: WP Dynamic Sidebar
* Plugin URI: http://wordpress.org/plugins/wp-dynamic-sidebar
* Description: Allows you to create widget areas easily and dynamically. You also can use shortcodes for each widget in posts or pages.
* Version: 1.0.5
* Author: Prayas Sapkota
* Author URI: http://prayas-sapkota.com.np
* License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
if (!defined('ABSPATH'))
    exit('Restricted Access');

$plugin = plugin_basename(__FILE__);

global $wp_sidebar;
$wp_sidebar_version = "1.0.5";

register_activation_hook(__FILE__, 'wp_dynamic_sidebar_install');

add_action('admin_menu', 'wp_dynamic_sidebar_actions');

add_action( 'admin_print_scripts', 'wp_dynamic_sidebar_admin_scripts');

add_action( 'admin_print_styles', 'wp_dynamic_sidebar_admin_styles');

add_action( 'widgets_init', 'wp_dynamic_sidebar_generate' );

function wp_dynamic_sidebar_actions() {
    add_submenu_page("options-general.php", "WP Dynamic Sidebar", "WP Dynamic Sidebar", 'manage_options', "wp-dynamic-sidebar.php", "wp_dynamic_sidebar_func");
}

function wp_dynamic_sidebar_admin_scripts() {
    wp_register_script('sidebar-scripts', plugins_url( '/js/scripts.js' , __FILE__ ), array('jquery'), '1.0', true);
    wp_enqueue_script('sidebar-scripts');
}

function wp_dynamic_sidebar_admin_styles() {
    wp_enqueue_style('sidebar-styles', plugins_url( '/css/style.css' , __FILE__ ), false, false, 'screen');
}

function wp_dynamic_sidebar_func() {
    ?>
    <div class="wrap wrap-dynamic-sidebar">
        <div id="icon-tools" class="icon32"><br></div>
        <h2>WP Dynamic Sidebar</h2>
        <p>Here you can create widget areas. Also you can use shortcodes for each widget in posts or pages.</p>
        <div class="col-1">
            <h3>Add New Sidebar</h3>
            <form id="new-sidebar">
                <p>
                    <label for="sidebar_name">Sidebar Name <span>*</span></label>
                    <input type="text" class="text-box" placeholder="Unique Sidebar Name" id="sidebar_name" name="sidebar_name" />
                </p>
                <p>
                    <label for="sidebar_desc">Description <span>*</span></label>
                    <textarea class="text-area" placeholder="Sidebar Description" id="sidebar_desc" name="sidebar_desc"></textarea>
                </p>
                <p>
                    <label for="sidebar_class">Class</label>
                    <input type="text" class="text-box" placeholder="CSS Class Name" id="sidebar_class" name="sidebar_class" />
                </p>
                <p>
                    <label for="sidebar_before_widget">Before Widget <span>*</span></label>
                    <textarea class="text-area" placeholder="&lt;li id=&#34;%1$s&#34; class=&#34;widget %2$s&#34;&gt;" id="sidebar_before_widget" name="sidebar_before_widget"></textarea>
                </p>
                <p>
                    <label for="sidebar_after_widget">After Widget <span>*</span></label>
                    <textarea class="text-area" placeholder="&lt;/li&gt;" id="sidebar_after_widget" name="sidebar_after_widget"></textarea>
                </p>
                <p>
                    <label for="sidebar_before_title">Before Title <span>*</span></label>
                    <textarea class="text-area" placeholder="&lt;h2 class=&#34;widgettitle&#34;&gt" id="sidebar_before_title" name="sidebar_before_title"></textarea>
                </p>
                <p>
                    <label for="sidebar_after_title">After Title <span>*</span></label>
                    <textarea class="text-area" placeholder="&lt;/h2&gt" id="sidebar_after_title" name="sidebar_after_title"></textarea>
                </p>
                <input class="button" type="button" id="add-new-sidebar" value="Add Sidebar" />
                <span id="response"></span>
            </form>
        </div>
        <div class="col-2">
            <h3>Dynamic Sidebar(s)</h3>
            <table id="dynamic-sidebar" class="wp-list-table widefat fixed posts" cellspacing="0">
                <thead>
                    <tr>
                        <th class="name">Sidebar Name</th>
                        <th class="shortcode">Shortcode</th>
                        <th class="description">Description</th>
                        <th class="action">Action</th>
                    </tr>
                </thead>
                <tbody id="the-list">
                    <?php
                    $sidebars = array();
                    if(get_option('wp-dynamic-sidebar-settings')) {
                        $sidebars = unserialize( get_option('wp-dynamic-sidebar-settings') );
                    }
                    if (count($sidebars) > 0) {
                        $counter = 0;
                        foreach($sidebars as $sidebar) {
                        ?>
                        <tr<?php if(($counter % 2) == 0) { echo ' class="alternate"'; } ?>>
                            <td class="name">
                                <strong><?php echo $sidebar['name']; ?></strong>
                            </td>
                            <td class="shortcode">
                                <div id="<?php echo $sidebar['id']; ?>" onclick="fnSelect('<?php echo $sidebar['id']; ?>')">[wp-dynamic-sidebar id="<?php echo $sidebar['id']; ?>"]</div>
                            </td>
                            <td class="description">
                                <?php echo $sidebar['description']; ?>
                            </td>
                            <td class="action">
                                <a href="javascript:void(0);" class="edit-dynamic-sidebar" data-id="<?php echo $sidebar['id']; ?>">Edit</a> | <a href="javascript:void(0);" data-id="<?php echo $sidebar['id']; ?>" class="delete-dynamic-sidebar">Delete</a>
                            </td>
                        </tr>
                        <?php
                            $counter++;
                        }
                    } else {
                    ?>
                    <tr class="no-sidebar">
                        <td colspan="4">You did not created the sidebar yet. You can create it from the form left.</td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <div id="popuup_message">This is a sample error/validation message to display.</div>
    <?php
}

add_action('wp_ajax_save_wp_dynamic_sidebar', 'save_wp_dynamic_sidebar_callback');
add_action('wp_ajax_nopriv_save_wp_dynamic_sidebar', 'save_wp_dynamic_sidebar_callback');

function save_wp_dynamic_sidebar_callback() {
    //echo $_POST['elements'];
    parse_str($_POST['elements'], $elements);
    //sidebar_name=&sidebar_desc=&sidebar_class=&sidebar_before_widget=&sidebar_after_widget=&sidebar_before_title=&sidebar_after_title=
    if(trim($elements['sidebar_name']) == '' || trim($elements['sidebar_desc']) == '' || trim($elements['sidebar_before_widget']) == '' || trim($elements['sidebar_after_widget']) == '' || trim($elements['sidebar_before_title']) == '' || trim($elements['sidebar_after_title']) == '') {
        die('* fields are mandatory.');
    }
    $s_name = $elements['sidebar_name'];
    $s_id = sanitize_title($elements['sidebar_name']);
    $s_desc = esc_textarea($elements['sidebar_desc']);
    $data = array(
        array(
            'name' => $elements['sidebar_name'],
            'id' => sanitize_title($elements['sidebar_name']),
            'description' => esc_textarea($elements['sidebar_desc']),
            'class' => $elements['sidebar_class'],
            'before_widget' => esc_textarea($elements['sidebar_before_widget']),
            'after_widget' => esc_textarea($elements['sidebar_after_widget']),
            'before_title' => esc_textarea($elements['sidebar_before_title']),
            'after_title' => esc_textarea($elements['sidebar_after_title'])
        )
    );
    if (get_option('wp-dynamic-sidebar-settings')) {
        $sidebars = unserialize( get_option('wp-dynamic-sidebar-settings') );
        foreach($sidebars as $key => $sidebar) {
            if( $sidebar['id'] === $s_id ) {
               die('Insert unique sidebar.');
            }
        }
        $data = array_merge($data, $sidebars);
    }
    //print_pre($data); die;
    $serialize = serialize($data);
    if (get_option('wp-dynamic-sidebar-settings')) {
        update_option('wp-dynamic-sidebar-settings', $serialize);
    } else {
        add_option('wp-dynamic-sidebar-settings', $serialize, '', 'yes');
    }
    die('<tr class="alternate"><td class="name"><strong>'.$s_name.'</strong></td><td class="shortcode"><div id="'.$s_id.'" onclick="fnSelect(\''.$s_id.'\')">[wp-dynamic-sidebar id="'.$s_id.'"]</div></td><td class="description">'.$s_desc.'</td><td class="action"><a href="javascript:void(0);" class="edit-dynamic-sidebar" data-id="'.$s_id.'">Edit</a> | <a href="javascript:void(0);" data-id="'.$s_id.'" class="delete-dynamic-sidebar">Delete</a></td></tr>');
}

add_action('wp_ajax_update_wp_dynamic_sidebar', 'update_wp_dynamic_sidebar_callback');
add_action('wp_ajax_nopriv_update_wp_dynamic_sidebar', 'update_wp_dynamic_sidebar_callback');

function update_wp_dynamic_sidebar_callback() {
    parse_str($_POST['elements'], $elements);
    $s_id = $_POST['slug'];
    $c_id = sanitize_title($elements['sidebar_name']);
    $c_name = $elements['sidebar_name'];
    $c_desc = esc_textarea($elements['sidebar_desc']);
    if(trim($elements['sidebar_name']) == '' || trim($elements['sidebar_desc']) == '' || trim($elements['sidebar_before_widget']) == '' || trim($elements['sidebar_after_widget']) == '' || trim($elements['sidebar_before_title']) == '' || trim($elements['sidebar_after_title']) == '') {
        die('* fields are mandatory.');
    }
    if (get_option('wp-dynamic-sidebar-settings')) {
        $sidebars = unserialize( get_option('wp-dynamic-sidebar-settings') );
        //print_pre($sidebars);
        foreach($sidebars as $key => $sidebar) {
            if( $sidebar['id'] === $c_id ) {
                if($c_id !== $s_id) {
                    die('Insert unique sidebar.');
                }
                $current_key = $key;
                break;
            } elseif($sidebar['id'] === $s_id) {
                $current_key = $key;
                break;
            }
        }
        $data = array(
            $current_key => array(
                'name' => $elements['sidebar_name'],
                'id' => sanitize_title($elements['sidebar_name']),
                'description' => esc_textarea($elements['sidebar_desc']),
                'class' => $elements['sidebar_class'],
                'before_widget' => esc_textarea($elements['sidebar_before_widget']),
                'after_widget' => esc_textarea($elements['sidebar_after_widget']),
                'before_title' => esc_textarea($elements['sidebar_before_title']),
                'after_title' => esc_textarea($elements['sidebar_after_title'])
            )
        );
        //print_pre($data);
        $new_data = array_replace($sidebars, $data);
        //print_pre($new_data);
        $serialize = serialize($new_data);
        update_option('wp-dynamic-sidebar-settings', $serialize);
        die('<td class="name"><strong>'.$c_name.'</strong></td><td class="shortcode"><div id="'.$c_id.'" onclick="fnSelect(\''.$c_id.'\')">[wp-dynamic-sidebar id="'.$c_id.'"]</div></td><td class="description">'.$c_desc.'</td><td class="action"><a href="javascript:void(0);" class="edit-dynamic-sidebar" data-id="'.$c_id.'">Edit</a> | <a href="javascript:void(0);" data-id="'.$c_id.'" class="delete-dynamic-sidebar">Delete</a></td>');
    }
    die;
}

add_action('wp_ajax_edit_wp_dynamic_sidebar', 'edit_wp_dynamic_sidebar_callback');
add_action('wp_ajax_nopriv_edit_wp_dynamic_sidebar', 'edit_wp_dynamic_sidebar_callback');

function edit_wp_dynamic_sidebar_callback() {
    $s_id = $_POST['sidebar_id'];
    $sidebars = unserialize( get_option('wp-dynamic-sidebar-settings') );
    foreach($sidebars as $key => $sidebar) {
        $current_key = $key;
        if( $sidebar['id'] === $s_id ) {
           break;
        }
    }
    echo json_encode($sidebars[$current_key]);
    die();
}

add_action('wp_ajax_delete_wp_dynamic_sidebar', 'delete_wp_dynamic_sidebar_callback');
add_action('wp_ajax_nopriv_delete_wp_dynamic_sidebar', 'delete_wp_dynamic_sidebar_callback');

function delete_wp_dynamic_sidebar_callback() {
    $s_id = $_POST['sidebar_id'];
    $sidebars = unserialize( get_option('wp-dynamic-sidebar-settings') );
    foreach($sidebars as $key => $sidebar) {
        if( $sidebar['id'] === $s_id ) {
            $current_key = $key;
            break;
        }
    }
    unset($sidebars[$current_key]);
    $sidebars = array_values($sidebars);
    $serialize = serialize($sidebars);
    update_option('wp-dynamic-sidebar-settings', $serialize);
    die('Successfully deleted.');
}

function wp_dynamic_sidebar_generate() {
    $sidebars = array();
    if(get_option('wp-dynamic-sidebar-settings')) {
        $sidebars = unserialize( get_option('wp-dynamic-sidebar-settings') );
    }
    //print_pre($sidebars);
    foreach($sidebars as $key => $sidebar) {
        register_sidebar(array(
            'name' => $sidebar['name'],
            'id' => $sidebar['id'],
            'description' => $sidebar['description'],
            'class' => $sidebar['class'],
            'before_widget' => html_entity_decode($sidebar['before_widget']),
            'after_widget' => html_entity_decode($sidebar['after_widget']),
            'before_title' => html_entity_decode($sidebar['before_title']),
            'after_title' => html_entity_decode($sidebar['after_title']),
        ));
    }
}

function wp_get_dynamic_sidebar($id) {
    $sidebar_contents = "";
    ob_start();
    dynamic_sidebar($id);
    $sidebar_contents = ob_get_contents();
    ob_end_clean();
    return $sidebar_contents;
}

function wp_dynamic_sidebar_create( $atts ) {
    if(function_exists('dynamic_sidebar')) {
        $id = $atts['id'];
        //dynamic_sidebar($id);
        return wp_get_dynamic_sidebar($id);
    }
}
     
add_shortcode( 'wp-dynamic-sidebar', 'wp_dynamic_sidebar_create' );

function wp_dynamic_sidebar_install() {
    global $wp_sidebar_version;
    add_option("wp_dynamic_sidebar_version", $wp_sidebar_version);
}

function wp_dynamic_sidebar_init() {
    load_plugin_textdomain( 'wp-dynamic-sidebar', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'admin_init', 'wp_dynamic_sidebar_init' );

// Add settings link on plugin page
function wp_dynamic_sidebar_link($links) {
    //print_r($links);
    $settings_link = '<a href="options-general.php?page=wp-dynamic-sidebar.php">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_filter( 'plugin_action_links_' . $plugin, 'wp_dynamic_sidebar_link' );

function wp_dynamic_sidebar_activate() {
    add_option('wp_dynamic_sidebar_redirect_on_1st_activation', 'true');
}
register_activation_hook(__FILE__, 'wp_dynamic_sidebar_activate');

function wp_dynamic_sidebar_redirect() {
    if(get_option('wp_dynamic_sidebar_redirect_on_1st_activation') === 'true') {
        update_option('wp_dynamic_sidebar_redirect_on_1st_activation', 'false');
        wp_redirect(admin_url('options-general.php?page=wp-dynamic-sidebar.php'));
        exit;
    }
}

add_action('admin_init', 'wp_dynamic_sidebar_redirect');