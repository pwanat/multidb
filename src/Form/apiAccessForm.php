<?php

namespace Drupal\multidb\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

/**
 * Form builder for the api access page.
 *
 * @internal
 */
class ApiAccessForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'api_access_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'multidb.apiaccess',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $users = User::loadMultiple();

    // Form constructor.
    $form = parent::buildForm($form, $form_state);

    $header = [
      'Label' => ['data' => t('User')],
      'Enabled' => ['data' => t('Access API')],
    ];

    $form['ntable'] = [
      '#type' => 'table',
      '#header' => $header,
      '#empty' => t('There are no users.'),
    ];

    // Build the table rows and columns.
    foreach ($users as $id => $user) {
      // Ommit user 0.
      if ($id == 0) {
        continue;
      }

      $form['ntable'][$id]['label'] = [
        '#type' => 'link',
        '#title' => $user->name->value,
        '#url' => Url::fromRoute('entity.user.canonical', ['user' => $id]),
      ];

      $form['ntable'][$id]['enabled'] = [
        '#type' => 'checkbox',
        '#default_value' => $user->get('user_multidb_access')->value ?: 0,
      ];

    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save changes'),
    ];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Edit Access rights submission handler.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    foreach ($form_state->getValues()['ntable'] as $uid => $values) {
      // If value has changed.
      if ((int) $values['enabled'] !== (int) $form['ntable'][$uid]['enabled']['#default_value']) {
        $user = User::load($uid);
        $user->set('user_multidb_access', (int) $values['enabled']);
        $user->save();
      }
    }

    return parent::submitForm($form, $form_state);
  }

}
