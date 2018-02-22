<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin CMS class
 *
 * @property CI_Lang $lang
 * @property News_Manager newsManager
 * @property Pages_Manager pagesManager
 */
class Admin extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->authManager->checkAccess('ADMIN__ACCESS');
  }

  public function admin_index()
  {
    $this->data['page_title'] = 'Tableau de bord';
    $this->load->library('Users_Manager', null, 'usersManager');
    $this->load->library('News_Manager', null, 'newsManager');
    $this->load->library('Pages_Manager', null, 'pagesManager');

    $date = new DateTime();
    $date->modify("-24 hours");
    $date24hBefore = $date->format('Y-m-d H:i:s');

    $this->data['widgets'] = [
      'users' => [
        'nb_total' => $this->usersManager->countAllUsers(),
        'nb_last_24h' => $this->usersManager->countAllUsersForDate($date24hBefore)
      ],
      'news' => [
        'nb_total' => $this->newsManager->countAllNews(),
        'nb_last_24h' => $this->newsManager->countAllNewsForDate($date24hBefore)
      ],
      'pages' => [
        'nb_total' => $this->pagesManager->countAllPages()
      ]
    ];
    $this->twig->display('admin/dashboard', $this->data);
  }
}
