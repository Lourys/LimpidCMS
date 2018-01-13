<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * News manager
 *
 */
class News_Manager
{
  private $limpid;

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
  public function countAllNews()
  {
    return $this->limpid->news->count_rows();
  }

  /**
   * Count all news for a date
   *
   * @param string $date
   *
   * @return int
   */
  public function countAllNewsForDate($date)
  {
    // Simple check
    if (empty($date)) {
      return null;
    }

    return $this->limpid->news->where('created_at', '>=', $date)->count_rows();
  }

  /**
   * Get news with LIMIT statement
   *
   * @param int $nb
   *
   * @return array|null
   */
  function getNewsPaginated($nb, $page)
  {
    // Simple check
    if (!is_int($nb)) {
      return null;
    }

    $total = $this->limpid->news->count_rows();
    if ($news = $this->limpid->news->with_author('fields:username, avatar')->fields('title, slug, content, created_at, edited_at')->order_by('edited_at, created_at', 'DESC')->paginate($nb, $total, $page))
      return $news;

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

    if ($news = $this->limpid->news->update($data, $id))
      return $news;

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
    if ($news = $this->limpid->news->get_all())
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

    if ($news = $this->limpid->news->get($id))
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

    if ($news = $this->limpid->news->with_author([
      'fields' => 'username, avatar, biography',
      'with' => ['relation' => 'group', 'fields' => 'name, color']
    ])->fields('title, content, created_at, edited_at, active')->get(['slug' => $slug]))

      return $news;

    return null;
  }
}

/* End of file News_Manager.php */
/* Location: ./application/libraries/News_Manager.php */