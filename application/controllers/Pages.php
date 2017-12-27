<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pages CMS class
 *
 * @property CI_Lang $lang
 * @property CI_Input $input
 * @property CI_Form_validation $form_validation
 * @property Auth_Manager $authManager
 * @property Pages_Manager $pagesManager
 * @property Twig $twig
 */
class Pages extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->library('Pages_Manager', null, 'pagesManager');
  }

  public function index()
  {
    $this->data['page_title'] = $this->lang->line('HOMEPAGE');
    $this->twig->display('pages/index', $this->data);
  }

  public function view($slug)
  {
    if ($this->data['page'] = $this->pagesManager->getPageBySlug($slug)) {
      if ($this->data['page']->active || $this->authManager->isPermitted($this->session->userdata('id'), 'PAGES__VIEW_DEACTIVATED')) {
        $this->data['page_title'] = $this->data['page']->title;
        // Render the view
        $this->twig->display('pages/view', $this->data);
      } else {
        // If page isn't active and user doesn't have permission to see it
        show_404();
      }
    } else {
      // If page was not found
      show_404();
    }
  }

  public function admin_add()
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'PAGES__ADD')) {
      $this->data['page_title'] = $this->lang->line('PAGE_CREATION');
      $this->load->helper('form');
      $this->load->library('form_validation');

      // Form rules check
      $this->form_validation->set_rules('title', $this->lang->line('TITLE'), 'required|min_length[3]|max_length[110]');
      $this->form_validation->set_rules('slug', $this->lang->line('SLUG'), 'required|min_length[1]|max_length[200]|regex_match[/^(?!-)((?:[a-z0-9]+-?)+)(?<!-)$/]|is_unique[pages.slug]');
      $this->form_validation->set_rules('content', $this->lang->line('CONTENT'), 'required|min_length[6]');
      $this->form_validation->set_rules('reachable', $this->lang->line('REACHABLE') . ' ?', 'in_list[true,]');
      // Custom error message
      $this->form_validation->set_message('regex_match', $this->lang->line('FORM_VALIDATION_REGEX_SLUG'));

      // If check passed
      if ($this->form_validation->run()) {
        if ($this->pagesManager->addPage($this->input->post('title'), $this->input->post('slug'), $this->input->post('content'), $this->input->post('reachable') ? true : false)) {
          // If news adding succeed
          $this->session->set_flashdata('success', $this->lang->line('ADD_SUCCEEDED'));
          redirect(route('pages/admin_manage'));
        } else {
          // If news adding failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
          redirect(current_url());
        }
      } else {
        // Render the view
        $this->twig->display('admin/pages/add', $this->data);
      }
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));

      redirect(route('admin/admin_index'));
    }
  }

  public function admin_manage()
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'PAGES__MANAGE')) {
      $this->data['page_title'] = $this->lang->line('PAGES_MANAGEMENT');
      $this->data['pages'] = $this->pagesManager->getPages();

      // Render the view
      $this->twig->display('admin/pages/manage', $this->data);
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));

      redirect(route('admin/admin_index'));
    }
  }

  public function admin_edit($id)
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'PAGES__EDIT')) {
      if ($this->data['page'] = $this->pagesManager->getPageByID($id)) {
        $this->data['page_title'] = $this->lang->line('PAGE_EDITION');
        $this->load->helper('form');
        $this->load->library('form_validation');

        // Add rule if input slug is different to initial slug
        $is_unique_rule = '';
        if ($this->data['page']->slug != $this->input->post('slug'))
          $is_unique_rule = '|is_unique[pages.slug]';

        // Form rules check
        $this->form_validation->set_rules('title', $this->lang->line('TITLE'), 'required|min_length[3]|max_length[110]');
        $this->form_validation->set_rules('slug', $this->lang->line('SLUG'), 'required|min_length[1]|max_length[200]|regex_match[/^(?!-)((?:[a-z0-9]+-?)+)(?<!-)$/]' . $is_unique_rule);
        $this->form_validation->set_rules('content', $this->lang->line('CONTENT'), 'required|min_length[6]');
        $this->form_validation->set_rules('reachable', $this->lang->line('REACHABLE') . ' ?', 'in_list[true,]');
        // Custom error message
        $this->form_validation->set_message('regex_match', $this->lang->line('FORM_VALIDATION_REGEX_SLUG'));

        // If check passed
        if ($this->form_validation->run()) {
          $data = array(
            'title' => $this->input->post('title'),
            'slug' => $this->input->post('slug'),
            'content' => $this->input->post('content'),
            'active' => $this->input->post('reachable') ? true : false
          );
          if ($this->pagesManager->editPage($id, $data))
            // If page editing succeed
            $this->session->set_flashdata('success', $this->lang->line('EDIT_SUCCEEDED'));
          else
            // If page editing failed
            $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));

          redirect(current_url());
        } else {
          // Render the view
          $this->twig->display('admin/pages/edit', $this->data);
        }
      } else {
        // If the page was not found
        $this->session->set_flashdata('error', $this->lang->line('PAGE_NOT_FOUND'));
        redirect(route('pages/admin_manage'));
      }
    } else {
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(route('admin/admin_index'));
    }
  }

  public function admin_delete($id)
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'PAGES__DELETE')) {
      if ($this->pagesManager->deletePage($id))
        // If page deleting succeed
        $this->session->set_flashdata('success', $this->lang->line('DELETE_SUCCEEDED'));
      else
        // If page deleting failed
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));

      redirect(route('pages/admin_manage'));
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));

      redirect(route('admin/admin_index'));
    }
  }

}
