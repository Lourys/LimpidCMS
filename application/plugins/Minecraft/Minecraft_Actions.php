<?php

class Minecraft_Actions
{
  private $limpid;

  public function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
  }

  public function onInstall()
  {
    if (!$this->limpid->db->table_exists('servers')) {
      $this->limpid->load->dbforge();

      // Fields initialization
      $fields = [
        'id' => [
          'type' => 'INT',
          'auto_increment' => true,
        ],
        'name' => [
          'type' => 'VARCHAR',
          'constraint' => 20,
        ],
        'ip_address' => [
          'type' => 'VARCHAR',
          'constraint' => 15,
        ],
        'port' => [
          'type' => 'INT',
          'constraint' => 5,
        ],
        'rcon_port' => [
          'type' => 'INT',
          'constraint' => 5,
        ],
        'rcon_pass' => [
          'type' => 'VARCHAR',
          'constraint' => 255,
        ],
        'game' => [
          'type' => 'VARCHAR',
          'constraint' => 100,
        ],
      ];
      $this->limpid->dbforge->add_key('id', TRUE);
      $this->limpid->dbforge->add_field($fields);

      // Create table
      if ($this->limpid->dbforge->create_table('servers', true)) {
        $permissions = [
          'MINECRAFT__OVERVIEW',
          'MINECRAFT__SETTINGS',
        ];
        // Insert plugin's permissions
        foreach ($permissions as $permission) {
          $this->limpid->db->insert('permissions', ['name' => $permission]);
        }
      }

      return false;
    }

    return true;
  }

  public function onEnable()
  {
    return true;
  }

  public function onDisable()
  {
    return true;
  }

  public function onUninstall()
  {
    return true;
  }

  public function onUpdate()
  {
    return true;
  }
}