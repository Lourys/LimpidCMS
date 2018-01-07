<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Groups_model extends CMS_Model
{
  public $table = 'groups';
  public $primary_key = 'id';

  public function __construct()
  {
    parent::__construct();
  }
}