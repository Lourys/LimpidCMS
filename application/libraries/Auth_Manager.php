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
   * @param string $permission
   * @param int $user_id
   *
   * @return bool|null Returns null if user is not found
   */
  function isPermitted($permission, $user_id = null)
  {
    // Simple checks
    if (empty($permission)) {
      return true;
    }
    if (empty($user_id)) {
      $user_id = $this->limpid->session->userdata('id');
    }

    if ($user_id && $row = $this->limpid->auth->with_group('fields:id, permissions')->fields(null)->get($user_id)) {
      // Check if is admin group
      if ($row['group']['id'] == 1)
        return true;

      return in_array($permission, explode(', ', $row['group']['permissions'])) ? true : false;
    }

    // User not found
    return null;
  }


  /**
   * Check access authorisation to content by given permission
   *
   * @param string $permission
   * @param int $user_id
   *
   * @return void
   */
  public function checkAccess($permission, $user_id = null)
  {
    // If user doesn't have required permission
    if (!$authorized = $this->isPermitted($permission, $user_id)) {
      $this->limpid->load->library('user_agent');

      $this->limpid->session->set_flashdata('error', $this->limpid->lang->line('PERMISSION_ERROR'));
      redirect($this->limpid->agent->referrer() ? $this->limpid->agent->referrer() : site_url());

      //show_error($this->limpid->lang->line('PERMISSION_ERROR'), $authorized === false ? 403 : 401, $this->limpid->lang->line('ERROR_ENCOUNTERED'));
    }
  }

  /**
   * Logout current user
   *
   * @return void
   */
  public function logout()
  {
    $this->limpid->session->unset_userdata(array_keys($this->limpid->session->userdata()));
  }
}

/* End of file Auth_Manager.php */
/* Location: ./application/libraries/Auth_Manager.php */