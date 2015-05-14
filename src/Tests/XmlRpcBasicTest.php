<?php

/**
 * @file
 * Contains \Drupal\xmlrpc\Tests\XmlRpcBasicTest.
 */

namespace Drupal\xmlrpc\Tests;

use Drupal\simpletest\WebTestBase;
use GuzzleHttp\Exception\ClientException;

/**
 * Perform basic XML-RPC tests that do not require addition callbacks.
 *
 * @group xmlrpc
 */
class XmlRpcBasicTest extends WebTestBase {

  use XmlRpcTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('xmlrpc');

  /**
   * Ensure that a basic XML-RPC call with no parameters works.
   */
  public function testListMethods() {
    // Minimum list of methods that should be included.
    $minimum = array(
      'system.multicall',
      'system.methodSignature',
      'system.getCapabilities',
      'system.listMethods',
      'system.methodHelp',
    );

    // Invoke XML-RPC call to get list of methods.
    $methods = $this->xmlRpcGet(['system.listMethods' => []]);

    // Ensure that the minimum methods were found.
    $count = 0;
    foreach ($methods as $method) {
      if (in_array($method, $minimum)) {
        $count++;
      }
    }

    $this->assertEqual($count, count($minimum), 'system.listMethods returned at least the minimum listing');
  }

  /**
   * Ensure that system.methodSignature returns an array of signatures.
   */
  public function testMethodSignature() {
    $signature = $this->xmlRpcGet(['system.methodSignature' => ['system.listMethods']]);
    $this->assert(is_array($signature) && !empty($signature) && is_array($signature[0]),
      'system.methodSignature returns an array of signature arrays.');
  }

  /**
   * Ensure that XML-RPC correctly handles invalid messages when parsing.
   */
  public function testInvalidMessageParsing() {
    $invalid_messages = array(
      array(
        'message' => xmlrpc_message(''),
        'assertion' => 'Empty message correctly rejected during parsing.',
      ),
      array(
        'message' => xmlrpc_message('<?xml version="1.0" encoding="ISO-8859-1"?>'),
        'assertion' => 'Empty message with XML declaration correctly rejected during parsing.',
      ),
      array(
        'message' => xmlrpc_message('<?xml version="1.0"?><params><param><value><string>value</string></value></param></params>'),
        'assertion' => 'Non-empty message without a valid message type is rejected during parsing.',
      ),
      array(
        'message' => xmlrpc_message('<methodResponse><params><param><value><string>value</string></value></param></methodResponse>'),
        'assertion' => 'Non-empty malformed message is rejected during parsing.',
      ),
    );

    foreach ($invalid_messages as $assertion) {
      $this->assertFalse(xmlrpc_message_parse($assertion['message']), $assertion['assertion']);
    }
  }

  /**
   * Ensure that XML-RPC correctly handles XML Accept headers.
   */
  public function testAcceptHeaders() {
    $request_header_sets = array(
      // Default.
      'implicit' => array(),
      'text/xml' => array(
        'Accept' => 'text/xml',
      ),
      'application/xml' => array(
        'Accept' => 'application/xml',
      )
    );

    foreach ($request_header_sets as $accept => $headers) {
      try {
        $methods = $this->xmlRpcGet(['system.listMethods' => []], $headers);
        $this->assertTrue(is_array($methods), strtr('@accept accept header is accepted', array('@accept' => $accept)));
      }
      catch (ClientException $e) {
        $this->fail($e);
      }
    }
  }
}
