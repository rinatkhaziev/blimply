<?php
namespace UrbanAirship;

class WpAirship extends Airship {


    /**
     * Send an authenticated request to the Urban Airship API. The request is
     * authenticated with the key and secret.
     *
     * @param string  $method      REST method for request
     * @param mixed   $body        Body of request, optional
     * @param string  $uri         URI for this request
     * @param string  $contentType Content type for the request, optional
     * @param int     $version     version # for API, optional, default is 3
     * @param mixed   $request     Request object for this operation (PushRequest, etc)
     *   optional
     * @return \Httpful\associative|string
     * @throws AirshipException
     */
    public function request( $method, $body, $uri, $contentType=null, $version=3, $request=null ) {
        $headers = array(
            'Authorization' => 'Basic ' . base64_encode( "{$this->key}:{$this->secret}" ),
            "Accept" => sprintf( self::VERSION_STRING, $version )
        ) ;

        if ( !is_null( $contentType ) ) {
            $headers["Content-type"] = $contentType;
        }
        $request = new \WP_Http;

        $logger = UALog::getLogger();
        $logger->debug( "Making request", array(
                "method" => $method,
                "uri" => $uri,
                "headers" => $headers,
                "body" => $body ) );

        $response = $request->request( $uri,  array( 'method' => $method, 'body' => $body, 'headers' => $headers ) );
        if ( is_wp_error( $response ) || 300 <= $response['response']['code'] )
            throw AirshipException::fromResponse( $mock_response );

        // TODO: map WP_HTTP API response to expected format/handle exceptioms better
        $mock_response = new \stdClass;
        $mock_response->raw_body = $response['body'];
        $mock_response->body = json_decode( $response['body'] );
        $mock_response->code = $response['response']['code'];

        $logger->debug( "Received response", array(
                "status" => $mock_response->code,
                "headers" => '',
                "body" => $mock_response->raw_body ) );

        return $mock_response;
    }
}
