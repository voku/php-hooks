<?php

use voku\helper\Hooks;

/**
 * Class HooksTest
 */
class HooksFilterTest extends \PHPUnit\Framework\TestCase
{

  /**
   * @var Hooks
   */
  protected $hooks;

  /**
   * test filter
   */
  public function testFilter()
  {
    $this->hooks->add_filter('foo', function ($content) {
        return '<b>' . $content . '</b>';
    });

    self::assertSame('<b>Hello world</b>', $this->hooks->apply_filters('foo', 'Hello world'));
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
