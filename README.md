modularphp
==========

Split your application logic in AMD (Asynchronous Module Definition), like RequireJS or AngularJS.
This library is experimental and is not already fully tested. USE AT YOUR OWN RISK!

Usage
-----

<code>
use Modular/Module;

Module::create('moduleA', array('moduleB'), function ($app) {
  // your application logic here
});

Module::create('moduleB', function ($app) {
  // you can change the $app var here too and the changes applies to moduleA
});

// select the initial module
Module::bootstrap('moduleA', new \Application()); // your application object
</code>

License
=======

This code is under MIT license.
