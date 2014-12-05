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
    $form_ids = implode("\n", $config->get('form_ids'));
    $form['form_ids'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Form IDs'),
      '#description' => $this->t('The list of form IDs to add a ReCAPTCHA. Add one form ID per line. <br />Examples: <ul><li>comment_comment_form</li><li>user_register_form</li><li>user_pass</li></ul>'),
      '#default_value' => $form_ids,
    );
    $form['api_keys'] = array(
      '#type' => 'details',
      '#title' => $this->t('API Keys'),
      '#description' => $this->t("Enter your site and secret keys provided at the <a href=\"!admin\">ReCAPTCHA site administration page</a>",
        ['!admin' => 'https://www.google.com/recaptcha/admin']),
    );
    $form['api_keys']['site_key'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Site Key'),
      '#description' => $this->t('Your site key'),
      '#default_value' => $config->get('site_key'),
    );
    $form['api_keys']['secret_key'] = array(
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
    $form_ids = array_filter(array_map(function ($form_id) {
      return (strtolower(trim($form_id)));
    }, explode("\n", $form_state->getValue('form_ids'))));
    $config->set('form_ids', $form_ids);
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
