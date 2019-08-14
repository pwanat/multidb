<?php

namespace Drupal\multidb\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Create form to save database information.
 */
class MultidbConfiguration extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'multidb_configuration';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['multidb.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Getting the configuration value.
    $default_value = $this->config('multidb.settings');

    $form['multidb_config'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configuration'),
      '#weight' => 5,
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['multidb_config']['multidb_driver'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Select Driver'),
      '#options' => $this->getDriverOptions(),
      '#default_value' => $default_value->get('multidb_driver'),
    ];
    $form['multidb_config']['multidb_host'] = [
      '#type' => 'textfield',
      '#maxlength' => 255,
      '#default_value' => $default_value->get('multidb_host'),
      '#required' => TRUE,
      '#title' => $this->t('Hostname'),
    ];
    $form['multidb_config']['multidb_database'] = [
      '#type' => 'textfield',
      '#maxlength' => 255,
      '#default_value' => $default_value->get('multidb_database'),
      '#required' => TRUE,
      '#title' => $this->t('Database Name'),
    ];
    $form['multidb_config']['multidb_username'] = [
      '#type' => 'textfield',
      '#maxlength' => 255,
      '#default_value' => $default_value->get('multidb_username'),
      '#required' => TRUE,
      '#title' => $this->t('Username'),
    ];
    $form['multidb_config']['multidb_password'] = [
      '#type' => 'textfield',
      '#maxlength' => 255,
      '#default_value' => $default_value->get('multidb_password'),
      '#required' => FALSE,
      '#title' => $this->t('Password'),
    ];
    $form['multidb_config']['multidb_port'] = [
      '#type' => 'textfield',
      '#maxlength' => 255,
      '#default_value' => $default_value->get('multidb_port') ? $default_value->get('multidb_port') : '3306' ,
      '#required' => TRUE,
      '#title' => $this->t('Port'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('multidb.settings');
    $multidb_host = $form_state->getValue('multidb_host');
    $multidb_username = $form_state->getValue('multidb_username');
    $multidb_password = $form_state->getValue('multidb_password');
    $multidb_port = $form_state->getValue('multidb_port');
    $multidb_database = $form_state->getValue('multidb_database');
    $multidb_driver = $form_state->getValue('multidb_driver');

    $config->set('multidb_host', $multidb_host)
      ->set('multidb_username', $multidb_username)
      ->set('multidb_password', $multidb_password)
      ->set('multidb_port', $multidb_port)
      ->set('multidb_database', $multidb_database)
      ->set('multidb_driver', $multidb_driver)
      ->save();
    drupal_set_message($this->t('Configuration has been saved.'));
    parent::submitForm($form, $form_state);
  }

  /**
   * Get Driver options.
   */
  protected function getDriverOptions() {
    return [
      '' => $this->t('-Select-'),
      'mysql' => $this->t('MySQL, MariaDB'),
    ];
  }

}
