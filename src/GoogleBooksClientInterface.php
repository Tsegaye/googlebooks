<?php
namespace Drupal\googlebooks;

interface GoogleBooksClientInterface {
  /**
   * Utilizes Drupal's httpClient to connect to Google Books
   * Info: https://books.google.com/
   * API Docs: https://developers.google.com/books/docs/v1/using
   *
   * @param string $method
   *   get, post, patch, delete, etc. See Guzzle documentation.
   * @param string $endpoint
   *   The PCO API endpoint (ex. people/v2/people)
   * @param array $query
   *   Query string parameters the endpoint allows (ex. ['per_page' => 50]
   * @param array $body (converted to JSON)
   *   Utilized for some endpoints
   * @return object
   *   \GuzzleHttp\Psr7\Response body
   */
  public function connect($method, $endpoint, $query, $body);
}