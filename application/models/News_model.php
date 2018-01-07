<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class News_model extends CMS_Model
{
  public $table = 'news';
  public $primary_key = 'id';

  public function __construct()
  {
    $this->has_one['author'] = ['Users_model', 'id', 'author_id'];


    parent::__construct();
  }
}