<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin CMS class
 *
 * @property CI_Lang $lang
 */
class Admin extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    if (!$authorized = $this->authManager->isPermitted($this->session->userdata('id'), 'ADMIN__ACCESS')) {
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(site_url());
      exit();
    }
  }

  public function admin_index()
  {
    $this->data['page_title'] = 'Tableau de bord';
    $this->twig->display('admin/dashboard', $this->data);
  }
}
