<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Shop_Events
{
  private $limpid;

  public function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    // Check if plugin is enabled
    if ($this->limpid->pluginsManager->getPlugin('Shop')['enabled']) {
      $this->limpid->lang->load('shop');
    }
  }
}