<?php
if (!defined('ABSPATH')) exit;
$openai_api_key = get_option('rgfb_openai_api_key');
$openai_model = get_option('rgfb_openai_model');
?>
<style>
    .addReadMore.showlesscontent .SecSec,
    .addReadMore.showlesscontent .readLess {
        display: none;
    }

    .addReadMore.showmorecontent .readMore {
        display: none;
    }

    .addReadMore .readMore,
    .addReadMore .readLess {
        font-weight: bold;
        margin-left: 2px;
        color: blue;
        cursor: pointer;
    }

    .addReadMoreWrapTxt.showmorecontent .SecSec,
    .addReadMoreWrapTxt.showmorecontent .readLess {
        display: block;
    }
</style>
<div class="wrap" id="rgfb_from_builder_wrap">
    <form id="rgfb_settings">
        <div class="row">
            <div class="col-md-12">
                <h2>Logs</h2>
            </div>
            <div class="col-md-12">

                <div id="primary" class="content-area">
                    <main id="main" class="site-main" role="main">

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <header class="entry-header">
                                <h1 class="entry-title"><?php the_title(); ?></h1>
                            </header>

                            <div class="entry-content">
                                <!-- Your custom table HTML goes here -->
                                <table class="table table-striped logs-table">
                                    <thead>
                                        <tr>
                                            <th>User IP</th>
                                            <th>Open AI Model</th>
                                            <th>Total Tokens</th>
                                            <th>Prompt Tokens</th>
                                            <th>Completion Tokens</th>
                                            <th>Image Cost</th>
                                            <th>Text Cost</th>
                                            <th>Total Cost (USD)</th>
                                            <th>Query</th>
                                            <th>Response</th>
                                            <th>Image</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Define the number of posts per page
                                        $posts_per_page = 10;

                                        // Get the current page number

                                        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
                                        // Update your WP_Query
                                        $custom_query = new WP_Query(array(
                                            'post_type' => 'rgfb_llm_logs',
                                            'posts_per_page' => $posts_per_page,
                                            'paged' => $current_page,
                                        ));
                                        // Example loop to fetch data
                                        // $custom_query = new WP_Query('post_type=rgfb_llm_logs&posts_per_page=-1');
                                        while ($custom_query->have_posts()) : $custom_query->the_post();
                                            // Get post ID
                                            $post_id = get_the_ID();

                                            // Get the post creation date
                                            $post_date = get_the_date('Y-m-d H:i:s');

                                            // Get all post meta data for the current post
                                            $all_meta_data = get_post_meta($post_id);
                                            $rgfb_llm_logs = unserialize($all_meta_data['rgfb_llm_logs'][0]);

                                        ?>
                                            <tr>
                                                <td><?php echo esc_html($rgfb_llm_logs['user_ip']); ?></td>
                                                <td><?php echo esc_html($rgfb_llm_logs['openai_model']); ?></td>
                                                <td><?php echo esc_html($rgfb_llm_logs['total_tokens']); ?></td>
                                                <td><?php echo esc_html($rgfb_llm_logs['prompt_tokens']); ?></td>
                                                <td><?php echo esc_html($rgfb_llm_logs['completion_tokens']); ?></td>
                                                <td><?php echo esc_html($rgfb_llm_logs['output_type'] == 'text_image' ? '0.040' : '0'); ?></td>
                                                <td><?php echo esc_html($rgfb_llm_logs['total_cost']); ?></td>
                                                <td>$<?php echo esc_html($rgfb_llm_logs['output_type'] == 'text_image' ? ($rgfb_llm_logs['total_cost'] + 0.040) : $rgfb_llm_logs['total_cost']); ?></td>
                                                <td width="10%" class="addReadMore showlesscontent"><?php echo wp_kses_post($rgfb_llm_logs['prompt']); ?></td>
                                                <td width="10%" class="addReadMore showlesscontent"><?php echo wp_kses_post($rgfb_llm_logs['answer']); ?></td>
                                                <td class="image-link">
                                                    <?php
                                                    if (@getimagesize($rgfb_llm_logs['image_url']) && !empty($rgfb_llm_logs['image_url']) && $rgfb_llm_logs['image_url'] != 'undefined') {
                                                        echo '<a href="' . esc_url($rgfb_llm_logs['image_url']) . '" download target="_blank"><strong>Download</strong></a>';
                                                    } else {
                                                        echo '';
                                                    } ?>
                                                </td>
                                                <td><?php echo esc_html($post_date); ?></td>
                                            </tr>

                                        <?php endwhile; ?>
                                        <tr class="pagination">
                                            <td colspan="12">
                                                <?php
                                                // Display pagination
                                                $big = 999999999; // an unlikely integer
                                                $paginate_links = paginate_links(array(
                                                    'base'    => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                                                    'format'  => '?paged=%#%',
                                                    'current' => $current_page,
                                                    'total'   => $custom_query->max_num_pages,
                                                ));
                                                echo wp_kses_post($paginate_links);

                                                ?>
                                            </td>
                                        </tr>

                                        <?php wp_reset_postdata(); ?>
                                    </tbody>
                                </table>

                            </div><!-- .entry-content -->

                            <footer class="entry-footer">
                                <?php edit_post_link('Edit', '<span class="edit-link">', '</span>'); ?>
                            </footer>
                        </article><!-- #post-<?php the_ID(); ?> -->

                    </main><!-- #main -->
                </div><!-- #primary -->

            </div>
        </div>
    </form>
</div>

<script>
    function AddReadMore() {
        //This limit you can set after how much characters you want to show Read More.
        var charLmt = 0;
        // Text to show when text is collapsed
        var readMoreTxt = "View";
        // Text to show when text is expanded
        var readLessTxt = "Hide";


        //Traverse all selectors with this class and manupulate HTML part to show Read More
        jQuery(".addReadMore").each(function() {
            if (jQuery(this).find(".firstSec").length)
                return;

            var allstr = jQuery(this).text();
            console.log('allstr', allstr)
            if (allstr.length > charLmt) {
                var firstSet = allstr.substring(0, charLmt);
                var secdHalf = allstr.substring(charLmt, allstr.length);
                var strtoadd = firstSet + "<span class='SecSec'>" + secdHalf + "</span><span class='readMore'  title='Click to View Response'>" + readMoreTxt + "</span><span class='readLess' title='Click to Show Less'>" + readLessTxt + "</span>";
                jQuery(this).html(strtoadd);
            }

        });
        //Read More and Read Less Click Event binding
        jQuery(document).on("click", ".readMore, .readLess", function() {
            jQuery(this).closest(".addReadMore").toggleClass("showlesscontent showmorecontent");
        });
    }
    AddReadMore();
</script>