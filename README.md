[![Build Status](https://travis-ci.org/voku/php-hooks.svg?branch=master)](https://travis-ci.org/voku/php-hooks)
[![Coverage Status](https://coveralls.io/repos/github/voku/php-hooks/badge.svg?branch=master)](https://coveralls.io/github/voku/php-hooks?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/voku/php-hooks/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/voku/php-hooks/?branch=master)
[![Codacy Badge](https://www.codacy.com/project/badge/6f6b0c6c9f4e4bc8ac0c9159fd86adb2)](https://www.codacy.com/app/voku/php-hooks)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/8ab3148c-61b5-4da6-be80-9018eb0b4441/mini.png)](https://insight.sensiolabs.com/projects/8ab3148c-61b5-4da6-be80-9018eb0b4441)
[![Latest Stable Version](https://poser.pugx.org/voku/php-hooks/v/stable)](https://packagist.org/packages/voku/php-hooks) 
[![Total Downloads](https://poser.pugx.org/voku/php-hooks/downloads)](https://packagist.org/packages/voku/php-hooks) 
[![Latest Unstable Version](https://poser.pugx.org/voku/php-hooks/v/unstable)](https://packagist.org/packages/voku/php-hooks)
[![PHP 7 ready](http://php7ready.timesplinter.ch/voku/php-hooks/badge.svg)](https://travis-ci.org/voku/php-hooks)
[![License](https://poser.pugx.org/voku/php-hooks/license)](https://packagist.org/packages/voku/php-hooks)

PHP-Hooks
=========

The PHP Hooks Class is a fork of the WordPress filters hook system rolled in to a class to be ported into any php based system  
*  This class is heavily based on the WordPress plugin API and most (if not all) of the code comes from there.

How to install?
=====

```shell
composer require voku/php-hooks
```

How to use?
=====

We start with a simple example ...

```php
<?php

$hooks = Hooks::getInstance();

$hooks->add_action('header_action','echo_this_in_header');

function echo_this_in_header(){
   echo 'this came from a hooked function';
}
```    

then all that is left for you is to call the hooked function when you want anywhere in your application, EX:

```php
<?php

$hooks = Hooks::getInstance();

echo '<div id="extra_header">';
$hooks->do_action('header_action');
echo '</div>';
```

and you output will be: `<div id="extra_header">this came from a hooked function</div>`

PS: you can also use method from a class for a hook e.g.: `$hooks->add_action('header_action', array($this, 'echo_this_in_header_via_method');`

Methods
=======
**ACTIONS:**

**add_action** Hooks a function on to a specific action.

     - @access public
     - @since 0.1
     - @param string $tag The name of the action to which the $function_to_add is hooked.
     - @param callback $function_to_add The name of the function you wish to be called.
     - @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
     - @param int $accepted_args optional. The number of arguments the function accept (default 1).

**do_action** Execute functions hooked on a specific action hook.

     - @access public
     - @since 0.1
     - @param string $tag The name of the action to be executed.
     - @param mixed $arg,... Optional additional arguments which are passed on to the functions hooked to the action.
     - @return null Will return null if $tag does not exist

**remove_action** Removes a function from a specified action hook.

     - @access public
     - @since 0.1
     - @param string $tag The action hook to which the function to be removed is hooked.
     - @param callback $function_to_remove The name of the function which should be removed.
     - @param int $priority optional The priority of the function (default: 10).
     - @return boolean Whether the function is removed.

**has_action** Check if any action has been registered for a hook.

     -  @access public
     -  @since 0.1
     -  @param string $tag The name of the action hook.
     -  @param callback $function_to_check optional.
     -  @return mixed If $function_to_check is omitted, returns boolean for whether the hook has anything registered.
      When checking a specific function, the priority of that hook is returned, or false if the function is not attached.
      When using the $function_to_check argument, this function may return a non-boolean value that evaluates to false (e.g.) 0, so use the === operator for testing the return value.


**did_action**  Retrieve the number of times an action is fired.

     - @access public
     - @since 0.1
     - @param string $tag The name of the action hook.
     - @return int The number of times action hook <tt>$tag</tt> is fired

**FILTERS:**

**add_filter** Hooks a function or method to a specific filter action.

     - @access public
     - @since 0.1
     - @param string $tag The name of the filter to hook the $function_to_add to.
     - @param callback $function_to_add The name of the function to be called when the filter is applied.
     - @param int $priority optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
     - @param int $accepted_args optional. The number of arguments the function accept (default 1).
     - @return boolean true

**remove_filter** Removes a function from a specified filter hook.

     - @access public
     - @since 0.1
     - @param string $tag The filter hook to which the function to be removed is hooked.
     - @param callback $function_to_remove The name of the function which should be removed.
     - @param int $priority optional. The priority of the function (default: 10).
     - @param int $accepted_args optional. The number of arguments the function accepts (default: 1).
     - @return boolean Whether the function existed before it was removed.


**has_filter** Check if any filter has been registered for a hook.

     - @access public
     - @since 0.1
     - @param string $tag The name of the filter hook.
     - @param callback $function_to_check optional.
     - @return mixed If $function_to_check is omitted, returns boolean for whether the hook has anything registered.
       When checking a specific function, the priority of that hook is  returned, or false if the function is not attached.
       When using the $function_to_check argument, this function may return a non-boolean value that evaluates to false (e.g.) 0, so use the === operator for testing the return value.

**apply_filters** Call the functions added to a filter hook.

     - @access public
     - @since 0.1
     - @param string $tag The name of the filter hook.
     - @param mixed $value The value on which the filters hooked to <tt>$tag</tt> are applied on.
     - @param mixed $var,... Additional variables passed to the functions hooked to <tt>$tag</tt>.
     - @return mixed The filtered value after all hooked functions are applied to it.

License
=======

Since this class is derived from the WordPress Plugin API so are the license and they are GPL http://www.gnu.org/licenses/gpl.html

  [1]: https://github.com/bainternet/PHP-Hooks/zipball/master
  [2]: https://github.com/bainternet/PHP-Hooks/tarball/master
  [3]: http://bainternet.github.com/PHP-Hooks/
