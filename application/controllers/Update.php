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
    $this->authManager->checkAccess('UPDATE__ACCESS');
  }

  public function admin_update()
  {
    $this->data['page_title'] = 'Tableau de bord';
    $this->twig->display('update/dashboard', $this->data);
  }
}
