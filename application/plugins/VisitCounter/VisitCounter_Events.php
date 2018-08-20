<?php
defined('BASEPATH') or exit('No direct script access allowed');

class VisitCounter_Events
{
  private $limpid;

  public function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    // Check if plugin is enabled
    if ($this->limpid->pluginsManager->getPlugin('VisitCounter')['enabled']) {
      $this->limpid->load->library('VisitCounter_Manager', null, 'visitCounterManager');
      $this->limpid->emitter->on('limpid.initialization', [$this, 'logUser']);
    }
  }

  /**
   * Log a user entry
   *
   * @return void
   */
  public function logUser()
  {
    if (!$this->limpid->visitCounterManager->getEntry($_SERVER['REMOTE_ADDR']))
      $this->limpid->visitCounterManager->logUser($_SERVER['REMOTE_ADDR']);
    else
      $this->limpid->visitCounterManager->editEntry($_SERVER['REMOTE_ADDR'], ['last_visit' => date('Y-m-d H:i:s')]);
  }
}