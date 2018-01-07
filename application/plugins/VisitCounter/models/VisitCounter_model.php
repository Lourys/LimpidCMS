<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class VisitCounter_model extends CMS_Model
{
  public $table = 'web_stats';
  public $primary_key = 'ip_address';

  public function __construct()
  {
    parent::__construct();
  }
}