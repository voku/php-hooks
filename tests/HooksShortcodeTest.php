<?php

use voku\helper\Hooks;

/**
 * Class HooksTest
 */
class HooksShortcodeTest extends PHPUnit_Framework_TestCase
{

  /**
   * @var Hooks
   */
  protected $hooks;

  /**
   * @param $attrs
   *
   * @return string
   */
  public function parse_youtube($attrs)
  {
    $hooks = Hooks::getInstance();

    // init
    $autoplay = '';
    $noControls = '';
    $list = '';
    $id = '';
    $width = '';
    $height = '';
    $color = '';
    $theme = '';
    $start = '';

    extract(
        $hooks->shortcode_atts(
            array(
                'autoplay',
                'noControls',
                'list'   => null,
                'id'     => null,
                'width'  => 640,
                'height' => 390,
                'color'  => 'red',
                'theme'  => 'dark',
                'start'  => 0,
            ),
            $attrs
        )
    );

    if (!$id && !$list) {
      return 'Missing id or list parameter';
    }

    $h = '<iframe type="text/html" frameborder=0 width=' . $width . ' height=' . $height . ' src="http://www.youtube.com/embed';
    if ($id) {
      $h .= '/' . $id;
    }
    $h .= '?color=' . $color . '&theme=' . $theme . '&autoplay=' . (int)$autoplay . '&controls=' . (int)!$noControls;
    if ($list) {
      $h .= '&listType=playlist&list=' . $list;
    } else {
      $h .= '&start=' . $start;
    }
    $h .= '" />';

    return $h;
  }

  public function testShortcode()
  {
    $hooks = Hooks::getInstance();
    $hooks->add_shortcode('youtube', array($this, 'parse_youtube'));

    $default_content = '[youtube id=iCUV3iv9xOs color=white theme=light]';
    $parsed_content = $hooks->do_shortcode($default_content);

    self::assertEquals('<iframe type="text/html" frameborder=0 width=640 height=390 src="http://www.youtube.com/embed/iCUV3iv9xOs?color=white&theme=light&autoplay=0&controls=1&start=0" />', $parsed_content);
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
