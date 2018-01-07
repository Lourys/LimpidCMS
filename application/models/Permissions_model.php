<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Permissions_model extends CMS_Model
{
  public $table = 'permissions';
  public $primary_key = 'id';

  public function __construct()
  {
    parent::__construct();
  }
}