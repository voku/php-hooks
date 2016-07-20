<?php

use voku\helper\Hooks;

/**
 * Class HooksTest
 */
class HooksTest extends PHPUnit_Framework_TestCase
{

  /**
   * @var Hooks
   */
  protected $hooks;

  /**
   * @var string
   */
  protected $testString_1 = 'lalllöäü123';

  /**
   * @var string
   */
  protected $testString_2 = 'lalll_§$§$&&//"?23';

  /**
   * @param $input
   *
   * @return string
   */
  public function hookTestString_1($input)
  {
    return $input . $this->testString_1;
  }

  /**
   * @param $input
   *
   * @return string
   */
  public function hookTestString_2($input)
  {
    return $input . $this->testString_2;
  }

  /**
   * test hooks
   */
  public function testHooks()
  {
    $this->hooks->add_filter('test', array($this, 'hookTestString_1'));
    $this->hooks->add_filter('test', array($this, 'hookTestString_2'));

    $lall = $this->hooks->apply_filters('test', '');

    self::assertSame($lall, $this->testString_1 . $this->testString_2);
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
