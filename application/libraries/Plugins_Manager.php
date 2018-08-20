<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Plugins_Manager
{

  private $plugins;

  private $plugins_uris;

  private $available_plugins_uris;

  private $available_plugins;

  private $enabled_plugins;

  private $disabled_plugins;

  private $admin_nav;

  private $account_nav;

  private $limpid;

  /**
   * Plugins_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;

    if (ENVIRONMENT == 'development' && file_exists(APPPATH . 'cache/plugins.json'))
      unlink(APPPATH . 'cache/plugins.json');

    $this->updatePluginList();
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
          $plugins[$plugin] = json_decode(file_get_contents(APPPATH . 'plugins/' . $plugin . '/settings.json'), true);
      }
      file_put_contents(APPPATH . 'cache/plugins.json', json_encode($plugins));
    }

    $this->plugins = json_decode(file_get_contents(APPPATH . 'cache/plugins.json'), true);
  }

  /**
   * Autoload all plugins files which handle events
   *
   * @return void
   */
  public function autoloadFiles()
  {
    if ($this->plugins != null) {
      foreach ($this->plugins as $plugin) {
        if ($plugin['enabled']) {
          $this->limpid->load->add_module($plugin['uri']);
          if (file_exists(APPPATH . 'plugins/' . $plugin['uri'] . '/' . $plugin['uri'] . '_Events.php')) {
            $eventFileName = $plugin['uri'] . '_Events';
            require_once(APPPATH . 'plugins/' . $plugin['uri'] . '/' . $eventFileName . '.php');
            new $eventFileName();
          }
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
    if (empty($this->available_plugins)) {
      $this->limpid->load->library('API_Manager', null, 'APIManager');
      $this->available_plugins = $this->limpid->APIManager->getAvailablePlugins();
    }

    return $this->available_plugins;
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
    if ($plugin_uri = preg_grep( "/" . $uri . "/i", $this->getAvailablePluginsUris())) {
      return $this->available_plugins[implode('', $plugin_uri)];
    }

    return false;
  }

  /**
   * Get all installed plugins
   *
   * @return array
   */
  public function getPlugins()
  {
    return $this->plugins;
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
    if ($plugin_uri = preg_grep( "/" . $uri . "/i", $this->getPluginsUris())) {
      return $this->plugins[implode('', $plugin_uri)];
    }

    return false;
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
        if ($val['enabled'])
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
        if (!$val['enabled'])
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
      if ($plugin['enabled']) {
        if (isset($plugin['admin']) && $plugin['admin'] === true) {
          $this->admin_nav[$plugin['name']]['home'] = $plugin['uri'] . '/admin_index';
        }
        if (isset($plugin['admin_settings']) && $plugin['admin_settings'] === true) {
          $this->admin_nav[$plugin['name']]['settings'] = $plugin['uri'] . '/admin_settings';
        }
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
      if ($plugin['enabled'] && isset($plugin['accountNav']) && $plugin['accountNav'] != null) {
        $this->account_nav[$plugin['name']] = $plugin['accountNav'];
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

    $plugin = $this->getPlugin($uri);
    $plugin['enabled'] = true;
    if (unlink(APPPATH . 'cache/plugins.json') && file_put_contents(APPPATH . 'plugins/' . $plugin['uri'] . '/settings.json', json_encode($plugin))) {
      $this->updatePluginList();
      return true;
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

    $plugin = $this->getPlugin($uri);
    $plugin['enabled'] = false;
    if (unlink(APPPATH . 'cache/plugins.json') && file_put_contents(APPPATH . 'plugins/' . $plugin['uri'] . '/settings.json', json_encode($plugin))) {
      $this->updatePluginList();
      return true;
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
    $plugin = $this->getAvailablePlugin($uri);
    if (!isset($plugin))
      return false;

    $this->limpid->load->library('API_Manager', null, 'APIManager');
    if ($path = $this->limpid->APIManager->downloadPlugin($plugin['uri'])) {
      $zip = new ZipArchive;
      if ($zip->open($path) === true) {
        $zip->extractTo(APPPATH . 'plugins/');
        $zip->close();

        $this->limpid->load->helper('file');
        delete_files(APPPATH . 'tmp', true);

        $pluginActions = $this->getPluginActions($plugin['uri']);
        if ($pluginActions->onInstall() !== true) {
          $this->uninstallPlugin($plugin['uri']);
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
   * Updates a plugin by uri
   *
   * @param string $uri
   *
   * @return bool|null
   */
  function updatePlugin($uri)
  {
    if (empty($uri))
      return null;

    $this->limpid->load->library('API_Manager', null, 'APIManager');
    if ($path = $this->limpid->APIManager->downloadPlugin($uri)) {
      $zip = new ZipArchive;
      if ($zip->open($path) === true) {
        $zip->extractTo(APPPATH . 'plugins/');
        $zip->close();

        $this->limpid->load->helper('file');
        delete_files(APPPATH . 'tmp', true);

        $pluginActions = $this->getPluginActions($uri);
        if ($pluginActions->onUpdate() !== true) {
          return null;
        }
        $this->updatePluginList();

        return true;
      }
    }

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
    $plugin = $this->getPlugin($uri);

    if (!isset($plugin)) {
      return false;
    }

    return (boolean) $plugin['enabled'];
  }

  /**
   * Checks if a plugin needs to be updated
   *
   * @param string $uri
   *
   * @return bool
   */
  function doesNeedToUpdate($uri)
  {
    if (!isset($this->available_plugins)) $this->getAvailablePlugins();

    if (!isset($this->plugins[$uri]) || !isset($this->available_plugins[$uri])) {
      return false;
    }

    if (str_replace('.', '', $this->plugins[$uri]['version']) < str_replace('.', '', $this->available_plugins[$uri]['version'])) {
      return true;
    }

    return false;
  }

  /**
   * Load plugin's actions file by uri
   *
   * @param object|string $plugin
   *
   * @return mixed
   */
  private function getPluginActions($plugin)
  {
    if (!is_string($plugin)) {
      return false;
    }

    $pluginActions = $plugin . '_Actions';
    if (file_exists(APPPATH . 'plugins/' . $plugin . '/' . $pluginActions . '.php')) {
      require_once(APPPATH . 'plugins/' . $plugin . '/' . $pluginActions . '.php');
      return new $pluginActions();
    }

    return null;
  }

  private function getPluginsUris()
  {
    if (isset($this->plugins_uris)) return $this->plugins_uris;

    foreach ($this->plugins as $uri => $plugin) {
      $this->plugins_uris[] = $uri;
    }

    return $this->plugins_uris;
  }

  private function getAvailablePluginsUris()
  {
    if (isset($this->available_plugins_uris)) return $this->available_plugins_uris;

    if (!isset($this->available_plugins)) $this->getAvailablePlugins();

    foreach ($this->available_plugins as $uri => $plugin) {
      $this->available_plugins_uris[] = $uri;
    }

    return $this->available_plugins_uris;
  }
}