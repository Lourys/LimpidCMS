<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Users manager
 *
 */
class Users_Manager
{
  /**
   * Auth_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    $this->limpid->load->model('Users_model', 'users');
  }

  /**
   * Register user
   *
   * @param string $username
   * @param string $email
   * @param string $password
   * @param int $group_id
   * @param string|null $ip_address
   *
   * @return bool|null
   */
  function registerUser($username, $email, $password, $group_id, $ip_address = null)
  {
    // Simple check
    if (empty($username) || empty($email) || empty($password) || empty($group_id)) {
      return null;
    }

    // Set data structure
    $data = array(
      'username' => $username,
      'email' => $email,
      'password' => $password,
      'group_id' => $group_id,
      'ip_address' => $ip_address
    );

    if ($user = $this->limpid->users->insert($data))
      return $user;

    return false;
  }

  /**
   * Update user's data
   *
   * @param int $user_id
   * @param array $data
   *
   * @return bool|null
   */
  function editUser($user_id, $data = [])
  {
    // Simple check
    if (empty($user_id) || empty($data)) {
      return null;
    }

    // If it's the admin user
    if (array_key_exists('group_id', $data) && $user_id == 1)
      return false;

    if ($user = (array)$this->limpid->users->find($user_id)) {
      $data = array_merge($user, $data);
      if ($user = $this->limpid->users->update($user_id, $data))
        return $user;
    }

    return null;
  }

  /**
   * Update users data with condition
   *
   * @param array $conditions
   * @param array $data
   *
   * @return bool|null
   */
  function editUsersWhere($conditions = [], $data = [])
  {
    // Simple check
    if (empty($conditions) || empty($data)) {
      return null;
    }

    return $this->limpid->users->update($conditions, $data);
  }

  /**
   * Delete user
   *
   * @param int $id
   *
   * @return bool|null
   */
  function deleteUser($id)
  {
    // Simple check
    if (empty($id)) {
      return null;
    }

    // Check if it's the admin user
    if ($id == 1) {
      return false;
    }

    if ($user = $this->limpid->users->delete($id))
      return $user;

    return null;
  }

  /**
   * Get user by id
   *
   * @param int $id
   *
   * @return object|null
   */
  function getUser($id)
  {
    // Simple check
    if (empty($id)) {
      return null;
    }

    if ($user = $this->limpid->users->find($id))
      return $user;

    return null;
  }

  /**
   * Get users
   *
   * @return array|null
   */
  function getUsers()
  {
    if ($user = $this->limpid->users->rawQuery('SELECT u.*, g.name AS group_name, g.color AS group_color FROM users u INNER JOIN groups g ON u.group_id = g.id'))
      return $user;

    return null;
  }

}

/* End of file Users_Manager.php */
/* Location: ./application/libraries/Users_Manager.php */