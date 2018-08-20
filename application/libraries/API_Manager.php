<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API manager
 *
 * @property CMS_Controller limpid
 */
class API_Manager
{
  /**
   * API_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
  }

  /**
   * Get all themes available as downloading
   *
   * @return bool|array
   */
  public function getAvailableThemes()
  {
    $themes = $this->curlCall('theme/list', ['key' => LICENSE_KEY]);
    if ($themes) {
      return json_decode($themes, true);
    }

    return false;
  }

  /**
   * Get all plugins available as downloading
   *
   * @return bool|array
   */
  public function getAvailablePlugins()
  {
    $plugins = $this->curlCall('plugin/list', ['key' => LICENSE_KEY]);
    if ($plugins) {
      return json_decode($plugins, true);
    }

    return false;
  }

  /**
   * Download a plugin and save it in tmp folder
   *
   * @param $uri
   *
   * @return bool
   */
  public function downloadPlugin($uri)
  {
    $path = APPPATH . 'tmp/' . $uri . '.zip';
    $file = fopen($path, "w+");
    if (fputs($file, $this->curlCall('plugin/download/' . $uri, ['key' => LICENSE_KEY])) && fclose($file)) {
      return $path;
    }

    return false;
  }

  /**
   * Make cURL call
   *
   * @param string $endpoint
   * @param array $parameters
   * @param string $method
   *
   * @return string|false
   */
  private function curlCall($endpoint, $parameters, $method = 'GET')
  {
    if ($method == 'GET') {
      $params = '?';
      foreach ($parameters as $parameter => $value) {
        $params .= $parameter . '=' . $value . '&';
      }
      $params = rtrim($params, '&');
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, API_URL . $endpoint . $params);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, "LimpiCMS v" . $this->limpid->config->item('cms_version'));
    curl_setopt($curl, CURLOPT_REFERER, $_SERVER['SERVER_NAME']);
    $curl_response = curl_exec($curl);
    if ($curl_response === false) {
      curl_close($curl);
      show_error('HTTP request failed.');
      return false;
    } else {
      curl_close($curl);
      return $curl_response;
    }
  }
}

/* End of file API_Manager.php */
/* Location: ./application/libraries/API_Manager.php */