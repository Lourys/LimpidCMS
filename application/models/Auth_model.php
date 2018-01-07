<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CMS_Model
{
  public $table = 'users';
  public $primary_key = 'id';

  public function __construct()
  {
    $this->has_one['group'] = ['Groups_model', 'id', 'group_id'];

    parent::__construct();
  }
}