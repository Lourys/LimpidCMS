<?php
/******************************************************************************
 * Copyright © LimpidCMS 2017 All Rights Reserved                             *
 *                                                                            *
 * This file and all its contents is copyright by LimpidCMS, and may not      *
 * adapted, edited, changed, transformed, published, republished,             *
 * distributed or redistributed, in any way without our written permission.   *
 ******************************************************************************/

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Main CMS class
 *
 * @property CI_Config $config
 * @property CI_Session $session
 * @property CMS_Router $router
 * @property CMS_Loader $load
 * @property CI_Hooks $hooks
 * @property Twig $twig
 * @property Auth_Manager $authManager
 * @property Menu_Manager $menuManager
 * @property Plugins_Manager $pluginsManager
 * @property array $data
 * @property CI_DB_query_builder $db
 * @property Pages_model $pages
 * @property Auth_model $auth
 * @property News_model $news
 * @property Menu_model $menu
 * @property Users_model $users
 * @property Groups_model $groups
 * @property Permissions_model $permissions
 * @property CI_DB_forge $dbforge
 * @property \Evenement\EventEmitter $emitter
 * @property CI_Output $output
 * @property Themes_Manager $themesManager
 * @property Example_model $example
 * @property Example_Manager $exampleManager
 * @property CI_Lang $lang
 */
class CMS_Controller extends CI_Controller
{
  public static $instance;

  public function __construct()
  {
    parent::__construct();
    self::$instance || self::$instance =& $this;
    $this->config->load('cms_settings');
    date_default_timezone_set($this->config->item('timezone'));
    $this->lang->load($this->config->item('theme'));

    if ($this->config->item('license') !== null) {
      $service_url = 'http://localhost/api.limpidcms.fr/src/public/api/v1/license/verify?key=' . $this->config->item('license');
      $curl = curl_init($service_url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      $curl_response = curl_exec($curl);
      if ($curl_response === false) {
        curl_close($curl);
        show_error('HTTP request failed.');
      } elseif (json_decode($curl_response, true)['type'] === 'error') {
        show_error('An error occurred while license check! (' . json_decode($curl_response, true)['msg'] . ')', json_decode($curl_response, true)['code'], 'License error');
        die();
      }
      curl_close($curl);
    } else {
      show_error('Missing license!', 500, 'License error');
      die();
    }


    $this->emitter = new Evenement\EventEmitter();
    $this->pluginsManager = new Plugins_Manager();
    $this->pluginsManager->loadEventHandlers();
    $this->emitter->emit('limpid.initialization');

    $this->load->helper('file');

    require(APPPATH . 'third_party/Twig_Extensions/Assets_Extension.php');
    require(APPPATH . 'third_party/Twig_Extensions/Lang_Extension.php');

    if ($this->router->module) {
      $config['paths'] = [];
      if (is_dir(APPPATH . 'themes/' . $this->config->item('theme') . '/' . $this->router->module . '/'))
        array_push($config['paths'], APPPATH . 'themes/' . $this->config->item('theme') . '/' . $this->router->module . '/');
      array_push($config['paths'], APPPATH . 'plugins/' . $this->router->module . '/views/');
      array_push($config['paths'], APPPATH . 'themes/' . $this->config->item('theme') . '/');
    } else {
      $config = [
        'paths' => [
          APPPATH . 'themes/' . $this->config->item('theme') . '/'
        ]
      ];
    }
    if (ENVIRONMENT == 'development')
      $config['cache'] = false;
    else
      $config['cache'] = APPPATH . 'cache/twig';

    $this->load->helper('route_helper');
    $this->load->library('Auth_Manager', null, 'authManager');
    $this->load->library('Themes_Manager', null, 'themesManager');
    $this->load->library('twig', $config);
    $this->twig->getTwig()->addExtension(new Assets_Extension($this->config->item('theme')));
    $this->twig->getTwig()->addExtension(new Lang_Extension());
    $this->twig->getTwig()->addExtension(new Twig_Extension_StringLoader());
    $this->twig->addGlobal('site_name', $this->config->item('site_name'));
    $this->twig->addGlobal('theme_config', $this->themesManager->getThemeConfig());
    $this->twig->addGlobal('this', $this);

    $this->data = array();
  }
}

class Limpid_Controller extends CMS_Controller
{
  public function __construct()
  {
    parent::__construct();

    // If is an admin method
    if (strpos($this->router->method, 'admin_') !== false) {
      $this->data['plugins_nav'] = $this->pluginsManager->getAdminNav();
    } else {
      $this->load->library('Menu_Manager', null, 'menuManager');
    }

    $this->emitter->on('beforePageRender', function () {
      $this->load->helper('Limpid_helper');
    });
  }
}

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
      $this->available_plugins = json_decode(@file_get_contents('http://localhost/codeigniter/public/availablePlugins.json'));
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