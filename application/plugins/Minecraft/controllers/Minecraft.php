<?php
/**
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 */

class Minecraft extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function admin_index()
  {
    $this->authManager->checkAccess('MINECRAFT__OVERVIEW');

    $this->data['page_title'] = 'Minecraft';
    $this->twig->display('admin/index', $this->data);
  }

  public function admin_settings()
  {
    $this->authManager->checkAccess('MINECRAFT__SETTINGS');

    $this->load->library('Minecraft_Manager', null, 'minecraftManager');
    $this->data['page_title'] = 'Paramètres Minecraft';
    $this->data['servers'] = $this->minecraftManager->getServers();

    $this->load->helper('form');
    $this->load->library('form_validation');

    // If delete server request
    if ($this->input->post('deleteServer') !== null || $this->input->post('editServer') !== null) {
      $this->form_validation->set_rules('id', 'ID', 'required|numeric|integer');
    }
    // If create or edit server request
    if ($this->input->post('createServer') !== null || $this->input->post('editServer') !== null) {
      $this->form_validation->set_rules('name', $this->lang->line('MINECRAFT_SERVER_NAME'), 'required|max_length[20]');
      $this->form_validation->set_rules('ip_address', $this->lang->line('MINECRAFT_SERVER_IP_ADDRESS'), 'required|max_length[15]');
      $this->form_validation->set_rules('port', $this->lang->line('MINECRAFT_SERVER_PORT'), 'required|integer|less_than[65535]');
      $this->form_validation->set_rules('rcon_port', $this->lang->line('MINECRAFT_RCON_PORT'), 'required|integer|less_than[65535]');
      $this->form_validation->set_rules('rcon_pass', $this->lang->line('MINECRAFT_RCON_PASS'), 'required');
    }

    if ($this->form_validation->run()) {
      if ($this->input->post('createServer') !== null) {
        if ($this->minecraftManager->addServer($this->input->post('name'), $this->input->post('ip_address'), $this->input->post('port'), $this->input->post('rcon_port'), $this->input->post('rcon_pass'))) {
          $this->session->set_flashdata('success', $this->lang->line('MINECRAFT_SERVER_SUCCESSFULLY_CREATED'));
        } else {
          // If server adding failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
        }
      }
      if ($this->input->post('editServer') !== null) {
        $data = [
          'name' => $this->input->post('name'),
          'ip_address' => $this->input->post('ip_address'),
          'port' => $this->input->post('port'),
          'rcon_port' => $this->input->post('rcon_port'),
          'rcon_pass' => $this->input->post('rcon_pass'),
        ];
        if ($this->minecraftManager->editServer($this->input->post('id'), $data)) {
          $this->session->set_flashdata('success', $this->lang->line('MINECRAFT_SERVER_SUCCESSFULLY_EDITED'));
        } else {
          // If server editing failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
        }
      }
      if ($this->input->post('deleteServer') !== null) {
        if ($this->minecraftManager->deleteServer($this->input->post('id'))) {
          $this->session->set_flashdata('success', $this->lang->line('MINECRAFT_SERVER_SUCCESSFULLY_DELETED'));
        } else {
          // If server deleting failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
        }
      }

      redirect(route('minecraft/admin_settings'));
    }

    $this->twig->display('admin/settings', $this->data);
  }
}