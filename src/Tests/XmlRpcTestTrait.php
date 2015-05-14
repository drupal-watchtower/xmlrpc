<?php

/**
 * @file
 * Contains \Drupal\xmlrpc\Tests\XmlRpcTestTrait.
 */

namespace Drupal\xmlrpc\Tests;

/**
 * Provides common functionality for XmlRpc test classes.
 */
trait XmlRpcTestTrait {

  /**
   * Invokes xmlrpc method.
   *
   * @param array $args
   *   An associative array whose keys are the methods to call and whose values
   *   are the arguments to pass to the respective method. If multiple methods
   *   are specified, a system.multicall is performed.
   * @param array $headers
   *   (optional) An array of headers to pass along.
   *
   * @return mixed
   *   The result of xmlrpc() function call.
   *
   * @see xmlrpc()
   */
  protected function xmlRpcGet(array $args, array $headers = []) {
    $url = \Drupal::url('xmlrpc.php', [], ['absolute' => TRUE]);

    return xmlrpc($url, $args, $headers);
  }

}
