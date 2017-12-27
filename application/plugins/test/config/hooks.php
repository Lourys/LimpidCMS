<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['test'] = array(
    'class' => 'Test',
    'function' => 'hello',
    'filename' => 'Test.php',
    'filepath' => 'plugins/test/controllers',
    'params' => array()
);

