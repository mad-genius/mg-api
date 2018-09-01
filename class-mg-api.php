<?php

/*
* MG_API
*
* Copyright (c) 2018 Mad Genius Inc (https://madg.com)
*
* By Blake Watson (@blakewatson)
* Licensed under the MIT license.
*
* @link https://github.com/mad-genius/mg-api
* @author Blake Watson
* @version 1.0
*/

class MG_API {
    public $api_base_url;

    public function __construct( $base_url = '', $hash = '' ) {
        // remove trailing space
        if( substr( $base_url, -1 ) === '/' ) {
            $base_url = substr( $base_url, 0, strlen( $base_url ) - 1 );
        }
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

    public function get( $url, $params = array() ) {
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

        return wp_remote_post( $url, $args );
    }
}
