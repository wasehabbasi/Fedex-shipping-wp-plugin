<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class FedEx_Shipping_Method extends WC_Shipping_Method {

    public function __construct($instance_id = 0) {
        $this->id = 'fedex_shipping';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('FedEx Shipping', 'woocommerce');
        $this->method_description = __('Fetches real-time FedEx shipping rates.', 'woocommerce');
        $this->enabled = 'yes';
        $this->init();
    }

    public function init() {
        $this->init_form_fields();
        $this->init_settings();
        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable FedEx Shipping', 'woocommerce'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Method Title', 'woocommerce'),
                'type' => 'text',
                'description' => __('Title shown during checkout.', 'woocommerce'),
                'default' => __('FedEx Shipping', 'woocommerce'),
                'desc_tip' => true
            )
        );
    }

    public function calculate_shipping($package = array()) {
        $fedex_shipping = new FedEx_Shipping();
        $rates = $fedex_shipping->fetch_fedex_shipping_rates(array(), $package);

        foreach ($rates as $rate_id => $rate) {
            $this->add_rate(array(
                'id' => $rate_id,
                'label' => $rate['label'],
                'cost' => $rate['cost'],
                'package' => $package
            ));
        }
    }
}
