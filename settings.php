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
        add_shortcode('digiwoo_order_details', array($this, 'render_order_details_shortcode'));
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

        $output = "<h2>Order Details for Order #" . $order->get_id() . "</h2>";

        // Displaying Billing Details
        $output .= "<h3>Billing Details</h3>";
        $output .= "<p>" . $order->get_formatted_billing_full_name() . "</p>";
        $output .= "<p>" . $order->get_billing_address_1() . "</p>";
        if ($order->get_billing_address_2()) {
            $output .= "<p>" . $order->get_billing_address_2() . "</p>";
        }
        $output .= "<p>" . $order->get_billing_city() . ", " . $order->get_billing_postcode() . "</p>";
        $output .= "<p>" . $order->get_billing_state() . ", " . $order->get_billing_country() . "</p>";
        $output .= "<p>" . $order->get_billing_phone() . "</p>";
        $output .= "<p>" . $order->get_billing_email() . "</p>";

        // Displaying Items Purchased
        $output .= "<h3>Items Purchased</h3>";
        $output .= "<ul>";
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $output .= "<li>";
            $output .= $product->get_name() . " x " . $item->get_quantity();
            $output .= "</li>";
        }
        $output .= "</ul>";

        // Displaying Total
        $output .= "<h3>Total: " . $order->get_formatted_order_total() . "</h3>";

        return $output;
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
    