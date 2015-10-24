<?php
    /**
     
    * Plugin Name: Tomate Ninja Cartoons
     
    * Plugin URI: tomateninja.com/tomate-ninja-cartoons
     
    * Description: A plugin to create cartoons and manage them easily
     
    * Version:  0.1
     
    * Author: Gabriel La Torre
     
    * Author URI: latorregabriel.com
     
    * License:  GPL2
     
    */
     
    add_action( 'init', 'tn_cartoons_init' );
     
    /**
     
    * Register an cartoon post type.
     
    *
     
    */
     
    function tn_cartoons_init() {

        $labels = array(

            'name' => __('Cartoons', 'post type general name', 'tn_cartoons'),

            'singular_name' => __('Cartoon', 'post type singular name', 'tn_cartoons'),

            'menu_name' => __('Cartoons', 'admin menu', 'tn_cartoons'),

            'name_admin_bar' => __('Cartoon', 'add new on admin bar', 'tn_cartoons'),

            'add_new' => __('Add New', 'cartoon', 'tn_cartoons'),

            'add_new_item' => __('Add New Cartoon', 'tn_cartoons'),

            'new_item' => __('New Cartoon', 'tn_cartoons'),

            'edit_item' => __('Edit Cartoon', 'tn_cartoons'),

            'view_item' => __('View Cartoon', 'tn_cartoons'),

            'all_items' => __('All Cartoons', 'tn_cartoons'),

            'search_items' => __('Search Cartoons', 'tn_cartoons'),

            'parent_item_colon' => __('Parent Cartoons:', 'tn_cartoons'),

            'not_found' => __('No cartoons found.', 'tn_cartoons'),

            'not_found_in_trash' => __('No cartoons found in Trash.', 'tn_cartoons')


        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'cartoon'),
            'capability_type' => 'post',
            'has_archive' => true,
            'Hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'author', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions')
        );


        register_post_type('cartoon', $args);

        /**
         * Adds a meta box to the post editing screen
         */
        function tn_cartoon_custom_meta()
        {
            add_meta_box('tn_cartoons_config_meta_box', __('Configuración del cartoon', 'tn_cartoons'), 'tn_meta_callback', 'cartoon');
        }

        add_action('add_meta_boxes', 'tn_cartoon_custom_meta');

        /**
         * Outputs the content of the meta box
         */
        function tn_meta_callback($post)
        {
            wp_nonce_field(basename(__FILE__), 'tn_link');
            $tn_cartoon_stored_meta = get_post_meta($post->ID);
            ?>

            <p>
                <label for="tn-cartoon-image" class="tn-row-title"><?php _e('Cartoon image', 'tn_cartoons') ?></label>
                <input type="text" name="tn-cartoon-image" id="tn-cartoon-image"
                       value="<?php if (isset ($tn_cartoon_stored_meta['tn-cartoon-image'])) echo $tn_cartoon_stored_meta['tn-cartoon-image'][0]; ?>"/>
                <input type="button" id="tn-cartoon-image-button" class="button"
                       value="<?php _e('Choose or Upload an Image', 'tn_cartoons') ?>"/>
            </p>
        <?php

        }

        /**
         * Saves the custom meta input
         */
        function tn_cartoon_save($post_id)
        {

            // Checks save status
            $is_autosave = wp_is_post_autosave($post_id);
            $is_revision = wp_is_post_revision($post_id);
            $is_valid_nonce = (isset($_POST['tn_link']) && wp_verify_nonce($_POST['tn_link'], basename(__FILE__))) ? 'true' : 'false';

            // Exits script depending on save status
            if ($is_autosave || $is_revision || !$is_valid_nonce) {
                return;
            }

            // Checks for input and sanitizes/saves if needed


            // Checks for input and saves if needed
            if (isset($_POST['tn-cartoon-image'])) {
                update_post_meta($post_id, 'tn-cartoon-image', $_POST['tn-cartoon-image']);
            }

        }

        add_action('save_post', 'tn_cartoon_save');

        /**
         * Loads the image management javascript
         */
        function tn_image_enqueue()
        {
            global $typenow;
            if ($typenow == 'cartoon') {
                wp_enqueue_media();

                // Registers and enqueues the required javascript.
                wp_register_script('meta-box-image', plugin_dir_url(__FILE__) . 'js/image-uploader.js', array('jquery'));
                wp_register_script('tn-fancy-box-js', plugin_dir_url(__FILE__) . 'js/fancyapps-fancyBox-18d1712/jquery.fancybox.js', array('jquery'));
                wp_localize_script('meta-box-image', 'meta_image',
                    array(
                        'title' => __('Choose or Upload an Image', 'prfx-textdomain'),
                        'button' => __('Use this image', 'prfx-textdomain'),
                    )
                );
                wp_enqueue_script('meta-box-image');
                wp_enqueue_script('tn-fancy-box-js');
                wp_enqueue_style('tn-fancy-box-css',  plugin_dir_url(__FILE__) . 'js/fancyapps-fancyBox-18d1712/jquery.fancybox.css');
            }
        }

        add_action('admin_enqueue_scripts', 'tn_image_enqueue');

    }


    // Creating the widget
    class wpb_widget extends WP_Widget {

        function __construct() {
            parent::__construct(
// Base ID of your widget
                'wpb_widget',

// Widget name will appear in UI
                __('Tomate Ninja Last Cartoon Widget', 'wpb_widget_domain'),

// Widget description
                array( 'description' => __( 'Add to see the ads you want', 'wpb_widget_domain' ), )
            );
        }

// Creating widget front-end
// This is where the action happens
        public function widget( $args, $instance ) {
// before and after widget arguments are defined by themes
            echo $args['before_widget'];

// This is where you run the code and display the output

                $queryObject = new WP_Query( 'post_type=cartoon&posts_per_page=1&orderby=date&order=asc' );
                // The Loop!
                if ($queryObject->have_posts()) {
                    ?>
                        <?php
                        while ($queryObject->have_posts()) {
                            $queryObject->the_post();
                            $tn_cartoon_stored_meta = get_post_meta(get_the_ID());
                            ?>

                            <div class="cartoon-box-widget">
                                <a href="<?php echo $instance['destination'] ?>">
                                    <div class="cartoon-box-thumbnail">
                                        <img src="<?php if (isset ($tn_cartoon_stored_meta['tn-cartoon-image'])) echo $tn_cartoon_stored_meta['tn-cartoon-image'][0]; ?>" />
                                    </div>
                                </a>
                            </div>
                        <?php
                        }
                        ?>
                <?php
                }
            echo $args['after_widget'];
        }

// Widget Backend
        public function form( $instance ) {
            if ( isset( $instance[ 'destination' ] ) ) {
                $destination = $instance[ 'destination' ];
            }
// Widget admin form
            ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'destination' ); ?>"><?php _e('Destination', 'tn_cartoons'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'destination' ); ?>" name="<?php echo $this->get_field_name( 'destination' ); ?>" type="text" value="<?php echo esc_attr( $destination ); ?>" />
            </p>
        <?php
        }

// Updating widget replacing old instances with new
        public function update( $new_instance, $old_instance ) {
            $instance = array();
            $instance['destination'] = ( ! empty( $new_instance['destination'] ) ) ? strip_tags( $new_instance['destination'] ) : '';
            return $instance;
        }
    } // Class wpb_widget ends here




    function tn_css_enqueue()
    {
        wp_register_style( 'tn-cartoon-widget-css', plugins_url('css/tomate-ninja-cartoon.css', __FILE__) );
        wp_enqueue_style( 'tn-cartoon-widget-css' );
    }

    add_action('wp_print_styles', 'tn_css_enqueue');

    // Register and load the widget
    function wpb_load_widget() {
        register_widget( 'wpb_widget' );
    }
    add_action( 'widgets_init', 'wpb_load_widget' );



    //Adding Template

    /* Filter the single_template with our custom function*/
//    add_filter('single_template', 'tn_catoon_template');
//
//    function tn_cartoon_template($single) {
//        global $wp_query, $post;
//
//        /* Checks for single template by post type */
//        if ($post->post_type == "POST TYPE NAME"){
//            if(file_exists(PLUGIN_PATH. '/templates/single-cartoon.php'))
//                return PLUGIN_PATH . '/templates/single-cartoon.php';
//        }
//        return $single;
//    }
