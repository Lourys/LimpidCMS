<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Output $output
 */
class Custom404 extends Limpid_Controller {

	public function index() 
    { 
        $this->output->set_status_header('404'); 
		
		$this->twig->display('errors/error_404');
    }
}
