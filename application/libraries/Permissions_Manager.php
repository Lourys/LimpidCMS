<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Permissions manager
 *
 * @property CMS_Controller limpid
 */
class Permissions_Manager
{
  /**
   * Auth_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    $this->limpid->load->model('Permissions_model', 'permissions');
  }

  /**
   * Get permissions by name
   *
   * @param string $name
   *
   * @return object|null
   */
  function getPermission($name)
  {
    // Simple check
    if (empty($name)) {
      return null;
    }

    if ($permission = $this->limpid->permissions->get(['name' => $name]))
      return $permission;

    return null;
  }

  /**
   * Get permissions
   *
   * @return array|null
   */
  function getPermissions()
  {
    if ($permissions = $this->limpid->permissions->order_by('name')->get_all())
      return $permissions;

    return null;
  }

  /**
   * Register a new permission
   *
   * @param string $name
   *
   * @return bool|int|null
   */
  function registerPermission($name)
  {
    // Simple check
    if (empty($name)) {
      return null;
    }

    return $this->limpid->permissions->insert(['name' => $name]);
  }

  /**
   * Deregister a permission by name
   *
   * @param string $name
   *
   * @return bool|null
   */
  function deregisterPermission($name)
  {
    // Simple check
    if (empty($name)) {
      return null;
    }

    return $this->limpid->permissions->delete(['name' => $name]);
  }

}

/* End of file Permissions_Manager.php */
/* Location: ./application/libraries/Permissions_Manager.php */