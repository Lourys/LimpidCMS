<?php
/**
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 */

class Shop extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->library('Shop_Manager', null, 'shopManager');
  }

  public function admin_index()
  {
    $this->authManager->checkAccess('SHOP__OVERVIEW');

    $this->data['page_title'] = $this->lang->line('SHOP');


    $this->twig->display('admin/index', $this->data);
  }

  public function admin_settings()
  {
    $this->authManager->checkAccess('SHOP__SETTINGS');

    $this->data['page_title'] = $this->lang->line('SHOP_SETTINGS');
    $this->data['currencies'] = [
      ['name' => '€', 'value' => '€'],
      ['name' => '$', 'value' => '$'],
      ['name' => '£', 'value' => '£']
    ];
    $this->load->helper('form');
    $this->load->library('form_validation');

    $this->twig->display('admin/settings', $this->data);
  }
}