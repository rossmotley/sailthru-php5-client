<?php

interface Sailthru_Client_TransportInterface
{
    /**
     * Perform an HTTP request
     *
     * Will return an array with two entries:
     *
     * [
     *   'body' => '{}', // Raw JSON string
     *   'headers' => '', // Raw headers string
     * ]
     *
     * Or, it will throw a Sailthru_Client_Exception exception.
     *
     * @param string $url
     * @param array $data
     * @param string $method
     * @param array $options
     * @return array
     * @throws Sailthru_Client_Exception
     */
    public function doRequest($url, array $data, $method = 'POST', $options = []);

    /**
     * Get information from last server response
     *
     * When used with cURL returns associative array as per
     * http://us.php.net/curl_getinfo. The exact structure of the array does NOT
     * form part of the interface, just that "some" array will be returned.
     *
     * @return array|null
     */
    public function getLastResponseInfo();
}
