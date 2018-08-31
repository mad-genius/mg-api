<?php

class MG_API {
    public $api_base_url;

    public function __construct( $base_url = '', $hash = '' ) {
        // the base url path of the API
        $this->api_base_url = $base_url;
        // the api authentication hash
        $this->api_hash = $hash;
    }

    public function endpoint( $path = null ) {
        if( $path === null ) return $this->api_base_url;
        if( strpos( $path, '/' ) !== 0 ) $path = "/$path";
        return $this->api_base_url . $path;
    }

    public function get( $url, $params = [] ) {
        $hash = $this->api_hash;
        
        if( count( $params ) > 0 ) $url .= '?';

        $is_first_param = true;
        foreach( $params as $key => $value ) {
            if( ! $is_first_param ) $url .= '&';
            $url .= urlencode( $key ) . '=' . urlencode( $value );
            $is_first_param = false;
        }

        $args = array();

        if( ! empty( $hash ) ) {
            $args['headers'] = array(
                'authorization' => "Basic $hash"
            );
        }

        return wp_remote_get( $url, $args );
    }

    public function post( $url, $params = array(), $args = array() ) {
        $hash = $this->api_hash;

        $args['method'] = 'POST';

        if( ! array_key_exists( 'headers', $args ) ) {
            $args['headers'] = array(
                'content-type' => 'application/json'
            );
        }

        if( ! empty( $params ) ) {
            $args['body'] = json_encode( $params );
        }

        if( ! empty( $hash ) ) {
            $args['headers']['authorization'] = "Basic $hash";
        }

        $response = wp_remote_post( $url, $args );

        if( is_wp_error( $response ) ) {
            error_log( $response->get_error_message() );
            error_log( print_r( $params, true ) );
            return;
        }

        if( $response['response']['code'] > 299 ) {
            error_log( print_r( $response, true ) );
            error_log( print_r( $params, true ) );
        }

        return $response;
    }
}
