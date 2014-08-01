<?php
/**
 * PHP Hooks Class (Modified)
 *
 * The PHP Hooks Class is a fork of the WordPress filters hook system rolled in 
 * to a class to be ported into any php based system
 *
 * This class is heavily based on the WordPress plugin API and most (if not all)
 * of the code comes from there.
 * 
 * 
 * @version     0.1.3
 * @copyright   2011 - 2013
 * @author      Ohad Raz (email: admin@bainternet.info)
 * @link        http://en.bainternet.info
 * @author      David Miles <david@amereservant.com>
 * @link        http://github.com/amereservant/PHP-Hooks
 * @author      Lars Moelleken <lars@moelleken.org>
 * @link        https://github.com/voku/PHP-Hooks/
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
 * @package PHP Hooks
 */

if (!class_exists('Hooks')) {

/**
* Hooks
*/
class Hooks implements MulticastDebug
{
  /**
   * Filters - holds list of hooks
   *
   * @var      array
   * @access   protected
   * @since    0.1
   */
  protected $filters = array();

  /**
   * Merged Filters
   *
   * @var      array
   * @access   protected
   * @since    0.1
   */
  protected $merged_filters = array();

  /**
   * Actions
   *
   * @var      array
   * @access   protected
   * @since    0.1
   */
  protected $actions = array();

  /**
   * Current Filter - holds the name of the current filter
   *
   * @var      array
   * @access   protected
   * @since    0.1
   */
  protected $current_filter = array();

  /**
   * is not allowed to call from outside: private!
   *
   */
  private function __construct() { }

  /**
   * prevent the instance from being cloned
   *
   * @return void
   */
  private function __clone() { }

  /**
   * prevent from being unserialized
   *
   * @return void
   */
  private function __wakeup() { }

  /**
   * Singleton Instance
   *
   * Returns a Singleton instance of this class.
   *
   * @param    void
   * @return   object  instanceof this class
   * @access   public
   * @static
   * @since    0.1.2
   */
  public static function getInstance() {
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
   * Add Filter
   *
   * Adds Hooks to a function or method to a specific filter action.
   *
   * @access   public
   * @since    0.1
   * @param    string  $tag                The name of the filter to hook the
   *                                       {@link $function_to_add} to.
   * @param    string  $function_to_add    The name of the function to be called
   *                                       when the filter is applied.
   * @param    integer $priority           (optional) Used to specify the order in
   *                                       which the functions associated with a
   *                                       particular action are executed (default: 10).
   *                                       Lower numbers correspond with earlier execution,
   *                                       and functions with the same priority are executed
   *                                       in the order in which they were added to the action.
   * @param    integer $accepted_args      (optional) The number of arguments the function accept (default 1).
   * @return   boolean true
   */
  public function add_filter( $tag, $function_to_add, $priority=10, $accepted_args=1 )
  {
    $idx =  $this->_filter_build_unique_id($tag, $function_to_add, $priority);

    $this->filters[$tag][$priority][$idx] = array(
      'function'      => $function_to_add,
      'accepted_args' => $accepted_args
    );

    // DEBUG
    //dump($this->filters, false);
    
    unset( $this->merged_filters[$tag] );
    
    return true;
  }

  /**
   * Remove Filter
   *
   * Removes a function from a specified filter hook.
   *
   * @param    string  $tag                The filter hook to which the function to be removed is hooked.
   * @param    string  $function_to_remove The name of the function which should be removed.
   * @param    integer $priority           (optional) The priority of the function (default: 10).
   * @param    integer $accepted_args      (optional) The number of arguments the function accepts (default: 1).
   * @return   boolean                     Whether the function existed before it was removed.
   * @access   public
   * @since    0.1
   */
  public function remove_filter( $tag, $function_to_remove, $priority=10 )
  {
    $function_to_remove = $this->_filter_build_unique_id($tag, $function_to_remove, $priority);

    if( !isset($this->filters[$tag][$priority][$function_to_remove]) )
    {
      return false;
    }

    unset($this->filters[$tag][$priority][$function_to_remove]);
    if( empty($this->filters[$tag][$priority]) )
    {
      unset($this->filters[$tag][$priority]);
    }

    unset($this->merged_filters[$tag]);

    return true;
  }

  /**
   * Remove All Filters
   *
   * Remove all of the hooks from a filter.
   *
   * @param    string  $tag        The filter to remove hooks from.
   * @param    int     $priority   The priority number to remove.
   * @return   bool                True when finished.
   */
  public function remove_all_filters( $tag, $priority=false )
  {
    if( isset($this->merged_filters[$tag]) )
    {
      unset($this->merged_filters[$tag]);
    }

    if( !isset($this->filters[$tag]) )
    {
      return true;
    }

    if( false !== $priority && isset($this->filters[$tag][$priority]) )
    {
      unset($this->filters[$tag][$priority]);
    } else {
      unset($this->filters[$tag]);
    }

    return true;
  }



  /**
   * Has Filter
   *
   * Check if any filter has been registered for the given hook.
   *
   * @param    string  $tag                The name of the filter hook.
   * @param    string  $function_to_check  (optional) Callback function name to check for.
   * @return   mixed                       If {@link $function_to_check} is omitted,
   *                                       returns boolean for whether the hook has
   *                                       anything registered.
   *                                       When checking a specific function, the priority
   *                                       of that hook is returned, or false if the
   *                                       function is not attached.
   *                                       When using the {@link $function_to_check} argument,
   *                                       this function may return a non-boolean value that
   *                                       evaluates to false
   *                                       (e.g.) 0, so use the === operator for testing the return value.
   * @access   public
   * @since    0.1
   */
  public function has_filter( $tag, $function_to_check=false )
  {
    $has = isset($this->filters[$tag]);
    if( false === $function_to_check || !$has )
    {
      return $has;
    }

    if( !($idx = $this->_filter_build_unique_id($tag, $function_to_check, false)) )
    {
      return false;
    }

    foreach( (array) array_keys($this->filters[$tag]) as $priority ) {
      if( isset($this->filters[$tag][$priority][$idx]) ) {
        return $priority;
      }
    }
    
    return false;
 }

  /**
   * Apply Filters
   *
   * Call the functions added to a filter hook.
   *
   * @param    string  $tag    The name of the filter hook.
   * @param    mixed   $value  The value on which the filters hooked to <tt>$tag</tt> are applied on.
   * @param    mixed   $var,.. Additional variables passed to the functions hooked to <tt>$tag</tt>.
   * @return   mixed           The filtered value after all hooked functions are applied to it.
   * @access   public
   * @since    0.1
   */
  public function apply_filters( $tag, $value, $debugOutput = false )
  {
    $args = array();

    // Do 'all' actions first
    if( isset($this->filters['all']) )
    {
      $this->current_filter[] = $tag;
      $args = func_get_args();
      $this->_call_all_hook($args);
    }

    if( !isset($this->filters[$tag]) )
    {
      if( isset($this->filters['all']) ) {
        array_pop($this->current_filter);
      }

      return $value;
    }

    if( !isset($this->filters['all']) )
    {
      $this->current_filter[] = $tag;
    }

    // Sort
    if( !isset($this->merged_filters[$tag]) )
    {
      ksort($this->filters[$tag]);
      $this->merged_filters[$tag] = true;
    }

    reset($this->filters[$tag]);

    if( empty($args) )
    {
      $args = func_get_args();
    }

    if ($debugOutput === true)
    {
      dump($this->filters[$tag], false);
    }
    
    do {
      foreach( (array) current($this->filters[$tag]) as $the_ ) {
        
        if( !is_null($the_['function']) )
        {
            // DEBUG
            if ($debugOutput === true)
            {
              echo 'call_user_func_array: before -> ' . $value . "\n<br>";
            }
          
            $args[1] = $value;
            $value   = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));

            // DEBUG
            if ($debugOutput === true)
            {
              echo 'call_user_func_array: after -> ' . $value . "\n<br>";
            }
        }
      }
    } while( next($this->filters[$tag]) !== false );

    array_pop( $this->current_filter );

    // DEBUG
    if ($debugOutput === true)
    {
      echo 'return: ' . $value . "\n<br>";
    }

    return $value;
  }

  /**
   * Apply Filters Ref Array
   *
   * Execute functions hooked on a specific filter hook, specifying arguments in an array.
   *
   * @param    string  $tag    The name of the filter hook.
   * @param    array   $args   The arguments supplied to the functions hooked to <tt>$tag</tt>
   * @return   mixed           The filtered value after all hooked functions are applied to it.
   * @access   public
   * @since    0.1
   */
  public function apply_filters_ref_array( $tag, $args, $debugOutput = false  )
  {
    // Do 'all' actions first
    if( isset($this->filters['all']) )
    {
      $this->current_filter[] = $tag;
      $all_args = func_get_args();
      $this->_call_all_hook($all_args);
    }

    if( !isset($this->filters[$tag]) )
    {
        if( isset($this->filters['all']) )
        {
          array_pop($this->current_filter);
        }

        return $args[0];
    }

    if( !isset($this->filters['all']) )
    {
      $this->current_filter[] = $tag;
    }

    // Sort
    if( !isset($this->merged_filters[$tag]) )
    {
        ksort($this->filters[$tag]);
        $this->merged_filters[$tag] = true;
    }

    reset( $this->filters[$tag] );

    if ($debugOutput === true)
    {
      dump($this->filters[$tag], false);
    }
    
    do {
      foreach( (array) current($this->filters[$tag]) as $the_ )
      {
        
        // DEBUG
        if ($debugOutput === true)
        {
          echo 'call_user_func_array: fefore -> ' . $args[0] . "\n<br>";
        }
        
        if( !is_null($the_['function']) )
        {
          $args[0] = call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
        }
        
        // DEBUG
        if ($debugOutput === true)
        {
          echo 'call_user_func_array: after -> ' . $args[0] . "\n<br>";
        }
        
      }
    } while( next($this->filters[$tag]) !== false );

    array_pop( $this->current_filter );

    // DEBUG
    if ($debugOutput === true)
    {
      echo 'return: ' . $args[0] . "\n<br>";
    }
    
    return $args[0];
  }

  /**
   * ACTIONS
   */

  /**
   * Add Action
   *
   * Hooks a function on to a specific action.
   *
   * @param    string  $tag             The name of the action to which the
   *                                    <tt>$function_to_add</tt> is hooked.
   * @param    string  $function_to_add The name of the function you wish to be called.
   * @param    integer $priority        (optional) Used to specify the order in which
   *                                    the functions associated with a particular
   *                                    action are executed (default: 10).
   *                                    Lower numbers correspond with earlier execution,
   *                                    and functions with the same priority are executed
   *                                    in the order in which they were added to the action.
   * @param    integer $accepted_args   (optional) The number of arguments the function accept (default 1).
   * @access   public
   * @since    0.1
   */
  public function add_action( $tag, $function_to_add, $priority=10, $accepted_args=1 )
  {
    return $this->add_filter($tag, $function_to_add, $priority, $accepted_args);
  }

  /**
   * Has Action
   *
   * Check if any action has been registered for a hook.
   *
   * @param    string  $tag                The name of the action hook.
   * @param    string  $function_to_check  (optional)
   * @return   mixed                       If <tt>$function_to_check</tt> is omitted,
   *                                       returns boolean for whether the hook has
   *                                       anything registered.
   *                                       When checking a specific function,
   *                                       the priority of that hook is returned,
   *                                       or false if the function is not attached.
   *                                       When using the <tt>$function_to_check</tt>
   *                                       argument, this function may return a non-boolean
   *                                       value that evaluates to false (e.g.) 0,
   *                                       so use the === operator for testing the return value.
   * @access   public
   * @since    0.1
   */
  public function has_action( $tag, $function_to_check=false )
  {
    return $this->has_filter($tag, $function_to_check);
  }

  /**
   * Remove Action
   *
   * Removes a function from a specified action hook.
   *
   * @param    string  $tag                The action hook to which the function to be removed is hooked.
   * @param    string  $function_to_remove The name of the function which should be removed.
   * @param    inter   $priority           (optional) The priority of the function (default: 10).
   * @return   boolean                     Whether the function is removed.
   * @access   public
   * @since    0.1
   */
  public function remove_action( $tag, $function_to_remove, $priority=10 )
  {
    return $this->remove_filter( $tag, $function_to_remove, $priority );
  }

  /**
   * Remove All Actions
   *
   * Remove all of the hooks from an action.
   *
   * @param    string  $tag        The action to remove hooks from.
   * @param    integer $priority   The priority number to remove them from.
   * @return   bool                True when finished.
   * @access   public
   * @since    0.1
   */
  public function remove_all_actions( $tag, $priority=false )
  {
    return $this->remove_all_filters($tag, $priority);
  }

  /**
   * Do Action
   *
   * Execute functions hooked on a specific action hook.
   *
   * @param    string  $tag    The name of the action to be executed.
   * @param    mixed   $arg,.. Optional additional arguments which are passed on
   *                           to the functions hooked to the action.
   * @return   null            Will return null if $tag does not exist in $filter array
   * @access   public
   * @since    0.1
   */
  public function do_action( $tag, $arg='' )
  {
    if( !is_array($this->actions) )
    {
      $this->actions = array();
    }

    if ( !isset($this->actions[$tag]) )
    {
      $this->actions[$tag] = 1;
    }
    else {
      ++$this->actions[$tag];
    }

    // Do 'all' actions first
    if( isset($this->filters['all']) )
    {
      $this->current_filter[] = $tag;
      $all_args = func_get_args();
      $this->_call_all_hook($all_args);
    }

    if( !isset($this->filters[$tag]) )
    {
        if( isset($this->filters['all']) )
        {
            array_pop($this->current_filter);
        }

        return;
    }

    if( !isset($this->filters['all']) )
    {
      $this->current_filter[] = $tag;
    }

    $args = array();

    if( is_array($arg) && 1 == count($arg) && isset($arg[0]) && is_object($arg[0]) )
    {
      $args[] =& $arg[0];
    } else {
      $args[] = $arg;
    }

    for ( $a = 2; $a < func_num_args(); $a++ )
    {
      $args[] = func_get_arg($a);
    }

    // Sort
    if( !isset($this->merged_filters[$tag]) )
    {
        ksort($this->filters[$tag]);
        $this->merged_filters[$tag] = true;
    }

    reset($this->filters[$tag]);

    do {
      foreach( (array) current($this->filters[$tag]) as $the_ )
      {
        if( !is_null($the_['function']) )
        {
          call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
        }
      }
    } while( next($this->filters[$tag]) !== false );

    array_pop($this->current_filter);
  }

  /**
   * Do Action Ref Array
   *
   * Execute functions hooked on a specific action hook, specifying arguments in an array.
   *
   * @param    string  $tag    The name of the action to be executed.
   * @param    array   $args   The arguments supplied to the functions hooked to <tt>$tag</tt>
   * @return   null            Will return null if $tag does not exist in $filter array
   * @access   public
   * @since    0.1
   */
  public function do_action_ref_array( $tag, $args )
  {
    if( !is_array($this->actions) )
    {
      $this->actions = array();
    }

    if( !isset($this->actions[$tag]) )
    {
       $this->actions[$tag] = 1;
    }
    else {
      ++$this->actions[$tag];
    }
         
    // Do 'all' actions first
    if( isset($this->filters['all']) )
    {
      $this->current_filter[] = $tag;
      $all_args = func_get_args();
      $this->_call_all_hook($all_args);
    }

    if( !isset($this->filters[$tag]) )
    {
      if( isset($this->filters['all']) )
      {
        array_pop($this->current_filter);
      }

      return;
    }

    if( !isset($this->filters['all']) )
    {
      $this->current_filter[] = $tag;
    }

    // Sort
    if( !isset($merged_filters[$tag] ) )
    {
        ksort($this->filters[$tag]);
        $merged_filters[$tag] = true;
    }

    reset($this->filters[$tag]);

    do {
      foreach( (array) current($this->filters[$tag]) as $the_ )
      {
        if( !is_null($the_['function']) )
        {
          call_user_func_array($the_['function'], array_slice($args, 0, (int) $the_['accepted_args']));
        }
      }
    } while( next($this->filters[$tag]) !== false );

     array_pop($this->current_filter);
  }

  /**
   * Did Action
   *
   * Retrieve the number of times an action has fired.
   *
   * @param    string  $tag    The name of the action hook.
   * @return   integer         The number of times action hook <tt>$tag</tt> is fired
   * @access   public
   * @since    0.1
   */
  public function did_action( $tag )
  {
    if( !is_array($this->actions) || !isset($this->actions[$tag]) ) {
      return 0;
    }

    return $this->actions[$tag];
  }

  /**
   * HELPERS
   */

  /**
   * Current Filter
   *
   * Retrieve the name of the current filter or action.
   *
   * @param    void
   * @return   string  Hook name of the current filter or action.
   * @access   public
   * @since    0.1
   */
  public function current_filter()
  {
    return end( $this->current_filter );
  }

  /**
   * Build Unique ID
   *
   * Build Unique ID for storage and retrieval.
   *
   * @param    string      $tag        Used in counting how many hooks were applied
   * @param    string      $function   Used for creating unique id
   * @param    int|bool    $priority   Used in counting how many hooks were applied.
   *                                   If === false and $function is an object reference,
   *                                   we return the unique id only if it already has one,
   *                                   false otherwise.
   * @return   string|bool             Unique ID for usage as array key or false if
   *                                   $priority === false and $function is an
   *                                   object reference, and it does not already have a unique id.
   * @access   private
   * @since    0.1
   */
  private function _filter_build_unique_id( $tag, $function, $priority )
  {
    static $filter_id_count = 0;

    if( is_string($function) )
    {
      return $function;
    }

    if( is_object($function) )
    {
      // Closures are currently implemented as objects
      $function = array( $function, '' );
    } else {
        $function = (array) $function;
    }

    if( is_object($function[0]) )
    {
      // Object Class Calling
      if( function_exists('spl_object_hash') )
      {
        return spl_object_hash($function[0]) . $function[1];
      }
      else {
        $obj_idx = get_class($function[0]) . $function[1];
        if( !isset($function[0]->filter_id) )
        {
          if( false === $priority )
          {
            return false;
          }

          $obj_idx .= isset($this->filters[$tag][$priority]) ? count((array)$this->filters[$tag][$priority]) : $filter_id_count;
          $function[0]->filter_id = $filter_id_count;
          ++$filter_id_count;
        }
        else {
          $obj_idx .= $function[0]->filter_id;
        }

        return $obj_idx;
      }
    }
    else if( is_string($function[0]) )
    {
      // Static Calling
      return $function[0] . $function[1];
    }
   }

  /**
   * Call "All" Hook
   *
   * @param    array   $args
   * @access   public
   * @since    0.1
   */
  public function __call_all_hook( $args )
  {
    reset($this->filters['all']);

    do {
      foreach( (array) current($this->filters['all']) as $the_ ) {
        if( !is_null($the_['function']) ) {
          call_user_func_array($the_['function'], $args);
        }
      }
    } while ( next($this->filters['all']) !== false );
  }

  /**
   * debug
   *
   * @param type $exit
   */
  public function debug($exit = false)
  {
    debugForClass($this, $exit);
  }
}//end class

}//end if

?>
