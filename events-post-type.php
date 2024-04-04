<?php
/*
Plugin Name: Custom Events Plugin
Description: This plugin creates a post type Events and we can display it's content through a shortcode  [events-display]
Author: Muhammad Qurban
Version: 1.0
*/

if (!defined('ABSPATH')) {
    exit;
}


function events_scripts()
{
    wp_enqueue_style('events-css', plugin_dir_url(__FILE__) . '/style.css');
    wp_enqueue_script('events-js', plugin_dir_url(__FILE__) . '/main.js');
}

add_action('wp_enqueue_scripts', 'events_scripts');



// Register Post type 

function custom_events_post_type()
{
    register_post_type('events', array(

        'menu_icon' => 'dashicons-airplane',
        'public' => true,
        'labels' => array(
            'name' => 'Events',
            'singular_name' => 'Event',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Event',
            'all_items' => 'All Events',
            'edit_item' => 'Edit Event'
        ),
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),

    ));
}

add_action('init', 'custom_events_post_type');


// Display it on front end 

function display_events_shortcode($atts)
{



    $atts = shortcode_atts(array(
        'posts_per_page' => -1,
        'hide_thumbnail' => false,
        'hide_excerpt' => false,
    ), $atts);


    $posts_per_page = intval($atts['posts_per_page']);
    $hide_thumbnail = $atts['hide_thumbnail'];
    $hide_excerpt = $atts['hide_excerpt'];


    $events_query = new WP_Query(array(
        'post_type' => 'events',
        'posts_per_page' => $posts_per_page,
    ));

    if ($events_query->have_posts()) {
        $output = ''; // Initialize output inside the if statement

        $output .= '<ul class="event-list">';

        while ($events_query->have_posts()) {
            $events_query->the_post();

            $output .= '<li>';
            $output .= '<h2>' . get_the_title() . '</h2>';

            if (!$hide_excerpt) {
                $output .= '<div>' . get_the_excerpt() . '</div>';
            }

            if (!$hide_thumbnail) {
                $output .= '<div>' . get_the_post_thumbnail() . '</div>'; // Consider error handling here
            }

            $output .= '<div><a href="' . get_permalink() . '">' . 'Read More' . '</a></div>';

            $output .= '</li>';
        }

        $output .= '</ul>';

        return $output;
    } else {
        // Handle the case where no events are found (optional)
        return '<p>No events found.</p>';
    }
}

add_shortcode('events-display', 'display_events_shortcode');


// Register custom module for Elementor
add_action('elementor/widgets/widgets_registered', function () {
    class Elementor_Custom_Events_Widget extends \Elementor\Widget_Base
    {

        public function get_name()
        {
            return 'custom-events-widget';
        }

        public function get_title()
        {
            return __('Custom Events', 'your-text-domain');
        }

        public function get_icon()
        {
            return 'eicon-posts-grid';
        }

        public function get_categories()
        {
            return ['general'];
        }

        protected function _register_controls()
        {
            // Define widget controls and settings here
            $this->start_controls_section(
                'section_content',
                [
                    'label' => __('Content', 'your-text-domain'),
                ]
            );

            $this->add_control(
                'posts_per_page',
                [
                    'label' => __('Number of Posts', 'your-text-domain'),
                    'type' => \Elementor\Controls_Manager::NUMBER,
                    'default' => 5, // Default number of posts
                    'min' => -1, // Minimum value allowed (-1 means no limit)
                ]
            );

            $this->add_control(
                'hide_thumbnail',
                [
                    'label' => __('Hide Thumbnail', 'your-text-domain'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __('Yes', 'your-text-domain'),
                    'label_off' => __('No', 'your-text-domain'),
                    'default' => 'yes',
                ]
            );

            $this->add_control(
                'hide_excerpt',
                [
                    'label' => __('Hide Excerpt', 'your-text-domain'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __('Yes', 'your-text-domain'),
                    'label_off' => __('No', 'your-text-domain'),
                    'default' => 'yes',
                ]
            );

            $this->end_controls_section();
        }

        protected function render()
        {
            $settings = $this->get_settings_for_display();

            // Extract settings
            $posts_per_page = $settings['posts_per_page'];
            $hide_thumbnail = $settings['hide_thumbnail'] === 'yes';
            $hide_excerpt = $settings['hide_excerpt'] === 'yes';

            // Call your display function with the extracted settings
            $output = display_events_shortcode(array(
                'posts_per_page' => $posts_per_page,
                'hide_thumbnail' => $hide_thumbnail,
                'hide_excerpt' => $hide_excerpt,
            ));

            echo $output;
        }
    }

    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new Elementor_Custom_Events_Widget());
});
