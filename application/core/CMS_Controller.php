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
 * @property API_Manager APIManager
 */
class CMS_Controller extends CI_Controller
{
  public static $instance;

  public function __construct()
  {
    parent::__construct();
    self::$instance || self::$instance =& $this;

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $this->config->load('config');
    date_default_timezone_set($this->config->item('timezone'));
    $this->lang->load($this->config->item('theme'));

    // Demo specific
    $this->load->library('Users_Manager', null, 'usersManager');
    $this->usersManager->editUser(1, [
      'username' => 'admin',
      'email' => 'lourys@protonmail.com',
      'password' => password_hash('admin123', PASSWORD_BCRYPT)
    ]);
    $this->config->edit_item('language', 'french', 'config.php');

    if (LICENSE_KEY !== null) {
      $last_check = @file_get_contents(APPPATH . 'secure/integrity');
      if (!$last_check) {
        $last_check = $this->getEncryptedLicenseData();
      }

      $last_check_decrypted = $this->decryptData($last_check);
      if ($last_check_decrypted == null) {
        show_error('An error occurred while license check! Please refresh the page', 500, 'License error');
      }
      $data = @json_decode($last_check_decrypted, true);

      if ($data['type'] === 'error') {
        show_error('An error occurred while license check! (' . $data['msg'] . ')', $data['code'], 'License error');
        die();
      }

      if ($data['timestamp'] > time() + 600) {
        $this->getEncryptedLicenseData();
      }
    } else {
      show_error('Missing license!', 500, 'License error');
      die();
    }


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
      $plugin_uri = $this->pluginsManager->getPlugin($this->router->module)['uri'];
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

  private function decryptData($base_64_data)
  {
    $pvt_key_str = '-----BEGIN PRIVATE KEY-----
MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCotHN7MrfrJHeN
XF6AjjTBFq+NvGahqJuR7y+/INB5oDPoeDyCzN+jgw3hnZ5Yd1m7NIad2qF6unKC
3MocjkMPwEX+MtlPaXf4LUSO7bAw9ChBDcFPADHRzH2Jx/Boi03lzZ7HoeobmPmJ
+Li74RnL6N55V9/eRVBc28q35arsDyXoLnfOHubzrNGaWPwFdBQsaEB0a3iJLF1E
m/7+n1X3czyE8anCEHfiVsEKbw21tRR2p+QUtKpN/zTqon+n9qE9OI2+yB698Mbe
v+ocIyV883+JJYP1t+OjvaCd+y7uc5Dw5mRLeIH3qgrukQlw1BNGDoLGtVA0xFWI
2afVvBcHAgMBAAECggEACdf9pQKgmKfYEfeBzB6AFYQtWifUFTqxWSKLtqtDftVK
MhZR8Y/ivLe456E6zA6qvbGi6TMImRCn/drEMEZcw16EtwBgjbGpvmFlzrEvxqt4
bdNPpDxuq78y1AdGj9MRCIem7B8WeeXDKbnJjazkxHEiChKGjYGd3s00VuafwoZH
dwVNGppVsLe7dOOlec1j/qQLMSxSQaMoNcObahKzFGM7yNSQs6sRPyqRpeGJ2TRt
5f2Dv7HPQowmBokslEGzqlNKMb6A5cl/FfDFCyBaXCUdF0l2Tj9ANM3IZa72xhjX
yqx76BaqK2crKarjrEPMSQ4JfarpQxAYFG586qyBAQKBgQDa430K2ZPQ3hjAohZf
nOWJ1c/+ovnWm3lrdT359+rbfiUoPrrH1eJEXJqodj6TO5NfTanrmH4dGRhY1zGR
a8kG/0YpkNhldD0zbVoacirMW8MVf7DtcRtRNkwP8LkfuyFgq5gkLns5Hk5BK/aX
2eebi9jow9KasbYVOYweI4gGyQKBgQDFTtFIixMZoP9vdJJAdkoitFDnfnbCMvXE
ObBq8FZo3H9W8qSSG5fqAB+oFIL5OOh2VklebeqSRUav+tvVxWXXj/VNWUU8v+ap
+XDbuJrK52IjlVWvtYL36dRL5HbIKscVQc5YcW++iO8qGxoWEIhHvLtSiHtqqyHb
UhObSr6HTwKBgAgPKjJ1EmE1XDnzfdllYHozuiXJAGPrc4wGVBLZEvej7GBP9vaW
pG8Z7cPYHrOzFNkEdgYrpjESFHho6/VLv6oXShELuTv7DKnRE+k3XOYLVuJ6whvr
9zKFSkWn+lj1vePeTYq/f1/6Aq2Zncm3hzSN8J2ZYC677lVXuhX9/uspAoGAJZvc
cINx5JS3m7sQlZD2mJ7ePQHyCRpFll2YrwmYruw4qY4eqGryDfxwDE28mVyrksLn
wUQaTj8+NhUPCjRSMUCTdWbqt6WshgGx7W/GsZ5hKn8wkgl7KV48xSqLKaRdxkOA
3YLh1eOsEs6Prl0AXQwweI7jV3W/QlrmudcJcikCgYAVydTMO0FszahKBgfCFWQW
sZqZmYrI2PfNbxVlACz84rYqGpaZQSnsLdaw9p5caeKlzzjCsT1/2Wq1yzfGbaU5
3+/9WGDg4PbZX6Wp5slDpB763KtUX0W7HRkrKFfo8i5CDqffxgd5bCUUpQO2iGWH
oItuiuEvWUxos45G4HaXSw==
-----END PRIVATE KEY-----';

    $pvt_key = openssl_pkey_get_private($pvt_key_str);
    $a_key = openssl_pkey_get_details($pvt_key);

    // Decrypt the data in the small chunks
    $chunkSize = ceil($a_key['bits'] / 8);
    $output = '';

    $encrypted = base64_decode($base_64_data);

    while ($encrypted) {
      $chunk = substr($encrypted, 0, $chunkSize);
      $encrypted = substr($encrypted, $chunkSize);
      $decrypted = '';
      if (!openssl_private_decrypt($chunk, $decrypted, $pvt_key)) {
        unlink(APPPATH . 'secure/integrity');
        return null;
      }
      $output .= $decrypted;
    }
    openssl_free_key($pvt_key);

    // Uncompress the unencrypted data.
    return gzuncompress($output);
  }

  private function getEncryptedLicenseData()
  {
    $service_url = API_URL . 'license/verify?key=' . LICENSE_KEY;
    $curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $curl_response = curl_exec($curl);
    if ($curl_response !== false) {
      curl_close($curl);
      file_put_contents(APPPATH . 'secure/integrity', $curl_response);
    } else {
      show_error('HTTP request failed.');
      return null;
    }

    return $curl_response;
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