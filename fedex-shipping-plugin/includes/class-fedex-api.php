<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class FedEx_API {
    private $api_url = 'https://apis.fedex.com/oauth/token';

    public function get_access_token() {
        $client_id = get_option('fedex_api_key');
        $client_secret = get_option('fedex_api_secret');

        if (!$client_id || !$client_secret) {
            return new WP_Error('missing_credentials', 'FedEx API credentials are missing.');
        }

        $cached_token = get_transient('fedex_access_token');
        if ($cached_token) {
            return $cached_token;
        }

        $response = wp_remote_post($this->api_url, array(
            'body' => http_build_query(array(
                'grant_type' => 'client_credentials',
                'client_id' => $client_id,
                'client_secret' => $client_secret
            )),
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['access_token'])) {
            set_transient('fedex_access_token', $body['access_token'], $body['expires_in']);
            return $body['access_token'];
        }

        return new WP_Error('api_error', 'Failed to retrieve FedEx access token.');
    }
}
