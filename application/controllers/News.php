<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * News CMS class
 *
 * @property CI_Lang $lang
 * @property CI_Input $input
 * @property CI_Pagination $pagination
 * @property CI_Form_validation $form_validation
 * @property Auth_Manager $authManager
 * @property News_Manager $newsManager
 * @property Twig $twig
 */
class News extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->library('News_Manager', null, 'newsManager');
  }

  public function view($slug)
  {
    if ($this->data['news'] = $this->newsManager->getNewsBySlug($slug)) {
      if ($this->data['news']->active || $this->authManager->isPermitted($this->session->userdata('id'), 'NEWS__VIEW_DEACTIVATED')) {
        $this->load->library('Users_Manager', null, 'usersManager');
        $this->load->library('Groups_Manager', null, 'groupsManager');
        $this->data['author'] = $this->usersManager->getUser($this->data['news']->author_id);
        $this->data['author']->group = $this->groupsManager->getGroup($this->data['author']->group_id);
        $this->data['page_title'] = $this->data['news']->title;

        // Render the view
        $this->twig->display('news/view', $this->data);
      } else {
        // If news isn't active and user doesn't have permission to see it
        show_404();
      }
    } else {
      // If news was not found
      show_404();
    }
  }

  public function listing($page)
  {
    require(APPPATH . 'third_party/Twig_Extensions/Text_Extension.php');
    $this->twig->getTwig()->addExtension(new Text_Extension());

    if ($this->data['news'] = $this->newsManager->getNewsLimited(12 * (intval($page) - 1), 12)) {
      $this->data['page_title'] = $this->lang->line('NEWS');

      // Load pagination library
      $this->load->library('pagination');
      $config['base_url'] = route('news/listing');
      $config['total_rows'] = $this->newsManager->countTotalNews();
      $config['per_page'] = 12;
      $config['use_page_numbers'] = TRUE;
      $config['prefix'] = 'page/';
      $config['full_tag_open'] = '<div class="text-center"><ul class="pagination">';
      $config['full_tag_close'] = '</ul></div>';
      $config['first_tag_open'] = '<li>';
      $config['first_tag_close'] = '</li>';
      $config['last_tag_open'] = '<li>';
      $config['last_tag_close'] = '</li>';
      $config['next_tag_open'] = '<li>';
      $config['next_tag_close'] = '</li>';
      $config['prev_tag_open'] = '<li>';
      $config['prev_tag_close'] = '</li>';
      $config['cur_tag_open'] = '<li class="active"><a href="#">';
      $config['cur_tag_close'] = '</a></li>';
      $config['num_tag_open'] = '<li>';
      $config['num_tag_close'] = '</li>';

      $this->pagination->initialize($config);

      $this->twig->display('news/listing', $this->data);
    } else {
      show_404();
    }
  }

  public function admin_add()
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'NEWS__ADD')) {
      $this->data['page_title'] = $this->lang->line('NEWS_CREATION');
      $this->load->helper('form');
      $this->load->library('form_validation');

      // Form rules check
      $this->form_validation->set_rules('title', $this->lang->line('NEWS_TITLE'), 'required|min_length[3]|max_length[110]');
      $this->form_validation->set_rules('slug', $this->lang->line('SLUG'), 'required|min_length[1]|max_length[200]|regex_match[/^(?!-)((?:[a-z0-9]+-?)+)(?<!-)$/]');
      $this->form_validation->set_rules('content', $this->lang->line('CONTENT'), 'required|min_length[6]');
      $this->form_validation->set_rules('active', $this->lang->line('ACTIVE') . ' ?', 'in_list[true,]');
      // Custom error message
      $this->form_validation->set_message('regex_match', $this->lang->line('FORM_VALIDATION_REGEX_SLUG'));

      // If check passed
      if ($this->form_validation->run()) {
        if ($this->newsManager->addNews($this->input->post('title'), $this->input->post('slug'), $this->input->post('content'), $this->session->userdata('id'), $this->input->post('active') ? true : false)) {
          // If news adding succeed
          $this->session->set_flashdata('success', $this->lang->line('NEWS_ADD_SUCCEEDED'));
          redirect(route('news/admin_manage'));
        } else {
          // If news adding failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
          redirect(current_url());
        }
      } else {
        // Render the view
        $this->twig->display('admin/news/add', $this->data);
      }
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(route('admin/admin_index'));
    }
  }

  public function admin_manage()
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'NEWS__MANAGE')) {
      $this->data['page_title'] = $this->lang->line('NEWS_MANAGEMENT');
      $this->data['news'] = $this->newsManager->getAllNews();

      // Render the view
      $this->twig->display('admin/news/manage', $this->data);
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(route('admin/index'));
    }
  }

  public function admin_edit($id)
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'NEWS__EDIT')) {
      if ($this->data['news'] = $this->newsManager->getNewsByID($id)) {
        $this->data['page_title'] = $this->lang->line('NEWS_EDITION');
        $this->load->helper('form');
        $this->load->library('form_validation');

        // Form rules check
        $this->form_validation->set_rules('title', $this->lang->line('TITLE'), 'required|min_length[3]|max_length[110]');
        $this->form_validation->set_rules('slug', $this->lang->line('SLUG'), 'required|min_length[1]|max_length[200]|regex_match[/^(?!-)((?:[a-z0-9]+-?)+)(?<!-)$/]');
        $this->form_validation->set_rules('content', $this->lang->line('CONTENT'), 'required|min_length[6]');
        $this->form_validation->set_rules('active', $this->lang->line('ACTIVE') . ' ?', 'in_list[true,]');
        // Custom error message
        $this->form_validation->set_message('regex_match', $this->lang->line('FORM_VALIDATION_REGEX_SLUG'));

        // If check passed
        if ($this->form_validation->run()) {
          $data = array(
            'title' => $this->input->post('title'),
            'slug' => $this->input->post('slug'),
            'content' => $this->input->post('content'),
            'active' => $this->input->post('active') ? true : false,
            'edited_at' => date('Y-m-d H:i:s')
          );
          if ($this->newsManager->editNews($id, $data))
            // If news editing succeed
            $this->session->set_flashdata('success', $this->lang->line('NEWS_EDIT_SUCCEEDED'));
          else
            // If news editing failed
            $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));

          redirect(current_url());
        } else {
          // Render the view
          $this->twig->display('admin/news/edit', $this->data);
        }
      } else {
        // If the news was not found
        $this->session->set_flashdata('error', $this->lang->line('NEWS_NOT_FOUND'));
        redirect(route('news/admin_manage'));
      }
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(route('admin/admin_index'));
    }
  }

  public function admin_delete($id)
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'NEWS__DELETE')) {
      if ($this->newsManager->deleteNews($id))
        // If news deleting succeed
        $this->session->set_flashdata('success', $this->lang->line('NEWS_DELETE_SUCCEEDED'));
      else
        // If news deleting failed
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));

      redirect(route('news/admin_manage'));
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));

      redirect(route('admin/admin_index'));
    }
  }

}
