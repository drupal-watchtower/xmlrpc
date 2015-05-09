<?php

/**
 * @file
 * Contains \Drupal\xmlrpc\Tests\XmlRpcMessagesTest.
 */

namespace Drupal\xmlrpc\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests large messages and method alterations.
 *
 * @group xmlrpc
 */
class XmlRpcMessagesTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('xmlrpc', 'xmlrpc_test');

  /**
   * Make sure that XML-RPC can transfer large messages.
   */
  public function testSizedMessages() {
    $xml_url = \Drupal::url('xmlrpc.php', [], ['absolute' => TRUE]);
    $sizes = [8, 80, 160];
    foreach ($sizes as $size) {
      $xml_message_l = xmlrpc_test_message_sized_in_kb($size);
      $xml_message_r = xmlrpc($xml_url, ['messages.messageSizedInKB' => [$size]]);

      $this->assertEqual($xml_message_l, $xml_message_r, format_string('XML-RPC messages.messageSizedInKB of %s Kb size received', ['%s' => $size]));
    }
  }

  /**
   * Ensure that hook_xmlrpc_alter() can hide even builtin methods.
   */
  public function testAlterListMethods() {
    // Ensure xmlrpc_test.alter() is disabled and retrieve regular list of methods.
    \Drupal::state()->set('xmlrpc_test.alter', FALSE);
    $url = \Drupal::url('xmlrpc.php', [], ['absolute' => TRUE]);
    $methods1 = xmlrpc($url, ['system.listMethods' => []]);

    // Enable the alter hook and retrieve the list of methods again.
    \Drupal::state()->set('xmlrpc_test.alter', TRUE);
    $methods2 = xmlrpc($url, ['system.listMethods' => []]);

    $diff = array_diff($methods1, $methods2);
    $this->assertTrue(is_array($diff) && !empty($diff), 'Method list is altered by hook_xmlrpc_alter');
    $removed = reset($diff);
    $this->assertEqual($removed, 'system.methodSignature', 'Hiding builting system.methodSignature with hook_xmlrpc_alter works');
  }

}
