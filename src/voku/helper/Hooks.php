<?php

namespace voku\helper;

/**
 * PHP Hooks Class (Modified)
 *
 * <p>
 * <br />
 * The PHP Hooks Class is a fork of the WordPress filters hook system rolled in
 * to a class to be ported into any php based system
 *
 * <br /><br />
 * This class is heavily based on the WordPress plugin API and most (if not all)
 * of the code comes from there.
 * </p>
 *
 * @copyright   2011 - 2017
 *
 * @author      Ohad Raz <admin@bainternet.info>
 * @link        http://en.bainternet.info
 * @author      David Miles <david@amereservant.com>
 * @link        http://github.com/amereservant/PHP-Hooks
 * @author      Lars Moelleken <lars@moelleken.org>
 * @link        https://github.com/voku/PHP-Hooks/
 * @author      Damien "Mistic" Sorel <contact@git.strangeplanet.fr>
 * @link        http://www.strangeplanet.fr
 *
 * @license     GNU General Public License v3.0 - license.txt
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     voku\helper
 */
class Hooks
{
  /**
   * Filters - holds list of hooks
   *
   * @var array
   */
  protected $filters = array();

  /**
   * Merged Filters
   *
   * @var array
   */
  protected $merged_filters = array();

  /**
   * Actions
   *
   * @var array
   */
  protected $actions = array();

  /**
   * Current Filter - holds the name of the current filter
   *
   * @var array
   */
  protected $current_filter = array();

  /**
   * Container for storing shortcode tags and their hook to call for the shortcode
   *
   * @var array
   */
  public static $shortcode_tags = array();

  /**
   * Default priority
   *
   * @const int
   */
  const PRIORITY_NEUTRAL = 50;

  /**
   * This class is not allowed to call from outside: private!
   */
  protected function __construct()
  {
  }

  /**
   * Prevent the object from being cloned.
   */
  protected function __clone()
  {
  }

  /**
   * Avoid serialization.
   */
  public function __wakeup()
  {
  }

  /**
   * Returns a Singleton instance of this class.
   *
   * @return Hooks
   */
  public static function getInstance()
  {
    static $instance;

    if (null === $instance) {
      $instance = new self();
    }

    return $instance;
  }

  /**
   * FILTERS
   */

  /**
   * Adds Hooks to a function or method to a specific filter action.
   *
   * @param    string       $tag             <p>
   *                                         The name of the filter to hook the
   *                                         {@link $function_to_add} to.
   *                                         </p>
   * @param    string|array $function_to_add <p>
   *                                         The name of the function to be called
   *                                         when the filter is applied.
   *                                         </p>
   * @param    integer      $priority        <p>
   *                                         [optional] Used to specify the order in
   *                                         which the functions associated with a
   *                                         particular action are executed (default: 50).
   *                                         Lower numbers correspond with earlier execution,
   *                                         and functions with the same priority are executed
   *                                         in the order in which they were added to the action.
   *                                         </p>
   * @param string          $include_path    <p>
   *                                         [optional] File to include before executing the callback.
   *                                         </p>
   *
   * @return boolean
   */
  public function add_filter($tag, $function_to_add, $priority = self::PRIORITY_NEUTRAL, $include_path = null)
  {
    $idx = $this->_filter_build_unique_id($function_to_add);

    $this->filters[$tag][$priority][$idx] = array(
        'function'     => $function_to_add,
        'include_path' => is_string($include_path) ? $include_path : null,
    );

    unset($this->merged_filters[$tag]);

    return true;
  }

  /**
   * Removes a function from a specified filter hook.
   *
   * @param string $tag                <p>The filter hook to which the function to be removed is hooked.</p>
   * @param mixed  $function_to_remove <p>The name of the function which should be removed.</p>
   * @param int    $priority           <p>[optional] The priority of the function (default: 50).</p>
   *
   * @return bool
   */
  public function remove_filter($tag, $function_to_remove, $priority = self::PRIORITY_NEUTRAL)
  {
    $function_to_remove = $this->_filter_build_unique_id($function_to_remove);

    if (!isset($this->filters[$tag][$priority][$function_to_remove])) {
      return false;
    }

    unset($this->filters[$tag][$priority][$function_to_remove]);
    if (empty($this->filters[$tag][$priority])) {
      unset($this->filters[$tag][$priority]);
    }

    unset($this->merged_filters[$tag]);

    return true;
  }

  /**
   * Remove all of the hooks from a filter.
   *
   * @param string $tag      <p>The filter to remove hooks from.</p>
   * @param bool   $priority <p>The priority number to remove.</p>
   *
   * @return bool
   */
  public function remove_all_filters($tag, $priority = false)
  {
    if (isset($this->merged_filters[$tag])) {
      unset($this->merged_filters[$tag]);
    }

    if (!isset($this->filters[$tag])) {
      return true;
    }

    if (false !== $priority && isset($this->filters[$tag][$priority])) {
      unset($this->filters[$tag][$priority]);
    } else {
      unset($this->filters[$tag]);
    }

    return true;
  }

  /**
   * Check if any filter has been registered for the given hook.
   *
   * <p>
   * <br />
   * <strong>INFO:</strong> Use !== false to check if it's true!
   * </p>
   *
   * @param    string $tag               <p>The name of the filter hook.</p>
   * @param    bool   $function_to_check <p>[optional] Callback function name to check for </p>
   *
   * @return   mixed                       <p>
   *                                       If {@link $function_to_check} is omitted,
   *                                       returns boolean for whether the hook has
   *                                       anything registered.
   *                                       When checking a specific function, the priority
   *                                       of that hook is returned, or false if the
   *                                       function is not attached.
   *                                       When using the {@link $function_to_check} argument,
   *                                       this function may return a non-boolean value that
   *                                       evaluates to false
   *                                       (e.g.) 0, so use the === operator for testing the return value.
   *                                       </p>
   */
  public function has_filter($tag, $function_to_check = false)
  {
    $has = isset($this->filters[$tag]);
    if (false === $function_to_check || !$has) {
      return $has;
    }

    if (!($idx = $this->_filter_build_unique_id($function_to_check))) {
      return false;
    }

    foreach ((array)array_keys($this->filters[$tag]) as $priority) {
      if (isset($this->filters[$tag][$priority][$idx])) {
        return $priority;
      }
    }

    return false;
  }

  /**
   * Call the functions added to a filter hook.
   *
   * <p>
   * <br />
   * <strong>INFO:</strong> Additional variables passed to the functions hooked to <tt>$tag</tt>.
   * </p>
   *
   * @param    string|array $tag   <p>The name of the filter hook.</p>
   * @param    mixed        $value <p>The value on which the filters hooked to <tt>$tag</tt> are applied on.</p>
   *
   * @return   mixed               <p>The filtered value after all hooked functions are applied to it.</p>
   */
  public function apply_filters($tag, $value)
  {
    $args = array();

    // Do 'all' actions first
    if (isset($this->filters['all'])) {
      $this->current_filter[] = $tag;
      $args = func_get_args();
      $this->_call_all_hook($args);
    }

    if (!isset($this->filters[$tag])) {
      if (isset($this->filters['all'])) {
        array_pop($this->current_filter);
      }

      return $value;
    }

    if (!isset($this->filters['all'])) {
      $this->current_filter[] = $tag;
    }

    // Sort
    if (!isset($this->merged_filters[$tag])) {
      ksort($this->filters[$tag]);
      $this->merged_filters[$tag] = true;
    }

    reset($this->filters[$tag]);

    if (empty($args)) {
      $args = func_get_args();
    }

    array_shift($args);

    do {
      foreach ((array)current($this->filters[$tag]) as $the_) {
        if (null !== $the_['function']) {

          if (null !== $the_['include_path']) {
            /** @noinspection PhpIncludeInspection */
            include_once $the_['include_path'];
          }

          $args[0] = $value;
          $value = call_user_func_array($the_['function'], $args);
        }
      }
    } while (next($this->filters[$tag]) !== false);

    array_pop($this->current_filter);

    return $value;
  }

  /**
   * Execute functions hooked on a specific filter hook, specifying arguments in an array.
   *
   * @param    string $tag  <p>The name of the filter hook.</p>
   * @param    array  $args <p>The arguments supplied to the functions hooked to <tt>$tag</tt></p>
   *
   * @return   mixed        <p>The filtered value after all hooked functions are applied to it.</p>
   */
  public function apply_filters_ref_array($tag, $args)
  {
    // Do 'all' actions first
    if (isset($this->filters['all'])) {
      $this->current_filter[] = $tag;
      $all_args = func_get_args();
      $this->_call_all_hook($all_args);
    }

    if (!isset($this->filters[$tag])) {
      if (isset($this->filters['all'])) {
        array_pop($this->current_filter);
      }

      return $args[0];
    }

    if (!isset($this->filters['all'])) {
      $this->current_filter[] = $tag;
    }

    // Sort
    if (!isset($this->merged_filters[$tag])) {
      ksort($this->filters[$tag]);
      $this->merged_filters[$tag] = true;
    }

    reset($this->filters[$tag]);

    do {
      foreach ((array)current($this->filters[$tag]) as $the_) {
        if (null !== $the_['function']) {

          if (null !== $the_['include_path']) {
            /** @noinspection PhpIncludeInspection */
            include_once $the_['include_path'];
          }

          $args[0] = call_user_func_array($the_['function'], $args);
        }
      }
    } while (next($this->filters[$tag]) !== false);

    array_pop($this->current_filter);

    return $args[0];
  }

  /**
   * Hooks a function on to a specific action.
   *
   * @param    string  $tag             <p>
   *                                    The name of the action to which the
   *                                    <tt>$function_to_add</tt> is hooked.
   *                                    </p>
   * @param    string  $function_to_add <p>The name of the function you wish to be called.</p>
   * @param    integer $priority        <p>
   *                                    [optional] Used to specify the order in which
   *                                    the functions associated with a particular
   *                                    action are executed (default: 50).
   *                                    Lower numbers correspond with earlier execution,
   *                                    and functions with the same priority are executed
   *                                    in the order in which they were added to the action.
   *                                    </p>
   * @param     string $include_path    <p>[optional] File to include before executing the callback.</p>
   *
   * @return bool
   */
  public function add_action($tag, $function_to_add, $priority = self::PRIORITY_NEUTRAL, $include_path = null)
  {
    return $this->add_filter($tag, $function_to_add, $priority, $include_path);
  }

  /**
   * Check if any action has been registered for a hook.
   *
   * <p>
   * <br />
   * <strong>INFO:</strong> Use !== false to check if it's true!
   * </p>
   *
   * @param    string   $tag               <p>The name of the action hook.</p>
   * @param bool|string $function_to_check <p>[optional]</p>
   *
   * @return   mixed                       <p>
   *                                       If <tt>$function_to_check</tt> is omitted,
   *                                       returns boolean for whether the hook has
   *                                       anything registered.
   *                                       When checking a specific function,
   *                                       the priority of that hook is returned,
   *                                       or false if the function is not attached.
   *                                       When using the <tt>$function_to_check</tt>
   *                                       argument, this function may return a non-boolean
   *                                       value that evaluates to false (e.g.) 0,
   *                                       so use the === operator for testing the return value.
   *                                       </p>
   */
  public function has_action($tag, $function_to_check = false)
  {
    return $this->has_filter($tag, $function_to_check);
  }

  /**
   * Removes a function from a specified action hook.
   *
   * @param string $tag                <p>The action hook to which the function to be removed is hooked.</p>
   * @param mixed  $function_to_remove <p>The name of the function which should be removed.</p>
   * @param int    $priority           <p>[optional] The priority of the function (default: 50).</p>
   *
   * @return bool <p>Whether the function is removed.</p>
   */
  public function remove_action($tag, $function_to_remove, $priority = self::PRIORITY_NEUTRAL)
  {
    return $this->remove_filter($tag, $function_to_remove, $priority);
  }

  /**
   * Remove all of the hooks from an action.
   *
   * @param string $tag      <p>The action to remove hooks from.</p>
   * @param bool   $priority <p>The priority number to remove them from.</p>
   *
   * @return bool
   */
  public function remove_all_actions($tag, $priority = false)
  {
    return $this->remove_all_filters($tag, $priority);
  }

  /**
   * Execute functions hooked on a specific action hook.
   *
   * @param    string $tag     <p>The name of the action to be executed.</p>
   * @param    mixed  $arg     <p>
   *                           [optional] Additional arguments which are passed on
   *                           to the functions hooked to the action.
   *                           </p>
   *
   * @return   bool            <p>Will return false if $tag does not exist in $filter array.</p>
   */
  public function do_action($tag, $arg = '')
  {
    if (!is_array($this->actions)) {
      $this->actions = array();
    }

    if (!isset($this->actions[$tag])) {
      $this->actions[$tag] = 1;
    } else {
      ++$this->actions[$tag];
    }

    // Do 'all' actions first
    if (isset($this->filters['all'])) {
      $this->current_filter[] = $tag;
      $all_args = func_get_args();
      $this->_call_all_hook($all_args);
    }

    if (!isset($this->filters[$tag])) {
      if (isset($this->filters['all'])) {
        array_pop($this->current_filter);
      }

      return false;
    }

    if (!isset($this->filters['all'])) {
      $this->current_filter[] = $tag;
    }

    $args = array();

    if (
        is_array($arg)
        &&
        isset($arg[0])
        &&
        is_object($arg[0])
        &&
        1 == count($arg)
    ) {
      $args[] =& $arg[0];
    } else {
      $args[] = $arg;
    }

    $numArgs = func_num_args();

    for ($a = 2; $a < $numArgs; $a++) {
      $args[] = func_get_arg($a);
    }

    // Sort
    if (!isset($this->merged_filters[$tag])) {
      ksort($this->filters[$tag]);
      $this->merged_filters[$tag] = true;
    }

    reset($this->filters[$tag]);

    do {
      foreach ((array)current($this->filters[$tag]) as $the_) {
        if (null !== $the_['function']) {

          if (null !== $the_['include_path']) {
            /** @noinspection PhpIncludeInspection */
            include_once $the_['include_path'];
          }

          call_user_func_array($the_['function'], $args);
        }
      }
    } while (next($this->filters[$tag]) !== false);

    array_pop($this->current_filter);

    return true;
  }

  /**
   * Execute functions hooked on a specific action hook, specifying arguments in an array.
   *
   * @param    string $tag  <p>The name of the action to be executed.</p>
   * @param    array  $args <p>The arguments supplied to the functions hooked to <tt>$tag</tt></p>
   *
   * @return   bool         <p>Will return false if $tag does not exist in $filter array.</p>
   */
  public function do_action_ref_array($tag, $args)
  {
    if (!is_array($this->actions)) {
      $this->actions = array();
    }

    if (!isset($this->actions[$tag])) {
      $this->actions[$tag] = 1;
    } else {
      ++$this->actions[$tag];
    }

    // Do 'all' actions first
    if (isset($this->filters['all'])) {
      $this->current_filter[] = $tag;
      $all_args = func_get_args();
      $this->_call_all_hook($all_args);
    }

    if (!isset($this->filters[$tag])) {
      if (isset($this->filters['all'])) {
        array_pop($this->current_filter);
      }

      return false;
    }

    if (!isset($this->filters['all'])) {
      $this->current_filter[] = $tag;
    }

    // Sort
    if (!isset($merged_filters[$tag])) {
      ksort($this->filters[$tag]);
      $merged_filters[$tag] = true;
    }

    reset($this->filters[$tag]);

    do {
      foreach ((array)current($this->filters[$tag]) as $the_) {
        if (null !== $the_['function']) {

          if (null !== $the_['include_path']) {
            /** @noinspection PhpIncludeInspection */
            include_once $the_['include_path'];
          }

          call_user_func_array($the_['function'], $args);
        }
      }
    } while (next($this->filters[$tag]) !== false);

    array_pop($this->current_filter);

    return true;
  }

  /**
   * Retrieve the number of times an action has fired.
   *
   * @param string $tag <p>The name of the action hook.</p>
   *
   * @return integer <p>The number of times action hook <tt>$tag</tt> is fired.</p>
   */
  public function did_action($tag)
  {
    if (!is_array($this->actions) || !isset($this->actions[$tag])) {
      return 0;
    }

    return $this->actions[$tag];
  }

  /**
   * Retrieve the name of the current filter or action.
   *
   * @return string <p>Hook name of the current filter or action.</p>
   */
  public function current_filter()
  {
    return end($this->current_filter);
  }

  /**
   * Build Unique ID for storage and retrieval.
   *
   * @param    string|array $function <p>Used for creating unique id.</p>
   *
   * @return   string|bool             <p>
   *                                   Unique ID for usage as array key or false if
   *                                   $priority === false and $function is an
   *                                   object reference, and it does not already have a unique id.
   *                                   </p>
   */
  private function _filter_build_unique_id($function)
  {
    if (is_string($function)) {
      return $function;
    }

    if (is_object($function)) {
      // Closures are currently implemented as objects
      $function = array(
          $function,
          '',
      );
    } else {
      $function = (array)$function;
    }

    if (is_object($function[0])) {
      // Object Class Calling
      return spl_object_hash($function[0]) . $function[1];
    }

    if (is_string($function[0])) {
      // Static Calling
      return $function[0] . $function[1];
    }

    return false;
  }

  /**
   * Call "All" Hook
   *
   * @param array $args
   */
  public function _call_all_hook($args)
  {
    reset($this->filters['all']);

    do {
      foreach ((array)current($this->filters['all']) as $the_) {
        if (null !== $the_['function']) {

          if (null !== $the_['include_path']) {
            /** @noinspection PhpIncludeInspection */
            include_once $the_['include_path'];
          }

          call_user_func_array($the_['function'], $args);
        }
      }
    } while (next($this->filters['all']) !== false);
  }

  /** @noinspection MagicMethodsValidityInspection */
  /**
   * @param array $args
   *
   * @deprecated use "this->_call_all_hook()"
   */
  public function __call_all_hook($args)
  {
    // <-- refactoring "__call_all_hook()" into "_call_all_hook()" is a breaking change (BC),
    // so we will only deprecate the usage

    $this->_call_all_hook($args);
  }

  /**
   * Add hook for shortcode tag.
   *
   * <p>
   * <br />
   * There can only be one hook for each shortcode. Which means that if another
   * plugin has a similar shortcode, it will override yours or yours will override
   * theirs depending on which order the plugins are included and/or ran.
   * <br />
   * <br />
   * </p>
   *
   * Simplest example of a shortcode tag using the API:
   *
   * <code>
   * // [footag foo="bar"]
   * function footag_func($atts) {
   *  return "foo = {$atts[foo]}";
   * }
   * add_shortcode('footag', 'footag_func');
   * </code>
   *
   * Example with nice attribute defaults:
   *
   * <code>
   * // [bartag foo="bar"]
   * function bartag_func($atts) {
   *  $args = shortcode_atts(array(
   *    'foo' => 'no foo',
   *    'baz' => 'default baz',
   *  ), $atts);
   *
   *  return "foo = {$args['foo']}";
   * }
   * add_shortcode('bartag', 'bartag_func');
   * </code>
   *
   * Example with enclosed content:
   *
   * <code>
   * // [baztag]content[/baztag]
   * function baztag_func($atts, $content='') {
   *  return "content = $content";
   * }
   * add_shortcode('baztag', 'baztag_func');
   * </code>
   *
   * @param string   $tag  <p>Shortcode tag to be searched in post content.</p>
   * @param callable $func <p>Hook to run when shortcode is found.</p>
   *
   * @return bool
   */
  public function add_shortcode($tag, $func)
  {
    if (is_callable($func)) {
      self::$shortcode_tags[$tag] = $func;

      return true;
    }

    return false;
  }

  /**
   * Removes hook for shortcode.
   *
   * @param string $tag <p>shortcode tag to remove hook for.</p>
   *
   * @return bool
   */
  public function remove_shortcode($tag)
  {
    if (isset(self::$shortcode_tags[$tag])) {
      unset(self::$shortcode_tags[$tag]);

      return true;
    }

    return false;
  }

  /**
   * This function is simple, it clears all of the shortcode tags by replacing the
   * shortcodes by a empty array. This is actually a very efficient method
   * for removing all shortcodes.
   *
   * @return bool
   */
  public function remove_all_shortcodes()
  {
    self::$shortcode_tags = array();

    return true;
  }

  /**
   * Whether a registered shortcode exists named $tag
   *
   * @param string $tag
   *
   * @return boolean
   */
  public function shortcode_exists($tag)
  {
    return array_key_exists($tag, self::$shortcode_tags);
  }

  /**
   * Whether the passed content contains the specified shortcode.
   *
   * @param string $content
   * @param string $tag
   *
   * @return bool
   */
  public function has_shortcode($content, $tag)
  {
    if (false === strpos($content, '[')) {
      return false;
    }

    if ($this->shortcode_exists($tag)) {
      preg_match_all('/' . $this->get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER);
      if (empty($matches)) {
        return false;
      }

      foreach ($matches as $shortcode) {
        if ($tag === $shortcode[2]) {
          return true;
        }

        if (!empty($shortcode[5]) && $this->has_shortcode($shortcode[5], $tag)) {
          return true;
        }
      }
    }

    return false;
  }

  /**
   * Search content for shortcodes and filter shortcodes through their hooks.
   *
   * <p>
   * <br />
   * If there are no shortcode tags defined, then the content will be returned
   * without any filtering. This might cause issues when plugins are disabled but
   * the shortcode will still show up in the post or content.
   * </p>
   *
   * @param string $content <p>Content to search for shortcodes.</p>
   *
   * @return string <p>Content with shortcodes filtered out.</p>
   */
  public function do_shortcode($content)
  {
    if (empty(self::$shortcode_tags) || !is_array(self::$shortcode_tags)) {
      return $content;
    }

    $pattern = $this->get_shortcode_regex();

    return preg_replace_callback(
        "/$pattern/s",
        array(
            $this,
            '_do_shortcode_tag',
        ),
        $content
    );
  }

  /**
   * Retrieve the shortcode regular expression for searching.
   *
   * <p>
   * <br />
   * The regular expression combines the shortcode tags in the regular expression
   * in a regex class.
   * <br /><br />
   *
   * The regular expression contains 6 different sub matches to help with parsing.
   * <br /><br />
   *
   * 1 - An extra [ to allow for escaping shortcodes with double [[]]<br />
   * 2 - The shortcode name<br />
   * 3 - The shortcode argument list<br />
   * 4 - The self closing /<br />
   * 5 - The content of a shortcode when it wraps some content.<br />
   * 6 - An extra ] to allow for escaping shortcodes with double [[]]<br />
   * </p>
   *
   * @return string The shortcode search regular expression
   */
  public function get_shortcode_regex()
  {
    $tagnames = array_keys(self::$shortcode_tags);
    $tagregexp = implode('|', array_map('preg_quote', $tagnames));

    // WARNING! Do not change this regex without changing __do_shortcode_tag() and __strip_shortcode_tag()
    // Also, see shortcode_unautop() and shortcode.js.
    return
        '\\[' // Opening bracket
        . '(\\[?)' // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
        . "($tagregexp)" // 2: Shortcode name
        . '(?![\\w-])' // Not followed by word character or hyphen
        . '(' // 3: Unroll the loop: Inside the opening shortcode tag
        . '[^\\]\\/]*' // Not a closing bracket or forward slash
        . '(?:'
        . '\\/(?!\\])' // A forward slash not followed by a closing bracket
        . '[^\\]\\/]*' // Not a closing bracket or forward slash
        . ')*?'
        . ')'
        . '(?:'
        . '(\\/)' // 4: Self closing tag ...
        . '\\]' // ... and closing bracket
        . '|'
        . '\\]' // Closing bracket
        . '(?:'
        . '(' // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
        . '[^\\[]*+' // Not an opening bracket
        . '(?:'
        . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
        . '[^\\[]*+' // Not an opening bracket
        . ')*+'
        . ')'
        . '\\[\\/\\2\\]' // Closing shortcode tag
        . ')?'
        . ')'
        . '(\\]?)'; // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
  }

  /**
   * Regular Expression callable for do_shortcode() for calling shortcode hook.
   *
   * @see self::get_shortcode_regex for details of the match array contents.
   *
   * @param array $m <p>regular expression match array</p>
   *
   * @return mixed <p><strong>false</strong> on failure</p>
   */
  private function _do_shortcode_tag($m)
  {
    // allow [[foo]] syntax for escaping a tag
    if ($m[1] == '[' && $m[6] == ']') {
      return substr($m[0], 1, -1);
    }

    $tag = $m[2];
    $attr = $this->shortcode_parse_atts($m[3]);

    // enclosing tag - extra parameter
    if (isset($m[5])) {
      return $m[1] . call_user_func(self::$shortcode_tags[$tag], $attr, $m[5], $tag) . $m[6];
    }

    // self-closing tag
    return $m[1] . call_user_func(self::$shortcode_tags[$tag], $attr, null, $tag) . $m[6];
  }

  /**
   * Retrieve all attributes from the shortcodes tag.
   *
   * <p>
   * <br />
   * The attributes list has the attribute name as the key and the value of the
   * attribute as the value in the key/value pair. This allows for easier
   * retrieval of the attributes, since all attributes have to be known.
   * </p>
   *
   * @param string $text
   *
   * @return array <p>List of attributes and their value.</p>
   */
  public function shortcode_parse_atts($text)
  {
    $atts = array();
    $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    $text = preg_replace("/[\x{00a0}\x{200b}]+/u", ' ', $text);
    if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
      foreach ($match as $m) {
        if (!empty($m[1])) {
          $atts[strtolower($m[1])] = stripcslashes($m[2]);
        } elseif (!empty($m[3])) {
          $atts[strtolower($m[3])] = stripcslashes($m[4]);
        } elseif (!empty($m[5])) {
          $atts[strtolower($m[5])] = stripcslashes($m[6]);
        } elseif (isset($m[7]) && $m[7] !== '') {
          $atts[] = stripcslashes($m[7]);
        } elseif (isset($m[8])) {
          $atts[] = stripcslashes($m[8]);
        }
      }
    } else {
      $atts = ltrim($text);
    }

    return $atts;
  }

  /**
   * Combine user attributes with known attributes and fill in defaults when needed.
   *
   * <p>
   * <br />
   * The pairs should be considered to be all of the attributes which are
   * supported by the caller and given as a list. The returned attributes will
   * only contain the attributes in the $pairs list.
   *
   * <br /><br />
   * If the $atts list has unsupported attributes, then they will be ignored and
   * removed from the final returned list.
   * </p>
   *
   * @param array  $pairs     <p>Entire list of supported attributes and their defaults.</p>
   * @param array  $atts      <p>User defined attributes in shortcode tag.</p>
   * @param string $shortcode <p>[optional] The name of the shortcode, provided for context to enable filtering.</p>
   *
   * @return array <p>Combined and filtered attribute list.</p>
   */
  public function shortcode_atts($pairs, $atts, $shortcode = '')
  {
    $atts = (array)$atts;
    $out = array();
    foreach ($pairs as $name => $default) {
      if (array_key_exists($name, $atts)) {
        $out[$name] = $atts[$name];
      } else {
        $out[$name] = $default;
      }
    }
    /**
     * Filter a shortcode's default attributes.
     *
     * <p>
     * <br />
     * If the third parameter of the shortcode_atts() function is present then this filter is available.
     * The third parameter, $shortcode, is the name of the shortcode.
     * </p>
     *
     * @param array $out   <p>The output array of shortcode attributes.</p>
     * @param array $pairs <p>The supported attributes and their defaults.</p>
     * @param array $atts  <p>The user defined shortcode attributes.</p>
     */
    if ($shortcode) {
      $out = $this->apply_filters(
          array(
              $this,
              "shortcode_atts_{$shortcode}",
          ), $out, $pairs, $atts
      );
    }

    return $out;
  }

  /**
   * Remove all shortcode tags from the given content.
   *
   * @param string $content <p>Content to remove shortcode tags.</p>
   *
   * @return string <p>Content without shortcode tags.</p>
   */
  public function strip_shortcodes($content)
  {

    if (empty(self::$shortcode_tags) || !is_array(self::$shortcode_tags)) {
      return $content;
    }

    $pattern = $this->get_shortcode_regex();

    return preg_replace_callback(
        "/$pattern/s",
        array(
            $this,
            '_strip_shortcode_tag',
        ),
        $content
    );
  }

  /**
   * Strip shortcode by tag.
   *
   * @param array $m
   *
   * @return string
   */
  private function _strip_shortcode_tag($m)
  {
    // allow [[foo]] syntax for escaping a tag
    if ($m[1] == '[' && $m[6] == ']') {
      return substr($m[0], 1, -1);
    }

    return $m[1] . $m[6];
  }

}
