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

    if ($this->authManager->isPermitted($this->session->userdata('id'), 'SETTINGS__EDIT')) {
      $this->data['page_title'] = $this->lang->line('SETTINGS_EDITION');
      $this->load->helper('form');
      $this->session->unmark_flash('success');
      $this->session->unmark_flash('error');
    } else {
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(site_url());
    }
  }

  public function admin_general()
  {
    $this->data['languages'] = [
      ['name' => 'Français', 'value' => 'french'],
      ['name' => 'English', 'value' => 'english']
    ];

    $this->data['timezones'] = $this->getTimezonesInput();

    if ($this->input->method() == 'post') {
      $data = $this->input->post();
      if ($this->editSettings($data)) {
        $this->session->set_flashdata('success', $this->lang->line('SETTINGS_SUCCESSFULLY_EDITED'));
      } else {
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
      }
    }

    $this->twig->display('admin/settings/general', $this->data);
  }

  public function admin_security()
  {
    if ($this->input->method() == 'post') {
      $data = $this->input->post();
      if ($this->editSettings($data)) {
        $this->session->set_flashdata('success', $this->lang->line('SETTINGS_SUCCESSFULLY_EDITED'));
      } else {
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
      }
    }

    $this->twig->display('admin/settings/security', $this->data);
  }

  public function admin_users()
  {
    if ($this->input->method() == 'post') {
      $data = $this->input->post();
      if ($this->editSettings($data)) {
        $this->session->set_flashdata('success', $this->lang->line('SETTINGS_SUCCESSFULLY_EDITED'));
      } else {
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
      }
    }

    $this->twig->display('admin/settings/users', $this->data);
  }

  private function editSettings($data)
  {
    foreach ($data as $index => $value) {
      if ($value === 'true')
        $value = 1;
      elseif ($value === 'false')
        $value = 0;

      if ($this->config->item($index) != $value) {
        if (!$this->config->edit_item($index, $value, 'config')) {
          return false;
        }
      }
    }

    return true;
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