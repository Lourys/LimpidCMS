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
    $this->limpid->load->model('VisitCounter_model', 'visitCounter');
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

    return $this->limpid->visitCounter->find($ip_address);
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

    if ($entry = (array)$this->limpid->visitCounter->find($ip_address)) {
      $data = array_merge($entry, $data);
      return $this->limpid->visitCounter->update(['ip_address' => $ip_address], $data);
    }

    return null;
  }
}