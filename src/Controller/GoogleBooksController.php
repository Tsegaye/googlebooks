<?php

namespace Drupal\googlebooks\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\googlebooks\Client\GoogleBooksClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GoogleBooksController extends ControllerBase {

  /**
   * @var \Drupal\googlebooks\Client\GoogleBooksClient
   */
  protected $googleBooksClient;
  /**
   * {@inheritdoc}
   */
  public function __construct(GoogleBooksClient $googleBooksClient) {
    $this->googleBooksClient = $googleBooksClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('googlebooks.client')
    );
  }

  public function content() {
    // This would get 50 people from Planning Center on page load.
    $query = [
      'q' => 'cyber',
      'maxResults' => 2
      //'key' => $this->googleBooksClient->getGoogleBooksKey()
    ];
    $request = $this->googleBooksClient->connect('get', 'books/v1/volumes', $query, []);
    $results = json_decode($request, TRUE);
    /*echo '<pre>';
    // useful for debugging and checking which fields actually are in each item of the response
    dpm( $results["items"] );
    echo '</pre>';*/
    $book_output_array = [];
    foreach ( $results["items"] as $item ) {
      $book_output = '<img src="' . $item['volumeInfo']['imageLinks']['thumbnail'] . '"/>"';
      $book_output .=  $item['volumeInfo']['title'];
      $book_output .= "<br /> \n";
      $book_output .= $item['volumeInfo']['publisher'];
      $book_output .= "<br /> \n";
      $book_output .= $item['volumeInfo']['publishedDate'];
      $book_output .= "<br /> \n";
      //echo '<a href="' . $item['volumeInfo']['previewLink'] . '">' . $item['volumeInfo']['previewLink'] . '</a>';
      if (isset($item['volumeInfo']["authors"])) {
        foreach ($item['volumeInfo']["authors"] as $author) {
          $authors[] = $author;
        }
        $authors_list = implode(', ', $authors);
        $book_output .= $authors_list;
      }
      if(isset($item['volumeInfo']['description'])) {
        $book_output .= $item['volumeInfo']['description'];
        $book_output .="<br /> \n";
      }
      if ($item['saleInfo']['saleability'] == 'FOR_SALE') {
        $book_output .= "Price: ";
        $book_output .= $item['saleInfo']['listPrice']['amount'];
        $book_output .=" <br /> \n";
      }
      else {
        $book_output .= "Price: 0";
        $book_output .= "<br /> \n";
      }
      /*echo '<pre>';
      // useful for debugging and checking which fields actually are in each item of the response
      var_dump( $item );
      echo '</pre>';*/
    }
    return [
      '#type' => 'markup',
      '#markup' => $book_output,
    ];
  }
}