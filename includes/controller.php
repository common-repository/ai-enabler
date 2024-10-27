<?php
if (!defined('ABSPATH')) exit;
class RGFBFront
{

    public function __construct()
    {
        $this->load();
    }

    public function load()
    {
        add_action('wp_enqueue_scripts', array($this, 'rgfb_enqueue_styles_scripts'));
        add_shortcode('ai_enabler', array($this, 'rgfb_form_render_shortcode'));
    }

    //Fontend Enqueue
    function rgfb_enqueue_styles_scripts()
    {
        // Enqueue Bootstrap CSS from CDN
        wp_enqueue_style('bootstrap-css', plugins_url('/assets/css/bootstrap/bootstrap.min.css', __FILE__), array(), '5.3.2', false);

        wp_enqueue_style('rgfb-form-render-style',  plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), '1.0', false);

        // Enqueue Bootstrap JavaScript from CDN
        wp_enqueue_script('bootstrap-js', plugins_url('/assets/js/bootstrap/bootstrap.min.js', __FILE__), array('jquery'), '4.5.2', false);

        wp_enqueue_script('formrender-js', plugins_url('/assets/js/form-builder/form-render.js', __FILE__), array('jquery'), '3.3.2', false);

        //google font
        wp_enqueue_style('google-fonts-roboto', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap', array(), '1.0');
        wp_enqueue_style('google-fonts-open-sans', 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap', array(), '1.0');
        wp_enqueue_style('google-fonts-lato', 'https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap', array(), '1.0');
        wp_enqueue_style('google-fonts-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap', array(), '1.0');

        // Localize wordpress ajax url
        $nonce = wp_create_nonce('rgfb-llm-logs-nonce');
        wp_localize_script('formrender-js', 'rgFormRenderAjax', array('ajaxurl' => admin_url('admin-ajax.php'), 'nonce'   => $nonce));
    }

    function register_rgfb_form_shortcode()
    {
        add_shortcode('ai_enabler', array($this, 'rgfb_form_render_shortcode'));
    }

    public function rgfb_form_render_shortcode($atts)
    {
        $shortcode_atts  = shortcode_atts(array(
            'id' =>  'Post ID',
        ), $atts);

        // Access parameters
        $post_id = esc_attr($shortcode_atts['id']);

        $post_meta = get_post_meta($post_id);
        $structure = $post_meta['structure'];
        $custom_style = $post_meta['custom_style'][0];
        $is_load_response_on_page_load = !empty($post_meta['is_load_response_on_page_load'][0]) ? $post_meta['is_load_response_on_page_load'][0] : '';
        $dalle_image_size_select = !empty($post_meta['dalle_image_size_select'][0]) ? $post_meta['dalle_image_size_select'][0] : '';
        $include_post_or_page_title_in_the_prompt = !empty($post_meta['include_post_or_page_title_in_the_prompt'][0]) ? $post_meta['include_post_or_page_title_in_the_prompt'][0] : '';

        if ($custom_style == 'on') {
            $custom_form_bgcolor = !empty($post_meta['custom_form_bgcolor'][0]) ? $post_meta['custom_form_bgcolor'][0] : '';
            $custom_font_family = !empty($post_meta['custom_font_family'][0]) ? $post_meta['custom_font_family'][0] : '';
            $custom_heading_font_size = !empty($post_meta['custom_heading_font_size'][0]) ? $post_meta['custom_heading_font_size'][0] . "px" : '';
            $custom_label_font_size = !empty($post_meta['custom_label_font_size'][0]) ? $post_meta['custom_label_font_size'][0] . "px" : '';
            $custom_output_font_size = !empty($post_meta['custom_output_font_size'][0]) ? $post_meta['custom_output_font_size'][0] . "px" : '';
            $custom_output_font_family = !empty($post_meta['custom_output_font_family'][0]) ? $post_meta['custom_output_font_family'][0] : '';
            $custom_form_width = !empty($post_meta['custom_form_width'][0]) ? $post_meta['custom_form_width'][0] . "px" : '';

            $form_style = '';
        } else {
            $form_style = $post_meta['style'];
            $inline_style = '';
        }
        $form_style = !empty($form_style) ? $form_style[0] : "";

        $formData = array(
            "data" => $structure[0],
            "custom_style" => $custom_style,
            "custom_form_bgcolor" => $custom_form_bgcolor,
            "custom_font_family" => $custom_font_family,
            "custom_heading_font_size" => $custom_heading_font_size,
            "custom_label_font_size" => $custom_label_font_size,
            "custom_output_font_size" => $custom_output_font_size,
            "custom_output_font_family" => $custom_output_font_family,
            "custom_form_width" => $custom_form_width,
        );

        wp_enqueue_script('rgfb-from-render-data-script', plugin_dir_url(__FILE__) . 'assets/js/form_render_data.js', array('jquery'), '1.0', false);
        wp_localize_script('rgfb-from-render-data-script', 'formData', $formData);
        $openai_api_key = get_option('rgfb_openai_api_key');
        $openai_model = get_option('rgfb_openai_model');
        $image_generation_model = get_option('rgfb_image_generation_model');
        $image_generation_strength = get_option('rgfb_image_generation_strength');
        $limit_image_requests_by_ip = get_option('rgfb_limit_image_requests_by_ip');

        // Get logs by ip
        $ip_segments = explode('.', filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP));
        $ip_searchable = implode('.', array_slice($ip_segments, 0, 2));

        $args = array(
            'post_type' => 'rgfb_llm_logs',
            'meta_query' => array(
                array(
                    'key' => 'rgfb_llm_logs_ip',
                    'value' => $ip_searchable,
                    'compare' => 'LIKE',
                ),
            ),
        );

        // Create a new WP_Query instance
        $query = new WP_Query($args);

        // Get the number of posts found
        $number_of_posts = $query->found_posts;
        $images = 0;
        while ($query->have_posts()) : $query->the_post();
            // Get post ID
            $post_id = get_the_ID();

            // Get the post creation date
            $post_date = get_the_date('Y-m-d H:i:s');

            // Get all post meta data for the current post
            $all_meta_data = get_post_meta($post_id);
            $rgfb_llm_logs = unserialize($all_meta_data['rgfb_llm_logs'][0]);
            if (!empty($rgfb_llm_logs['image_url'])) {
                $images += 1;
            }
        endwhile;


        $post_title = trim(wp_title('', false));

        ob_start(); // Start output buffering
        echo "<div id='rgfb-form-div'>"
            . "<input type='hidden' id='rgfb_post_title' value='" . esc_attr($post_title) . "' />"
            . "<input type='hidden' id='include_post_or_page_title_in_the_prompt' value='" . esc_attr($include_post_or_page_title_in_the_prompt) . "' />"
            . "<input type='hidden' id='dalle_image_size_select' value='" . esc_attr($dalle_image_size_select) . "' />"
            . "<input type='hidden' id='is_load_response_on_page_load' value='" . esc_attr($is_load_response_on_page_load) . "' />"
            . "<input type='hidden' id='openai_model' value='" . esc_attr($openai_model) . "' />"
            . "<input type='hidden' id='image_generation_model' value='" . esc_attr($image_generation_model) . "' />"
            . "<input type='hidden' id='image_generation_strength' value='" . esc_attr($image_generation_strength) . "' />"
            . "<input type='hidden' name='openai_api_key' id='openai_api_key' value='" . esc_attr($openai_api_key) . "'  />"
            . "<input type='hidden' name='limit_image_requests_by_ip' id='limit_image_requests_by_ip' value='" . esc_attr($limit_image_requests_by_ip) . "'  />"
            . "<input type='hidden' name='number_of_image_requests_by_ip' id='number_of_image_requests_by_ip' value='" . esc_attr($images) . "'  />"
            . "<form  class='" . esc_attr($form_style) . "' id='rgfb-form-render' method='post'></form>"
            . "</div>";

        return ob_get_clean();
    }
}

global $rgfbFrontClass;

$rgfbFrontClass = new RGFBFront();
