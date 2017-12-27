<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * News manager
 *
 */
class News_Manager
{
  /**
   * News_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    $this->limpid->load->model('News_model', 'news');
  }

  /**
   * Count all news
   *
   * @return int
   */
  function countTotalNews()
  {
    return $this->limpid->news->countAll();
  }

  /**
   * Get news with LIMIT statement
   *
   * @param int $limit
   * @param int $offset
   *
   * @return array|null
   */
  function getNewsLimited($limit, $offset)
  {
    // Simple check
    if (!is_int($limit) || !is_int($offset)) {
      return null;
    }

    if ($page = $this->limpid->news->getAllLimitedOrdered($limit, $offset, 'edited_at, created_at', 'DESC'))
      return $page;

    return null;
  }

  /**
   * Create news
   *
   * @param string $title
   * @param string $slug
   * @param string $content
   * @param int $author_id
   * @param bool $active
   *
   * @return bool|null
   */
  function addNews($title, $slug, $content, $author_id, $active = true)
  {
    // Simple check
    if (empty($title) || empty($slug) || empty($author_id)) {
      return null;
    }

    // Set data structure
    $data = array(
      'title' => $title,
      'slug' => $slug,
      'content' => $content,
      'author_id' => $author_id,
      'active' => $active
    );

    if ($news = $this->limpid->news->insert($data))
      return $news;

    return null;
  }

  /**
   * Edit news' data
   *
   * @param int $id
   * @param array $data
   *
   * @return bool|null
   */
  function editNews($id, $data = [])
  {
    // Simple check
    if (empty($id) || empty($data)) {
      return null;
    }

    if ($news = (array)$this->limpid->news->find($id)) {
      $data = array_merge($news, $data);
      if ($news = $this->limpid->news->update($id, $data))
        return $news;
    }

    return null;
  }

  /**
   * Delete news
   *
   * @param int $id
   *
   * @return bool|null
   */
  function deleteNews($id)
  {
    // Simple check
    if (empty($id)) {
      return null;
    }

    if ($news = $this->limpid->news->delete($id))
      return $news;

    return null;
  }

  /**
   * Get all news
   *
   * @return array|null
   */
  function getAllNews()
  {
    if ($news = $this->limpid->news->getAll())
      return $news;

    return null;
  }

  /**
   * Get news by id
   *
   * @param int $id
   *
   * @return object|null
   */
  function getNewsByID($id)
  {
    // Simple check
    if (empty($id)) {
      return null;
    }

    if ($news = $this->limpid->news->find($id))
      return $news;

    return null;
  }

  /**
   * Get news by slug
   *
   * @param string $slug
   *
   * @return object|null
   */
  function getNewsBySlug($slug)
  {
    // Simple check
    if (empty($slug)) {
      return null;
    }

    if ($news = $this->limpid->news->find(['slug' => $slug]))
      return $news;

    return null;
  }
}

/* End of file News_Manager.php */
/* Location: ./application/libraries/News_Manager.php */