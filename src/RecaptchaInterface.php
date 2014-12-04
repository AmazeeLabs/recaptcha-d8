<?php

/**
 * @file
 * Contains Drupal\recaptcha\RecaptchaInterface
 */

namespace Drupal\recaptcha;
use Drupal\Core\Config\Config;

/**
 * Provides integration with the Google Recaptcha service.
 */
interface RecaptchaInterface {

  /**
   * Verify the recaptcha response.
   *
   * @param string $response
   *   The response string from the recaptcha form.
   *
   * @return array
   *   An associative array containing:
   *   - success: TRUE if the captcha verification was successful.
   *   - error-codes: An array of error codes if the verification failed.
   */
  public function verify($response);

}
