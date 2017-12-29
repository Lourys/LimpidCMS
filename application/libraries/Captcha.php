<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//
//  A simple PHP CAPTCHA script
//
//  Copyright 2011 by Cory LaViska for A Beautiful Site, LLC
//
//  EDITED BY LimpidCMS
//

class Captcha
{

  function __construct()
  {
    $this->limpid =& CMS_Controller::$instance;
  }

  /**
   * Generate a new php captcha
   *
   * @return string
   */
  function generateCaptcha()
  {
    $bg_path = APPPATH . 'third_party/Captcha/backgrounds/';
    $font_path = APPPATH . 'third_party/Captcha/fonts/';

    // Default values
    $captcha_config = array(
      'min_length' => 5,
      'max_length' => 5,
      'backgrounds' => array_values(array_diff(scandir($bg_path), array('..', '.'))),
      'fonts' => array_values(array_diff(scandir($font_path), array('..', '.'))),
      'characters' => 'ABCDEFGHJKLMNPRSTUVWXYZabcdefghjkmnprstuvwxyz23456789',
      'min_font_size' => 28,
      'max_font_size' => 28,
      'color' => '#636366',
      'angle_min' => -10,
      'angle_max' => 10,
      'shadow' => true,
      'shadow_color' => '#e5e5e5',
      'shadow_offset_x' => -1,
      'shadow_offset_y' => 1
    );

    $captcha_config['code'] = '';
    $length = mt_rand($captcha_config['min_length'], $captcha_config['max_length']);
    while (strlen($captcha_config['code']) < $length) {
      $captcha_config['code'] .= substr($captcha_config['characters'], mt_rand() % (strlen($captcha_config['characters'])), 1);
    }

    $this->limpid->session->set_userdata('captchaCode', $captcha_config['code']);

    /* ------------------------------------------------- */

    $background = $bg_path . $captcha_config['backgrounds'][mt_rand(0, count($captcha_config['backgrounds']) - 1)];
    list($bg_width, $bg_height, $bg_type, $bg_attr) = getimagesize($background);

    $captcha = imagecreatefrompng($background);

    $color = $this->hex2rgb($captcha_config['color']);
    $color = imagecolorallocate($captcha, $color['r'], $color['g'], $color['b']);

    // Determine text angle
    $angle = mt_rand($captcha_config['angle_min'], $captcha_config['angle_max']) * (mt_rand(0, 1) == 1 ? -1 : 1);

    // Select font randomly
    $font = $font_path . $captcha_config['fonts'][mt_rand(0, count($captcha_config['fonts']) - 1)];

    //Set the font size.
    $font_size = mt_rand($captcha_config['min_font_size'], $captcha_config['max_font_size']);
    $text_box_size = imagettfbbox($font_size, $angle, $font, $captcha_config['code']);

    // Determine text position
    $box_width = abs($text_box_size[6] - $text_box_size[2]);
    $box_height = abs($text_box_size[5] - $text_box_size[1]);
    $text_pos_x_min = 0;
    $text_pos_x_max = ($bg_width) - ($box_width);
    $text_pos_x = mt_rand($text_pos_x_min, $text_pos_x_max);
    $text_pos_y_min = $box_height;
    $text_pos_y_max = ($bg_height) - ($box_height / 2);
    if ($text_pos_y_min > $text_pos_y_max) {
      $temp_text_pos_y = $text_pos_y_min;
      $text_pos_y_min = $text_pos_y_max;
      $text_pos_y_max = $temp_text_pos_y;
    }
    $text_pos_y = mt_rand($text_pos_y_min, $text_pos_y_max);

    // Draw shadow
    if ($captcha_config['shadow']) {
      $shadow_color = $this->hex2rgb($captcha_config['shadow_color']);
      $shadow_color = imagecolorallocate($captcha, $shadow_color['r'], $shadow_color['g'], $shadow_color['b']);
      imagettftext($captcha, $font_size, $angle, $text_pos_x + $captcha_config['shadow_offset_x'], $text_pos_y + $captcha_config['shadow_offset_y'], $shadow_color, $font, $captcha_config['code']);
    }

    // Draw text
    imagettftext($captcha, $font_size, $angle, $text_pos_x, $text_pos_y, $color, $font, $captcha_config['code']);

    ob_start();
    imagepng($captcha);
    $imgdata = ob_get_contents();
    ob_end_clean();

    $base64 = 'data:image/png;base64,' . base64_encode($imgdata);

    return $base64;
  }

  private function hex2rgb($hex_str, $return_string = false, $separator = ',')
  {
    $hex_str = preg_replace("/[^0-9A-Fa-f]/", '', $hex_str); // Gets a proper hex string
    $rgb_array = array();
    if (strlen($hex_str) == 6) {
      $color_val = hexdec($hex_str);
      $rgb_array['r'] = 0xFF & ($color_val >> 0x10);
      $rgb_array['g'] = 0xFF & ($color_val >> 0x8);
      $rgb_array['b'] = 0xFF & $color_val;
    } elseif (strlen($hex_str) == 3) {
      $rgb_array['r'] = hexdec(str_repeat(substr($hex_str, 0, 1), 2));
      $rgb_array['g'] = hexdec(str_repeat(substr($hex_str, 1, 1), 2));
      $rgb_array['b'] = hexdec(str_repeat(substr($hex_str, 2, 1), 2));
    } else {
      return false;
    }
    return $return_string ? implode($separator, $rgb_array) : $rgb_array;
  }
}