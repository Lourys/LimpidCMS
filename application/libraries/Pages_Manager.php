<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pages manager
 *
 * @property CMS_Controller limpid
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
   * Count all pages
   *
   * @return int
   */
  public function countAllPages()
  {
    return $this->limpid->pages->count_rows();
  }

  /**
   * Count all news for a date
   *
   * @param string $date
   *
   * @return int
   */
  public function countAllPagesForDate($date)
  {
    // Simple check
    if (empty($date)) {
      return null;
    }

    return $this->limpid->pages->where('created_at', '>=', $date)->count_rows();
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

    if ($page = $this->limpid->pages->update($data, $id))
      return $page;

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
    if ($page = $this->limpid->pages->get_all())
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

    if ($page = $this->limpid->pages->get($id))
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

    if ($page = $this->limpid->pages->get(['slug' => $slug]))
      return $page;

    return null;
  }
}

/* End of file Pages_Manager.php */
/* Location: ./application/libraries/Pages_Manager.php */