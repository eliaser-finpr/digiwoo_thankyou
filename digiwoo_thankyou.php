<?php
/**
 * Plugin Name: YPF Thank You Page Woocommerce
 * Description: Custom Thank You page for WooCommerce
 * Version: 1.1.3
 * Author: Ardika JM-Consulting
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Digiwoo_ThankYou')) {

    class Digiwoo_ThankYou {

        public function __construct() {
            add_action('plugins_loaded', array($this, 'init'));
            add_action('woocommerce_thankyou', array($this, 'custom_redirect'));
            add_filter('woocommerce_get_settings_pages', array($this, 'add_settings_page'));
        }

        public function init() {
            if (!class_exists('WC_Payment_Gateway')) {
                return;
            }
            
            add_action('woocommerce_loaded', function() {
                include_once 'settings.php';
            });

        }

        public function custom_redirect($order_id) {
            
            
            $enabled = get_option('digiwoo_thankyou_enabled');
            $thank_you_page = get_option('digiwoo_thankyou_page');
            $failed_page = get_option('digiwoo_failed_page');
            
            $order = wc_get_order($order_id);
            $order_status = $order->get_status();

            // If we're already on the custom thank you page or failed payment page, exit early to avoid redirection loop.
            if (is_page($thank_you_page) || is_page($failed_page)) {
                return;
            }

            // Redirect to failed payment page for certain order statuses
            if (in_array($order_status, ['failed', 'cancelled', 'pending', 'on-hold'])) {
                $redirect_failed_url = add_query_arg(
                    array(
                        'order-received' => $order_id,
                        'key'            => $order->get_order_key(),
                    ),
                    get_permalink($failed_page)
                );
                
                if ($redirect_failed_url) {
                    wp_safe_redirect($redirect_failed_url);
                    exit;
                }
            }

            // Redirect to custom thank you page for other cases
            if ($enabled === 'yes' && $thank_you_page) {
                $redirect_url = add_query_arg(
                    array(
                        'order-received' => $order_id,
                        'key'            => $order->get_order_key(),
                    ),
                    get_permalink($thank_you_page)
                );
                
                if ($redirect_url) {
                    wp_safe_redirect($redirect_url);
                    exit;
                }
            }
        }


        public function add_settings_page($settings) {
            $settings[] = include 'settings.php';
            return $settings;
        }
    }

    new Digiwoo_ThankYou();
}

function order_status_title_shortcode() {
    // Check if the necessary query parameters are present
    if ( isset( $_GET['order-received'] ) && isset( $_GET['key'] ) ) {
        $order_id = sanitize_text_field( $_GET['order-received'] );
        $order_key = sanitize_text_field( $_GET['key'] );

        // Get the order object
        $order = wc_get_order( $order_id );

        // Check if the order exists and the order key matches
        if ( $order && $order->get_order_key() == $order_key ) {
            // Check the order status
            switch ( $order->get_status() ) {
                case 'completed':
                    return '<h2>Payment Status: Success</h2>';
                case 'failed':
                case 'pending':
                case 'cancelled':
                    return '<h2>Payment Status: Failed</h2>';
                default:
                    return '<h2>Payment Status: ' . ucfirst( $order->get_status() ) . '</h2>';
            }
        }
    }
    return '<h1>Invalid Order</h1>';
}
add_shortcode( 'order_status_title', 'order_status_title_shortcode' );

add_filter( 'body_class', 'custom_order_status_body_class' );
function custom_order_status_body_class( $classes ) {
    if ( is_page() && isset( $_GET['order-received'] ) && isset( $_GET['key'] ) ) {
        $order_id = sanitize_text_field( $_GET['order-received'] );
        $order_key = sanitize_text_field( $_GET['key'] );

        $order = wc_get_order( $order_id );

        if ( $order && $order->get_order_key() == $order_key ) {
            switch ( $order->get_status() ) {
                case 'completed':
                    $classes[] = 'ypf-apg-order-completed';
                    break;
                case 'failed':
                case 'pending':
                case 'cancelled':
                    $classes[] = 'ypf-apg-order-failed';
                    break;
            }
        }
    }
    return $classes;
}

/**
 * Register scripts and styles for Elementor test widgets.
 */
function digiwoo_thank_you_redirect_enqueue() {
    wp_register_script( 'digiwoo-thank-you-js', plugins_url( '/public/assets/js/digiwoo_thankyou.js', __FILE__ ), array('jquery'), '1.0.0', true );
    wp_enqueue_script('digiwoo-thank-you-js');
}
add_action( 'wp_enqueue_scripts', 'digiwoo_thank_you_redirect_enqueue');