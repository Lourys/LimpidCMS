<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Example_model extends CMS_Model
{
    protected $table = 'web_stats';
    protected $primaryKey = 'ip_address';
}