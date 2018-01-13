<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/******************************************************************************
 * Copyright © LimpidCMS 2017 All Rights Reserved                             *
 *                                                                            *
 * This file and all its contents is copyright by LimpidCMS, and may not      *
 * adapted, edited, changed, transformed, published, republished,             *
 * distributed or redistributed, in any way without our written permission.   *
 ******************************************************************************/

if (!function_exists('displayCaptcha')) {
  function displayCaptcha()
  {
    $Config =& load_class('Config');

    // reCAPTCHA generation
    if ($Config->item('recaptchaEnabled') == true && is_array($Config->item('recaptchaSettings'))) {
      $script = '<script type="text/javascript" async>
                    var submitButton = document.getElementById("captchaProtected");
                    
                    if (submitButton !== null) {
                      var scriptTag = document.getElementsByTagName(\'script\');
                      scriptTag = scriptTag[scriptTag.length - 1];
                      var script = document.createElement(\'script\');
                      script.src = \'https://www.google.com/recaptcha/api.js?hl=' . $Config->item('language') . '&onload=onloadCallback&render=explicit\';
                      script.type = \'text/javascript\';
                      scriptTag.appendChild(script);
                      
                      var onloadCallback = function() {
                        grecaptcha.render(\'captchaProtected\', {
                          \'sitekey\' : \'' . $Config->item('recaptchaSettings')['site_key'] . '\',
                          \'callback\' : function(token) {
                              submitButton.closest("form").submit();
                          }
                        });
                      };
                    }
                  </script>';

      return $script;
    }

    // LimpidCMS CAPTCHA generation
    if ($Config->item('limpidCaptchaEnabled') == true && function_exists('gd_info')) {
      $limpid =& CMS_Controller::$instance;
      $limpid->load->library('Captcha');
      $script = '<script type="text/javascript" async>
                   $("<img src=\'' . $limpid->captcha->generateCaptcha() . ' \' alt=\'captcha\'><div class=\'form-group\'><label for=\'captchaAnswer\'>Captcha :</label><input name=\'captcha_answer\' type=\'text\' class=\'form-control\' id=\'captchaAnswer\'></input></div>").insertBefore( "#captchaProtected" );
                 </script>';

      return $script;
    }

    return false;
  }
}

if (!function_exists('validation_errors')) {
  function validation_errors()
  {
    return null;
  }
}

if (!function_exists('load_widget')) {
  function load_widget($pluginName)
  {
    $limpid =& CMS_Controller::$instance;
    $limpid->load->library($pluginName . '_Widget', null, $pluginName . 'Widget');

    return;
  }
}