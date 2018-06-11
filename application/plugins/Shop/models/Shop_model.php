<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop_model extends CMS_Model
{
  public $table = 'shop';
  public $primary_key = 'id';

  public function __construct()
  {
    parent::__construct();
  }
}