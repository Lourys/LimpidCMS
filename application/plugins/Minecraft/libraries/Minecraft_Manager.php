<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Minecraft manager
 *
 */
class Minecraft_Manager
{
  private $limpid;

  /**
   * Minecraft_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    // Check if plugin is enabled
    if ($this->limpid->pluginsManager->getPlugin('Minecraft')->enabled) {
      $this->limpid->load->model('Minecraft_model', 'minecraft');
    } else {
      return false;
    }
  }

  public function addServer($name, $ip_address, $port, $rcon_port, $rcon_pass)
  {
    // Simple check
    if (empty($name) || empty($ip_address) || empty($port) || empty($rcon_port) || empty($rcon_pass)) {
      return null;
    }

    // Set data structure
    $data = [
      'name' => $name,
      'ip_address' => $ip_address,
      'port' => $port,
      'rcon_port' => $rcon_port,
      'rcon_pass' => $rcon_pass,
      'game' => 'minecraft'
    ];

    if ($server = $this->limpid->minecraft->insert($data))
      return $server;

    return null;
  }

  function editServer($id, $data = [])
  {
    // Simple check
    if (empty($id) || empty($data)) {
      return null;
    }

    if ($server = $this->limpid->minecraft->update($data, $id))
      return $server;

    return null;
  }

  function deleteServer($id)
  {
    // Simple check
    if (empty($id)) {
      return null;
    }

    if ($server = $this->limpid->minecraft->delete($id))
      return $server;

    return null;
  }

  public function getServers()
  {
    if ($server = $this->limpid->minecraft->get_all(['game' => 'minecraft']))
      return $server;

    return null;
  }
}