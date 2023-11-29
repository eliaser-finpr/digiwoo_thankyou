<?php
/**
 * Plugin Name: Digiwoo Thank You Page Woocommerce
 * Description: Custom Thank You page for WooCommerce
 * Version: 1.1.2
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
            // If we're already on the custom thank you page, exit early to avoid redirection loop.
            if (is_page(get_option('digiwoo_thankyou_page'))) {
                return;
            }
            
            $enabled = get_option('digiwoo_thankyou_enabled');
            $thank_you_page = get_option('digiwoo_thankyou_page');
            
            if ($enabled === 'yes' && $thank_you_page) {
                $order = wc_get_order($order_id);
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

