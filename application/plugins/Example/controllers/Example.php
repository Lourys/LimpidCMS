<?php

class Example extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {

  }

  public function test()
  {
    var_dump($this);
  }
}