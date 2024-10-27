<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap" id="rgfb_from_builder_wrap">
    <?php esc_html(wp_nonce_field('save-form-nonce', 'save_form_nonce')); ?>
    <h2>Drag-and-Drop Form Builder</h2>
    <div class="row">
        <div class="col-md-6">
            <label for="rg_frmbulder_form_name">Form Name: </label>
            <input type="text" name="rg_frmbulder_form_name" id="rg_frmbulder_form_name" placeholder="Enter Form Name" class="form-control">
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
                        <div class="image" style="background-image: url(<?php echo  esc_url(plugin_dir_url(__DIR__) . 'assets/images/01.png'); ?>)"></div>
                    </label>
                </div>

                <div>
                    <label class="radio-img">
                        <input type="radio" name="layout" value="formstyle2" />
                        <div class="image" style="background-image: url(<?php echo  esc_url(plugin_dir_url(__DIR__) . 'assets/images/02.png'); ?>)"></div>
                    </label>
                </div>

                <div>
                    <label class="radio-img">
                        <input type="radio" name="layout" value="formstyle3" />
                        <div class="image" style="background-image: url(<?php echo  esc_url(plugin_dir_url(__DIR__) . 'assets/images/03.png'); ?>)"></div>
                    </label>
                </div>

                <div>
                    <label class="radio-img">
                        <input type="radio" name="layout" value="formstyle4" />
                        <div class="image" style="background-image: url(<?php echo  esc_url(plugin_dir_url(__DIR__) . 'assets/images/04.png'); ?>)"></div>
                    </label>
                </div>

                <div>
                    <label class="radio-img">
                        <input type="radio" name="layout" value="formstyle5" />
                        <div class="image" style="background-image: url(<?php echo  esc_url(plugin_dir_url(__DIR__) . 'assets/images/05.png'); ?>)"></div>
                    </label>
                </div>

                <div>
                    <label class="radio-img">
                        <input type="radio" name="layout" value="formstyle6" />
                        <div class="image" style="background-image: url(<?php echo  esc_url(plugin_dir_url(__DIR__) . 'assets/images/06.png'); ?>)"></div>
                    </label>
                </div>

                <div>
                    <label class="radio-img">
                        <input type="radio" name="layout" value="formstyle7" />
                        <div class="image" style="background-image: url(<?php echo  esc_url(plugin_dir_url(__DIR__) . 'assets/images/07.png'); ?>)"></div>
                    </label>
                </div>

                <div>
                    <label class="radio-img">
                        <input type="radio" name="layout" value="formstyle8" />
                        <div class="image" style="background-image: url(<?php echo  esc_url(plugin_dir_url(__DIR__) . 'assets/images/08.png'); ?>)"></div>
                    </label>
                </div>

            </div>
        </div>

        <div>

            <div id="custom_form_style">
                <input type="checkbox" name="custom_form_style_chk" id="rgfb_custom_form_style_chk" value="on"> Custom Form Style
                <div id="custom_form_style_element" class="d-none">
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

                <div class="message_instruction">
                    <p>Fill out the sections below to shape and design your form. If you want to give prompt instructions,
                        use the 'hidden input' option. Don't forget to include a 'submit' button so your form can be sent
                        and the info can be worked on.</p>
                </div>

            </div>
            <div class="mb-3"></div>
            <div class="build_wrap_area" id="build-wrap"></div>
            <div class="new-action-buttons mt-4">
                <button id="save-data" class="save_btn">Save</button>
                <button id="trash" class="clear_btn">Clear All</button>
            </div>
        </div>