<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Themes manager
 *
 * @property CMS_Controller limpid
 */
class Themes_Manager
{

  private $themes;

  private $available_themes;

  private $enabled_theme;

  /**
   * Themes_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;

    if (ENVIRONMENT == 'development' && file_exists(APPPATH . 'cache/themes.json'))
      unlink(APPPATH . 'cache/themes.json');

    $this->updateThemeList();
  }

  /**
   * Update the themes list by generating a json file if doesn't exists
   *
   * @return void
   */
  protected function updateThemeList()
  {
    $themes = [];
    if (!file_exists(APPPATH . 'cache/themes.json') || ENVIRONMENT == 'development') {
      foreach (array_diff(scandir(APPPATH . 'themes'), array('..', '.')) as $theme) {
        if (file_exists(APPPATH . 'themes/' . $theme . '/settings.json'))
          $themes[$theme] = json_decode(file_get_contents(APPPATH . 'themes/' . $theme . '/settings.json'), true);
      }
      file_put_contents(APPPATH . 'cache/themes.json', json_encode($themes));
    }

    $this->themes = json_decode(file_get_contents(APPPATH . 'cache/themes.json'), true);
  }

  /**
   * Get all downloadable themes
   *
   * @return array
   */
  public function getAvailableThemes()
  {
    if (empty($this->available_themes)) {
      $this->limpid->load->library('API_Manager', null, 'APIManager');
      $this->available_themes = $this->limpid->APIManager->getAvailableThemes();
    }

    return $this->available_themes;
  }

  /**
   * Get an available theme by uri
   *
   * @param string $uri
   *
   * @return mixed|false
   */
  public function getAvailableTheme($uri)
  {
    return isset($this->available_themes[$uri]) ? $this->available_themes[$uri] : false;
  }

  /**
   * Get all installed themes
   *
   * @return array
   */
  public function getThemes()
  {
    return $this->themes;
  }

  /**
   * Get an installed theme by uri
   *
   * @param string $uri
   *
   * @return mixed|false
   */
  public function getTheme($uri)
  {
    return isset($this->themes[$uri]) ? $this->themes[$uri] : false;
  }

  /**
   * Get the theme's config
   *
   * @return mixed|null
   */
  public function getThemeConfig()
  {
    if (file_exists(APPPATH . 'themes/' . $this->getEnabledTheme()['uri'] . '/config.json'))
      return json_decode(file_get_contents(APPPATH . 'themes/' . $this->getEnabledTheme()['uri'] . '/config.json'), true);

    return null;
  }

  /**
   * Get currently enabled theme
   *
   * @return array|null
   */
  function getEnabledTheme()
  {
    if (!empty($this->enabled_theme))
      return $this->enabled_theme;

    foreach ($this->themes as $theme) {
      if ($theme['enabled']) {
        $this->enabled_theme = $theme;
        break;
      }
    }
    if (empty($this->enabled_theme))
      return null;

    return $this->enabled_theme;
  }

  /**
   * Enable a theme by uri
   *
   * @param $uri
   *
   * @return bool|null
   */
  function enableTheme($uri)
  {
    if (empty($uri))
      return null;

    foreach ($this->themes as $theme) {
      if ($theme['uri'] == $uri) {
        // Disable active theme
        $themes = $this->getThemes();
        foreach ($themes as $item) {
          if ($item['enabled']) {
            $item['enabled'] = false;
            file_put_contents(APPPATH . 'themes/' . $item['uri'] . '/settings.json', json_encode($item));
            break;
          }
        }

        // Enable requested theme
        $theme['enabled'] = true;
        if (unlink(APPPATH . 'cache/themes.json') && file_put_contents(APPPATH . 'themes/' . $theme['uri'] . '/settings.json', json_encode($theme))) {
          $this->limpid->config->edit_item('theme', $uri, 'config');
          $this->updateThemeList();
          return true;
        }
      }
    }

    return false;
  }

  /**
   * Install a theme by uri
   *
   * @param $uri
   *
   * @return bool|null
   */
  function installTheme($uri)
  {
    $theme = $this->getAvailableTheme($uri);
    if (!isset($theme))
      return false;

    $this->limpid->load->library('API_Manager', null, 'APIManager');
    if ($path = $this->limpid->APIManager->downloadTheme($theme['uri'])) {
      $zip = new ZipArchive;
      if ($zip->open($path) === true) {
        $zip->extractTo(APPPATH . 'themes/');
        $zip->close();

        $this->limpid->load->helper('file');
        delete_files(APPPATH . 'tmp', true);

        rename(APPPATH . 'themes/' . $uri . '/_assets', './assets/' . $uri);
        rename(APPPATH . 'themes/' . $uri . '/_langs', APPPATH . 'language/');

        $this->updateThemeList();

        return true;
      }
    }

    return false;
  }


  /**
   * Uninstall a theme by uri
   *
   * @param $uri
   *
   * @return bool|null
   */
  function uninstallTheme($uri)
  {
    if (empty($uri))
      return null;

    $this->limpid->load->helper('file');
    if (delete_files(APPPATH . 'themes/' . $uri, true) && rmdir(APPPATH . 'themes/' . $uri) && delete_files('./assets/' . $uri, true) && rmdir('./assets/' . $uri))
      return true;

    return false;
  }

  /**
   * Updates a theme by uri
   *
   * @param string $uri
   *
   * @return bool|null
   */
  function updateTheme($uri)
  {
    if (empty($uri))
      return null;

    $this->limpid->load->library('API_Manager', null, 'APIManager');
    if ($path = $this->limpid->APIManager->downloadTheme($uri)) {
      $zip = new ZipArchive;
      if ($zip->open($path) === true) {
        $zip->extractTo(APPPATH . 'themes/');
        $zip->close();

        $this->limpid->load->helper('file');
        delete_files(APPPATH . 'tmp', true);

        rename(APPPATH . 'themes/' . $uri . '/_assets', './assets/' . $uri);
        rename(APPPATH . 'themes/' . $uri . '/_langs', APPPATH . 'language/');

        $this->updateThemeList();

        return true;
      }
    }

    return false;
  }

  /**
   * Check if a theme is enabled by uri
   *
   * @param $uri
   *
   * @return mixed
   */
  function isEnabled($uri)
  {
    return $this->themes[$uri]['enabled'];
  }

  /**
   * Checks if a theme needs to be updated
   *
   * @param string $uri
   *
   * @return bool
   */
  function doesNeedToUpdate($uri)
  {
    if (!isset($this->available_themes)) $this->getAvailableThemes();

    if (!isset($this->themes[$uri]) || !isset($this->available_themes[$uri])) {
      return false;
    }

    return version_compare($this->themes[$uri]['version'], $this->available_themes[$uri]['version'], '<');
  }
}

/* End of file Themes_Manager.php */
/* Location: ./application/libraries/Themes_Manager.php */