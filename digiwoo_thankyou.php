<?php
/**
 * Plugin Name: YPF Thank You Page Woocommerce
 * Description: Custom Thank You page for WooCommerce
 * Version: 1.1.4
 * Author: Ardika JM-Consulting
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Digiwoo_ThankYou')) {

    class Digiwoo_ThankYou
    {

        public function __construct()
        {
            add_action('plugins_loaded', array($this, 'init'));
            add_action('woocommerce_thankyou', array($this, 'custom_redirect'));
            add_filter('woocommerce_get_settings_pages', array($this, 'add_settings_page'));
        }

        public function init()
        {
            if (!class_exists('WC_Payment_Gateway')) {
                return;
            }

            add_action('woocommerce_loaded', function () {
                include_once 'settings.php';
            });

        }

        public function custom_redirect($order_id)
        {
            $enabled = get_option('digiwoo_thankyou_enabled');
            $digiwoo_thankyou_page_challenge_en = get_option('digiwoo_thankyou_page_challenge_en');
            $digiwoo_thankyou_page_free_trial_en = get_option('digiwoo_thankyou_page_free_trial_en');
            $digiwoo_thankyou_page_challenge_ja = get_option('digiwoo_thankyou_page_challenge_ja');
            $digiwoo_thankyou_page_free_trial_ja = get_option('digiwoo_thankyou_page_free_trial_ja');
            $failed_page = get_option('digiwoo_failed_page');

            $order = wc_get_order($order_id);
            $order_status = $order->get_status();

            // If we're already on the custom thank you page or failed payment page, exit early to avoid redirection loop.
            if (is_page($digiwoo_thankyou_page_challenge_en) || is_page($digiwoo_thankyou_page_free_trial_en) || is_page($digiwoo_thankyou_page_challenge_ja) || is_page($digiwoo_thankyou_page_free_trial_ja) || is_page($failed_page)) {
                return;
            }

            // Redirect to failed payment page for certain order statuses
            if (in_array($order_status, ['failed', 'cancelled', 'pending', 'on-hold'])) {
                $redirect_failed_url = add_query_arg(
                    array(
                        'order-received' => $order_id,
                        'key' => $order->get_order_key(),
                    ),
                    get_permalink($failed_page)
                );

                if ($redirect_failed_url) {
                    wp_safe_redirect($redirect_failed_url);
                    exit;
                }
            }

            // Redirect based on language if enabled
            if ($enabled === 'yes') {
                $redirect_url = $this->get_pll_language_redirect_url($order_id, $order, $digiwoo_thankyou_page_challenge_en, $digiwoo_thankyou_page_free_trial_en, $digiwoo_thankyou_page_challenge_ja, $digiwoo_thankyou_page_free_trial_ja);
                if ($redirect_url) {
                    wp_safe_redirect($redirect_url);
                    exit;
                }
            }
        }

        private function get_pll_language_redirect_url($order_id, $order, $digiwoo_thankyou_page_challenge_en, $digiwoo_thankyou_page_free_trial_en, $digiwoo_thankyou_page_challenge_ja, $digiwoo_thankyou_page_free_trial_ja)
        {
            $language = isset($_COOKIE['pll_language']) ? $_COOKIE['pll_language'] : null;
            $billing_cat_product = strtolower(str_replace(" ", "", $_POST['billing_cat_product']));

            switch ($language) {
                case 'en':
                    setcookie('billing_cat_product', $billing_cat_product, time() + 3600, '/');
                    if (!empty($billing_cat_product)) {
                        if ($billing_cat_product === 'challenge') {
                            return $this->get_redirect_url($digiwoo_thankyou_page_challenge_en, $order_id, $order);
                        }
                        if ($billing_cat_product === 'free-trial') {
                            return $this->get_redirect_url($digiwoo_thankyou_page_free_trial_en, $order_id, $order);
                        }
                    }
                case 'ja':
                    setcookie('billing_cat_product', $billing_cat_product, time() + 3600, '/');
                    if (!empty($billing_cat_product)) {
                        if ($billing_cat_product === 'challenge') {
                            return $this->get_redirect_url($digiwoo_thankyou_page_challenge_ja, $order_id, $order);
                        }
                        if ($billing_cat_product === 'free-trial') {
                            return $this->get_redirect_url($digiwoo_thankyou_page_free_trial_ja, $order_id, $order);
                        }
                    }
                default:
                    setcookie('billing_cat_product', $billing_cat_product, time() + 3600, '/');
                    if (!empty($billing_cat_product)) {
                        if ($billing_cat_product === 'challenge') {
                            return $this->get_redirect_url($digiwoo_thankyou_page_challenge_ja, $order_id, $order);
                        }
                        if ($billing_cat_product === 'free-trial') {
                            return $this->get_redirect_url($digiwoo_thankyou_page_free_trial_ja, $order_id, $order);
                        }
                    }
            }
        }

        private function get_redirect_url($url, $order_id, $order)
        {
            return add_query_arg(
                array(
                    'order-received' => $order_id,
                    'key' => $order->get_order_key(),
                ),
                get_permalink($url)
            );
        }

        public function add_settings_page($settings)
        {
            $settings[] = include 'settings.php';
            return $settings;
        }
    }

    new Digiwoo_ThankYou();
}

function order_status_title_shortcode()
{
    // Check if the necessary query parameters are present
    if (isset($_GET['order-received']) && isset($_GET['key'])) {
        $order_id = sanitize_text_field($_GET['order-received']);
        $order_key = sanitize_text_field($_GET['key']);

        // Get the order object
        $order = wc_get_order($order_id);

        // Check if the order exists and the order key matches
        if ($order && $order->get_order_key() == $order_key) {
            // Check the order status
            switch ($order->get_status()) {
                case 'completed':
                    return '<h2>Payment Status: Success</h2>';
                case 'failed':
                case 'pending':
                case 'cancelled':
                    return '<h2>Payment Status: Failed</h2>';
                default:
                    return '<h2>Payment Status: ' . ucfirst($order->get_status()) . '</h2>';
            }
        }
    }
    return '<h1>Invalid Order</h1>';
}
add_shortcode('order_status_title', 'order_status_title_shortcode');

add_filter('body_class', 'custom_order_status_body_class');
function custom_order_status_body_class($classes)
{
    if (is_page() && isset($_GET['order-received']) && isset($_GET['key'])) {
        $order_id = sanitize_text_field($_GET['order-received']);
        $order_key = sanitize_text_field($_GET['key']);

        $order = wc_get_order($order_id);

        if ($order && $order->get_order_key() == $order_key) {
            switch ($order->get_status()) {
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
function digiwoo_thank_you_redirect_enqueue()
{
    wp_register_script('digiwoo-thank-you-js', plugins_url('/public/assets/js/digiwoo_thankyou.js', __FILE__), array('jquery'), '1.0.0', true);
    wp_enqueue_script('digiwoo-thank-you-js');
}
add_action('wp_enqueue_scripts', 'digiwoo_thank_you_redirect_enqueue');