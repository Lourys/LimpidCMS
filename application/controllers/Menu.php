<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Menu_Manager $menuManager
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Lang $lang
 */
class Menu extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    if (!$this->authManager->isPermitted($this->session->userdata('id'), 'MENU__MANAGE')) {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(route('admin/admin_index'));
      exit();
    }
    $this->load->library('Menu_Manager', null, 'menuManager');
  }

  public function admin_manage()
  {
    require(APPPATH . 'third_party/Twig_Extensions/Text_Extension.php');
    $this->twig->getTwig()->addExtension(new Text_Extension());

    $this->load->helper('form');
    $this->load->library('form_validation');
    $this->data['page_title'] = $this->lang->line('MENU_MANAGEMENT');

    $this->data['links'] = $this->menuManager->getLinks();
    $this->data['sublinks'] = $this->menuManager->getSublinks();

    // If delete link request
    if ($this->input->post('deleteLink') !== null || $this->input->post('editLink') !== null) {
      $this->form_validation->set_rules('id', 'ID', 'required|numeric|integer');
    }
    // If create or edit link request
    if ($this->input->post('createLink') !== null || $this->input->post('editLink') !== null) {
      $this->form_validation->set_rules('title', $this->lang->line('LINK_TITLE'), 'required|max_length[60]');
      if (!$this->input->post('dropdown')) {
        $this->form_validation->set_rules('url', $this->lang->line('LINK_ADDRESS'), 'required|max_length[255]');
      }
      $this->form_validation->set_rules('dropdown', $this->lang->line('DROPDOWN_MENU') . ' ?', 'in_list[true,]');
    }
    // If create sublink request
    if ($this->input->post('createSublink') !== null) {
      if (!$this->input->post('divider')) {
        $this->form_validation->set_rules('title', $this->lang->line('LINK_TITLE'), 'required|max_length[60]');
        $this->form_validation->set_rules('url', $this->lang->line('LINK_ADDRESS'), 'required|max_length[255]');
      }
      $this->form_validation->set_rules('parent', $this->lang->line('PARENT_LINK'), 'numeric|integer');
      $this->form_validation->set_rules('divider', $this->lang->line('LINK_DIVIDER') . ' ?', 'in_list[true,]');
    }

    if ($this->form_validation->run()) {
      // If create link request
      if ($this->input->post('createLink') !== null) {
        if ($this->input->post('dropdown')) {
          if ($this->menuManager->addDropdownLink($this->input->post('title')))
            // If dropdown link adding succeed
            $this->session->set_flashdata('success', $this->lang->line('LINK_SUCCESSFULLY_CREATED'));
          else
            // If dropdown link adding failed
            $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
        } else {
          if ($this->menuManager->addNormalLink($this->input->post('title'), $this->input->post('url')))
            // If "normal" link adding succeed
            $this->session->set_flashdata('success', $this->lang->line('LINK_SUCCESSFULLY_CREATED'));
          else
            // If "normal" link adding failed
            $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
        }
      }
      // If create sublink request
      if ($this->input->post('createSublink') !== null) {
        if ($this->input->post('divider')) {
          if ($this->menuManager->addSublinkDivider($this->input->post('parent')))
            // If divider sublink adding succeed
            $this->session->set_flashdata('success', $this->lang->line('SUBLINK_SUCCESSFULLY_CREATED'));
          else
            // If divider sublink adding failed
            $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
        } else {
          if ($this->menuManager->addSublink($this->input->post('title'), $this->input->post('url'), $this->input->post('parent')))
            // If sublink adding succeed
            $this->session->set_flashdata('success', $this->lang->line('SUBLINK_SUCCESSFULLY_CREATED'));
          else
            // If sublink adding failed
            $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
        }
      }
      // If edit link request
      if ($this->input->post('editLink') !== null) {
        $data = array(
          'title' => $this->input->post('title')
        );
        if (!$this->input->post('dropdown'))
          $data['url'] = $this->input->post('url');

        if ($this->menuManager->editLink($this->input->post('id'), $data))
          // If link editing succeed
          $this->session->set_flashdata('success', $this->lang->line('LINK_SUCCESSFULLY_EDITED'));
        else
          // If link editing failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
      }
      // If delete link request
      if ($this->input->post('deleteLink') !== null) {
        if ($this->menuManager->deleteLink($this->input->post('id')))
          // If link deleting succeed
          $this->session->set_flashdata('success', $this->lang->line('LINK_SUCCESSFULLY_EDITED'));
        else
          // If link deleting failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
      }

      redirect(route('menu/admin_manage'));
    } else {
      // Render the view
      $this->twig->display('admin/menu/manage', $this->data);
    }
  }

  /**
   * Ajax called method to order links in the menu
   */
  public function ajax_edit_links_position()
  {
    $data = json_decode($_POST['position']);
    for ($i = 0; $i < count($data); $i++)
      $this->menuManager->editLinksPosition($data[$i], $i);
  }
}