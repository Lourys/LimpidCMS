<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Authentication manager
 *
 * @property CMS_Controller limpid
 */
class Auth_Manager
{
  /**
   * Auth_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    $this->limpid->load->model('Auth_model', 'auth');
  }

  /**
   * Log user by username
   *
   * @param string $username
   * @param string $password
   *
   * @return object|null
   */
  function loginByUsername($username, $password)
  {
    // Simple check
    if (empty($username) || empty($password)) {
      return null;
    }

    if ($user = $this->limpid->auth->fields('id, username, password')->get(['username' => $username])) {
      if (password_verify($password, $user['password'])) {
        $this->limpid->session->set_userdata('id', $user['id']);

        return $user;
      }
    }

    return null;
  }

  /**
   * Log user by username
   *
   * @param string $email
   * @param string $password
   *
   * @return object|null
   */
  function loginByEmail($email, $password)
  {
    // Simple check
    if (empty($email) || empty($password)) {
      return null;
    }

    if ($user = $this->limpid->auth->fields('username, password')->get(['email' => $email])) {
      if (password_verify($password, $user['password'])) {
        $this->limpid->session->set_userdata('id', $user['id']);

        return $user;
      }
    }

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

    if ($user = $this->limpid->auth->get($id))
      return $user;

    return null;
  }

  /**
   * Check if user is logged
   *
   * @return bool
   */
  function isLogged()
  {
    return !is_null($this->limpid->session->userdata('id')) ? true : false;
  }

  /**
   * Check if user has permission
   *
   * @param int $user_id
   * @param string $permission
   *
   * @return bool|null
   */
  function isPermitted($user_id, $permission)
  {
    // Simple check
    if (empty($user_id) || empty($permission)) {
      return null;
    }

    if ($row = $this->limpid->auth->with_group('fields:id, permissions')->fields(null)->get($user_id)) {
      // Check if is admin group
      if ($row['group']['id'] == 1)
        return true;

      return in_array($permission, explode(', ', $row['groups']['permissions'])) ? true : false;
    }

    //
    // Not logged
    return null;
  }

  /**
   * Logout current user
   */
  public function logout()
  {
    $this->limpid->session->unset_userdata(array_keys($this->limpid->session->userdata()));
  }
}

/* End of file Auth_Manager.php */
/* Location: ./application/libraries/Auth_Manager.php */