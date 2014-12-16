<?php

/**
 * @file
 * Contains Drupal\recaptcha\Recaptcha
 */

namespace Drupal\recaptcha;

use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
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
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Creates a new Recaptcha instance.
   *
   * @param \GuzzleHttp\ClientInterface $client
   *   The http client.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(ClientInterface $client, RequestStack $request_stack, ConfigFactoryInterface $config_factory, LoggerInterface $logger) {
    $this->client = $client;
    $this->requestStack = $request_stack;
    $this->config = $config_factory->get('recaptcha.settings');
    $this->logger = $logger;
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
      $this->logger->error('Error requesting recaptcha verification', ['exception' => $e]);
      return [
        'success' => FALSE,
        'error-codes' => ['request-exception' => $e->getMessage()]
      ];
    }
  }

}
