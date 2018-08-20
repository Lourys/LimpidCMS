<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Shop manager
 *
 */
class Shop_Manager
{
  private $limpid;
  private $pingData = [];

  /**
   * Shop_Manager constructor.
   */
  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
    // Check if plugin is enabled
    if ($this->limpid->pluginsManager->getPlugin('Minecraft')['enabled']) {
      $this->limpid->load->model('Minecraft_model', 'minecraft');
    } else {
      return false;
    }
  }

  public function addServer($params)
  {
    $params = array_merge(array(
      'name' => null,
      'ip_address' => null,
      'port' => 25565,
      'rcon_port' => null,
      'rcon_pass' => null,
      'timeout' => null
    ), $params);

    // Create variables from parameter list
    extract($params);

    // Simple check
    if (empty($name) || empty($ip_address) || empty($port)) {
      return null;
    }

    // Set data structure
    $data = [
      'name' => $name,
      'ip_address' => $ip_address,
      'port' => $port,
      'rcon_port' => $rcon_port,
      'rcon_pass' => $rcon_pass,
      'timeout' => $timeout,
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

  public function countServers()
  {
    if ($server = $this->limpid->minecraft->count_rows(['game' => 'minecraft']))
      return $server;

    return null;
  }

  public function sendCommand($id, $command)
  {
    if ($server = $this->limpid->minecraft->get(['game' => 'minecraft', 'id' => $id])) {
      $rconInstance = 'rcon_' . $server['id'];
      if (!isset($this->limpid->$rconInstance)) {
        $this->limpid->load->library('MinecraftRcon', [
          'host' => $server['ip_address'],
          'port' => $server['rcon_port'],
          'password' => $server['rcon_pass'],
          'timeout' => $server['timeout']
        ], $rconInstance);

        if ($this->limpid->$rconInstance->connect()) {
          return $this->limpid->$rconInstance->sendCommand($command);
        } else {
          unset($this->limpid->$rconInstance);
        }
      } else {
        return $this->limpid->$rconInstance->sendCommand($command);
      }

      return false;
    }

    return null;
  }

  public function ping($id)
  {
    if ($server = $this->limpid->minecraft->get(['game' => 'minecraft', 'id' => $id])) {
      if (isset($this->pingData[$server['id']])) {
        return $this->pingData[$server['id']];
      }

      $pingInstance = 'ping_' . $server['id'];
      if (!isset($this->limpid->$pingInstance)) {
        $this->limpid->load->library('MinecraftPing', [
          'host' => $server['ip_address'],
          'port' => $server['port']
        ], $pingInstance);
      }

      $this->pingData[$server['id']] = $this->limpid->$pingInstance->Query();
      return $this->pingData[$server['id']];
    }

    return null;
  }
}