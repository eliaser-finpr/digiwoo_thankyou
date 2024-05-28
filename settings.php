<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Settings_Digiwoo_ThankYou extends WC_Settings_Page
{

    public function __construct()
    {
        $this->id = 'digiwoo_thankyou';
        $this->label = __('YPF Thank You', 'digiwoo-thankyou');

        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_page'), 20);
        add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
        add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
    }

    public function get_settings()
    {
        $settings = array(
            'section_title' => array(
                'name' => __('YPF Thank You Settings', 'digiwoo-thankyou'),
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
            'thank_you_page_challenge_en' => array(
                'title' => __('Thank You Page - Challenge - English', 'digiwoo-thankyou-challenge-en'),
                'type' => 'select',
                'options' => $this->get_pages(),
                'default' => '',
                'id' => 'digiwoo_thankyou_page_challenge_en'
            ),
            'thank_you_page_free_trial_en' => array(
                'title' => __('Thank You Page - Free Trial - English', 'digiwoo-thankyou-free-trial-en'),
                'type' => 'select',
                'options' => $this->get_pages(),
                'default' => '',
                'id' => 'digiwoo_thankyou_page_free_trial_en'
            ),
            'thank_you_page_challenge_ja' => array(
                'title' => __('Thank You Page - Challenge - English', 'digiwoo-thankyou-challenge-ja'),
                'type' => 'select',
                'options' => $this->get_pages(),
                'default' => '',
                'id' => 'digiwoo_thankyou_page_challenge_ja'
            ),
            'thank_you_page_free_trial_ja' => array(
                'title' => __('Thank You Page - Free Trial - English', 'digiwoo-thankyou-free-trial-ja'),
                'type' => 'select',
                'options' => $this->get_pages(),
                'default' => '',
                'id' => 'digiwoo_thankyou_page_free_trial_ja'
            ),
            'failed_page' => array(
                'title' => __('Failed Payment Page', 'digiwoo-thankyou'),
                'type' => 'select',
                'options' => $this->get_pages(),
                'default' => '',
                'id' => 'digiwoo_failed_page'
            ),
            'section_end' => array(
                'type' => 'sectionend',
                'id' => 'digiwoo_thankyou_section_end'
            )
        );
        return apply_filters('wc_digiwoo_thankyou_settings', $settings);
    }


    private function get_pages()
    {
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
