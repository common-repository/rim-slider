<?php

/*
  Plugin Name: Rim Slider
  Plugin URI: http://www.rimazrauf.com/
  Description: Slider Component for WordPress Developed br Rimaz Rauf
  Version: 1.0
  Author: Rimaz Rauf
  Author URI: http://www.rimazrauf.com/
  License: GPLv2 or later
 */


// Activation and Deactivation Hooks
function rim_slider_activation() {

}

register_activation_hook(__FILE__, 'rim_slider_activation');

function rim_slider_deactivation() {

}

register_deactivation_hook(__FILE__, 'rim_slider_deactivation');


// Registering the JQuery plugins & CSS

add_action('wp_enqueue_scripts', 'rim_scripts');

function rim_scripts() {
    global $post;

    wp_enqueue_script('jquery');

    wp_register_script('slidesjs_core', plugins_url('js/jquery.slides.min.js', __FILE__), array("jquery"));
    wp_enqueue_script('slidesjs_core');


    wp_register_script('slidesjs_init', plugins_url('js/slidesjs.initialize.js', __FILE__));
    wp_enqueue_script('slidesjs_init');

    $effect      = (get_option('rim_effect') == '') ? "slide" : get_option('rim_effect');
    $interval    = (get_option('rim_interval') == '') ? 2000 : get_option('rim_interval');
    $autoplay    = (get_option('rim_autoplay') == 'enabled') ? true : false;
    $playBtn    = (get_option('rim_playbtn') == 'enabled') ? true : false;
        $config_array = array(
            'effect' => $effect,
            'interval' => $interval,
            'autoplay' => $autoplay,
            'playBtn' => $playBtn
        );

    wp_localize_script('slidesjs_init', 'setting', $config_array);

}

add_action('wp_enqueue_scripts', 'rim_styles');

function rim_styles() {

    wp_register_style('slidesjs_example', plugins_url('css/example.css', __FILE__));
    wp_enqueue_style('slidesjs_example');
    wp_register_style('slidesjs_fonts', plugins_url('css/font-awesome.min.css', __FILE__));
    wp_enqueue_style('slidesjs_fonts');
}


// Shortcode Scripts

add_shortcode("rim_slider", "rim_display_slider");

function rim_display_slider($attr, $content) {

    extract(shortcode_atts(array(
                'id' => ''
                    ), $attr));

    $gallery_images = get_post_meta($id, "_rim_gallery_images", true);
    $gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();



    $plugins_url = plugins_url();


    $html = '<div class="container">
    <div id="slides">';

    foreach ($gallery_images as $gal_img) {
        if ($gal_img != "") {
            $html .= "<img src='" . $gal_img . "' />";
        }
    }

    $html .= '<a href="#" class="slidesjs-previous slidesjs-navigation"><i class="icon-chevron-left icon-large"></i></a>
      <a href="#" class="slidesjs-next slidesjs-navigation"><i class="icon-chevron-right icon-large"></i></a>
    </div>
  </div>';

    return $html;
}


// Add the Menue in the Dashboard

add_action('init', 'rim_register_slider');

function rim_register_slider() {
    $labels = array(
        'menu_name' => _x('Rim Sliders', 'rimaz_slider'),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => 'Rim Slideshows',
        'supports' => array('title', 'editor'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type('rimaz_slider', $args);
}

/* Define shortcode column in Rhino Slider List View */

add_filter('manage_edit-rimaz_slider_columns', 'rim_set_custom_edit_rimaz_slider_columns');
add_action('manage_rimaz_slider_posts_custom_column', 'rim_custom_rimaz_slider_column', 10, 2);

function rim_set_custom_edit_rimaz_slider_columns($columns) {
    return $columns
    + array('slider_shortcode' => __('Shortcode'));
}

function rim_custom_rimaz_slider_column($column, $post_id) {

    $slider_meta = get_post_meta($post_id, "_rim_slider_meta", true);
    $slider_meta = ($slider_meta != '') ? json_decode($slider_meta) : array();

    switch ($column) {
        case 'slider_shortcode':
            echo "[rimslider id='$post_id' /]";
            break;
    }
}


// Meta Box to the post

add_action('add_meta_boxes', 'rim_slider_meta_box');

function rim_slider_meta_box() {

    add_meta_box("rim-slider-images", "Slider Images", 'rim_view_slider_images_box', "rimaz_slider", "normal");
}

function rim_view_slider_images_box() {
    global $post;

    $gallery_images = get_post_meta($post->ID, "_rim_gallery_images", true);
    // print_r($gallery_images);exit;
    $gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();

    // Use nonce for verification
    $html = '<input type="hidden" name="rim_slider_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

    $html .= '<table class="form-table">';

    $html .= "
          <tr>
            <th style=''><label for='Upload Images'>Image 1</label></th>
            <td><input name='gallery_img[]' id='rim_slider_upload' type='text' value='" . $gallery_images[0] . "'  /></td>
          </tr>
          <tr>
            <th style=''><label for='Upload Images'>Image 2</label></th>
            <td><input name='gallery_img[]' id='rim_slider_upload' type='text' value='" . $gallery_images[1] . "' /></td>
          </tr>
          <tr>
            <th style=''><label for='Upload Images'>Image 3</label></th>
            <td><input name='gallery_img[]' id='rim_slider_upload' type='text'  value='" . $gallery_images[2] . "' /></td>
          </tr>
          <tr>
            <th style=''><label for='Upload Images'>Image 4</label></th>
            <td><input name='gallery_img[]' id='rim_slider_upload' type='text' value='" . $gallery_images[3] . "' /></td>
          </tr>
          <tr>
            <th style=''><label for='Upload Images'>Image 5</label></th>
            <td><input name='gallery_img[]' id='rim_slider_upload' type='text' value='" . $gallery_images[4] . "' /></td>
          </tr>          

        </table>";

    echo $html;
}

/* Save Slider Options to database */
add_action('save_post', 'rim_save_slider_info');

function rim_save_slider_info($post_id) {


    // verify nonce
    if (!wp_verify_nonce($_POST['rim_slider_box_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // check permissions
    if ('rimaz_slider' == $_POST['post_type'] && current_user_can('edit_post', $post_id)) {

        /* Save Slider Images */
        //echo "<pre>";print_r($_POST['gallery_img']);exit;
        $gallery_images = (isset($_POST['gallery_img']) ? $_POST['gallery_img'] : '');
        $gallery_images = strip_tags(json_encode($gallery_images));
        update_post_meta($post_id, "_rim_gallery_images", $gallery_images);

       
    } else {
        return $post_id;
    }
}


// Add Menu Control

add_action('admin_menu', 'rim_plugin_settings');

function rim_plugin_settings() {
    //creecho ate new top-level menu
    add_menu_page('Rim Slider Settings', 'Rim Slider Settings', 'administrator', 'rim_settings', 'rim_display_settings');
}

function rim_display_settings() {

    $slide_effect = (get_option('rim_effect') == 'slide') ? 'selected' : '';
    $fade_effect = (get_option('rim_effect') == 'fade') ? 'selected' : '';
    $interval = (get_option('rim_interval') != '') ? get_option('rim_interval') : '2000';
    $autoplay  = (get_option('rim_autoplay') == 'enabled') ? 'checked' : '' ;
    $playBtn  = (get_option('rim_playBtn') == 'enabled') ? 'checked' : '' ;

    $html = '<div class="wrap">

            <form method="post" name="options" action="options.php">

            <h2>Select Your Settings</h2>' . wp_nonce_field('update-options') . '
            <table width="100%" cellpadding="10" class="form-table">
                <tr>
                    <td align="left" scope="row">
                    <label>Slider Effect</label><select name="rim_effect" >
                      <option value="slide" ' . $slide_effect . '>Slide</option>
                      <option value="fade" '.$fade_effect.'>Fade</option>
                    </select>
             

                    </td> 
                </tr>
                <tr>
                    <td align="left" scope="row">
                    <label>Enable Auto Play</label><input type="checkbox" '.$autoplay.' name="rim_autoplay" 
                    value="enabled" />

                    </td> 
                </tr>
                <tr>
                    <td align="left" scope="row">
                    <label>Enable Play Button</label><input type="checkbox" '.$playBtn.' name="rim_playBtn" 
                    value="enabled" />

                    </td> 
                </tr>
                <tr>
                    <td align="left" scope="row">
                    <label>Transition Interval</label><input type="text" name="rim_interval" 
                    value="' . $interval . '" />

                    </td> 
                </tr>
            </table>
            <p class="submit">
                <input type="hidden" name="action" value="update" />  
                <input type="hidden" name="page_options" value="rim_autoplay,rim_effect,rim_interval,rim_playBtn" /> 
                <input type="submit" name="Submit" value="Update" />
            </p>
            </form>

        </div>';
    echo $html;
}
?>