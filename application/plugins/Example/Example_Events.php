<?php
/**
 * Created by PhpStorm.
 * User: lucas
 * Date: 8/29/2017
 * Time: 10:48 PM
 */

class Example_Events
{
    public function __construct()
    {
        $this->limpid =& CMS_Controller::$instance;
        // Check if plugin is enabled
        if ($this->limpid->pluginsManager->getPlugin('Example')->enabled) {
            $this->limpid->load->add_module('Example'); // Indexes plugin folder
            $this->limpid->load->library('Example_Manager', null, 'exampleManager');
            $this->limpid->emitter->on('limpid.initialization', [$this, 'logUser']);
            $this->limpid->emitter->on('example.getAdminWidget', [$this, 'getAdminWidget']);
        }
    }

    /**
     * Log a user entry
     *
     * @return void
     */
    public function logUser()
    {
        if (!$this->limpid->exampleManager->getEntry($_SERVER['REMOTE_ADDR']))
            $this->limpid->exampleManager->logUser($_SERVER['REMOTE_ADDR']);
        else
            $this->limpid->exampleManager->editEntry($_SERVER['REMOTE_ADDR'], ['last_visit' => date('Y-m-d H:i:s')]);
    }
}