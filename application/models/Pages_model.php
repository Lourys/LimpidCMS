<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pages_model extends CMS_Model
{
  public $table = 'pages';
  public $primary_key = 'id';

  public function __construct()
  {
    parent::__construct();
  }
}