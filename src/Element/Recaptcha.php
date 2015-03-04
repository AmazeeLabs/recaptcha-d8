<?php

/**
 * @file
 * Contains Drupal\recaptcha\Element\Recaptcha
 */

namespace Drupal\recaptcha\Element;

/**
 * Provides a recaptcha form element.
 */
class Recaptcha {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => FALSE,
      '#pre_render' => [[$class, 'preRenderRecaptcha']],
      '#element_validate' => [[$class, 'validateRecaptcha']],
      '#theme' => 'recaptcha',
    ];
  }

  /**
   * Prepares a #type 'recaptcha' render element for theme_input().
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #return_value, #description, #required,
   *   #attributes, #checked.
   *
   * @return array
   *   The $element with prepared variables ready for theme_input().
   */
  public static function preRenderRecaptcha(array $element) {
    $site_key = static::getConfig()->get('site_key');
    $element['#site_key'] = $site_key;
    $element['#attached']['library'][] = 'recaptcha/recaptcha';
    return $element;
  }

  /**
   * Element validate callback for #recaptcha form element property.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic input element.
   * @param array $form_state
   *   The current state of the form.
   */
  public static function validateRecaptcha(&$element, &$form_state) {

    // See https://developers.google.com/recaptcha/docs/verify.
    $user_input = $form_state['input'];
    $recaptcha_response = $user_input['g-recaptcha-response'];

    $result = static::recaptcha()->verify($recaptcha_response);
    if (!$result['success']) {
      \Drupal::formBuilder()->setError($element, $form_state, t('The recaptcha was incorrect.'));
      $error_codes = $result['error_codes'];
      if (in_array('missing-input-secret', $error_codes)) {
        watchdog('recaptcha', 'The secret parameter is missing.');
      }
      if (in_array('invalid-input-secret', $error_codes)) {
        watchdog('recaptcha', 'The secret parameter is invalid or malformed.');
      }
      if (in_array('missing-input-response', $error_codes)) {
        watchdog('recaptcha', 'The response parameter is missing.');
      }
      if (in_array('invalid-input-response', $error_codes)) {
        watchdog('recaptcha', 'The response parameter is invalid or malformed.');
      }
    }
  }

  /**
   * Gets the recaptcha config.
   *
   * @return \Drupal\Core\Config\Config
   *   The recaptcha config.
   */
  protected static function getConfig() {
    return \Drupal::config('recaptcha.settings');
  }

  /**
   * Gets the recaptcha service.
   *
   * @return \Drupal\recaptcha\RecaptchaInterface
   *   The recaptcha service.
   */
  protected static function recaptcha() {
    return \Drupal::service('recaptcha');
  }

}
