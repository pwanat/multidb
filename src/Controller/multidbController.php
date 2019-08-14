<?php

namespace Drupal\multidb\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\multidb\ExternalDBConnectionService;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\DatabaseExceptionWrapper;

/**
 * Multidb Controller is used to render Orders HTMLs and Orders API.
 */
class MultidbController extends ControllerBase implements ContainerInjectionInterface {


  protected $externalDBConnectionService;
  protected $database;
  protected $twig;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    try {
      return new static(
        $container->get('external_db_connection.service'),
        $container->get('twig')
      );
    }
    catch (\Exception $e) {
      return new static(
        $container->get('config.factory')
        );
    }
  }

  /**
   * Constructs a new multidbController object.
   */
  public function __construct(ExternalDBConnectionService $externalDBConnectionService, \Twig_Environment $twig) {
    $this->externalDBConnectionService = $externalDBConnectionService;
    $this->twig = $twig;
  }

  /**
   * Action getDataJson.
   */
  public function getDataJson(string $order_id) : JsonResponse {
    $order = $this->loadOrder($order_id);
    if (property_exists($order, 'error')) {
      return new JsonResponse(
        [
          'error' => $order->error,
        ], $order->error_nr);
    }

    $response = new JsonResponse($order, 200);
    $response->setEncodingOptions(JSON_PRETTY_PRINT);

    $headers = $response->headers;
    $headers->set('Access-Control-Allow-Origin', '*');
    $headers->set('Content-Type', 'application/json');

    return $response;
  }

  /**
   * Action getDataHTML.
   *
   * @param string $order_id
   *   A string containing order id from request.
   */
  public function getDataHtml(string $order_id) {
    $order = $this->loadOrder($order_id);
    if (property_exists($order, 'error')) {
      drupal_set_message($order->error, 'error');
      return [];
    }
    $twigFilePath = drupal_get_path('module', 'multidb') . '/templates/order.html.twig';
    $template = $this->twig->loadTemplate($twigFilePath);
    $order_array = json_decode(json_encode($order), TRUE);
    $markup = $template->render($order_array);
    return [
      '#markup' => $markup,
    ];
  }

  /**
   * Uses ExternalDBConnectionService to connect to external database.
   *
   * @params string $order_id
   *  A string containing order id from request
   */
  public function loadOrder(string $order_id) {
    $data = new \stdClass();
    if (!is_numeric($order_id)) {
      $data->error = 'Invalid order number';
      $data->error_nr = 404;
      return $data;
    }

    $this->externalDBConnectionService->createConnection();
    $this->database = Database::getConnection();
    if (!$this->database) {
      $data->error = 'Could not connect to the database';
      $data->error_nr = 404;
      return $data;
    }
    try {
      $order = $this->getOrder($order_id);
      // Set dafault database.
      $this->externalDBConnectionService->setDefaultConnection();

      if (!$order) {
        $data->error = 'No data for order nr ' . $order_id;
        $data->error_nr = 404;
        return $data;
      }
      return $order;
    }
    catch (DatabaseExceptionWrapper $e) {
      // Handle database exception.
      drupal_set_message($e->getMessage(), 'error');
      watchdog_exception('multidb', $e);
      return FALSE;
    }
    catch (\Exception $e) {
      drupal_set_message($e->getMessage(), 'error');
      watchdog_exception('multidb', $e);
      return FALSE;
    }
  }

  /**
   * Gets full order information.
   *
   * @params Integer $order_id
   *  An integer containing order id
   */
  public function getOrder(int $order_id) {
    $order = $this->getOrderInfo($order_id);
    if (!$order) {
      return FALSE;
    }
    $order->order_details = $this->getOrderDetails($order_id);
    return $order;
  }

  /**
   * Gets header order information.
   *
   * @params Integer $order_id
   *  An integer containing order id
   */
  public function getOrderInfo(int $order_id) {
    $query = $this->database->select('orders', 'od');
    $query->where('od.id = ' . $order_id);
    $query->fields(
      'od',
      [
        'id',
        'ship_name',
        'ship_address',
        'ship_city',
        'ship_state_province',
        'ship_zip_postal_code',
        'ship_country_region',
      ]);
    $query->join('customers', 'cu', 'cu.id = od.customer_id');
    $query->fields(
      'cu',
      [
        'id',
        'company',
        'first_name',
        'last_name',
        'email_address',
      ]);
    $order = $query->execute()->fetch();
    return $order;
  }

  /**
   * Gets order details.
   *
   * @params Integer $order_id
   *  An integer containing order id
   */
  public function getOrderDetails(int $order_id) {
    $query = $this->database->select('order_details', 'dt');
    $query->where('dt.order_id = ' . $order_id);
    $query->fields('dt', ['id', 'product_id', 'quantity', 'unit_price']);
    $query->join('products', 'p', 'p.id = dt.product_id');
    $query->fields('p', ['id', 'product_code', 'product_name']);
    $query->orderBy('dt.id');
    $order_details = $query->execute()->fetchAll();
    return $order_details;
  }

}
