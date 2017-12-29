<?php

/**
 * Logs CMS class
 *
 * @property CI_Lang $lang
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 */
class Logs extends Limpid_Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function admin_index()
  {
    $this->load->helper('form');
    $this->load->library('form_validation');

    if ($this->authManager->isPermitted($this->session->userdata('id'), 'LOGS_VIEW')) {
      $this->data['page_title'] = $this->lang->line('LOGS');

      $this->data['thresholds'] = [
        ['name'  => $this->lang->line('DISABLE_LOGGING'), 'value' => 0],
        ['name'  => $this->lang->line('ERRORS_LOGGING'), 'value' => 1],
        ['name'  => $this->lang->line('DEBUG_LOGGING'), 'value' => 2],
        ['name'  => $this->lang->line('INFO_LOGGING'), 'value' => 3],
        ['name'  => $this->lang->line('ALL_LOGGING'), 'value' => 4]
      ];

      $logFiles = array_values(array_diff(scandir(APPPATH . 'logs'), array('..', '.', 'index.html')));
      for ($i = 0; $i < count($logFiles); $i++) {
        $size = filesize(APPPATH . 'logs/' . $logFiles[$i]);
        if ($size < 100) {
          $size = $size . ' ' . $this->lang->line('BYTE_SYMBOL');
        } elseif ($size >= 100 && $size < 1048576) {
          $size = round($size / 1024, 1) . ' ' . $this->lang->line('KILOBYTE_SYMBOL');
        } elseif ($size >= 1048576 && $size < 1073741824) {
          $size = round($size / 1024 / 1024, 1) . ' ' . $this->lang->line('MEGABYTE_SYMBOL');
        }
        $this->data['logFiles'][$i] = [
          'name' => $logFiles[$i],
          'size' => $size
        ];
      }

      $this->form_validation->set_rules('threshold', $this->lang->line('LOG_THRESHOLD'), 'required|greater_than_equal_to[0]|less_than_equal_to[4]');
      if ($this->form_validation->run()) {
        if ($this->config->edit_item('log_threshold', (int) $this->input->post('threshold'), 'config')) {
          $this->session->set_flashdata('success', $this->lang->line('LOG_THRESHOLD_SUCCESSFULLY_EDITED'));
        } else {
          $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
        }

        redirect(current_url());
      }

      $this->twig->display('admin/logs/index', $this->data);
    } else {
      $this->session->set_flashdata('error', $this->lang->line('PERMISSION_ERROR'));
      redirect(site_url());
    }
  }

  public function admin_download($file)
  {
    $file = APPPATH . 'logs/' . $file;

    if (file_exists($file)) {
      header('Content-Description: File Transfer');
      header('Content-Type: application/octet-stream');
      header('Content-Disposition: attachment; filename="'.basename($file).'"');
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . filesize($file));
      readfile($file);
      echo '<script>javascript:window.close()</script>';
      exit;
    } else {
      show_404();
    }
  }

  public function admin_delete($file)
  {
    $file = APPPATH . 'logs/' . $file;

    if (file_exists($file)) {
      if (unlink($file)) {
        $this->session->set_flashdata('success', $this->lang->line('LOG_SUCCESSFULLY_DELETED'));
      } else {
        $this->session->set_flashdata('error', $this->lang->line('INTERNAL_ERROR'));
      }

      redirect(route('logs/admin_index'));
    } else {
      show_404();
    }
  }

}