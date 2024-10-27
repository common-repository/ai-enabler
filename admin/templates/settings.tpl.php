<?php
if (!defined('ABSPATH')) exit;
$openai_api_key = get_option('rgfb_openai_api_key');
$openai_model = get_option('rgfb_openai_model');
$image_generation_model = get_option('rgfb_image_generation_model');
$limit_image_requests_by_ip = get_option('rgfb_limit_image_requests_by_ip');
$image_generation_strength = get_option('rgfb_image_generation_strength');
?>
<div class="wrap" id="rgfb_from_builder_wrap">
    <form id="rgfb_settings">
        <?php esc_html(wp_nonce_field('save-settings-nonce', 'save_settings_nonce')); ?>
        <div class="row">
            <div class="col-md-12">
                <h2>Settings</h2>
            </div>
            <div class="col-md-4">
                <label for="rgfb_openai_api_key">OpenAI API KEY: </label>
                <input type="text" name="rgfb_openai_api_key" id="rgfb_openai_api_key" placeholder="Enter OpenAI API Key" class="form-control" value="<?php echo esc_attr(!empty($openai_api_key) ? $openai_api_key : '') ?>">
            </div>
            <div class="col-md-4">
                <label for="rgfb_openai_model">Text Generation Model: </label>
                <select name="rgfb_openai_model" id="rgfb_openai_model" class="form-control form-select">
                    <option value="" disabled selected>Select Text Generation Model</option>
                    <option value="gpt-4-1106-preview" <?php echo $openai_model == 'gpt-4-1106-preview' ? 'selected' : '' ?>>gpt-4-1106-preview</option>
                    <option value="gpt-4" <?php echo $openai_model == 'gpt-4' ? 'selected' : '' ?>>gpt-4</option>
                    <option value="gpt-4-32k" <?php echo $openai_model == 'gpt-4-32k' ? 'selected' : '' ?>>gpt-4-32k</option>
                    <option value="gpt-3.5-turbo-1106" <?php echo $openai_model == 'gpt-3.5-turbo-1106' ? 'selected' : '' ?>>gpt-3.5-turbo-1106</option>
                    <option value="gpt-3.5-turbo" <?php echo $openai_model == 'gpt-3.5-turbo' ? 'selected' : '' ?>>gpt-3.5-turbo</option>
                    <option value="gpt-3.5-turbo-16k" <?php echo $openai_model == 'gpt-3.5-turbo-16k' ? 'selected' : '' ?>>gpt-3.5-turbo-16k</option>
                    <option value="text-davinci-003" <?php echo $openai_model == 'text-davinci-003' ? 'selected' : '' ?>>text-davinci-003</option>
                    <option value="text-davinci-002" <?php echo $openai_model == 'text-davinci-002' ? 'selected' : '' ?>>text-davinci-002</option>
                    <option value="text-curie-001" <?php echo $openai_model == 'text-curie-001' ? 'selected' : '' ?>>text-curie-001</option>
                    <option value="text-babbage-001" <?php echo $openai_model == 'text-babbage-001' ? 'selected' : '' ?>>text-babbage-001</option>
                    <option value="text-ada-001" <?php echo $openai_model == 'text-ada-001' ? 'selected' : '' ?>>text-ada-001</option>
                    <option value="davinci" <?php echo $openai_model == 'davinci' ? 'selected' : '' ?>>davinci</option>
                    <option value="curie" <?php echo $openai_model == 'curie' ? 'selected' : '' ?>>curie</option>
                    <option value="babbage" <?php echo $openai_model == 'babbage' ? 'selected' : '' ?>>babbage</option>
                    <option value="ada" <?php echo $openai_model == 'ada' ? 'selected' : '' ?>>ada</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="rgfb_openai_model">Image Generation Model: </label>
                <select name="rgfb_image_generation_model" id="rgfb_image_generation_model" class="form-control form-select">
                    <option value="" disabled selected>Select Image Generation Model</option>
                    <option value="dalle3" <?php echo $image_generation_model == 'dalle3' ? 'selected' : '' ?>>Dall-E 3</option>
                    <option value="stabilityai" <?php echo $image_generation_model == 'stabilityai' ? 'selected' : '' ?>>Stability AI</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="rgfb_openai_model">Image Generation Strength: <br><small>Set the Image Strength from 0 to 1. Default 0.35</small></label>
                <input type="number" class="form-control" id="rgfb_image_generation_strength" name="rgfb_image_generation_strength" min="0" max="1" value="<?php echo esc_attr(!empty($image_generation_strength) ? $image_generation_strength : 0.35); ?>">
            </div>
            <div class="col-md-4 mt-3">
                <label for="rgfb_limit_image_requests_by_ip">Limit Image Requests by IP: </label>
                <input type="text" name="rgfb_limit_image_requests_by_ip" id="rgfb_limit_image_requests_by_ip" placeholder="How many image requests for free users" class="form-control" value="<?php echo esc_attr(!empty($limit_image_requests_by_ip) ? $limit_image_requests_by_ip : '') ?>">
            </div>
            <input type="hidden" class="form-control" id="rgfb_image_generation_strength" name="rgfb_image_generation_strength" min="0" max="1" value="0.7">
            <input type="hidden" name="rgfb_limit_image_requests_by_ip" id="rgfb_limit_image_requests_by_ip" placeholder="How many image requests for free users" class="form-control" value="10">
            <div class="col-md-12">
                <div class="select_form_style_section">
                    <div class="new-action-buttons mt-4">
                        <button id="save-settings" class="save_btn">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>