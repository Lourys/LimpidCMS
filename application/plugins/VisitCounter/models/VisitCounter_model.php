<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class VisitCounter_model extends CMS_Model
{
    protected $table = 'web_stats';
    protected $primaryKey = 'ip_address';
}