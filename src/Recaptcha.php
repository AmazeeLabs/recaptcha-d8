<?php

/**
 * @file
 * Contains Drupal\recaptcha\Recaptcha
 */

namespace Drupal\recaptcha;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a client for the Google Recaptcha service.
 */
class Recaptcha implements RecaptchaInterface {

  /**
   * The recaptcha config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Creates a new Recaptcha instance.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The http client.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ClientInterface $client, RequestStack $request_stack, ConfigFactoryInterface $config_factory) {
    $this->client = $client;
    $this->requestStack = $request_stack;
    $this->config = $config_factory->get('recaptcha.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function verify($response) {
    try {
      $secret = $this->config->get('secret_key');
      $client_ip = $this->requestStack->getCurrentRequest()->getClientIp();
      $response = $this->client->get("https://www.google.com/recaptcha/api/siteverify",
        [
          'query' => [
            'secret' => $secret,
            'response' => $response,
            'remoteip' => $client_ip,
          ]
        ]);
      return $response->json();
    }
    catch (RequestException $e) {
      watchdog_exception('recaptcha', $e, "Error requesting recaptcha verification \n\n<br /><br /> %type: !message in %function (line %line of %file).");
      return [
        'success' => FALSE,
        'error-codes' => ['request-exception' => $e->getMessage()]
      ];
    }
  }

}
