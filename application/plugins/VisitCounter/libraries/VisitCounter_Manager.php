<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * VisitCounter manager
 *
 */
class VisitCounter_Manager
{
  private $limpid;

  /**
   * VisitCounter_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    // Check if plugin is enabled
    if ($this->limpid->pluginsManager->getPlugin('VisitCounter')['enabled']) {
      $this->limpid->load->model('VisitCounter_model', 'visitCounter');
    } else {
      return false;
    }
  }

  /**
   * Register an entry
   *
   * @param string $ip_address
   *
   * @return bool|int|null
   */
  public function logUser($ip_address)
  {
    // Simple check
    if (empty($ip_address)) {
      return null;
    }

    return $this->limpid->visitCounter->insert(['ip_address' => $ip_address, 'first_visit' => date('Y-m-d H:i:s')]);
  }


  /**
   * Get entry by ip_address
   *
   * @param string $ip_address
   *
   * @return array|null|object
   */
  function getEntry($ip_address)
  {
    // Simple check
    if (empty($ip_address)) {
      return null;
    }

    return $this->limpid->visitCounter->get($ip_address);
  }


  /**
   * Count all entries
   *
   * @return int
   */
  public function countAllEntries()
  {
    return $this->limpid->visitCounter->count_rows();
  }


  /**
   * Count all entries for a date
   *
   * @param string $date
   *
   * @return int
   */
  public function countAllEntriesForDate($date)
  {
    // Simple check
    if (empty($date)) {
      return null;
    }

    return $this->limpid->visitCounter->where('last_visit', '>=', $date)->count_rows();
  }


  /**
   * Edit an entry
   *
   * @param string $ip_address
   * @param array $data
   *
   * @return null|bool
   */
  function editEntry($ip_address, $data = [])
  {
    // Simple check
    if (empty($ip_address) || empty($data)) {
      return null;
    }

    return $this->limpid->visitCounter->where($ip_address)->update($data);
  }
}