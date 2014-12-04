<?php

/**
 * @file
 * Contains Drupal\recaptcha\Element\Recaptcha
 */

namespace Drupal\recaptcha\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a recaptcha form element.
 *
 * @RenderElement("recaptcha")
 */
class Recaptcha extends RenderElement {

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
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // See https://developers.google.com/recaptcha/docs/verify.
    return $form_state->get('g-recaptcha-response');
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
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function validateRecaptcha(&$element, FormStateInterface $form_state) {
    $value = $element['#value'];

    $result = static::recaptcha()->verify($value);
    if (!$result['success']) {
      $form_state->setError($element, 'The recaptcha was incorrect.');
      $error_codes = $result['error_codes'];
      if (in_array('missing-input-secret', $error_codes)) {
        static::logger()->error('The secret parameter is missing.');
      }
      if (in_array('invalid-input-secret', $error_codes)) {
        static::logger()
          ->error('The secret parameter is invalid or malformed.');
      }
      if (in_array('missing-input-response', $error_codes)) {
        static::logger()
          ->error('The response parameter is missing.');
      }
      if (in_array('invalid-input-response', $error_codes)) {
        static::logger()
          ->error('The response parameter is invalid or malformed.');
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

  /**
   * Gets the logger service.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger channel
   */
  protected static function logger() {
    return \Drupal::service('logger.channel.recaptcha');
  }

}
