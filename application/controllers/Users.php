<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Users CMS class
 *
 * @property CI_Lang $lang
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property Users_Manager $usersManager
 * @property Groups_Manager $groupsManager
 * @property CI_Upload $upload
 * @property CI_User_agent $agent
 */
class Users extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
    $this->load->library('Users_Manager', null, 'usersManager');
  }


  public function register()
  {
    if (!$this->authManager->isLogged()) {
      $this->data['page_title'] = $this->lang->line('REGISTER');

      $this->load->helper('form');
      $this->load->library('form_validation');
      $this->load->library('Groups_Manager', null, 'groupsManager');

      // Form rules check
      $this->form_validation->set_rules('username', $this->lang->line('USERNAME'), 'required|min_length[3]|max_length[25]|is_unique[users.username]|alpha_dash');
      $this->form_validation->set_rules('email', $this->lang->line('EMAIL'), 'required|min_length[3]|max_length[105]|valid_email|is_unique[users.email]');
      $this->form_validation->set_rules('password', $this->lang->line('PASSWORD'), 'required|min_length[' . $this->config->item('password')['min_length'] . ']|max_length[' . $this->config->item('password')['max_length'] . ']');
      $this->form_validation->set_rules('password_confirm', $this->lang->line('PASSWORD_CONFIRMATION'), 'required|matches[password_confirm]');
      if ($this->config->item('recaptchaEnabled'))
        $this->form_validation->set_rules('g-recaptcha-response', $this->lang->line('RECAPTCHA'), 'required');
      if ($this->config->item('limpidCaptchaEnabled') && function_exists('gd_info'))
        $this->form_validation->set_rules('captcha_answer', $this->lang->line('CAPTCHA'), 'required|regex_match[/' . $this->session->userdata('captchaCode') . '/]', [
          'regex_match' => $this->lang->line('INCORRECT_CAPTCHA')
        ]);

      // If check passed
      if ($this->form_validation->run()) {

        // reCAPTCHA validation
        if ($this->config->item('recaptchaEnabled') == true) {
          $recaptcha = new \ReCaptcha\ReCaptcha($this->config->item('recaptchaSettings')['secret_key']);
          $response = $recaptcha->verify($this->input->post('g-recaptcha-response'), $_SERVER['REMOTE_ADDR']);
          // If check failed
          if (!$response->isSuccess()) {
            $this->session->set_flashdata('error', $response->getErrorCodes());
            redirect(current_url());
            die();
          }
        }

        if ($user_id = $this->usersManager->registerUser($this->input->post('username'), $this->input->post('email'), password_hash($this->input->post('password'), PASSWORD_BCRYPT, ['cost' => 14]), $this->groupsManager->getDefaultGroup()[0]['id'], $_SERVER['REMOTE_ADDR'])) {
          // If registration succeed
          $this->emitter->emit('users.creation', [$user_id]);
          $this->session->set_flashdata('success', $this->lang->line('REGISTER_SUCCEEDED'));
          redirect(route('auth/login'));
        } else {
          // If registration failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
          redirect(current_url());
        }
      }

      // Render the view
      $this->twig->display('users/register', $this->data);
    } else {
      // If user is already logged
      $this->session->set_flashdata('error', $this->lang->line('ALREADY_LOGGED'));
      redirect(site_url());
    }
  }


  public function profile($username)
  {
    $username = urldecode($username);
    $this->data['page_title'] = $this->lang->line('PROFILE_OF') . ' ' . $username;
    $this->data['user'] = $this->usersManager->getUser(['username' => $username]);

    // Render the view
    $this->twig->display('users/profile', $this->data);
  }


  public function account()
  {
    if ($this->authManager->isLogged()) {
      $this->data['page_title'] = $this->lang->line('MY_ACCOUNT');

      $this->load->library('form_validation');
      $this->load->helper('form');

      $this->data['account_nav'] = $this->pluginsManager->getAccountNav();
      $this->data['user'] = $this->usersManager->getUser($this->session->userdata('id'));

      // Form rules check
      if ($this->input->post('username') != $this->data['user']['username'])
        $this->form_validation->set_rules('username', $this->lang->line('USERNAME'), 'required|min_length[3]|max_length[25]|is_unique[users.username]|alpha_dash');
      if ($this->input->post('email') != $this->data['user']['email'])
        $this->form_validation->set_rules('email', $this->lang->line('EMAIL'), 'required|min_length[3]|max_length[105]|valid_email|is_unique[users.email]');
      if ($this->input->post('password') != '' || $this->input->post('password_confirm') != '') {
        $this->form_validation->set_rules('password', $this->lang->line('PASSWORD'), 'required|min_length[' . $this->config->item('password')['min_length'] . ']|max_length[' . $this->config->item('password')['max_length'] . ']');
        $this->form_validation->set_rules('password_confirm', $this->lang->line('PASSWORD_CONFIRMATION'), 'required|matches[password_confirm]');
      }
      $this->form_validation->set_rules('biography', $this->lang->line('BIOGRAPHY'), 'max_length[150]');

      // If check passed
      if ($this->form_validation->run()) {
        $data = [
          'username' => $this->input->post('username'),
          'email' => $this->input->post('email'),
          'password' => $this->input->post('password') != ''
            ? password_hash($this->input->post('password'), PASSWORD_BCRYPT, ['cost' => 14])
            : $this->data['user']['password'],
          'biography' => $this->input->post('biography')
        ];
        if ($this->usersManager->editUser($this->session->userdata('id'), $data)) {
          // If user edit succeed
          $this->session->set_flashdata('success', $this->lang->line('SAVING_CHANGES_SUCCEEDED'));
        } else {
          // If user edit failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
        }

        redirect(route('users/account'));
      } else {
        // Render the view
        $this->twig->display('users/account', $this->data);
      }
    } else {
      // If user isn't logged
      $this->session->set_flashdata('error', $this->lang->line('LOGIN_NEEDED'));
      redirect(site_url(), 'auto', 401);
    }
  }


  public function edit_avatar()
  {
    if ($this->authManager->isLogged()) {
      $this->load->library('user_agent', null, 'agent');
      $redirect = $this->agent->referrer() ? $this->agent->referrer() : site_url();

      if ($authorized = $this->authManager->isPermitted($this->session->userdata('id'), 'MISCELLANEOUS__AVATAR_EDITION')) {
        $this->load->library('form_validation');
        $this->load->helper('form');

        $this->form_validation->set_rules('user_id', 'ID', 'required');

        if ($this->form_validation->run()) {
          // Demo specific
          $this->session->set_flashdata('error', 'Cette fonctionnalité est désactivée pour la version démo');
          redirect($redirect);

          // Check if user is permitted to edit others avatars
          if ($this->input->post('user_id') != $this->session->userdata('id') && !$authorized = $this->authManager->isPermitted($this->session->userdata('id'), 'USERS__EDIT')) {
            $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
            show_error($this->lang->line('PERMISSION_ERROR'), $authorized === false ? 403 : 401, $this->lang->line('ERROR_ENCOUNTERED'));
          }

          $extension = strtolower(pathinfo($_FILES['avatarUpload']['name'], PATHINFO_EXTENSION));
          $fileName = uniqid();

          $config['upload_path'] = './uploads/avatars/';
          $config['allowed_types'] = 'jpg|png|gif';
          $config['max_size'] = $this->config->item('avatar')['max_size'];
          $config['max_width'] = $this->config->item('avatar')['max_width'];
          $config['max_height'] = $this->config->item('avatar')['max_height'];
          $config['overwrite'] = true;
          $config['file_name'] = $fileName . '.' . $extension;

          $this->load->library('upload', $config);

          if ($this->upload->do_upload('avatarUpload')) {
            $user = $this->usersManager->getUser($this->input->post('user_id'));
            if (file_exists('./uploads/avatars/' . $user['avatar'])) {
              unlink('./uploads/avatars/' . $user['avatar']);
              $this->usersManager->editUser($this->input->post('user_id'), ['avatar' => $fileName . '.' . $extension]);
              $this->session->set_flashdata('success', $this->lang->line('AVATAR_SUCCESSFULLY_UPLOADED'));
            } else {
              $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
            }
          } else {
            $this->session->set_flashdata('error', $this->upload->display_errors());
          }

          redirect($redirect);
        }
      } else {
        $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
        show_error($this->lang->line('PERMISSION_ERROR'), $authorized === false ? 403 : 401, $this->lang->line('ERROR_ENCOUNTERED'));
      }
    } else {
      // If user isn't logged
      $this->session->set_flashdata('error', $this->lang->line('LOGIN_NEEDED'));
      redirect(route('users/login'), 'auto', 401);
    }
  }


  public function delete_avatar()
  {
    if ($this->authManager->isLogged()) {
      $this->load->library('user_agent', null, 'agent');
      $redirect = $this->agent->referrer() ? $this->agent->referrer() : site_url();

      if ($authorized = $this->authManager->isPermitted($this->session->userdata('id'), 'MISCELLANEOUS__AVATAR_EDITION')) {
        $this->load->library('form_validation');
        $this->load->helper('form');

        $this->form_validation->set_rules('user_id', 'ID', 'required');

        if ($this->form_validation->run()) {
          // Check if user is permitted to edit others avatars
          if ($this->input->post('user_id') != $this->session->userdata('id') && !$this->authManager->isPermitted($this->session->userdata('id'), 'USERS__EDIT')) {
            $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
            show_error($this->lang->line('PERMISSION_ERROR'), $authorized === false ? 403 : 401, $this->lang->line('ERROR_ENCOUNTERED'));
          }
          $user = $this->usersManager->getUser($this->input->post('user_id'));

          if ($user['avatar'] != $this->config->item('avatar')['default_img']) {
            if (file_exists('./uploads/avatars/' . $user['avatar']) &&
              unlink('./uploads/avatars/' . $user['avatar']) &&
              $this->usersManager->editUser($this->input->post('user_id'), ['avatar' => $this->config->item('avatar')['default_img']])) {

              $this->session->set_flashdata('success', $this->lang->line('AVATAR_SUCCESSFULLY_DELETED'));
            } else {
              $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
            }
          }

          redirect($redirect);
        }
      } else {
        $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
        show_error($this->lang->line('PERMISSION_ERROR'), $authorized === false ? 403 : 401, $this->lang->line('ERROR_ENCOUNTERED'));
      }
    } else {
      // If user isn't logged
      $this->session->set_flashdata('error', $this->lang->line('LOGIN_NEEDED'));
      redirect(route('users/login'), 'auto', 401);
    }
  }


  public function admin_add()
  {
    if ($authorized = $this->authManager->isPermitted($this->session->userdata('id'), 'USERS__ADD')) {
      $this->data['page_title'] = $this->lang->line('USER_CREATION');
      $this->load->helper('form');
      $this->load->library('form_validation');
      $this->load->library('Groups_Manager', null, 'groupsManager');

      $this->data['groups'] = $this->groupsManager->getGroups();

      $groups = '';
      for ($i = 0; $i < count($this->data['groups']); $i++) {
        $groups .= $this->data['groups'][$i]['id'] . ',';
        $this->data['groups'][$i]['value'] = $this->data['groups'][$i]['id'];
      }

      // Form rules check
      $this->form_validation->set_rules('username', $this->lang->line('USERNAME'), 'required|min_length[3]|max_length[25]|is_unique[users.username]|alpha_dash');
      $this->form_validation->set_rules('email', $this->lang->line('EMAIL'), 'required|min_length[3]|max_length[105]|valid_email|is_unique[users.email]');
      $this->form_validation->set_rules('password', $this->lang->line('PASSWORD'), 'required|min_length[6]|matches[password_confirm]');
      $this->form_validation->set_rules('password_confirm', $this->lang->line('PASSWORD_CONFIRMATION'), 'required|min_length[6]');
      $this->form_validation->set_rules('group', $this->lang->line('GROUP'), 'required|in_list[' . $groups . ']');

      // If check passed
      if ($this->form_validation->run()) {
        if ($user_id = $this->usersManager->registerUser($this->input->post('username'), $this->input->post('email'), password_hash($this->input->post('password'), PASSWORD_BCRYPT, ['cost' => 14]), $this->input->post('group'))) {
          // If user adding succeed
          $this->emitter->emit('users.creation', [$user_id]);
          $this->session->set_flashdata('success', $this->lang->line('USERS_ADD_SUCCEEDED'));
          redirect(route('users/admin_manage'));
        } else {
          // If user adding failed
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
          redirect(current_url());
        }
      }

      // Render the view
      $this->twig->display('admin/users/add', $this->data);
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));

      show_error($this->lang->line('PERMISSION_ERROR'), $authorized === false ? 403 : 401, $this->lang->line('ERROR_ENCOUNTERED'));
    }
  }


  public function admin_manage()
  {
    if ($authorized = $this->authManager->isPermitted($this->session->userdata('id'), 'USERS__MANAGE')) {
      $this->data['page_title'] = $this->lang->line('USERS_MANAGEMENT');
      $this->data['users'] = $this->usersManager->getUsers();

      // Render the view
      $this->twig->display('admin/users/manage', $this->data);
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));

      show_error($this->lang->line('PERMISSION_ERROR'), $authorized === false ? 403 : 401, $this->lang->line('ERROR_ENCOUNTERED'));
    }
  }


  public function admin_edit($id)
  {
    if ($authorized = $this->authManager->isPermitted($this->session->userdata('id'), 'USERS__EDIT')) {
      if ($this->data['user'] = $this->usersManager->getUser($id)) {
        $this->data['page_title'] = $this->lang->line('USER_EDITION');
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('Groups_Manager', null, 'groupsManager');

        $this->data['groups'] = $this->groupsManager->getGroups();
        $this->data['user']['group_id'] = array($this->data['user']['group_id']);

        $groups = '';
        for ($i = 0; $i < count($this->data['groups']); $i++) {
          $groups .= $this->data['groups'][$i]['id'] . ',';
          $this->data['groups'][$i]['value'] = $this->data['groups'][$i]['id'];
        }

        // Form rules check
        if ($this->data['user']['username'] != $this->input->post('username'))
          $this->form_validation->set_rules('username', $this->lang->line('USERNAME'), 'required|min_length[3]|max_length[25]|is_unique[users.username]|alpha_dash');
        if ($this->data['user']['email'] != $this->input->post('email'))
          $this->form_validation->set_rules('email', $this->lang->line('EMAIL'), 'required|min_length[3]|max_length[105]|valid_email|is_unique[users.email]');
        if ($this->input->post('password') != '') {
          $this->form_validation->set_rules('password', $this->lang->line('PASSWORD'), 'required|min_length[6]|matches[password_confirm]');
          $this->form_validation->set_rules('password_confirm', $this->lang->line('PASSWORD_CONFIRMATION'), 'required|min_length[6]');
        }
        $this->form_validation->set_rules('biography', $this->lang->line('BIOGRAPHY'), 'max_length[150]');
        $this->form_validation->set_rules('group', $this->lang->line('GROUP'), 'required|in_list[' . $groups . ']');

        // If check passed
        if ($this->form_validation->run()) {
          $data = array(
            'username' => $this->input->post('username'),
            'email' => $this->input->post('email'),
            'password' => $this->input->post('password') != ''
              ? password_hash($this->input->post('password'), PASSWORD_BCRYPT, ['cost' => 14])
              : $this->data['user']['password'],
            'biography' => $this->input->post('biography'),
            'group_id' => $this->input->post('group'),
          );

          if ($this->usersManager->editUser($id, $data))
            // If user editing succeed
            $this->session->set_flashdata('success', $this->lang->line('USERS_EDIT_SUCCEEDED'));
          else
            // If user editing failed
            $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));

          redirect(current_url());
        } else {
          // Render the view
          $this->twig->display('admin/users/edit', $this->data);
        }
      } else {
        // If the page was not found
        $this->session->set_flashdata('error', $this->lang->line('USER_NOT_FOUND'));
        redirect(route('users/admin_manage'));
      }
    } else {
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      show_error($this->lang->line('PERMISSION_ERROR'), $authorized === false ? 403 : 401, $this->lang->line('ERROR_ENCOUNTERED'));
    }
  }


  public function admin_delete($id)
  {
    if ($authorized = $this->authManager->isPermitted($this->session->userdata('id'), 'USERS__DELETE')) {

      // Avatar deletion
      $user = $this->usersManager->getUser($id);
      if ($user['avatar'] && $user['avatar'] != $this->config->item('avatar')['default_img']) {
        if (file_exists('./uploads/avatars/' . $user['avatar'])) {
          if (!unlink('./uploads/avatars/' . $user['avatar'])) {
            var_dump('./uploads/avatars/' . $user['avatar']);
            $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
            redirect(route('users/admin_manage'));
          }
        }
      }

      // User deletion
      if ($this->usersManager->deleteUser($id))
        // If user deleting succeed
        $this->session->set_flashdata('success', $this->lang->line('USERS_DELETE_SUCCEEDED'));
      else
        // If user deleting failed
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));

      redirect(route('users/admin_manage'));
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));

      show_error($this->lang->line('PERMISSION_ERROR'), $authorized === false ? 403 : 401, $this->lang->line('ERROR_ENCOUNTERED'));
    }
  }


  public function take_control($id)
  {
    // If already logged as and want log back
    if ($this->session->userdata('real_id') == $id) {
      // Log back
      $this->session->set_userdata('id', $id);
      $this->session->unset_userdata('real_id');
      redirect(route('pages/index'));
    }

    if ($authorized = $this->authManager->isPermitted($this->session->userdata('id'), 'USERS__TAKE_CONTROL')) {
      // Log as the requested user
      $this->session->set_userdata('real_id', $this->session->userdata('id'));
      $this->session->set_userdata('id', $id);

      redirect(route('pages/index'));
    } else {
      // If user doesn't have required permission
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));

      show_error($this->lang->line('PERMISSION_ERROR'), $authorized === false ? 403 : 401, $this->lang->line('ERROR_ENCOUNTERED'));
    }
  }

}

