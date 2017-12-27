<?php

class CMS_Config extends CI_Config
{
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Edit config item
   *
   * @param string $item
   * @param mixed $value
   * @param string $file
   *
   * @return bool|null
   */
  public function edit_item($item, $value, $file)
  {
    if (empty($item) || empty($file))
      return null;

    $file = str_replace('.php', '', $file);
    $config = file_get_contents(APPPATH . 'config/' . $file . '.php');
    $lines = preg_split('(\r\n|\n|\r)', $config);
    for ($i = 2; $i < count($lines); $i++) {
      if (strpos($lines[$i], $item)) {
        echo $item;
        echo $file;
        echo $lines[$i];
        if ($value === null) {
          $lines[$i] = '$config[\'' . addslashes($item) . '\'] = null;';
          break;
        } else {
          switch (gettype($value)) {
            case 'boolean':
            case 'integer':
            case 'double':
              $lines[$i] = '$config[\'' . addslashes($item) . '\'] = ' . addslashes($value) . ';';
              $edition = true;
              break 2;
            case 'string':
              $lines[$i] = '$config[\'' . addslashes($item) . '\'] = \'' . addslashes($value) . '\';';
              $edition = true;
              break 2;
            case 'array':
              $lines[$i] = '$config[\'' . addslashes($item) . '\'] = ' . preg_replace('/[ \t]+/', '', preg_replace('/[\r\n]+/', '', var_export(array_map('addslashes', $value), true))) . ';';
              $edition = true;
              break 2;
            default:
              $lines[$i] = '$config[\'' . addslashes($item) . '\'] = \'\';';
              $edition = true;
              break 2;
          }
        }
      }

      $edition = false;
    }

    if ($edition) {
      $config = implode(PHP_EOL, $lines);
      var_dump(file_put_contents(APPPATH . 'config/' . $file . '.php', $config));
      if (file_put_contents(APPPATH . 'config/' . $file . '.php', $config))
        return true;
    }

    return false;
  }


  /**
   * Add config item
   *
   * @param string $item
   * @param mixed $value
   * @param string $file
   *
   * @return bool|null
   */
  public function add_item($item, $value, $file)
  {
    if (empty($item) || empty($file))
      return null;

    $file = str_replace('.php', '', $file);
    $config = file_get_contents(APPPATH . 'config/' . $file . '.php');
    if ($value == null)
      $config .= PHP_EOL . '$config[\'' . $item . '\'] = null;';
    else
      switch (gettype($value)) {
        case 'boolean':
        case 'integer':
        case 'double':
          $config .= PHP_EOL . '$config[\'' . $item . '\'] = ' . $value . ';';
          break;
        case 'string':
          $config .= PHP_EOL . '$config[\'' . $item . '\'] = \'' . $value . '\';';
          break;
        case 'array':
          $config .= PHP_EOL . '$config[\'' . $item . '\'] = ' . preg_replace('/[ \t]+/', '', preg_replace('/[\r\n]+/', '', var_export($value, true))) . ';';
          break;
        default:
          $config .= PHP_EOL . '$config[\'' . $item . '\'] = \'\';';
          break;
      }

    if (file_put_contents(APPPATH . 'config/' . $file . '.php', $config))
      return true;

    return false;
  }
}