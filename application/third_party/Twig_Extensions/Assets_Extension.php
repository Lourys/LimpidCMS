<?php

class Assets_Extension extends Twig_Extension
{
    private $theme;

    public function __construct($theme)
    {
        $this->theme = $theme;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('css', [$this, 'cssPath']),
            new Twig_SimpleFunction('js', [$this, 'jsPath']),
            new Twig_SimpleFunction('img', [$this, 'imgPath']),
        ];
    }

    public function cssPath($file)
    {
        return base_url('assets/' . $this->theme . '/css/' . $file . '.css');
    }

    public function jsPath($file)
    {
        return base_url('assets/' . $this->theme . '/js/' . $file . '.js');
    }

    public function imgPath($file)
    {
        return base_url('assets/' . $this->theme . '/img/' . $file);
    }
}