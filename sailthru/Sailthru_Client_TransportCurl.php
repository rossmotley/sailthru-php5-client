<?php

class Sailthru_Client_TransportCurl implements Sailthru_Client_TransportInterface
{
    const DEFAULT_READ_TIMEOUT = 10000;
    const DEFAULT_CONNECT_TIMEOUT = 10000;

    /**
     * @var array
     */
    private $defaultOptions;

    /**
     * Holds an array of data about the last request
     *
     * @var array|null
     */
    private $lastRequestInfo;

    /**
     * @param array $defaultOptions
     */
    public function __construct($defaultOptions = array())
    {
        if (!isset($defaultOptions['http_headers'])) {
            $defaultOptions['http_headers'] = ["User-Agent: Sailthru API PHP5 Client"];
        }
        if (!isset($defaultOptions['timeout'])) {
            $defaultOptions['timeout'] = self::DEFAULT_READ_TIMEOUT;
        }
        if (!isset($defaultOptions['connect_timeout'])) {
            $defaultOptions['connect_timeout'] = self::DEFAULT_CONNECT_TIMEOUT;
        }

        $this->defaultOptions = $defaultOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function doRequest($url, array $data, $method = 'POST', $options = [])
    {
        $ch = curl_init();
        $options = array_merge($this->defaultOptions, $options);

        $isFileUpload = isset($options['file_upload']) ? $options['file_upload'] : false;

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($isFileUpload) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
            }
        } else {
            $url .= '?'.http_build_query($data, '', '&');
            if ($method != 'GET') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $options['timeout']);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $options['connect_timeout']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $options['http_headers']);

        $response = curl_exec($ch);
        $this->lastRequestInfo = curl_getinfo($ch);

        $errorMessage = false === $response ? curl_error($ch) : null;

        curl_close($ch);

        if (false === $response) {
            throw new Sailthru_Client_Exception(
                sprintf('Error with curl transport from url %s: %s', $url, $errorMessage),
                Sailthru_Client_Exception::CODE_TRANSPORT_ERROR
            );
        }

        if (!$response) {
            throw new Sailthru_Client_Exception(
                "Bad response received from $url",
                Sailthru_Client_Exception::CODE_RESPONSE_EMPTY
            );
        }

        // parse headers and body (!)
        $parts = explode("\r\n\r\nHTTP/", $response);
        $parts = (count($parts) > 1 ? 'HTTP/' : '').array_pop($parts); // deal with HTTP/1.1 100 Continue before other headers
        list($headers, $body) = explode("\r\n\r\n", $parts, 2);

        return [
            'body' => $body,
            'headers' => $headers,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getLastResponseInfo()
    {
        return $this->lastRequestInfo;
    }
}
