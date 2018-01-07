<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu_model extends CMS_Model
{
  public $table = 'menu';
  public $primary_key = 'id';

  public function __construct()
  {
    parent::__construct();
  }
}