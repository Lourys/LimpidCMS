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
    $this->limpid->load->dbforge();

    // Fields initialization
    $fields = array(
      'ip_address' => array(
        'type' => 'VARCHAR',
        'constraint' => '15'
      ),
      'first_visit' => array(
        'type' => 'TIMESTAMP'
      ),
      'last_visit' => array(
        'type' => 'TIMESTAMP',
        'null' => true,
      ),
    );
    $this->limpid->dbforge->add_key('ip_address', TRUE);
    $this->limpid->dbforge->add_field($fields);

    // Create table
    return $this->limpid->dbforge->create_table('web_stats', true);
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