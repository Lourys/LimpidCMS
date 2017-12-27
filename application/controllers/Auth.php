<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Authentication CMS class
 *
 * @property CI_Lang $lang
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property Auth_Manager $authManager
 */
class Auth extends Limpid_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->lang->load('auth');
    }

    public function login()
    {
        if (!$this->authManager->isLogged()) {
            $this->data['page_title'] = $this->lang->line('LOGIN');

            $this->load->helper('form');
            $this->load->library('form_validation');

            // Form rules check
            $this->form_validation->set_rules('account', $this->lang->line('USERNAME_OR_EMAIL'), 'required|min_length[3]|max_length[105]');
            $this->form_validation->set_rules('password', $this->lang->line('PASSWORD'), 'required|min_length[6]');

            // If check passed
            if ($this->form_validation->run()) {
                // Attempt login
                if ($this->authManager->loginByEmail($this->input->post('account'), $this->input->post('password')) || $this->authManager->loginByUsername($this->input->post('account'), $this->input->post('password'))) {
                    $this->session->set_flashdata('success', $this->lang->line('LOGIN_SUCCEEDED'));
                    redirect(site_url());
                } else {
                    $this->session->set_flashdata('error', $this->lang->line('INCORRECT_CREDENTIALS'));
                    redirect(current_url());
                }
            }

            $this->twig->display('auth/login', $this->data);
        } else {
            $this->session->set_flashdata('error', $this->lang->line('ALREADY_LOGGED'));
            redirect(site_url());
        }
    }

    public function logout()
    {
        if ($this->authManager->isLogged()) {
            $this->authManager->logout();
            $this->session->set_flashdata('success', $this->lang->line('SUCCESSFULLY_LOGOUT'));
        } else {
            $this->session->set_flashdata('error', $this->lang->line('NEED_LOGIN'));
        }

        redirect(site_url());
    }


}

