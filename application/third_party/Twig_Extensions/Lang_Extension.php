<?php

/**
 * @property CMS_Controller limpid
 */
class Lang_Extension extends Twig_Extension
{
  public function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
  }

  public function getFunctions()
  {
    return [
      new Twig_SimpleFunction('lang', [$this, 'getLangLine'], ['is_safe' => ['html']])
    ];
  }

  public function getLangLine($line)
  {
    return $this->limpid->lang->line($line);
  }
}