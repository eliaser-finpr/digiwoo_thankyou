<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Settings_Digiwoo_ThankYou extends WC_Settings_Page {

    public function __construct() {
        $this->id    = 'digiwoo_thankyou';
        $this->label = __('Digiwoo Thank You', 'digiwoo-thankyou');

        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_page'), 20);
        add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
        add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
        add_filter('query_vars', array($this, 'add_custom_query_vars'));
        add_shortcode('digiwoo_order_details', array($this, 'render_order_details_shortcode'));
    }    

    public function add_custom_query_vars($vars) {
        $vars[] = 'order-received';
        $vars[] = 'key';
        return $vars;
    }

    public function render_order_details_shortcode() {
        if (!isset($_GET['order-received']) || !isset($_GET['key'])) {
            return 'Invalid order details.';
        }

        $order_id = intval($_GET['order-received']);
        $order_key = sanitize_text_field($_GET['key']);

        $order = wc_get_order($order_id);

        if (!$order || $order->get_order_key() !== $order_key) {
            return 'Invalid order details.';
        }

        ob_start();
        
        // Display order details here. 
        // For simplicity, we're using WooCommerce's default order details template. 
        // You can customize this further as needed.
        wc_get_template('checkout/order-receipt.php', array('order' => $order));

        return ob_get_clean();
    }

    public function get_settings() {
        $settings = array(
            'section_title' => array(
                'name' => __('Digiwoo Thank You Settings', 'digiwoo-thankyou'),
                'type' => 'title',
                'desc' => '',
                'id' => 'digiwoo_thankyou_section_title'
            ),
            'enabled' => array(
                'title' => __('Enable/Disable', 'digiwoo-thankyou'),
                'type' => 'checkbox',
                'label' => __('Enable Custom Thank You Page', 'digiwoo-thankyou'),
                'default' => 'no',
                'id' => 'digiwoo_thankyou_enabled'
            ),
            'thank_you_page' => array(
                'title' => __('Thank You Page', 'digiwoo-thankyou'),
                'type' => 'select',
                'options' => $this->get_pages(),
                'default' => '',
                'id' => 'digiwoo_thankyou_page'
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id' => 'digiwoo_thankyou_section_end'
            )
        );
        return apply_filters('wc_digiwoo_thankyou_settings', $settings);
    }

    private function get_pages() {
        $pages = get_pages();
        $options = array();
        if ($pages) {
            foreach ($pages as $page) {
                $options[$page->ID] = $page->post_title;
            }
        }
        return $options;
    }
}

return new WC_Settings_Digiwoo_ThankYou();
