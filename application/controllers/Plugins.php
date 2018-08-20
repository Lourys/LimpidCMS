<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property Plugins_Manager $pluginsManager
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_Lang $lang
 */
class Plugins extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->authManager->checkAccess('PLUGINS__MANAGEMENT');
  }

  public function admin_available()
  {
    $this->data['page_title'] = $this->lang->line('PLUGINS_INSTALLATION');

    $this->data['plugins'] = $this->pluginsManager->getAvailablePlugins();

    // Render the view
    $this->twig->display('admin/plugins/available', $this->data);
  }

  public function admin_manage()
  {
    $this->data['page_title'] = $this->lang->line('PLUGINS_MANAGEMENT');
    $this->data['plugins'] = $this->pluginsManager->getPlugins();

    // Render the view
    $this->twig->display('admin/plugins/manage', $this->data);
  }

  public function admin_enable($uri)
  {
    if ($this->pluginsManager->enablePlugin($uri))
      $this->session->set_flashdata('success', $this->lang->line('PLUGIN_SUCCESSFULLY_ENABLED'));
    else
      $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));

    redirect(route('plugins/admin_manage'));
  }

  public function admin_disable($uri)
  {
    if ($this->pluginsManager->disablePlugin($uri))
      $this->session->set_flashdata('success', $this->lang->line('PLUGIN_SUCCESSFULLY_DISABLED'));
    else
      $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));

    redirect(route('plugins/admin_manage'));
  }

  public function admin_install($uri)
  {
    if (!$this->pluginsManager->getPlugin($uri)) {
      $plugin = $this->pluginsManager->getAvailablePlugin($uri);

      if ($this->pluginsManager->installPlugin($plugin['uri']))
        $this->session->set_flashdata('success', $this->lang->line('PLUGIN_SUCCESSFULLY_INSTALLED'));
      else
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
    } else {
      $this->session->set_flashdata('error', $this->lang->line('PLUGIN_ALREADY_INSTALLED'));
    }

    redirect(route('plugins/admin_manage'));
  }

  public function admin_uninstall($uri)
  {
    // Demo specific
    $this->session->set_flashdata('error', 'Cette fonctionnalité est désactivée pour la version démo');
    redirect(route('plugins/admin_manage'));

    if ($this->pluginsManager->isEnabled($uri)) {
      $this->session->set_flashdata('error', $this->lang->line('DISABLE_PLUGIN_BEFORE_UNINSTALLING'));
    } else {
      if ($this->pluginsManager->uninstallPlugin($uri))
        $this->session->set_flashdata('success', $this->lang->line('PLUGIN_SUCCESSFULLY_UNINSTALLED'));
      else
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
    }

    redirect(route('plugins/admin_manage'));
  }

  public function admin_update($uri)
  {
    if ($plugin = $this->pluginsManager->getPlugin($uri)) {
      if ($this->pluginsManager->updatePlugin($plugin['uri']))
        $this->session->set_flashdata('success', $this->lang->line('PLUGIN_SUCCESSFULLY_UPDATED'));
      else
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
    } else {
      $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
    }

    redirect(route('plugins/admin_manage'));
  }

}