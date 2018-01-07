<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Groups manager
 *
 * @property CMS_Controller limpid
 */
class Groups_Manager
{
  /**
   * Auth_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    $this->limpid->load->model('Groups_model', 'groups');
  }

  /**
   * Create a group
   *
   * @param string $name
   * @param string $color
   * @param array $permissions
   *
   * @return bool|null
   */
  function createGroup($name, $color, $permissions)
  {
    // Simple check
    if (empty($name) || empty($color)) {
      return null;
    }

    $data = [
      'name' => $name,
      'color' => $color,
      'permissions' => is_array($permissions) ? implode(', ', $permissions) : ''
    ];

    if ($group = $this->limpid->groups->insert($data))
      return $group;

    return null;
  }

  /**
   * Update group's data
   *
   * @param int $group_id
   * @param array $data
   *
   * @return bool|null
   */
  function editGroup($group_id, $data = [])
  {
    // Simple check
    if (empty($group_id) || empty($data)) {
      return null;
    }

    if ($group = $this->limpid->groups->update($data, $group_id))
      return $group;

    return null;
  }

  /**
   * Delete group
   *
   * @param int $id
   *
   * @return bool|null
   */
  function deleteGroup($id)
  {
    // Simple check
    if (empty($id)) {
      return null;
    }

    // Check if it's the admin group
    if ($id == 1) {
      return false;
    }

    if ($group = $this->limpid->groups->delete($id))
      return $group;

    return null;
  }

  /**
   * Get group by id
   *
   * @param int $id
   *
   * @return object|null
   */
  function getGroup($id)
  {
    // Simple check
    if (empty($id)) {
      return null;
    }

    if ($group = $this->limpid->groups->get($id))
      return $group;

    return null;
  }

  /**
   * Get groups
   *
   * @return array|null
   */
  function getGroups()
  {
    if ($user = $this->limpid->groups->order_by('name')->get_all())
      return $user;

    return null;
  }

  /**
   * Get default group
   *
   * @return object|null
   */
  function getDefaultGroup()
  {
    if ($group = $this->limpid->groups->get(['default_group' => true]))
      return $group;

    return null;
  }

}

/* End of file Groups_Manager.php */
/* Location: ./application/libraries/Groups_Manager.php */