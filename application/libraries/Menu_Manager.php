<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Menu manager
 *
 * @property CMS_Controller limpid
 */
class Menu_Manager
{
  /**
   * Menu_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    $this->limpid->load->model('Menu_model', 'menu');
  }

  /**
   * Count all rows
   *
   * @return int
   */
  public function countAll()
  {
    return $this->limpid->menu->count_rows();
  }

  /**
   * Get all principal links
   *
   * @return array|null
   */
  function getLinks()
  {
    if ($links = $this->limpid->menu->order_by('position')->fields('id, title, url, is_dropdown')->get_all(['parent_id' => null]))
      return $links;

    return null;
  }

  /**
   * Get all sublinks
   *
   * @return array|null
   */
  function getSublinks()
  {
    if ($links = $this->limpid->menu->order_by('position')->fields('id, title, url, parent_id, is_divider')->get_all(['parent_id !=' => null]))
      return $links;

    return null;
  }

  /**
   * Add normal link to the menu
   *
   * @param string $title
   * @param string $url
   *
   * @return bool|int|null
   */
  function addNormalLink($title, $url)
  {
    // Simple check
    if (empty($title) || empty($url)) {
      return null;
    }

    // Default values
    $data = [
      'title' => $title,
      'url' => $url,
      'parent_id' => null,
      'is_dropdown' => false,
      'is_divider' => false,
      'position' => $this->countAll()
    ];

    if ($link = $this->limpid->menu->insert($data))
      return $link;

    return null;
  }

  /**
   * Add dropdown link to the menu
   *
   * @param string $title
   *
   * @return bool|int|null
   */
  function addDropdownLink($title)
  {
    // Simple check
    if (empty($title)) {
      return null;
    }

    // Default values
    $data = [
      'title' => $title,
      'url' => '#',
      'parent_id' => null,
      'is_dropdown' => true,
      'is_divider' => false,
      'position' => $this->countAll()
    ];

    if ($link = $this->limpid->menu->insert($data))
      return $link;

    return null;
  }

  /**
   * Add sublink to a dropdown
   *
   * @param string $title
   * @param string $url
   * @param int $parent_id
   *
   * @return bool|int|null
   */
  function addSublink($title, $url, $parent_id)
  {
    // Simple check
    if (empty($title) || empty($url) || empty($parent_id)) {
      return null;
    }

    // Default values
    $data = [
      'title' => $title,
      'url' => $url,
      'parent_id' => $parent_id,
      'is_dropdown' => false,
      'is_divider' => false,
      'position' => $this->countAll()
    ];

    if ($link = $this->limpid->menu->insert($data))
      return $link;

    return null;
  }

  /**
   * Add sublink divider to a dropdown
   *
   * @param int $parent_id
   *
   * @return bool|int|null
   */
  function addSublinkDivider($parent_id)
  {
    // Simple check
    if (empty($parent_id)) {
      return null;
    }

    // Default values
    $data = [
      'title' => '',
      'url' => '',
      'parent_id' => $parent_id,
      'is_dropdown' => false,
      'is_divider' => true,
      'position' => $this->countAll()
    ];

    if ($link = $this->limpid->menu->insert($data))
      return $link;

    return null;
  }

  /**
   * Update link position in the menu
   *
   * @param int $id
   * @param int $position
   *
   * @return string|array|null
   */
  function editLinksPosition($id, $position)
  {
    // Simple check
    if (empty($id) || !is_int($position)) {
      return null;
    }

    if ($link = $this->limpid->menu->update(['position' => $position], $id))
      return $link;

    return null;
  }

  /**
   * Update link's data
   *
   * @param int $link_id
   * @param array $data
   *
   * @return bool|null
   */
  function editLink($link_id, $data = [])
  {
    // Simple check
    if (empty($link_id) || empty($data)) {
      return null;
    }

    if ($link = $this->limpid->menu->update($data, $link_id))
      return $link;

    return null;
  }

  /**
   * Delete a link
   *
   * @param int $link_id
   *
   * @return Menu_model|null
   */
  function deleteLink($link_id)
  {
    // Simple check
    if (empty($link_id)) {
      return null;
    }

    if ($link = $this->limpid->menu->where(['id' => $link_id, 'parent_id' => $link_id], 'OR'))
      return $link;

    return null;
  }


}

/* End of file Menu_Manager.php */
/* Location: ./application/libraries/Menu_Manager.php */