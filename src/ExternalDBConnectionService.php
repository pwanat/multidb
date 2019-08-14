<?php

namespace Drupal\multidb;

use Drupal;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\ConnectionNotDefinedException;

/**
 * External DB Connection Service.
 */
class ExternalDBConnectionService {

  /**
   * Create connection with new database information.
   */
  public function createConnection() {
    // Set data in $info array.
    $info = [
      'database' => $this->getConfig('multidb_database'),
      'username' => $this->getConfig('multidb_username'),
      'password' => $this->getConfig('multidb_password'),
      'prefix' => '',
      'host' => $this->getConfig('multidb_host'),
      'port' => $this->getConfig('multidb_port'),
      'driver' => $this->getConfig('multidb_driver'),
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    ];
    // Add connection with new database setting.
    Database::addConnectionInfo('external_db_connection', 'default', $info);
    try {
      // Active new connection.
      Database::setActiveConnection('external_db_connection');
    }
    catch (ConnectionNotDefinedException $e) {
      // Active default connection if new connection is not stablished.
      Database::setActiveConnection('default');
    }
  }

  /**
   * Return setting value.
   */
  public function getConfig($config) {
    return Drupal::config('multidb.settings')->get($config);
  }

  /**
   * Set default connection.
   */
  public function setDefaultConnection() {
    Database::setActiveConnection();
  }

}
