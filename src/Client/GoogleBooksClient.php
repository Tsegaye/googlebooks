<?php
namespace Drupal\googlebooks\Client;

use Drupal\Core\Config\ConfigFactory;
use Drupal\googlebooks\GoogleBooksClientInterface;
use Drupal\key\KeyRepositoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class GoogleBooksClient implements GoogleBooksClientInterface {

  /**
   * An http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Google Books Token.
   *
   * @var string
   */
  protected $token;

  /**
   * Google Books Secret.
   *
   * @var string
   */
  protected $secret;

  /**
   * Planning Center Base URI.
   *
   * @var string
   */
  protected $base_uri;

  /**
   * GoogleBooksClient constructor.
   */
  public function __construct(ClientInterface $http_client, KeyRepositoryInterface $key_repo, ConfigFactory $config_factory) {
    $this->httpClient = $http_client;
    $config = $config_factory->get('googlebooks.settings');
    //$this->token = $config->get('token');
    $this->secret = $config->get('secret');
    $this->secret = $key_repo->getKey($this->secret)->getKeyValue();
    $this->base_uri = $config->get('base_uri');
  }

  /**
   * { @inheritdoc }
   */
  public function connect($method, $endpoint, $query, $body) {
    try {
      $response = $this->httpClient->{$method}(
        $this->base_uri . $endpoint,
        $this->buildOptions($query, $body)
      );
    }
    catch (RequestException $exception) {
      drupal_set_message(t('Failed to complete Google Books API Task "%error"', ['%error' => $exception->getMessage()]), 'error');
      \Drupal::logger('googlebooks_api')->error('Failed to complete Google Books API Task "%error"', ['%error' => $exception->getMessage()]);
      return FALSE;
    }
    $headers = $response->getHeaders();
    //$this->throttle($headers);
    // TODO: Possibly allow returning the whole body.
    return $response->getBody()->getContents();
  }

  /**
   * Build options for the client.
   */
  private function buildOptions($query, $body) {
    $options = [];
    $options['auth'] = $this->auth();
    if ($body) {
      $options['body'] = $body;
    }
    if ($query) {
      $options['query'] = $query;
    }
    return $options;
  }

  /**
   * Throttle response.
   *
   * 100 per 60s allowed.
   * This is an example, currently not used with the Books api. If
   * Google supports a header variable, similar to X-GOOGLE-BOOKS-API-Request-Rate-Count,
   * we can use of it.
   * Originally this example worked for https://api.planningcenteronline.com/people/v2/people
   *
   */
  private function throttle($headers) {
    print_r($headers['X-GOOGLE-BOOKS-API-Request-Rate-Count'][0]);
    if ($headers['X-GOOGLE-BOOKS-API-Request-Rate-Count'][0] > 99) {
      return sleep(60);
    }
    return TRUE;
  }
  /**
   * Handle authentication.
   */
  private function auth() {
    //return [$this->token, $this->secret]; works if there's token
    return $this->secret;
  }

}