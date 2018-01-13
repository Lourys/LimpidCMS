<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Themes_Manager $themesManager
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Lang $lang
 */
class Themes extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    if (!$authorized = $this->authManager->isPermitted($this->session->userdata('id'), 'THEMES__MANAGEMENT')) {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      show_error($this->lang->line('PERMISSION_ERROR'), $authorized === false ? 403 : 401, $this->lang->line('ERROR_ENCOUNTERED'));
      exit();
    }
    $this->load->library('Themes_Manager', null, 'themesManager');
  }

  public function admin_available()
  {
    $this->data['page_title'] = $this->lang->line('THEMES_INSTALLATION');

    $this->data['themes'] = $this->themesManager->getAvailableThemes();

    // Render the view
    $this->twig->display('admin/themes/available', $this->data);
  }

  public function admin_manage()
  {
    $this->data['page_title'] = $this->lang->line('THEMES_MANAGEMENT');
    $this->data['themes'] = $this->themesManager->getThemes();

    // Render the view
    $this->twig->display('admin/themes/manage', $this->data);
  }

  public function admin_enable($uri)
  {
    if ($this->themesManager->enableTheme($uri))
      $this->session->set_flashdata('success', $this->lang->line('THEME_SUCCESSFULLY_ENABLED'));
    else
      $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));

    redirect(route('themes/admin_manage'));
  }

  public function admin_install($uri)
  {
    if (!$this->themesManager->getTheme($uri)) {
      $theme = $this->themesManager->getAvailableTheme($uri);
      if ($this->themesManager->installTheme($theme->uri))
        $this->session->set_flashdata('success', $this->lang->line('THEME_SUCCESSFULLY_INSTALLED'));
      else
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
    } else {
      $this->session->set_flashdata('error', $this->lang->line('THEME_ALREADY_INSTALLED'));
    }

    redirect(route('themes/admin_manage'));
  }

  public function admin_uninstall($uri)
  {
    if ($this->themesManager->isEnabled($uri)) {
      $this->session->set_flashdata('error', $this->lang->line('DISABLE_THEME_BEFORE_UNINSTALLING'));
    } else {
      if ($this->themesManager->uninstallTheme($uri))
        $this->session->set_flashdata('success', $this->lang->line('THEME_SUCCESSFULLY_UNINSTALLED'));
      else
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
    }

    redirect(route('themes/admin_manage'));
  }

  public function admin_config()
  {
    if ($theme = $this->themesManager->getEnabledTheme()) {
      $this->load->helper('form');
      $this->data['config'] = json_decode(file_get_contents(APPPATH . 'themes/' . $theme->uri . '/config.json'), true);
      if ($this->input->method() == 'post') {
        if (file_put_contents(APPPATH . 'themes/' . $theme->uri . '/config.json', json_encode($this->input->post()))) {
          $this->session->set_flashdata('success', $this->lang->line('CONFIG_SUCCESSFULLY_EDITED'));

          redirect(current_url());
        } else {
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
        }
      }

      $this->twig->display('config', $this->data);
    } else {
      $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
    }
  }

}