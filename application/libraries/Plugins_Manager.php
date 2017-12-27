<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Plugins manager
 *
 * @property CMS_Controller $CI
 */
class Plugins_Manager
{

  private $plugins;

  private $available_plugins;

  private $enabled_plugins;

  private $disabled_plugins;

  private $admin_nav;

  private $account_nav;

  /**
   * Plugins_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;

    if (ENVIRONMENT == 'development' && file_exists(APPPATH . 'cache/plugins.json'))
      unlink(APPPATH . 'cache/plugins.json');

    $this->updatePluginList();

    if (empty($this->available_plugins))
      $this->available_plugins = json_decode(file_get_contents('http://localhost/codeigniter/public/availablePlugins.json'));
  }

  /**
   * Update the plugins list by generating a json file if doesn't exists
   *
   * @return void
   */
  private function updatePluginList()
  {
    $plugins = array();
    if (!file_exists(APPPATH . 'cache/plugins.json') || ENVIRONMENT == 'development') {
      foreach (array_diff(scandir(APPPATH . 'plugins'), array('..', '.')) as $plugin) {
        if (file_exists(APPPATH . 'plugins/' . $plugin . '/settings.json'))
          $plugins[$plugin] = json_decode(file_get_contents(APPPATH . 'plugins/' . $plugin . '/settings.json'));
      }
      file_put_contents(APPPATH . 'cache/plugins.json', json_encode($plugins));
    }

    $this->plugins = json_decode(file_get_contents(APPPATH . 'cache/plugins.json'));
  }

  /**
   * Autoload all plugins files which handle events
   *
   * @return void
   */
  public function loadEventHandlers()
  {
    if ($this->plugins != null) {
      foreach ($this->plugins as $plugin) {
        if ($plugin->enabled && file_exists(APPPATH . 'plugins/' . $plugin->uri . '/' . $plugin->uri . '_Events.php')) {
          $eventFileName = $plugin->uri . '_Events';
          require_once(APPPATH . 'plugins/' . $plugin->uri . '/' . $eventFileName . '.php');
          new $eventFileName();
        }
      }
    }
  }

  /**
   * Get all downloadable plugins
   *
   * @return array
   */
  public function getAvailablePlugins()
  {
    return (array)$this->available_plugins;
  }

  /**
   * Get an available plugin by uri
   *
   * @param string $uri
   *
   * @return mixed
   */
  public function getAvailablePlugin($uri)
  {
    return isset($this->available_plugins->$uri) ? $this->available_plugins->$uri : false;
  }

  /**
   * Get all installed plugins
   *
   * @return array
   */
  public function getPlugins()
  {
    return (array)$this->plugins;
  }

  /**
   * Get an installed plugin by uri
   *
   * @param string $uri
   *
   * @return mixed
   */
  public function getPlugin($uri)
  {
    return isset($this->plugins->$uri) ? $this->plugins->$uri : false;
  }

  /**
   * Get all enabled plugins
   *
   * @return array|null
   */
  function getEnabledPlugins()
  {
    if ($this->enabled_plugins == null) {
      foreach ($this->plugins as $key => $val) {
        if ($val->enabled)
          $this->enabled_plugins[$key] = $val;
      }
      if (empty($this->enabled_plugins))
        return null;
    }

    return $this->enabled_plugins;
  }

  /**
   * Get all disabled plugins
   *
   * @return array|null
   */
  function getDisabledPlugins()
  {
    if ($this->disabled_plugins == null) {
      foreach ($this->plugins as $key => $val) {
        if (!$val->enabled)
          $this->disabled_plugins[$key] = $val;
      }
      if (empty($this->disabled_plugins))
        return null;
    }

    return $this->disabled_plugins;
  }

  /**
   * Get plugins' management links
   *
   * @return array|null
   */
  function getAdminNav()
  {
    foreach ($this->plugins as $plugin) {
      if ($plugin->enabled && isset($plugin->admin) && $plugin->admin != null) {
        $this->admin_nav[$plugin->name] = $plugin->admin;
      }
    }

    if (empty($this->admin_nav))
      return null;

    return $this->admin_nav;
  }

  /**
   * Get plugins' links attached to user account
   *
   * @return array|null
   */
  function getAccountNav()
  {
    foreach ($this->plugins as $plugin) {
      if ($plugin->enabled && isset($plugin->accountNav) && $plugin->accountNav != null) {
        $this->account_nav[$plugin->name] = $plugin->accountNav;
      }
    }

    if (empty($this->account_nav))
      return null;

    return $this->account_nav;
  }

  /**
   * Enable a plugin by uri
   *
   * @param string $uri
   *
   * @return bool|null
   */
  function enablePlugin($uri)
  {
    if (empty($uri))
      return null;

    $pluginActions = $this->getPluginActions($uri);
    if (!$pluginActions->onEnable())
      return null;

    foreach ($this->plugins as $plugin) {
      if ($plugin->uri == $uri) {
        $plugin->enabled = true;
        if (unlink(APPPATH . 'cache/plugins.json') && file_put_contents(APPPATH . 'plugins/' . $plugin->uri . '/settings.json', json_encode($plugin))) {
          $this->updatePluginList();
          return true;
        }
      }
    }

    return false;
  }

  /**
   * Disabled a plugin by uri
   *
   * @param string $uri
   *
   * @return bool|null
   */
  function disablePlugin($uri)
  {
    if (empty($uri))
      return null;

    $pluginActions = $this->getPluginActions($uri);
    if (!$pluginActions->onDisable())
      return null;

    foreach ($this->plugins as $plugin) {
      if ($plugin->uri == $uri) {
        $plugin->enabled = false;
        if (unlink(APPPATH . 'cache/plugins.json') && file_put_contents(APPPATH . 'plugins/' . $plugin->uri . '/settings.json', json_encode($plugin))) {
          $this->updatePluginList();
          return true;
        }
      }
    }

    return false;
  }


  /**
   * Installs a plugin by uri
   *
   * @param string $uri
   *
   * @return bool|null
   */
  function installPlugin($uri)
  {
    if (empty($uri))
      return null;

    if (file_put_contents(APPPATH . 'tmp/plugin.zip', fopen('http://localhost/codeigniter/public/' . $uri . '.zip', 'r'))) {
      $zip = new ZipArchive;
      if ($zip->open(APPPATH . 'tmp/plugin.zip') === TRUE) {
        $zip->extractTo(APPPATH . 'plugins/');
        $zip->close();

        $this->limpid->load->helper('file');
        delete_files(APPPATH . 'tmp', true);

        $pluginActions = $this->getPluginActions($uri);
        if ($pluginActions->onInstall() !== true) {
          $this->uninstallPlugin($uri);
          return null;
        }
        $this->updatePluginList();

        return true;
      }
    }

    return false;
  }


  /**
   * Uninstalls a plugin by uri
   *
   * @param string $uri
   *
   * @return bool|null
   */
  function uninstallPlugin($uri)
  {
    if (empty($uri))
      return null;

    $pluginActions = $this->getPluginActions($uri);
    if (!$pluginActions->onUninstall())
      return null;

    $this->limpid->load->helper('file');
    if (delete_files(APPPATH . 'plugins/' . $uri, true) && rmdir(APPPATH . 'plugins/' . $uri))
      return true;

    return false;
  }

  /**
   * Checks if a plugin is enabled by uri
   *
   * @param string $uri
   *
   * @return boolean
   */
  function isEnabled($uri)
  {
    return (boolean)$this->plugins->$uri->enabled;
  }


  /**
   * Load plugin's actions file by uri
   *
   * @param string $uri
   *
   * @return mixed
   */
  private function getPluginActions($uri)
  {
    $pluginActions = $uri . '_Actions';
    require_once(APPPATH . 'plugins/' . $uri . '/' . $pluginActions . '.php');
    return new $pluginActions();
  }


}

/* End of file Plugins_Manager.php */
/* Location: ./application/libraries/Plugins_Manager.php */