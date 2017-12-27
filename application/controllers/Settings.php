<?php

/**
 * Settings CMS class
 *
 * @property CI_Form_validation $form_validation
 * @property CI_Lang $lang
 * @property CI_Input $input
 */
class Settings extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->lang->load('settings');
  }

  public function admin_general()
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'SETTINGS__EDIT')) {
      $this->data['page_title'] = $this->lang->line('SETTINGS_EDITION');
      $this->data['settings'] = $this->config->config;

      $this->load->helper('form');
      $this->load->library('form_validation');

      // Form rules check
      $this->form_validation->set_rules('send', '', 'required');

      $this->data['languages'] = [
        ['name' => 'Français', 'value' => 'french'],
        ['name' => 'English', 'value' => 'english']
      ];

      // If check passed
      if ($this->form_validation->run()) {
        $data = $this->input->post();
        unset($data['send']);
        foreach ($data as $index => $value) {
          if ($value === 'true')
            $value = 1;
          elseif ($value === 'false')
            $value = 0;

          if (!$this->config->edit_item($index, $value, 'cms_settings')) {
            $this->config->edit_item($index, $value, 'config');
          }
        }

        $this->session->set_flashdata('success', $this->lang->line('SETTINGS_SUCCESSFULLY_EDITED'));
        redirect(current_url());
      }

      $this->twig->display('admin/settings/general', $this->data);
    } else {
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(site_url());
    }
  }
}