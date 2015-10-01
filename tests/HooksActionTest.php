<?php

use voku\helper\Hooks;

/**
 * Class HooksTest
 */
class HooksActionTest extends PHPUnit_Framework_TestCase
{

  /**
   * @var Hooks
   */
  protected $hooks;

  /**
   * test action
   */
  public function testAction()
  {
    $done = false;

    $this->hooks->add_action('bar', function () use (&$done) {
        $done = true;
    });

    $this->hooks->do_action('bar');

    self::assertTrue($done);
  }

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   */
  protected function setUp()
  {
    $this->hooks = Hooks::getInstance();
  }
}
