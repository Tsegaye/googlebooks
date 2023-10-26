<?php

namespace Drupal\googlebooks\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormState;
use Drupal\googlebooks\Client\GoogleBooksClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BooksController extends ControllerBase {

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
    // we don't need formBuilder()->getForm as we need the $form_state
    // Instead, we are going to use formBuilder()->buildForm
    //$searchForm = \Drupal::formBuilder()->getForm('Drupal\googlebooks\Form\GoogleBookSearchForm');
    $form_state = new FormState();
    $form_state->setRebuild();
    $searchForm = \Drupal::formBuilder()->buildForm('Drupal\googlebooks\Form\GoogleBooksSearchForm', $form_state);
    $test = \Drupal::entityTypeManager()->getStorage('webform')->load('test');
    
    //echo '<pre>';
    //var_dump($searchForm);
    $bookquery = $searchForm['booklayout']["googlebooksearch"]["#value"];
    //echo '</pre>';
    // This would get certain number of books on page load.
    $query = [
      'q' => $bookquery,
      'maxResults' => 10,
      'startIndex' => 0,
    ];

    $books = [];

    //if (($query['q']) != "") {
      $request = $this->googleBooksClient->connect('get', 'books/v1/volumes', $query, []);
      $book_data = json_decode($request, TRUE);
      /*echo '<pre>';
      // useful for debugging and checking which fields actually are in each item of the response
      dpm( $book_data );
      echo '</pre>';*/
      foreach ($book_data["items"] as $key => $value) {
        $books[$key]['thumbnail'] = $value['volumeInfo']['imageLinks']['thumbnail'];
        //$book_thumbnail[] = '<img src="' . $item['volumeInfo']['imageLinks']['thumbnail'] . '"/>"';
        //$book_title[] =  $item['volumeInfo']['title'];
        $books[$key]['title'] = $value['volumeInfo']['title'];
        if (isset($value['volumeInfo']['description'])) {
          $books[$key]['description'] = $value['volumeInfo']['description'];
        }
        else {
          $books[$key]['description'] = NULL;
        }

        if (isset($value['volumeInfo']['industryIdentifiers'])) {
          $books[$key]['isbn'] = $value['volumeInfo']['industryIdentifiers'][1]['identifier'];
        }
        if (isset($value['volumeInfo']["authors"])) {
          foreach ($value['volumeInfo']["authors"] as $author) {
            $books[$key]['authors'][] = $author;
          }
        }

        $books[$key]['publishedDate'] = $value['volumeInfo']['publishedDate'];
        if (isset($value['volumeInfo']['publisher'])) {
          $books[$key]['publisher'] = $value['volumeInfo']['publisher'];
        }

        if ($value['saleInfo']['saleability'] == 'FOR_SALE') {
          $books[$key]['price'] = $value['saleInfo']['listPrice']['amount'];
        }
        else {
          $books[$key]['price'] = 'Not For Sale';
        }
        /*echo '<pre>';
        // useful for debugging and checking which fields actually are in each item of the response
        var_dump( $item );
        echo '</pre>';*/
      }
    //}
    /*echo '<pre>';
    var_dump($books);
    echo '</pre>';*/
    //var_dump($book_title);

    return [
      '#theme' => 'book_template',
      '#form' => $searchForm,
      '#books' => $books
    ];
    /*return [
      'form' => $searchForm,
      ];*/
  }
}