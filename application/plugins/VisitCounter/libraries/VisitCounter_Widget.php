<?php
defined('BASEPATH') or exit('No direct script access allowed');

class VisitCounter_Widget
{
  private $limpid;

  public function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    // Check if plugin is enabled
    if ($this->limpid->pluginsManager->getPlugin('VisitCounter')->enabled) {
      $this->limpid->load->library('VisitCounter_Manager', null, 'visitCounterManager');
    } else {
      return false;
    }
  }

  /**
   * Get default widget data
   */
  public function defaultWidget()
  {
    $date = new DateTime();
    $date->modify("-5 minutes");
    $date5minBefore = $date->format('Y-m-d H:i:s');
    $date->modify("-24 hours");
    $date24hBefore = $date->format('Y-m-d H:i:s');

    $data = [
      'nb_total'    => $this->limpid->visitCounterManager->countAllEntries(),
      'nb_since_5m' => $this->limpid->visitCounterManager->countAllEntriesForDate($date5minBefore),
      'nb_since_24h' => $this->limpid->visitCounterManager->countAllEntriesForDate($date24hBefore),
    ];

    return $data;
  }
}