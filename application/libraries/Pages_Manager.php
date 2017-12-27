<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pages manager
 *
 */
class Pages_Manager
{
  /**
   * Pages_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    $this->limpid->load->model('Pages_model', 'pages');
  }

  /**
   * Create page
   *
   * @param string $title
   * @param string $slug
   * @param string $content
   * @param bool $active
   *
   * @return bool|null
   */
  function addPage($title, $slug, $content, $active = true)
  {
    // Simple check
    if (empty($title) || empty($slug)) {
      return null;
    }

    // Set data structure
    $data = array(
      'title' => $title,
      'slug' => $slug,
      'content' => $content,
      'active' => $active
    );

    if ($page = $this->limpid->pages->insert($data))
      return $page;

    return null;
  }

  /**
   * Edit page's data
   *
   * @param int $id
   * @param array $data
   *
   * @return bool|null
   */
  function editPage($id, $data = [])
  {
    // Simple check
    if (empty($id) || empty($data)) {
      return null;
    }

    if ($page = (array)$this->limpid->pages->find($id)) {
      $data = array_merge($page, $data);
      if ($page = $this->limpid->pages->update($id, $data))
        return $page;
    }

    return null;
  }

  /**
   * Delete page
   *
   * @param int $id
   *
   * @return bool|null
   */
  function deletePage($id)
  {
    // Simple check
    if (empty($id)) {
      return null;
    }

    if ($page = $this->limpid->pages->delete($id))
      return $page;

    return null;
  }

  /**
   * Get pages
   *
   * @return array|null
   */
  function getPages()
  {
    if ($page = $this->limpid->pages->getAll())
      return $page;

    return null;
  }

  /**
   * Get page by id
   *
   * @param int $id
   *
   * @return object|null
   */
  function getPageByID($id)
  {
    // Simple check
    if (empty($id)) {
      return null;
    }

    if ($page = $this->limpid->pages->find($id))
      return $page;

    return null;
  }

  /**
   * Get page by slug
   *
   * @param string $slug
   *
   * @return object|null
   */
  function getPageBySlug($slug)
  {
    // Simple check
    if (empty($slug)) {
      return null;
    }

    if ($page = $this->limpid->pages->find(['slug' => $slug]))
      return $page;

    return null;
  }
}

/* End of file Pages_Manager.php */
/* Location: ./application/libraries/Pages_Manager.php */