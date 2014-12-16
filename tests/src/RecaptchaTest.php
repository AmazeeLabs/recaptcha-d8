<?php
/**
 * @file
 * Contains \Drupal\recaptcha\RecaptchaTest
 */

namespace Drupal\Tests\recaptcha;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\Client;
use Drupal\recaptcha\Recaptcha;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests the recaptcha service.
 *
 * @coversDefaultClass \Drupal\recaptcha\Recaptcha
 * @group recaptcha
 */
class RecaptchaTest extends UnitTestCase {

  /**
   * The http client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->httpClient = new Client();


    $this->configFactory = $this->getConfigFactoryStub([
      'recaptcha.settings' => ['secret_key' => $this->randomMachineName()]
    ]);

    $this->requestStack = new RequestStack();
    $request = Request::create('/foo', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
    $this->requestStack->push($request);

  }

  /**
   * Tests the verification feature.
   *
   * @covers ::verify
   */
  public function testVerifySuccess() {

    $http_response = new Response(200, [], Stream::factory(fopen(__DIR__ . '/response-success.json', 'r+')));
    $mock = new Mock([$http_response]);

    // Add the mock subscriber to the client.
    $this->httpClient->getEmitter()->attach($mock);

    $logger = $this->getMock('\Psr\Log\LoggerInterface');
    $logger->expects($this->never())
      ->method('error');

    $recaptcha = new Recaptcha($this->httpClient, $this->requestStack, $this->configFactory, $logger);
    $response = $recaptcha->verify($this->randomMachineName());

    $this->assertTrue($response['success'], "The response was success");

  }

  /**
   * Tests an error response.
   *
   * @covers ::verify
   */
  public function testVerifyError() {

    $http_response = new Response(400, [], Stream::factory("There was an error"));
    $mock = new Mock([$http_response]);

    // Add the mock subscriber to the client.
    $this->httpClient->getEmitter()->attach($mock);

    $logger = $this->getMock('\Psr\Log\LoggerInterface');
    $logger->expects($this->once())
      ->method('error');

    $recaptcha = new Recaptcha($this->httpClient, $this->requestStack, $this->configFactory, $logger);
    $response = $recaptcha->verify($this->randomMachineName());

    $this->assertFalse($response['success'], "The response was not a success");
  }

}
