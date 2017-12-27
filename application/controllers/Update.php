<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Update CMS class
 *
 * @property CI_Lang $lang
 */
class Update extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    if (!$this->authManager->isPermitted($this->session->userdata('id'), 'UPDATE__ACCESS')) {
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(site_url());
      exit();
    }
  }

  public function admin_update()
  {
    $this->data['page_title'] = 'Tableau de bord';
    $this->twig->display('update/dashboard', $this->data);
  }
}
