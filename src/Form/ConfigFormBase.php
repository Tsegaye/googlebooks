<?php

namespace Drupal\googlebooks\Form;

use Drupal\Core\Form\ConfigFormBase as DrupalConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

abstract class ConfigFormBase extends DrupalConfigFormBase {
  const CONFIG_NAME = 'googlebooks.settings';

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return [self::CONFIG_NAME];
  }

  /**
   * Returns this modules configuration object.
   */
  protected function getConfig() {
    return $this->config(self::CONFIG_NAME);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->getConfig();
    $values = $form_state->getValues();
    foreach ($values as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
  }

}