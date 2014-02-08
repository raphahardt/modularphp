<?php

namespace Modular;

class Module
{
  /** @var self[] */
  static protected $modules = array();

  /** @var string[] */
  public $dependencies = array();

  /**
   * Constructor of your defined module
   *
   * @var \Closure
   */
  public $constructor = null;

  /**
   * Indicates if module is already booted
   * @var bool
   */
  public $started = false;

  /**
   * Filename where module is defined
   * @var string
   */
  public $file = '';

  /**
   * Returns TRUE if argument is a closure or a invokable object
   * @param mixed $callable
   * @return bool
   */
  private static function isClosure($callable)
  {
    return (is_object($callable) && method_exists($callable, '__invoke'));
  }

  /**
   * Defines a new module.
   *
   *
   *
   * @param string $name Nome of the module.
   * @param string[] $dependencies Names of modules that module depends on
   * @param callable $constructor
   * @param string $file
   * @return self
   * @throws \InvalidArgumentException If $constructor is not a callable, this exception is thrown
   */
  final public static function create($name, $dependencies = array(), $constructor = null, $file = null)
  {

    // shifting arguments in case of omission of $dependencies
    if (self::isClosure($dependencies)) {
      $file = $constructor;
      $constructor = $dependencies;
      $dependencies = array();
    }

    // asserts
    if (!self::isClosure($constructor)) {
      throw new \InvalidArgumentException('Module constructor needs to be a Closure or invokable object.');
    }

    // getting the file that create() was called to "autoload" him after in bootstrap()
    // if you don't pass __FILE__ in $file argument, it will be backtraced to get the filename
    // if you really concern about performance, you need to pass __FILE__ in every module definition
    if (is_null($file)) {
      $trace = debug_backtrace(0);
      $trace = $trace[0];
      $file = $trace['file'];
    }

    /** @var self $module */
    $module = new static();
    $module->dependencies = $dependencies;
    $module->constructor = $constructor;
    $module->file = $file;
    static::$modules[$name] = $module;
    return $module;
  }

  final public static function bootstrap($name, $application = null)
  {
    // lazy load the module
    static $basedir;
    if (!$basedir) {
      $trace = debug_backtrace(0);
      $trace = $trace[0];
      $basedir = dirname($trace['file']);
    }
    include_once $basedir . '/' . $name . '.php';

    // extract the basename of module
    $parts = explode('/', $name);
    $name = end($parts);

    /** @var self $module */
    $module = static::$modules[$name];
    if ($module->started) return $module; // already loaded, break possible infinite loops of dependency

    $basedir = dirname($module->file);

    $constructor = $module->constructor;
    $dependencies = $module->dependencies;
    $module->started = true;

    foreach ($dependencies as $dependency) {
      // bootstrap the module dependencies before the main boostraped module
      static::bootstrap($dependency, $application);
    }

    // executes the module constructor, passing your application object
    return $constructor($application);
  }

}