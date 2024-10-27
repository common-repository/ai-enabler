<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class RGFB_Form_Builder_List_Table extends WP_List_Table
{
    function __construct()
    {
        global $status, $page;

        parent::__construct(array(
            'singular' => 'Item',
            'plural' => 'Items',
            'ajax' => false
        ));
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }



    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],  //Let's simply repurpose the table's singular label ("movie")
            $item['id']                //The value of the checkbox should be the record's id
        );
    }

    function get_columns()
    {
        $columns = array(
            'cb'        => '<input type="checkbox" />',
            'title' => __('Name', 'ai-enabler'),
            'shortcode' => __('Shortcode', 'ai-enabler'),
            'author' => __('Author', 'ai-enabler'),
            'date' => __('Date', 'ai-enabler'),
            // Add more columns as needed
        );
        return $columns;
    }
    function get_sortable_columns()
    {
        $sortable_columns = array(
            'title'     => array('title', true),     //true means it's already sorted
            'shortcode'    => array('shortcode', false),
        );
        return $sortable_columns;
    }

    function get_bulk_actions()
    {
        $actions = array(
            'delete'    => 'Delete'
        );
        return $actions;
    }


    function process_bulk_action()
    {
        // security check!
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {

            $nonce  = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            if (!wp_verify_nonce($nonce, $action))
                wp_die('Nope! Security check failed!');
        }

        $action = $this->current_action();

        //Detect when a bulk action is being triggered...
        if ($action === 'delete') {
            $id = isset($_REQUEST['id']) ? sanitize_text_field($_REQUEST['id']) : null;
            if (!empty($id)) {
                $ids[] = $id;
                foreach ($ids as $id) {
                    // Perform deletion for each record (post) with the provided ID
                    wp_delete_post($id, true); // Set the second parameter to true to force delete (bypassing trash)
                    echo '<div class="notice notice-success is-dismissible"><p>Records has been deleted.</p></div>';
                }
            }
        }

        if ($action === 'bulk_trash') {

            $delete_ids = isset($_POST['bulk-delete']) ? (
                is_array($_POST['bulk-delete']) ?
                array_map('absint', array_map('sanitize_text_field', $_POST['bulk-delete'])) :
                array_map('absint', array_map('sanitize_text_field', isset($_POST['bulk-delete']) ? $_POST['bulk-delete'] : array()))
            ) : array();            

            foreach ($delete_ids as $id) {
                self::delete_item($id);
            }

            // show admin notice
            echo '<div class="notice notice-success is-dismissible"><p>Bulk Deleted.</p></div>';
        }
    }
    
    function prepare_items()
    {

        $this->process_bulk_action();

        $args = array(
            'post_type' => 'rg_form_builder_post',
            'posts_per_page' => -1, // Retrieve all entries
            'orderby' => 'date', // Order by date or any other parameter
            'order' => 'DESC' // Order in descending order, change to 'ASC' for ascending
        );

        $custom_query = new WP_Query($args);

        if ($custom_query->have_posts()) {
            while ($custom_query->have_posts()) {
                $custom_query->the_post();
                // Get and display the custom meta data
                $form_structure = get_post_meta(get_the_ID(), 'structure', true);

                $title = get_the_title();
                $shortcode = get_post_meta(get_the_ID(), 'shortcode', true);
                $data[] = [
                    "id" => get_the_ID(),
                    'title' => $title,
                    'shortcode' => $shortcode,
                    "author" => 'asd',
                    "date" => 'asd',
                ];
            }
        }
        wp_reset_postdata();

        if ($custom_query->have_posts()) {
            $sortable = $this->get_sortable_columns();
            $this->_column_headers = array($this->get_columns(), array(), array());

            $per_page = 10;
            $current_page = $this->get_pagenum();

            // Slice the data to display the current page
            $data_slice = array_slice($data, ($current_page - 1) * $per_page, $per_page);

            $total_items = count($data);
            $this->items = $data_slice;

            $this->set_pagination_args(array(
                'total_items' => $total_items,                  //WE have to calculate the total number of items
                'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
                'total_pages' => ceil($total_items / $per_page)   //WE have to calculate the total number of pages
            ));
        }
        
        echo '<div class="wrap">
            <h1 class="wp-heading-inline">Form List</h1>
            <a href="'. esc_url(admin_url('admin.php?page=ai-enabler')).'" class="page-title-action">Add Form</a>
            <hr class="wp-header-end">
        </div>';
        
    }

    function column_title($item)
    {
        // Sanitize the page, action, id, and paged variables
        $page = isset($_REQUEST['page']) ? sanitize_text_field($_REQUEST['page']) : '';
        $action = 'edit'; // Generally static but ensure it's a valid action if it's dynamic
        $id = isset($item['id']) ? absint($item['id']) : 0; // Use absint to ensure it's a positive integer
        $paged = isset($_REQUEST['paged']) ? absint($_REQUEST['paged']) : '';
        $nonce = wp_create_nonce('form-list-nonce-' . $id, 'nonce');


        // Build links using sanitized and validated data
        $edit_link = sprintf('<a href="?page=%s&action=%s&id=%s&nonce=%s">Edit</a>', esc_attr($page), esc_attr($action), esc_attr($id), esc_attr($nonce));
        $copy_link = sprintf('<a href="?page=%s&action=copy&id=%s&nonce=%s">Copy</a>', esc_attr($page), esc_attr($id), esc_attr($nonce));
        $delete_link = sprintf('<a href="?page=%s&action=delete&id=%s&nonce=%s">Delete</a>', esc_attr($page), esc_attr($id), esc_attr($nonce));

        // Append paged parameter if set
        if ($paged) {
            $edit_link = sprintf('<a href="?page=%s&action=edit&id=%s&paged=%s&nonce=%s">Edit</a>', esc_attr($page), esc_attr($id), esc_attr($paged), esc_attr($nonce));
            $copy_link = sprintf('<a href="?page=%s&action=copy&id=%s&paged=%s&nonce=%s">Copy</a>', esc_attr($page), esc_attr($id), esc_attr($paged), esc_attr($nonce));
            $delete_link = sprintf('<a href="?page=%s&action=delete&id=%s&paged=%s&nonce=%s">Delete</a>', esc_attr($page), esc_attr($id), esc_attr($paged), esc_attr($nonce));
        }

        //Build row actions
        $actions = array(
            'edit'      => $edit_link,
            'copy'      => $copy_link,
            'delete'    => $delete_link,
        );

        //Return the title contents
        return sprintf(
            '<strong>%1$s</strong> <span style="color:silver"></span>%3$s',
            esc_html($item['title']),
            esc_attr($item['id']),
            $this->row_actions($actions)
        );
    }


    public function column_shortcode($item)
    {
        $shortcodes = array($item['shortcode']);

        $output = '';

        foreach ($shortcodes as $shortcode) {
            $output .= "\n" . '<span class="shortcode"><input type="text"'
                . ' onfocus="this.select();" readonly="readonly"'
                . ' value="' . esc_attr($shortcode) . '"'
                . ' class="large-text code" /></span>';
        }

        return trim($output);
    }

    public function column_author($item)
    {
        $post = get_post($item['id']);

        if (!$post) {
            return;
        }

        $author = get_userdata($post->post_author);

        if (false === $author) {
            return;
        }

        return esc_html($author->display_name);
    }

    public function column_date($item)
    {
        $datetime = get_post_datetime($item['id']);
        //return $datetime;
        if (false === $datetime) {
            return '';
        }

        $t_time = sprintf(
            /* translators: 1: date, 2: time */
            __('%1$s at %2$s', 'ai-enabler'),
            $datetime->format(__('Y/m/d', 'ai-enabler')),
            $datetime->format(__('g:i a', 'ai-enabler'))
        );

        return $t_time;
    }

    public function column_id($item)
    {
        $post = get_post($item['id']);

        if (!$post) {
            return;
        }

        return esc_html($item['id']);
    }
}

// function rgfb_handle_bulk_actions()
// {
//     // Check if the bulk action is for trashing
//     if (isset($_POST['action']) && $_POST['action'] === 'bulk-trash') {
//         $ids = isset($_POST['item']) ? $_POST['item'] : array();

//         foreach ($ids as &$id) {
//             $id = filter_var($id, FILTER_SANITIZE_STRING);
//             wp_trash_post($id);
//         }
//     }
// }

// add_action('admin_action_bulk-trash', 'rgfb_handle_bulk_actions');
