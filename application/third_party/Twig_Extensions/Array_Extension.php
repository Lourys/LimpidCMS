<?php

class Array_Extension extends Twig_Extension
{

  public function getFilters()
  {
    return array(
      new Twig_SimpleFilter('cast_to_array', [$this, 'castToArray'])
    );
  }

  public function castToArray($stdClassObject)
  {
    return (array) $stdClassObject;
  }
}