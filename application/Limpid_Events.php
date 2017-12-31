<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property Users_Manager $usersManager
 */
class Limpid_Events
{
  private $limpid;

  public function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;

    if ($this->limpid->config->item('gravatarEnabled') == true) {
      $this->limpid->emitter->on('auth.login', [$this, 'setGravatarPicture']);
    }
  }

  /**
   * Set Gravatar picture to an user if he uses it
   *
   * @param $user_id int
   * @return void
   */
  public function setGravatarPicture($user_id)
  {
    $this->limpid->load->library('Users_Manager', null, 'usersManager');
    $user = $this->limpid->usersManager->getUser($user_id);
    $fileName = uniqid();
    if ($user->avatar == null) {
      $hasGravatar = strpos(@get_headers('https://www.gravatar.com/avatar/' . md5($user->email) . '?d=404')[0], '200') === false ? false : true;
      if ($hasGravatar) {
        if (@file_put_contents('./uploads/avatars/' . $fileName . '.jpg', @file_get_contents('https://www.gravatar.com/avatar/' . md5($user->email) . '?s=' . $this->limpid->config->item('avatar')['max_width']))) {
          $this->limpid->usersManager->editUser($user->id, ['avatar' => $fileName . '.jpg']);
        } else {
          echo'aïe!';
          die();
        }
      }
    }
  }

}