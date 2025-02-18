<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class FedEx_Shipping {
    private $api_url = 'https://apis.fedex.com/rate/v1/rates/quotes';
    private $fedex_api;

    public function __construct() {
        $this->fedex_api = new FedEx_API();
        add_filter('woocommerce_package_rates', array($this, 'fetch_fedex_shipping_rates'), 10, 2);
    }

    public function fetch_fedex_shipping_rates($rates, $package) {
        $access_token = $this->fedex_api->get_access_token();
        if (is_wp_error($access_token)) {
            return $rates;
        }

        $destination = $package['destination'];
        $weight = WC()->cart->get_cart_contents_weight();
        $account_number = get_option('fedex_account_number');

        $request_body = array(
            'accountNumber' => array('value' => $account_number),
            'requestedShipment' => array(
                'shipper' => array(
                    'address' => array('postalCode' => '12345', 'countryCode' => 'US')
                ),
                'recipient' => array(
                    'address' => array('postalCode' => $destination['postcode'], 'countryCode' => $destination['country'])
                ),
                'requestedPackageLineItems' => array(array('weight' => array('units' => 'LB', 'value' => $weight)))
            )
        );

        $response = wp_remote_post($this->api_url, array(
            'body' => json_encode($request_body),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ),
        ));

        if (is_wp_error($response)) {
            return $rates;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!isset($body['rateReplyDetails'])) {
            return $rates;
        }

        foreach ($body['rateReplyDetails'] as $rate) {
            $service_name = $rate['serviceType'];
            $price = $rate['ratedShipmentDetails'][0]['totalNetCharge']['amount'];

            $rates['fedex_' . strtolower($service_name)] = array(
                'id' => 'fedex_' . strtolower($service_name),
                'label' => 'FedEx ' . ucwords(str_replace('_', ' ', strtolower($service_name))),
                'cost' => $price,
                'calc_tax' => 'per_order'
            );
        }

        return $rates;
    }
}

new FedEx_Shipping();
