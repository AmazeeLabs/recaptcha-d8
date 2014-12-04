<?php

/**
 * @file
 * Contains Drupal\recaptcha\Form\SettingsForm
 */

namespace Drupal\recaptcha\Form;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Administration form for ReCAPTCHA settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recaptcha_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('recaptcha.settings');
    $form['site_key'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Site Key'),
      '#description' => $this->t('Your site key'),
      '#default_value' => $config->get('site_key'),
    );
    $form['secret_key'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Secret Key'),
      '#description' => $this->t('Your secret key'),
      '#default_value' => $config->get('secret_key'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('recaptcha.settings');
    $config->set('site_key', $form_state->getValue('site_key'));
    $config->set('secret_key', $form_state->getValue('secret_key'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
