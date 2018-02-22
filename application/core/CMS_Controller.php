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
 * @property Groups_Manager $groupsManager
 * @property Permissions_model $permissions
 * @property CI_DB_forge $dbforge
 * @property \Evenement\EventEmitter $emitter
 * @property CI_Output $output
 * @property Themes_Manager $themesManager
 * @property CI_Lang $lang
 * @property Users_Manager $usersManager
 * @property Captcha $captcha
 * @property Minecraft_model minecraft
 * @property Minecraft_Manager minecraftManager
 * @property CI_User_agent agent
 */
class CMS_Controller extends CI_Controller
{
  public static $instance;

  public function __construct()
  {
    parent::__construct();
    self::$instance || self::$instance =& $this;

    $this->config->load('config');
    date_default_timezone_set($this->config->item('timezone'));
    $this->lang->load($this->config->item('theme'));

    // Demo specific
    $this->load->library('Users_Manager', null, 'usersManager');
    $this->usersManager->editUser(1, [
      'username' => 'admin',
      'email' => 'admin@admin.fr',
      'password' => password_hash('admin123', PASSWORD_BCRYPT)
    ]);
    $this->config->edit_item('language', 'french', 'config.php');

    /*if ($this->config->item('license') !== null) {
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
    }*/


    $this->emitter = new Evenement\EventEmitter();
    require_once(APPPATH . 'Limpid_Events.php');
    new Limpid_Events();
    $this->load->library('Plugins_Manager', null, 'pluginsManager');
    $this->pluginsManager->autoloadFiles();
    $this->emitter->emit('limpid.initialization');
    $this->load->helper('file');

    require(APPPATH . 'third_party/Twig_Extensions/Assets_Extension.php');
    require(APPPATH . 'third_party/Twig_Extensions/Lang_Extension.php');
    require(APPPATH . 'third_party/Twig_Extensions/Array_Extension.php');

    if ($this->router->module) {
      $config['paths'] = [];
      $plugin_uri = $this->pluginsManager->getPlugin($this->router->module)->uri;
      if (is_dir(APPPATH . 'themes/' . $this->config->item('theme') . '/' . $plugin_uri . '/'))
        array_push($config['paths'], APPPATH . 'themes/' . $this->config->item('theme') . '/plugins/' . $plugin_uri . '/');
      array_push($config['paths'], APPPATH . 'plugins/' . $plugin_uri . '/views/');
      if (is_dir(APPPATH . 'themes/default/plugins/' . $plugin_uri . '/')) {
        array_push($config['paths'], APPPATH . 'themes/default/plugins/' . $plugin_uri . '/');
      }
      array_push($config['paths'], APPPATH . 'themes/' . $this->config->item('theme') . '/');
      array_push($config['paths'], APPPATH . 'themes/default/');
    } else {
      $config = [
        'paths' => [
          APPPATH . 'themes/' . $this->config->item('theme') . '/',
          APPPATH . 'themes/default/'
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
    $this->twig->getTwig()->addExtension(new Array_Extension());
    $this->twig->getTwig()->addExtension(new Twig_Extension_StringLoader());
    $this->twig->addGlobal('site_name', $this->config->item('site_name'));
    $this->twig->addGlobal('settings', $this->config->config);
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
    if ($this->router->module) {
      if (!$this->pluginsManager->isEnabled($this->router->module))
        show_404();
    }

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