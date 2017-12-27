<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Themes manager
 *
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
    if (empty($this->available_themes))
      $this->available_themes = json_decode(file_get_contents('http://localhost/codeigniter/public/availableThemes.json'));
  }

  /**
   * Update the themes list by generating a json file if doesn't exists
   *
   * @return void
   */
  protected function updateThemeList()
  {
    $themes = array();
    if (!file_exists(APPPATH . 'cache/themes.json') || ENVIRONMENT == 'development') {
      foreach (array_diff(scandir(APPPATH . 'themes'), array('..', '.')) as $theme) {
        if (file_exists(APPPATH . 'themes/' . $theme . '/settings.json'))
          $themes[$theme] = json_decode(file_get_contents(APPPATH . 'themes/' . $theme . '/settings.json'));
      }
      file_put_contents(APPPATH . 'cache/themes.json', json_encode($themes));
    }

    $this->themes = json_decode(file_get_contents(APPPATH . 'cache/themes.json'));
  }

  /**
   * Get all downloadable themes
   *
   * @return array
   */
  public function getAvailableThemes()
  {
    return (array)$this->available_themes;
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
    return isset($this->available_themes->$uri) ? $this->available_themes->$uri : false;
  }

  /**
   * Get all installed themes
   *
   * @return array
   */
  public function getThemes()
  {
    return (array)$this->themes;
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
    return isset($this->themes->$uri) ? $this->themes->$uri : false;
  }

  /**
   * Get the theme's config
   *
   * @return mixed|null
   */
  public function getThemeConfig()
  {
    if (file_exists(APPPATH . 'themes/' . $this->getEnabledTheme()->uri . '/config.json'))
      return json_decode(file_get_contents(APPPATH . 'themes/' . $this->getEnabledTheme()->uri . '/config.json'), true);

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
      if ($theme->enabled) {
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
      if ($theme->uri == $uri) {
        // Disable active theme
        $themes = $this->getThemes();
        foreach ($themes as $item) {
          if ($item->enabled) {
            $item->enabled = false;
            file_put_contents(APPPATH . 'themes/' . $item->uri . '/settings.json', json_encode($item));
            break;
          }
        }

        // Enable requested theme
        $theme->enabled = true;
        if (unlink(APPPATH . 'cache/themes.json') && file_put_contents(APPPATH . 'themes/' . $theme->uri . '/settings.json', json_encode($theme))) {
          $this->limpid->config->load('cms_settings');
          $this->limpid->config->edit_item('theme', $uri, 'cms_settings');
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
    if (empty($uri))
      return null;

    if (file_put_contents(APPPATH . 'tmp/theme.zip', fopen('http://localhost/codeigniter/public/' . $uri . '.zip', 'r'))) {
      $zip = new ZipArchive;
      if ($zip->open(APPPATH . 'tmp/theme.zip') === TRUE) {
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
   * Check if a theme is enabled by uri
   *
   * @param $uri
   *
   * @return mixed
   */
  function isEnabled($uri)
  {
    return $this->themes->$uri->enabled;
  }
}

/* End of file Themes_Manager.php */
/* Location: ./application/libraries/Themes_Manager.php */