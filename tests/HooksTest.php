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
   * test hooks instance
   *
   * WARNING: you have to run "$this->testHooks()" first
   */
  public function testHooksInstance()
  {
    $lall = $this->hooks->apply_filters('test', '');

    self::assertSame($lall, $this->testString_1 . $this->testString_2);
  }

  public function testHasFunctions()
  {
    $hooks = Hooks::getInstance();

    self::assertSame(true, $hooks->remove_all_filters('testFilter'));
    self::assertSame(true, $hooks->remove_all_actions('testAction'));

    self::assertFalse($hooks->has_filter(''));
    self::assertFalse($hooks->has_filter(' '));
    self::assertFalse($hooks->has_filter('testFilter'));
    self::assertFalse($hooks->has_filter('testFilter', 'time'));
    self::assertFalse($hooks->has_action('testAction', 'time'));

    self::assertSame(true, $hooks->add_filter('testFilter', 'time'));
    self::assertSame(true, $hooks->add_action('testAction', 'time'));

    self::assertTrue($hooks->has_filter('testFilter', 'time') !== false);
    self::assertTrue($hooks->has_action('testAction', 'time') !== false);

    self::assertFalse($hooks->has_filter('testFilter', 'print_r'));
    self::assertFalse($hooks->has_action('testAction', 'print_r'));

    self::assertTrue($hooks->has_filter('testFilter'));
    self::assertTrue($hooks->has_action('testAction'));

    self::assertFalse($hooks->has_filter('notExistingFilter'));
    self::assertFalse($hooks->has_action('notExistingAction'));
  }

  public function testRemoveOneFunctions()
  {
    $hooks = Hooks::getInstance();

    $hooks->remove_all_filters('testFilter');
    $hooks->remove_all_actions('testAction');

    self::assertFalse($hooks->has_filter('testFilter', 'time'));
    self::assertFalse($hooks->has_action('testAction', 'time'));

    $hooks->add_filter('testFilter', 'time');
    $hooks->add_action('testAction', 'time');

    self::assertFalse($hooks->remove_filter('testFilter', 'print_r'));
    self::assertFalse($hooks->remove_action('testAction', 'print_r'));

    self::assertTrue($hooks->has_filter('testFilter', 'time') !== false);
    self::assertTrue($hooks->has_action('testAction', 'time') !== false);

    self::assertTrue($hooks->remove_filter('testFilter', 'time'));
    self::assertTrue($hooks->remove_action('testAction', 'time'));

    self::assertFalse($hooks->has_filter('testFilter', 'time'));
    self::assertFalse($hooks->has_action('testAction', 'time'));
  }

  public function testRemoveAllFunctions()
  {
    $hooks = Hooks::getInstance();

    self::assertSame(true, $hooks->remove_all_filters('testFilter'));
    self::assertSame(true, $hooks->remove_all_actions('testAction'));

    self::assertSame(true, $hooks->add_filter('testFilter', 'time', 10));
    self::assertSame(true, $hooks->add_filter('testFilter', 'print_r', 10));
    self::assertSame(true, $hooks->add_filter('testFilter', 'time', 25));
    self::assertSame(true, $hooks->add_action('testAction', 'time', 10));
    self::assertSame(true, $hooks->add_action('testAction', 'print_r', 10));
    self::assertSame(true, $hooks->add_action('testAction', 'time', 25));

    self::assertTrue($hooks->remove_all_filters('testFilter', 10));
    self::assertTrue($hooks->remove_all_actions('testAction', 10));

    self::assertTrue($hooks->has_filter('testFilter'));
    self::assertTrue($hooks->has_action('testAction'));

    self::assertSame(25, $hooks->has_filter('testFilter', 'time'));
    self::assertSame(25, $hooks->has_action('testAction', 'time'));

    self::assertTrue($hooks->remove_all_filters('testFilter'));
    self::assertTrue($hooks->remove_all_actions('testAction'));

    self::assertFalse($hooks->has_filter('testFilter'));
    self::assertFalse($hooks->has_action('testAction'));
  }

  public function testRunHookFunctions()
  {
    $hooks = Hooks::getInstance();

    self::assertSame(true, $hooks->remove_all_filters('testFilter'));
    self::assertSame(true, $hooks->remove_all_actions('testAction'));

    self::assertSame(false, $hooks->do_action('testAction'));
    self::assertSame(false, $hooks->do_action_ref_array('testNotExistingAction', array()));
    self::assertSame('Foo', $hooks->apply_filters('testFilter', 'Foo'));

    self::assertSame(false, $hooks->do_action_ref_array('testAction', array('test')));
    self::assertSame('Foo', $hooks->apply_filters_ref_array('testFilter', array('Foo')));

    $mock = $this->getMock('stdClass', array('doSomeAction', 'applySomeFilter'));
    $mock->expects(self::exactly(4))->method('doSomeAction');
    $mock->expects(self::exactly(10))->method('applySomeFilter')->willReturn('foo');

    self::assertSame(true, $hooks->add_action('testAction', array($mock, 'doSomeAction')));
    self::assertSame(true, $hooks->add_filter('testFilter', array($mock, 'applySomeFilter')));

    self::assertSame(2, $hooks->did_action('testAction'));
    self::assertSame(true, $hooks->do_action('testAction'));
    self::assertSame(3, $hooks->did_action('testAction'));
    self::assertSame('foo', $hooks->apply_filters('testFilter', 'Foo'));

    self::assertSame(true, $hooks->add_filter('all', array($mock, 'applySomeFilter')));

    self::assertSame(false, $hooks->do_action('notExistingAction'));
    self::assertSame('Foo', $hooks->apply_filters('notExistingFilter', 'Foo')); // unmodified value

    self::assertSame(true, $hooks->do_action('testAction', (object)array('foo' => 'bar')));
    self::assertSame(true, $hooks->do_action('testAction', 'param1', 'param2', 'param3', 'param4'));
    self::assertSame(true, $hooks->do_action_ref_array('testAction', array('test')));
    self::assertSame('foo', $hooks->apply_filters('testFilter', 'Foo'));
    self::assertSame('foo', $hooks->apply_filters_ref_array('testFilter', array('Foo')));
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
