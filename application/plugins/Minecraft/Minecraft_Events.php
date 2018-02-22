<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Minecraft_Events
{
  private $limpid;

  public function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    // Check if plugin is enabled
    if ($this->limpid->pluginsManager->getPlugin('Minecraft')->enabled) {

    }
  }
}