<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Groups CMS class
 *
 * @property CI_Lang $lang
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property Groups_Manager $groupsManager
 * @property Permissions_Manager $permissionsManager
 * @property Users_Manager $usersManager
 */
class Groups extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->library('Groups_Manager', null, 'groupsManager');
  }


  public function admin_add()
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'GROUPS__ADD')) {
      $this->data['page_title'] = $this->lang->line('GROUP_CREATION');
      $this->load->helper('form');
      $this->load->library('form_validation');

      $this->load->library('Permissions_Manager', null, 'permissionsManager');
      $this->data['permissions'] = $this->permissionsManager->getPermissions();

      // Build the permissions list
      $permissions = '';
      for ($i = 0; $i < count($this->data['permissions']); $i++) {
        $permissions .= $this->data['permissions'][$i]->name . ',';
        $this->data['permissions'][$i]->value = $this->data['permissions'][$i]->name;
        $this->data['permissions'][$i]->name = $this->lang->line($this->data['permissions'][$i]->name);
      }

      // Form rules check
      $this->form_validation->set_rules('name', $this->lang->line('GROUP_NAME'), 'required|min_length[3]|max_length[60]');
      $this->form_validation->set_rules('color', $this->lang->line('GROUP_COLOR'), 'required|exact_length[7]');
      $this->form_validation->set_rules('permissions', $this->lang->line('GROUP_PERMISSIONS'), 'in_list[' . $permissions . ']');

      // If check passed
      if ($this->form_validation->run()) {
        if ($this->groupsManager->createGroup($this->input->post('name'), $this->input->post('color'), $this->input->post('permissions'))) {
          // If group creation succeed
          $this->session->set_flashdata('success', $this->lang->line('GROUPS_ADD_SUCCEEDED'));
          redirect(route('groups/manage'));
        } else {
          // If group creation failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
          redirect(current_url());
        }
      } else {
        // Render the view
        $this->twig->display('admin/groups/add', $this->data);
      }
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));

      redirect(route('admin/admin_index'));
    }
  }


  public function admin_manage()
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'GROUPS__MANAGE')) {
      $this->data['page_title'] = $this->lang->line('GROUPS_MANAGEMENT');
      $this->data['groups'] = $this->groupsManager->getGroups();

      // Render the view
      $this->twig->display('admin/groups/manage', $this->data);
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));

      redirect(route('admin/admin_index'));
    }
  }


  public function admin_edit($id)
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'GROUPS__EDIT')) {
      if ($this->data['group'] = $this->groupsManager->getGroup($id)) {
        $this->data['group']->permissions = explode(',', $this->data['group']->permissions);
        $this->data['page_title'] = $this->lang->line('GROUP_EDITION');
        $this->load->helper('form');
        $this->load->library('form_validation');

        // Form rules check
        $this->form_validation->set_rules('name', $this->lang->line('GROUP_NAME'), 'required|min_length[3]|max_length[60]');
        $this->form_validation->set_rules('color', $this->lang->line('GROUP_COLOR'), 'required|exact_length[7]');

        // If check passed
        if ($this->form_validation->run()) {
          $data = [
            'name' => $this->input->post('name'),
            'color' => $this->input->post('color')
          ];

          if ($this->groupsManager->editGroup($id, $data)) {
            // If group edition succeed
            $this->session->set_flashdata('success', $this->lang->line('GROUPS_EDIT_SUCCEEDED'));
            redirect(route('groups/admin_manage'));
          } else {
            // If group edition failed
            $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
            redirect(current_url());
          }
        } else {
          // Render the view
          $this->twig->display('admin/groups/edit', $this->data);
        }
      } else {
        // If the group was not found
        $this->session->set_flashdata('error', $this->lang->line('GROUP_NOT_FOUND'));
        redirect(route('groups/admin_manage'));
      }
    } else {
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(route('admin/admin_index'));
    }
  }

  public function admin_make_default($id)
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'GROUPS__EDIT')) {
      if ($this->groupsManager->getGroup($id)) {
        $default_group = $this->groupsManager->getDefaultGroup();
        if ($this->groupsManager->editGroup($default_group[0]->id, ['default_group' => false]) && $this->groupsManager->editGroup($id, ['default_group' => true])) {
          $this->session->set_flashdata('success', $this->lang->line('MAKE_DEFAULT_SUCCEED'));
        } else {
          // If group edition failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
        }

        redirect(route('groups/admin_manage'));
      } else {
        // If the group was not found
        $this->session->set_flashdata('error', $this->lang->line('GROUP_NOT_FOUND'));
        redirect(route('groups/admin_manage'));
      }
    } else {
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(route('admin/admin_index'));
    }
  }


  public function admin_delete($id)
  {
    if ($this->authManager->isPermitted($this->session->userdata('id'), 'GROUPS__DELETE')) {
      $this->load->library('Users_Manager', null, 'usersManager');
      // Replace actual users' group by default group
      $this->usersManager->editUsersWhere(['group_id' => $id], ['group_id' => $this->groupsManager->getDefaultGroup()[0]->id]);

      if ($this->groupsManager->deleteGroup($id))
        // If group deleting succeed
        $this->session->set_flashdata('success', $this->lang->line('GROUPS_DELETE_SUCCEEDED'));
      else
        // If group deleting failed
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));

      redirect(route('groups/admin_manage'));
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));

      redirect(route('admin/admin_index'));
    }
  }
}

