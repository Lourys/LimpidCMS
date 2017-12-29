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

      $this->data['timezones'] = $this->getTimezonesInput();

      // If check passed
      if ($this->form_validation->run()) {
        $data = $this->input->post();
        unset($data['send']);
        foreach ($data as $index => $value) {
          if ($value === 'true')
            $value = 1;
          elseif ($value === 'false')
            $value = 0;

          if ($this->config->item($index) != $value) {
            if (!$this->config->edit_item($index, $value, 'config')) {
              $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
              redirect(current_url());
            }
          }
        }

        $this->session->set_flashdata('success', $this->lang->line('SETTINGS_SUCCESSFULLY_EDITED'));
      }

      $this->twig->display('admin/settings/general', $this->data);
      $this->session->unmark_flash('success');
      $this->session->unmark_flash('error');
    } else {
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(site_url());
    }
  }

  private function getTimezonesInput()
  {
    $result = [];
    $timezones = [];
    $regions = [
      $this->lang->line('AFRICA') => DateTimeZone::AFRICA,
      $this->lang->line('AMERICA') => DateTimeZone::AMERICA,
      $this->lang->line('ANTARCTICA') => DateTimeZone::ANTARCTICA,
      $this->lang->line('ASIA') => DateTimeZone::ASIA,
      $this->lang->line('ATLANTIC') => DateTimeZone::ATLANTIC,
      $this->lang->line('EUROPE') => DateTimeZone::EUROPE,
      $this->lang->line('INDIAN') => DateTimeZone::INDIAN,
      $this->lang->line('PACIFIC') => DateTimeZone::PACIFIC
    ];

    foreach ($regions as $name => $mask) {
      $zones = DateTimeZone::listIdentifiers($mask);
      foreach ($zones as $timezone) {
        $time = new DateTime(NULL, new DateTimeZone($timezone));
        $hour = $this->config->item('language') == 'english' ? $time->format('g:i a') : $time->format('H:i');
        $timezones[$name][$timezone] = explode('/', $timezone)[1] . ' - ' . $hour;
      }
    }

    foreach ($timezones as $region => $list) {
      if (!empty($result))
        $result[] = ['name' => '', 'value' => '', 'disabled' => true];
      $result[] = ['name' => $region, 'value' => '', 'disabled' => true, 'style' => 'font-weight:bold;color:#000'];
      foreach ($list as $timezone => $name) {
        $result[] = ['name' => $name, 'value' => $timezone];
      }
    }

    return $result;
  }
}