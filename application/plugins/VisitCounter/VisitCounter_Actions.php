<?php

class VisitCounter_Actions
{
  private $limpid;

  public function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
  }

  public function onInstall()
  {
    if (!$this->limpid->db->table_exists('web_stats')) {
      $this->limpid->load->dbforge();

      // Fields initialization
      $fields = [
        'ip_address' => [
          'type' => 'VARCHAR',
          'constraint' => '15'
        ],
        'first_visit' => [
          'type' => 'TIMESTAMP'
        ],
        'last_visit' => [
          'type' => 'TIMESTAMP',
          'null' => true,
        ],
      ];
      $this->limpid->dbforge->add_key('ip_address', TRUE);
      $this->limpid->dbforge->add_field($fields);

      // Create table
      return $this->limpid->dbforge->create_table('web_stats', true);
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
    $this->limpid->load->dbforge();

    // Drop table
    return $this->limpid->dbforge->drop_table('web_stats', TRUE);
  }

  public function onUpdate()
  {
    return true;
  }
}