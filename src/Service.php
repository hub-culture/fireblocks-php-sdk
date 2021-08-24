<?php
/**
 * @author        Tharanga Kothalawala <tharanga.kothalawala@gmail.com>
 * @copyright (c) 2021 by HubCulture Ltd.
 */

namespace Hub\FireBlocksSdk;

use Exception;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Service
 * @package Hub\FireBlocksSdk
 */
class Service
{
    const API_BASE_URL = 'https://api.fireblocks.io';
    const SIGNED_TOKEN_EXPIRE_IN_SECONDS = 10;

    /**
     * @var array runtime configuration containing the credentials etc.
     */
    protected $config;

    /**
     * @var Client curl client.
     */
    protected $client;

    /**
     * @var \Psr\Log\LoggerInterface logger for this class.
     */
    private $logger;

    /**
     * Service constructor.
     *
     * @param array                         $config runtime configuration containing the credentials etc. public and
     *                                              private keys are mandatory.
     * @param \Psr\Log\LoggerInterface|null $logger Pass a logger instance to collect debug output in to your own
     *                                              logging output.
     *
     * @throws InvalidArgumentException when required config keys are not found.
     */
    public function __construct(array $config, $logger = null)
    {
        if (empty($config['private_key_file'])) {
            throw new InvalidArgumentException("Missing 'private_key_file'. Visit https://docs.fireblocks.com/api/#issuing-api-credentials for more information.");
        }
        if (!is_readable($config['private_key_file'])) {
            throw new InvalidArgumentException("File given in 'private_key_file' is not readable");
        }
        if (empty($config['api_key'])) {
            throw new InvalidArgumentException("Missing 'api_key'. Visit https://docs.fireblocks.com/api/#signing-a-request for instructions for obtaining such a key.");
        }

        $this->config = array_merge(
            array(
                'base_url' => self::API_BASE_URL,
                'verify' => true,

                // this will write any request to a log file
                'debug' => false,
                'log_file' => '/tmp/hub-fireblocks-api-client.log', // debug output is written to here if enabled above

                // https://docs.fireblocks.com/api/#issuing-api-credentials
                'private_key_file' => '', // path to the API client's private key which was approved by the support
                'api_key' => '',
            ),
            $config
        );

        if ($this->config['verify'] === false) {
            $this->client = new Client(array('verify' => false));
        } else {
            $this->client = new Client(array('verify' => true));
        }

        // set the logger if psr logger is available and a valid one passed in
        if (interface_exists('\Psr\Log\LoggerInterface') && $logger instanceof \Psr\Log\LoggerInterface) {
            $this->logger = $logger;
        }
    }

    /**
     * Use this to set an access token.
     *
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->config['token'] = $accessToken;
    }

    /**
     * Returns the currently set and used access token.
     *
     * @return string currently set and used access token
     */
    public function getAccessToken()
    {
        return $this->config['token'];
    }

    /**
     * Use this to do a GET request.
     *
     * @param string $api    The API relative url
     * @param array  $params request parameters / payload
     *
     * @return array
     */
    protected function get($api, array $params = array())
    {
        if (!empty($params)) {
            if (strpos($api, '?') === false) {
                $api = sprintf('%s?%s', $api, http_build_query($params));
            } else {
                $api = sprintf('%s&%s', $api, http_build_query($params));
            }
        }

        return $this->requestWithForm($api, 'get');
    }

    /**
     * Use this to do a PUT request.
     *
     * @param string $api    The API relative url
     * @param array  $params request parameters / payload
     *
     * @return array
     */
    protected function put($api, array $params = array())
    {
        return $this->requestWithForm($api, 'put', $params);
    }

    /**
     * Use this to do a POST request with JSON request body
     *
     * @param string $api    The API relative url
     * @param array  $params request parameters / payload to be submitted as JSON
     *
     * @return array
     */
    protected function postJson($api, array $params = array())
    {
        return $this->requestWithJson($api, 'post', $params);
    }

    /**
     * Use this to do a POST request.
     *
     * @param string $api    The API relative url
     * @param array  $params request parameters / payload
     *
     * @return array
     */
    protected function postFormData($api, array $params = array())
    {
        return $this->requestWithForm($api, 'post', $params);
    }

    /**
     * Use this to do a DELETE request.
     *
     * @param string $api    The API relative url
     * @param array  $params request parameters / payload
     *
     * @return array
     */
    protected function delete($api, array $params = array())
    {
        return $this->requestWithForm($api, 'delete', $params);
    }

    /**
     * Use this to send any HTTP request with JSON request body.
     *
     * @param string $api    The API relative url
     * @param string $method HTTP method to use
     * @param array  $params request parameters / payload
     *
     * @return array
     */
    protected function requestWithJson($api, $method = 'get', array $params = array())
    {
        return $this->rawRequest(
            $api,
            array('body' => json_encode($params), 'headers' => ['content-type' => 'application/json']),
            $method
        );
    }

    /**
     * Use this to send any HTTP request with request body as a form submission.
     *
     * @param string $api    The API relative url
     * @param string $method HTTP method to use
     * @param array  $params request parameters / payload
     *
     * @return array
     */
    protected function requestWithForm($api, $method = 'get', array $params = array())
    {
        return $this->rawRequest(
            $api,
            array('form_params' => $params),
            $method
        );
    }

    /**
     * This logs any given string message to the sdk client default log file and to any application provided logger.
     *
     * @param string $string The message to log
     */
    protected function log($string)
    {
        if (!$this->config['debug']) {
            return;
        }

        // log to the sdk client default log file
        if (!empty($this->config['log_file'])) {
            $now = date('Y-m-d H:i:s');
            file_put_contents($this->config['log_file'], "[{$now}] [DEBUG] {$string}" . PHP_EOL, FILE_APPEND);
        }

        // also log to the application provided logger
        if (!is_null($this->logger)) {
            $this->logger->debug($string);
        }
    }

    /**
     * Use this to send a raw request of any type. Any types meant a form submission or a json or anything else
     * supported by the GuzzleHttp library.
     *
     * @param string $api     api endpoint
     * @param array  $payload request parameters / payload
     * @param string $method  HTTP method to use
     *
     * @return array
     */
    private function rawRequest($api, array $payload, $method = 'get')
    {
        $method = strtolower($method);
        $errorResponse = null;

        $this->generateAccessToken($api, $payload);
        if (empty($payload['headers'])) {
            $payload['headers'] = $this->getHeaders();
        } else {
            $payload['headers'] = array_merge($payload['headers'], $this->getHeaders());
        }

        try {
            $this->debug($api, $method, $payload);
            /** @var ResponseInterface $response */
            $response = $this->client->$method(
                sprintf('%s%s', $this->config['base_url'], $api),
                $payload
            );

            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $ex) {
            $errorResponse = $ex->getResponse()->getBody()->getContents();
        } catch (Exception $ex) {
            $errorResponse = $ex->getMessage();
        }

        if (!is_null($errorResponse)) {
            throw new FireBlocksApiException($errorResponse);
        }

        return [];
    }

    /**
     * Returns headers for the API request with a token if available.
     *
     * @see https://docs.fireblocks.com/api/#signing-a-request
     * @return array
     */
    private function getHeaders()
    {
        $headers = array();

        if (!empty($this->config['token'])) {
            $headers['Authorization'] = sprintf('Bearer %s', $this->config['token']);
        }
        if (!empty($this->config['api_key'])) {
            $headers['X-API-Key'] = $this->config['api_key'];
        }

        return $headers;
    }

    /**
     * This generates a new access token by signing the request with the specified private key.
     *
     * @param string      $uri The URI part of the request (e.g., /v1/transactions).
     * @param string|null $requestBody
     *
     * @see https://docs.fireblocks.com/api/?javascript#signing-a-request
     */
    private function generateAccessToken($uri, $requestBody = null)
    {
        $payload = array(
            // The URI part of the request (e.g., /v1/transactions).
            'uri' => $uri,
            // Unique number or string. Each API request needs to have a different nonce.
            'nonce' => rand(1000, getrandmax()),
            // The time at which the JWT was issued, in seconds since Epoch.
            'iat' => time(),
            'exp' => time() + self::SIGNED_TOKEN_EXPIRE_IN_SECONDS,
            // The API Key.
            'sub' => $this->config['api_key'],
        );

        // Hex-encoded SHA-256 hash of the raw HTTP request body
        if (!empty($requestBody['body']) && is_string($requestBody['body'])) {
            $payload['bodyHash'] = hash('sha256', $requestBody['body']);
        }

        $this->setAccessToken(
            JWT::encode($payload, file_get_contents($this->config['private_key_file']), 'RS256')
        );
    }

    /**
     * Use this method to debug log the requests going out.
     *
     * @param string $api     called relative api endpoint
     * @param string $method  HTTP method used.
     * @param array  $payload request data used.
     */
    private function debug($api, $method, array $payload)
    {
        if (!$this->config['debug']) {
            return;
        }

        $string = "curl --insecure -X%s '%s' %s %s";

        // headers
        $headerString = array();
        if (!empty($payload['headers']) && is_array($payload['headers'])) {
            foreach ($payload['headers'] as $header => $value) {
                $headerString[] = "-H '{$header}:{$value}'";
            }
        }
        $headerString = implode(' ', $headerString);

        // data
        $dataString = '';
        $data = array();
        if (!empty($payload['body'])) {
            // if json
            $dataString = "--data " . $payload['body'];
        } elseif (!empty($payload['form_params']) && is_array($payload['form_params'])) {
            // if form data
            foreach ($payload['form_params'] as $formParam => $value) {
                if (is_string($value) || is_numeric($value)) {
                    $data[] = sprintf("-F '%s=%s'", $formParam, $value);
                }
                if (is_array($value)) {
                    foreach ($value as $eachValue) {
                        $data[] = sprintf("-F '%s[]=%s'", $formParam, $eachValue);
                    }
                }
            }

            $dataString = implode(' ', $data);
        }

        $string = sprintf($string, strtoupper($method), $this->config['base_url'] . $api, $headerString, $dataString);
        $this->log($string);
    }
}
