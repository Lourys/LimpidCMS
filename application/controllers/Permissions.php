<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Permissions CMS class
 *
 * @property CI_Lang $lang
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property Permissions_Manager $permissionsManager
 * @property Groups_Manager $groupsManager
 */
class Permissions extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->library('Permissions_Manager', null, 'permissionsManager');
    $this->load->library('Groups_Manager', null, 'groupsManager');
  }


  public function admin_manage($group_id)
  {
    if ($authorized = $this->authManager->isPermitted($this->session->userdata('id'), 'PERMISSIONS_MANAGE')) {
      if ($this->data['group'] = $this->groupsManager->getGroup($group_id)) {
        $this->data['group']['permissions'] = explode(', ', $this->data['group']['permissions']);

        $this->data['page_title'] = $this->lang->line('PERMISSIONS_MANAGEMENT');

        $this->load->helper('form');
        $this->load->library('form_validation');
        $detailedPermissions = $this->permissionsManager->getPermissions();

        $permissions = '';
        for ($i = 0; $i < count($detailedPermissions); $i++) {
          $permissions .= $detailedPermissions[$i]['name'] . ',';
          $this->data['permissions'][explode('_', $detailedPermissions[$i]['name'])[0]][]['name'] = $detailedPermissions[$i]['name'];
        }

        // Form rules check
        $this->form_validation->set_rules('permissions', $this->lang->line('PERMISSIONS'), 'in_list[' . $permissions . ']');

        // If check passed
        if ($this->form_validation->run()) {
          $data = array(
            'permissions' => is_array($this->input->post('permissions')) ? implode(', ', $this->input->post('permissions')) : ''
          );

          if ($this->groupsManager->editGroup($group_id, $data)) {
            // If permission edition succeed
            $this->session->set_flashdata('success', $this->lang->line('PERMISSIONS_EDIT_SUCCEEDED'));
            redirect(route('permissions/admin_manage/' . $group_id));
          } else {
            // If permission edition failed
            $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
            redirect(current_url());
          }
        } else {
          // Render the view
          $this->twig->display('admin/permissions/manage', $this->data);
        }
      } else {
        // If the group was not found
        $this->session->set_flashdata('error', $this->lang->line('GROUP_NOT_FOUND'));
        redirect(route('groups/admin_manage'));
      }
    } else {
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(route('admin/admin_index'), 'auto', $authorized === false ? 403 : 401);
    }
  }

}

