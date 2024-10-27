<?php
if (!defined('ABSPATH')) exit;
class RGFBAdmin
{

    public function __construct()
    {

        $this->load();
        session_start();
    }
    public function load()
    {

        // Add Javascript and CSS for admin screens
        add_action('admin_enqueue_scripts', array($this, 'rgfb_enqueue_admin_styles_scripts'), 10);

        //Add Admin Menu
        add_action('admin_menu', array($this, 'rgfb_generate_form_builder_menu'), 20);

        //Save Form Builder Structure
        add_action('wp_ajax_save_rgfb_form_builder', array($this, 'rgfb_save_form_builder'), 30);

        //Update Form Builder Sreucture
        add_action('wp_ajax_update_rgfb_form_builder', array($this, 'rgfb_update_form_builder'), 30);

        //Form List
        add_filter('rgfb_form_builder_list', array($this, 'rgfb_form_list_callback'), 30);

        //Settings Menu
        add_filter('rgfb_form_builder_settings', array($this, 'rgfb_form_settings_callback'), 30);

        //Save Settings Data
        add_action('wp_ajax_rgfb_save_settings', array($this, 'rgfb_save_settings'), 30);

        //Save LLM Logs
        add_action('wp_ajax_rgfb_save_llm_logs', array($this, 'rgfb_save_llm_logs'), 30);
        add_action('wp_ajax_nopriv_rgfb_save_llm_logs', array($this, 'rgfb_save_llm_logs'), 30);


        //List LLM Logs
        add_filter('rgfb_llm_logs', array($this, 'rgfb_llm_logs_callback'), 30);
    }
    /**
     * Admin Enqueue scripts and styles
     */
    public function rgfb_enqueue_admin_styles_scripts()
    {

        wp_enqueue_style('bootstrap-css', plugins_url('/assets/css/bootstrap/bootstrap.min.css', __FILE__),  array(), '5.3.2', false);

        wp_enqueue_style('jquery-confirm-css', plugins_url('/assets/css/jquery-confirm/jquery-confirm.css', __FILE__), array(), '3.3.2', false);
        wp_enqueue_style('rgfb-custom-style', plugins_url('/assets/css/style.css', __FILE__),  array(), '1.0', false);

        //Slick
        wp_enqueue_style('slick', plugins_url('/assets/css/slick-carousel/slick.min.css', __FILE__), array(), '1.8.1', false);
        wp_enqueue_style('slick-theme', plugins_url('/assets/css/slick-carousel/slick-theme.min.css', __FILE__), array(), '1.8.1', false);
        wp_enqueue_script('slick', plugins_url('/assets/js/slick-carousel/slick.min.js', __FILE__), array('jquery'), '1.0', false);

        //Jquery
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker');

        // Jquery UI
        wp_enqueue_script('jquery-ui', plugins_url('/assets/js/jquery-ui/jquery-ui.js', __FILE__), array(), '1.0', false);

        //formbuilder
        wp_enqueue_script('formbuilder-js', plugins_url('/assets/js/form-builder/form-builder.js', __FILE__), array('jquery', 'jquery-ui-core'), '1.0', false);

        //alert-js
        wp_enqueue_script('query-confirm-js', plugins_url('/assets/js/jquery-confirm/jquery-confirm.js', __FILE__), array('jquery'), '3.3.2', false);

        wp_enqueue_script('rgfb-custom-script', plugins_url('/assets/js/script.js', __FILE__), array('jquery'), '1.0', false);
        wp_enqueue_script('rgfb-custom-script-addform', plugins_url('/assets/js/add_form.js', __FILE__), array('jquery'), '1.0', false);
        wp_enqueue_script('rgfb-custom-script-settings', plugins_url('/assets/js/settings.js', __FILE__), array('jquery'), '1.0', false);
        wp_enqueue_script('rgfb-custom-script-logs', plugins_url('/assets/js/logs.js', __FILE__), array('jquery'), '1.0', false);

        wp_localize_script('ai-enabler-ajax', 'rgFormBuilderAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

        //google font
        wp_enqueue_style('google-fonts-roboto', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap', array(), '1.0');
        wp_enqueue_style('google-fonts-open-sans', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap', array(), '1.0');
        wp_enqueue_style('google-fonts-lato', 'https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap', array(), '1.0');
        wp_enqueue_style('google-fonts-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap', array(), '1.0');
    }

    /**
     * Generate Menu
     */
    public function rgfb_generate_form_builder_menu()
    {
        // Add a top-level menu item
        add_menu_page(
            'AI Enabler',
            'AI Enabler',
            'manage_options',
            'ai-enabler',
            array($this, 'rgfb_form_builder_rander'),
            'dashicons-chart-pie',
        );

        // Add a submenu item under the top-level menu
        add_submenu_page(
            'ai-enabler',
            'Form List',
            'Form List',
            'manage_options',
            'rgfb_form_builder_list',
            array($this, 'rgfb_form_list_callback')
        );

        // Add settings submenu item under the top-level menu
        add_submenu_page(
            'ai-enabler',
            'Settings',
            'Settings',
            'manage_options',
            'rgfb_form_builder_settings',
            array($this, 'rgfb_form_settings_callback')
        );

        // Add logs submenu item under the top-level menu
        add_submenu_page(
            'ai-enabler',
            'Logs',
            'Logs',
            'manage_options',
            'rgfb_llm_logs',
            array($this, 'rgfb_llm_logs_callback')
        );
    }

    public function rgfb_form_builder_rander()
    {
        include(__DIR__ . '/templates/form.tpl.php');
        wp_die();
    }

    //Save Form Structures
    public function rgfb_save_form_builder()
    {
        check_ajax_referer('save-form-nonce', 'nonce');

        if (isset($_POST['formData'])) {
            parse_str(wp_unslash(sanitize_text_field($_POST['formData'])), $form_data);
        } else {
            wp_send_json(['status' => false, 'msg' => 'No form data provided']);
            wp_die();
        }

        $form_name = sanitize_text_field($form_data['formname']);
        $style = sanitize_text_field($form_data['style']);
        $custom_style = sanitize_text_field($form_data['custom_style']);
        $custom_form_bgcolor = sanitize_text_field($form_data['custom_form_color']);
        $custom_font_family = sanitize_text_field($form_data['custom_font_family']);
        $custom_heading_font_size = sanitize_text_field($form_data['custom_heading_font_size']);
        $custom_label_font_size = sanitize_text_field($form_data['custom_label_font_size']);
        $custom_output_font_size = sanitize_text_field($form_data['custom_output_font_size']);
        $custom_output_font_family = sanitize_text_field($form_data['custom_output_font_family']);
        $custom_form_width = sanitize_text_field($form_data['custom_form_width']);
        $is_load_response_on_page_load = sanitize_text_field($form_data['is_load_response_on_page_load']);
        $dalle_image_size_select = sanitize_text_field($form_data['dalle_image_size_select']);
        $include_post_or_page_title_in_the_prompt = sanitize_text_field($form_data['include_post_or_page_title_in_the_prompt']);

        $form_data = sanitize_text_field($form_data['data']);
        if ($form_name == "" || empty($form_name)) {
            $result = array('status' => false, 'msg' => 'Form name is required');
            wp_send_json($result);
        }

        if ($form_data == '' || empty($form_data)) {
            $result = array('status' => false, 'msg' => 'No form elements');
            wp_send_json($result);
        }
        $meta_data = array(
            'structure' => $form_data,
            'style' => $style,
            'custom_style' => $custom_style,
            'custom_form_bgcolor' => $custom_form_bgcolor,
            'custom_font_family' => $custom_font_family,
            'custom_heading_font_size' => $custom_heading_font_size,
            'custom_label_font_size' => $custom_label_font_size,
            'custom_output_font_size' => $custom_output_font_size,
            'custom_output_font_family' => $custom_output_font_family,
            'custom_form_width' => $custom_form_width,
            'is_load_response_on_page_load' => $is_load_response_on_page_load,
            'dalle_image_size_select' => $dalle_image_size_select,
            'include_post_or_page_title_in_the_prompt' => $include_post_or_page_title_in_the_prompt,
        );
        $post_id = wp_insert_post(array(
            'post_type' => 'rg_form_builder_post',
            'post_title' => $form_name,
            'post_status' => 'publish',
            'meta_input' => $meta_data
        ));

        if ($post_id) {
            $sc = '[ai_enabler id="' . $post_id . '"]';
            update_post_meta($post_id, 'shortcode', $sc);
            $result = array('status' => true, 'msg' => 'Record has been added successfully.', 'redirect_url' => admin_url('admin.php?page=rgfb_form_builder_list'));
        } else {
            $result = array('status' => false, 'msg' => 'Unable to insert the record', 'redirect_url' => '');
        }

        wp_send_json($result);
        wp_die(); // Always include this to terminate the script properly
    }

    /**
     * Form List
     */
    public function rgfb_form_list_callback()
    {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

        if ($action == '') {
            require_once('includes/class-ai-enabler-list-table.php');
            $deleted = isset($_SESSION['rgfb_deleted_record']) ? absint($_SESSION['rgfb_deleted_record']) : 0;

            if ($deleted) {
                echo '<div class="notice notice-success is-dismissible"><p>Records has been deleted.</p></div>';
                unset($_SESSION['rgfb_deleted_record']);
            }

            $my_list_table = new RGFB_Form_Builder_List_Table();
            $my_list_table->prepare_items();
            $my_list_table->display();
        } else if ($action == 'edit') {
            $item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            check_admin_referer('form-list-nonce-' . $item_id, 'nonce');

            $post = get_post($item_id);
            $post_meta = get_post_meta($item_id);
            $default_formData = "[
                {
                  type: 'header',
                  subtype: 'h1',
                  label: 'No records found',
                },
              ]";
            $structure = !empty($post_meta['structure']) ? $post_meta['structure'][0] : $default_formData;
            $style = !empty($post_meta['style']) ? $post_meta['style'][0] : '';
            $custom_style = !empty($post_meta['custom_style']) ? $post_meta['custom_style'][0] : "";
            $custom_form_bgcolor = !empty($post_meta['custom_form_bgcolor']) ? $post_meta['custom_form_bgcolor'][0] : "";
            $custom_font_family = !empty($post_meta['custom_font_family']) ? $post_meta['custom_font_family'][0] : "";
            $custom_heading_font_size = !empty($post_meta['custom_heading_font_size']) ? $post_meta['custom_heading_font_size'][0] : "";
            $custom_label_font_size = !empty($post_meta['custom_label_font_size']) ? $post_meta['custom_label_font_size'][0] : "";
            $custom_output_font_size = !empty($post_meta['custom_output_font_size']) ? $post_meta['custom_output_font_size'][0] : "";
            $custom_output_font_family = !empty($post_meta['custom_output_font_family']) ? $post_meta['custom_output_font_family'][0] : "";
            $custom_form_width = !empty($post_meta['custom_form_width']) ? $post_meta['custom_form_width'][0] : "";
            $is_load_response_on_page_load = !empty($post_meta['is_load_response_on_page_load']) ? $post_meta['is_load_response_on_page_load'][0] : "";
            $dalle_image_size_select = !empty($post_meta['dalle_image_size_select']) ? $post_meta['dalle_image_size_select'][0] : "";
            $include_post_or_page_title_in_the_prompt = !empty($post_meta['include_post_or_page_title_in_the_prompt']) ? $post_meta['include_post_or_page_title_in_the_prompt'][0] : "";

            $formData = array(
                "data" => $structure,
                'style' => $style,
                'custom_style' => $custom_style,
                'custom_form_bgcolor' => $custom_form_bgcolor,
                'custom_font_family' => $custom_font_family,
                'custom_heading_font_size' => $custom_heading_font_size,
                'custom_label_font_size' => $custom_label_font_size,
                'custom_output_font_size' => $custom_output_font_size,
                'custom_output_font_family' => $custom_output_font_family,
                'custom_form_width' => $custom_form_width,
                'is_load_response_on_page_load' => $is_load_response_on_page_load,
                'dalle_image_size_select' => $dalle_image_size_select,
                'include_post_or_page_title_in_the_prompt' => $include_post_or_page_title_in_the_prompt,
            );

            wp_enqueue_script('rgfb-from-edit-data-script', plugin_dir_url(__FILE__) . 'assets/js/form_edit.js', array('jquery'), '1.0', false);
            wp_localize_script('rgfb-from-edit-data-script', 'formData', $formData);

            echo '<div class="wrap" id="rgfb_from_builder_wrap">
                ' . wp_kses_post(wp_nonce_field('edit-form-nonce', 'edit_form_nonce')) . '
                <h2>Edit Drag-and-Drop Form Builder</h2>
               
                <div class="row">
                    <div class="col-md-6">
                        <label for="rg_frmbulder_form_name">Form Name: </label>
                        <input type="text" name="rg_frmbulder_form_name" id="rg_frmbulder_form_name" placeholder="Enter Form Name" class="form-control" value="' . esc_attr($post->post_title) . '">
                    </div>
                    <div class="col-md-6">
                        <label for="rgfb_dalle_image_size_select">Dall-E Image Size:</label>
                        <select name="dalle_image_size_select" class="form-control" id="rgfb_dalle_image_size_select">
                            <option value="1024x1024">1024x1024</option>
                            <option value="720x720">720x720</option>
                            <option value="512x512">512x512</option>
                            <option value="256x256">256x256</option>
                        </select>
                    </div>
                    <div class="col-md-6 mt-4 mb-2">
                        <input class="form-check-input mt-1" type="checkbox" name="is_load_response_on_page_load" id="rgfb_is_load_response_on_page_load" value="on">
                        <label class="form-check-label ml-4" for="is_load_response_on_page_load">
                            Load response on page load
                        </label>
                    </div>
                    <div class="col-md-6 mt-4 mb-2">
                        <input class="form-check-input mt-1" type="checkbox" name="include_post_or_page_title_in_the_prompt" id="include_post_or_page_title_in_the_prompt" value="on">
                        <label class="form-check-label ml-4" for="include_post_or_page_title_in_the_prompt">
                            Include post/page title in the prompt
                        </label>
                    </div>
                </div>
                <div class="select_form_style_section">
                    <p class="style_headline">Please Select Form Style</p>
                    <div class="form_wrap">
                        <div class="autoplay">
                            <div>
                                <label class="radio-img">
                                    <input type="radio" name="layout" value="formstyle1" />
                                    <div class="image" style="background-image: url(' . esc_url(plugin_dir_url(__FILE__) . 'assets/images/01.png') . ')"></div>
                                </label>
                            </div>

                            <div>
                                <label class="radio-img">
                                    <input type="radio" name="layout" value="formstyle2" />
                                    <div class="image" style="background-image: url(' . esc_url(plugin_dir_url(__FILE__) . 'assets/images/02.png') . ')"></div>
                                </label>
                            </div>

                            <div>
                                <label class="radio-img">
                                    <input type="radio" name="layout" value="formstyle3" />
                                    <div class="image" style="background-image: url(' . esc_url(plugin_dir_url(__FILE__) . 'assets/images/03.png') . ')"></div>
                                </label>
                            </div>

                            <div>
                                <label class="radio-img">
                                    <input type="radio" name="layout" value="formstyle4" />
                                    <div class="image" style="background-image: url(' . esc_url(plugin_dir_url(__FILE__) . 'assets/images/04.png') . ')"></div>
                                </label>
                            </div>

                            <div>
                                <label class="radio-img">
                                    <input type="radio" name="layout" value="formstyle5" />
                                    <div class="image" style="background-image: url(' . esc_url(plugin_dir_url(__FILE__) . 'assets/images/05.png') . ')"></div>
                                </label>
                            </div>

                            <div>
                                <label class="radio-img">
                                    <input type="radio" name="layout" value="formstyle6" />
                                    <div class="image" style="background-image: url(' . esc_url(plugin_dir_url(__FILE__) . 'assets/images/06.png') . ')"></div>
                                </label>
                            </div>

                            <div>
                                <label class="radio-img">
                                    <input type="radio" name="layout" value="formstyle7" />
                                    <div class="image" style="background-image: url(' . esc_url(plugin_dir_url(__FILE__) . 'assets/images/07.png') . ')"></div>
                                </label>
                            </div>

                            <div>
                                <label class="radio-img">
                                    <input type="radio" name="layout" value="formstyle8" />
                                    <div class="image" style="background-image: url(' . esc_url(plugin_dir_url(__FILE__) . 'assets/images/08.png') . ')"></div>
                                </label>
                            </div>

                        </div>
                    </div>
                    
                <div>
                <div id="custom_form_style">
                    <input type="checkbox" name="custom_form_style_chk" id="rgfb_custom_form_style_chk" value="on"> Custom Form Style
                    <div id="custom_form_style_element">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="">Form background </label>
                                <input type="color" name="rgfb_custom_form_color" id="rgfb_custom_form_color" class="form-control">
                            </div>
                            <div class="col-6 mb-3">
                                <label for="">Font Family </label>
                                <select name="rgfb_custom_font_family" id="rgfb_custom_font_family" class="form-control form-select">
                                    <option value="None">None</option>
                                    <option value="Georgia, Times New Roman, Times, serif">Serif</option>
                                    <option value="Helvetica Neue, Helvetica, Arial, sans-serif">Sans-Serif</option>
                                    <option value="Consolas, Courier New, Courier, monospace">Monospace</option>
                                    <option value="Lucida Handwriting, Lucida Calligraphy, cursive">Cursive</option>
                                    <option value="Calibri, Garamond, Candara, sans-serif">Calibri</option>
                                    <option value="Verdana, Trebuchet MS, Gill Sans, sans-serif">Verdana</option>
                                    <option value="Roboto, Lato, Segoe UI, Arial, sans-serif">Roboto</option>
                                    <option value="Lato, Roboto, Helvetica Neue, Arial, sans-serif">Lato</option>
                                    <option value="Helvetica Neue, Arial, Liberation Sans, sans-serif">Helvetica</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="">Heading Font Size (px)</label>
                                <input type="number" name="rgfb_custom_heading_font_size" id="rgfb_custom_heading_font_size" class="form-control">
                            </div>
                            <div class="col-6 mb-3">
                                <label for="">Label Font Size (px)</label>
                                <input type="number" name="rgfb_custom_label_font_size" id="rgfb_custom_label_font_size" class="form-control">
                            </div>
                        </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="">Output Font Size (px)</label>
                            <input type="number" name="rgfb_custom_output_font_size" id="rgfb_custom_output_font_size" class="form-control">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="">Output Font Family </label>
                            <select name="rgfb_custom_output_font_family" id="rgfb_custom_output_font_family" class="form-control form-select">
                            <option value="None">None</option>
                            <option value="Georgia, Times New Roman, Times, serif">Serif</option>
                            <option value="Helvetica Neue, Helvetica, Arial, sans-serif">Sans-Serif</option>
                            <option value="Consolas, Courier New, Courier, monospace">Monospace</option>
                            <option value="Lucida Handwriting, Lucida Calligraphy, cursive">Cursive</option>
                            <option value="Calibri, Garamond, Candara, sans-serif">Calibri</option>
                            <option value="Verdana, Trebuchet MS, Gill Sans, sans-serif">Verdana</option>
                            <option value="Roboto, Lato, Segoe UI, Arial, sans-serif">Roboto</option>
                            <option value="Lato, Roboto, Helvetica Neue, Arial, sans-serif">Lato</option>
                            <option value="Helvetica Neue, Arial, Liberation Sans, sans-serif">Helvetica</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="">Form Width (px)</label>
                            <input type="number" name="rgfb_custom_form_width" id="rgfb_custom_form_width" class="form-control">
                        </div>
                    </div>
                    </div>
                </div>
                <div id="edit-form-build-wrap" class="build_wrap_area"></div>
                <div class="new-action-buttons mt-4">
                    <button id="save-data" class="save_btn"">Save</button>
                    <button id="trash" class="clear_btn">Clear All</button>
                    <input type="hidden" name="item_id" id="rgfb_formbuilder_item" value="' . esc_attr(base64_encode($item_id)) . '">
                </div>
            </div>';
        } else if ($action == 'delete') {
            $item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            check_admin_referer('form-list-nonce-' . $item_id, 'nonce');
            wp_delete_post($item_id, true);
            $_SESSION['rgfb_deleted_record'] = true;

            echo  '<script>location.href ="' . esc_url(admin_url('admin.php?page=rgfb_form_builder_list')) . '"</script>';
            exit();
        } else if ($action == 'copy') {
            $post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            check_admin_referer('form-list-nonce-' . $post_id, 'nonce');
            // Get the existing post by ID
            $post = get_post($post_id);

            // Check if the post exists
            if (!empty($post)) {
                // Create an array of post data
                $post_data = array(
                    'post_title'    => $post->post_title . ' - Copy',
                    'post_content'  => $post->post_content,
                    'post_status'   => $post->post_status,
                    'post_author'   => $post->post_author,
                    'post_type'     => $post->post_type,
                );

                // Insert the new post and get its ID
                $new_post_id = wp_insert_post($post_data);

                // Optionally, you can copy additional meta data from the old post to the new post
                $post_meta = get_post_meta($post_id);
                foreach ($post_meta as $key => $value) {
                    if ($key == 'shortcode') {
                        $shortcode = '[ai_enabler id="' . $new_post_id . '"]';
                        update_post_meta($new_post_id, $key, $shortcode);
                    } else {
                        update_post_meta($new_post_id, $key, $value[0]);
                    }
                }
            }

            echo  '<script>location.href ="' . esc_url(admin_url('admin.php?page=rgfb_form_builder_list')) . '"</script>';
            exit();
        }
    }


    //Update Form Structures
    public function rgfb_update_form_builder()
    {
        check_ajax_referer('edit-form-nonce', 'nonce');

        if (isset($_POST['formData'])) {
            parse_str(wp_unslash(sanitize_text_field($_POST['formData'])), $form_data);
        } else {
            wp_send_json(['status' => false, 'msg' => 'No form data provided']);
            wp_die();
        }

        $form_name = sanitize_text_field($form_data['formname']);
        $style = sanitize_text_field($form_data['style']);
        $item_id = sanitize_text_field(base64_decode($form_data['id']));

        $custom_style = sanitize_text_field($form_data['custom_style']);
        $custom_form_bgcolor = sanitize_text_field($form_data['custom_form_color']);
        $custom_font_family = sanitize_text_field($form_data['custom_font_family']);
        $custom_heading_font_size = sanitize_text_field($form_data['custom_heading_font_size']);
        $custom_label_font_size = sanitize_text_field($form_data['custom_label_font_size']);
        $custom_output_font_size = sanitize_text_field($form_data['custom_output_font_size']);
        $custom_output_font_family = sanitize_text_field($form_data['custom_output_font_family']);
        $custom_form_width = sanitize_text_field($form_data['custom_form_width']);
        $is_load_response_on_page_load = sanitize_text_field($form_data['is_load_response_on_page_load']);
        $dalle_image_size_select = sanitize_text_field($form_data['dalle_image_size_select']);
        $include_post_or_page_title_in_the_prompt = sanitize_text_field($form_data['include_post_or_page_title_in_the_prompt']);

        $form_data = wp_unslash($form_data['data']);



        if ($item_id == "" || empty($item_id) || $item_id == 0) {
            $result = array('status' => false, 'msg' => 'Undefined record');
            wp_send_json($result);
        }

        if ($form_name == "" || empty($form_name)) {
            $result = array('status' => false, 'msg' => 'Form name is required');
            wp_send_json($result);
        }

        if ($form_data == '' || empty($form_data)) {
            $result = array('status' => false, 'msg' => 'No form elements');
            wp_send_json($result);
        }

        $post_id = wp_update_post(array(
            'ID' => $item_id,
            'post_title' => $form_name,
        ));
        if ($post_id) {
            //Update Structure
            $meta_key1 = 'structure';
            $meta_value1 = $form_data;
            update_post_meta($post_id, $meta_key1, $meta_value1);

            //Update Style
            $meta_key2 = 'style';
            $meta_value2 = $style;
            update_post_meta($post_id, $meta_key2, $meta_value2);

            //Update Custom Style
            $meta_key3 = 'custom_style';
            $meta_value3 = $custom_style;
            update_post_meta($post_id, $meta_key3, $meta_value3);

            //Update Custom Form BG Color
            $meta_key4 = 'custom_form_bgcolor';
            $meta_value4 = $custom_form_bgcolor;
            update_post_meta($post_id, $meta_key4, $meta_value4);

            //Update Custom Font family
            $meta_key5 = 'custom_font_family';
            $meta_value5 = $custom_font_family;
            update_post_meta($post_id, $meta_key5, $meta_value5);

            //Update Custom heading font size
            $meta_key6 = 'custom_heading_font_size';
            $meta_value6 = $custom_heading_font_size;
            update_post_meta($post_id, $meta_key6, $meta_value6);

            //Update Custom label font size
            $meta_key7 = 'custom_label_font_size';
            $meta_value7 = $custom_label_font_size;
            update_post_meta($post_id, $meta_key7, $meta_value7);

            //Update Custom output font size
            $meta_key8 = 'custom_output_font_size';
            $meta_value8 = $custom_output_font_size;
            update_post_meta($post_id, $meta_key8, $meta_value8);

            //Update Custom output font size
            $meta_key9 = 'custom_output_font_family';
            $meta_value9 = $custom_output_font_family;
            update_post_meta($post_id, $meta_key9, $meta_value9);

            //Update Custom form width
            $meta_key10 = 'custom_form_width';
            $meta_value10 = $custom_form_width;
            update_post_meta($post_id, $meta_key10, $meta_value10);

            //Update Custom form width
            $meta_key11 = 'is_load_response_on_page_load';
            $meta_value11 = $is_load_response_on_page_load;
            update_post_meta($post_id, $meta_key11, $meta_value11);

            //Update Custom form width
            $meta_key12 = 'dalle_image_size_select';
            $meta_value12 = $dalle_image_size_select;
            update_post_meta($post_id, $meta_key12, $meta_value12);

            //Update include post or page title in the prompt
            $meta_key13 = 'include_post_or_page_title_in_the_prompt';
            $meta_value13 = $include_post_or_page_title_in_the_prompt;
            update_post_meta($post_id, $meta_key13, $meta_value13);

            $result = array('status' => true, 'msg' => 'Record has been updated successfully.');
        } else {
            $result = array('status' => false, 'msg' => 'Unable to update the record');
        }

        wp_send_json($result);
        wp_die(); // Always include this to terminate the script properly
    }

    public function rgfb_form_settings_callback()
    {
        include(__DIR__ . '/templates/settings.tpl.php');
        wp_die();
    }

    //Save Settings
    public function rgfb_save_settings()
    {

        check_ajax_referer('save-settings-nonce', 'nonce');

        $rgfb_openai_api_key = sanitize_text_field($_POST['formData'][2]['value']);
        $rgfb_openai_model = sanitize_text_field($_POST['formData'][3]['value']);
        $rgfb_image_generation_model = sanitize_text_field($_POST['formData'][4]['value']);
        $rgfb_image_generation_strength = sanitize_text_field($_POST['formData'][5]['value']);
        $rgfb_limit_image_requests_by_ip = sanitize_text_field($_POST['formData'][6]['value']);


        if (empty(trim($rgfb_openai_api_key))) {
            $result = array('status' => false, 'msg' => 'Openai API key is required');
            wp_send_json($result);
        }

        if (empty(trim($rgfb_openai_model))) {
            $result = array('status' => false, 'msg' => 'Please select a text generation model');
            wp_send_json($result);
        }

        if (empty(trim($rgfb_image_generation_model))) {
            $result = array('status' => false, 'msg' => 'Please select an image generation model');
            wp_send_json($result);
        }

        if (empty(trim($rgfb_image_generation_strength))) {
            $result = array('status' => false, 'msg' => 'Please select an image generation strength');
            wp_send_json($result);
        }

        update_option('rgfb_openai_api_key', $rgfb_openai_api_key);
        update_option('rgfb_openai_model', $rgfb_openai_model);
        update_option('rgfb_image_generation_model', $rgfb_image_generation_model);
        update_option('rgfb_image_generation_strength', $rgfb_image_generation_strength);
        update_option('rgfb_limit_image_requests_by_ip', $rgfb_limit_image_requests_by_ip);

        $result = array('status' => true, 'msg' => 'Settings saved successfully');

        wp_send_json($result);
        wp_die();
    }

    //Save Form Structures
    public function rgfb_save_llm_logs()
    {
        check_ajax_referer('rgfb-llm-logs-nonce', 'nonce');

        if (isset($_POST['formData'])) {
            parse_str(wp_unslash(sanitize_text_field($_POST['formData'])), $form_data);
        } else {
            wp_send_json(['status' => false, 'msg' => 'No form data provided']);
            wp_die();
        }

        $openai_api_key = sanitize_text_field($form_data['openai_api_key']);
        $openai_model = sanitize_text_field($form_data['openai_model']);
        $completion_tokens = sanitize_text_field($form_data['completion_tokens']);
        $prompt_tokens = sanitize_text_field($form_data['prompt_tokens']);
        $total_cost = sanitize_text_field($form_data['total_cost']);
        $total_tokens = sanitize_text_field($form_data['total_tokens']);
        $prompt = sanitize_text_field($form_data['prompt']);
        $answer = sanitize_text_field($form_data['answer']);
        $image_url = esc_url_raw($form_data['image_url']);
        $output_type = sanitize_text_field($form_data['output_type']);

        $image_url = $this->download_external_image($image_url);
        $meta_data = array(
            'rgfb_llm_logs' => array(
                'openai_model' => $openai_model,
                'completion_tokens' => $completion_tokens,
                'prompt_tokens' => $prompt_tokens,
                'total_cost' => $total_cost,
                'total_tokens' => $total_tokens,
                'prompt' => $prompt,
                'answer' => $answer,
                'image_url' => $image_url,
                'user_ip' => filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP),
                'output_type' => $output_type,
            )
        );
        $post_id = wp_insert_post(array(
            'post_type' => 'rgfb_llm_logs',
            'post_title' => 'RGFB LLM Logs',
            'post_status' => 'publish',
            'meta_input' => $meta_data
        ));

        if ($post_id) {
            $sc = '[ai_enabler id="' . $post_id . '"]';
            update_post_meta($post_id, 'shortcode', $sc);
            $result = array('status' => true, 'msg' => 'Record has been added successfully.');
        } else {
            $result = array('status' => false, 'msg' => 'Unable to insert the record');
        }

        wp_send_json($result);
        wp_die(); // Always include this to terminate the script properly
    }

    /**
     * LLM Logs
     */
    public function rgfb_llm_logs_callback()
    {
        include(__DIR__ . '/templates/logs.tpl.php');
        wp_die();
    }

    function download_external_image($image_url)
    {
        $image_url = esc_url_raw($image_url);
        $image_data = wp_remote_get($image_url, array('timeout' => 30));
        if (is_wp_error($image_data)) {
            return;
        }

        $upload_dir = wp_upload_dir();

        $file_extension = pathinfo($image_url, PATHINFO_EXTENSION);

        if (empty($file_extension)) {
            $file_extension = 'png';
        }

        $new_file_name = 'image_' . time() . '.' . $file_extension;

        global $wp_filesystem;
        if (!is_object($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        if (!$wp_filesystem) {
            return;
        }

        $image_path = $upload_dir['path'] . '/' . $new_file_name;
        $image_data = $image_data['body'];

        if (!$wp_filesystem->put_contents($image_path, $image_data, FS_CHMOD_FILE)) {
            return;
        }

        $image_url = $upload_dir['url'] . '/' . $new_file_name;

        return $image_url;
    }
}


global $rgfbAdminClass;

$rgfbAdminClass = new RGFBAdmin();
