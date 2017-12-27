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

    return false;
  }
}

if (!function_exists('validation_errors')) {
  function validation_errors()
  {
    return null;
  }
}

