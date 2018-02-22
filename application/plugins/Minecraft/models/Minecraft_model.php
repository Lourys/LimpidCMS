<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Minecraft_model extends CMS_Model
{
  public $table = 'servers';
  public $primary_key = 'id';

  public function __construct()
  {
    parent::__construct();
  }
}