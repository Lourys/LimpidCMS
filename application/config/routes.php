<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Pages';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/* Index page */
$route[''] = 'pages/index';

if ($this->config->item('language') === 'french') {

  #########################
  ## ------------------- ##
  ##  >>   GENERAL   <<  ##
  ## ------------------- ##
  #########################

  /* Actualités */
  $route['actualites'] = 'news/listing/1';
  $route['actualites/page/(:num)'] = 'news/listing/$1';
  $route['actualite/(:any)'] = 'news/view/$1';

  /* Utilisateurs */
  $route['inscription'] = 'users/register';
  $route['compte'] = 'users/account';
  $route['profil/(:any)'] = 'users/profile/$1';

  /* Authentification */
  $route['connexion'] = 'auth/login';
  $route['deconnexion'] = 'auth/logout';

  /* Pages */
  $route['page/(:any)'] = 'pages/view/$1';


  ################################
  ## -------------------------- ##
  ##  >>   ADMINISTRATION   <<  ##
  ## -------------------------- ##
  ################################

  /* Admin */
  $route['panel'] = 'admin/admin_index';

  /* Menu */
  $route['panel/menu/gestion'] = 'menu/admin_manage';

  /* Pages */
  $route['panel/pages/creer'] = 'pages/admin_add';
  $route['panel/pages/gestion'] = 'pages/admin_manage';
  $route['panel/pages/editer/(:num)'] = 'pages/admin_edit/$1';
  $route['panel/pages/supprimer/(:num)'] = 'pages/admin_delete/$1';

  /* Actualités */
  $route['panel/actualites/creer'] = 'news/admin_add';
  $route['panel/actualites/gestion'] = 'news/admin_manage';
  $route['panel/actualites/editer/(:num)'] = 'news/admin_edit/$1';
  $route['panel/actualites/supprimer/(:num)'] = 'news/admin_delete/$1';

  /* Utilisateurs */
  $route['panel/utilisateurs/creer'] = 'users/admin_add';
  $route['panel/utilisateurs/gestion'] = 'users/admin_manage';
  $route['panel/utilisateurs/editer/(:num)'] = 'users/admin_edit/$1';
  $route['panel/utilisateurs/supprimer/(:num)'] = 'users/admin_delete/$1';
  $route['panel/utilisateurs/controler/(:num)'] = 'users/take_control/$1';

  /* Groupes & Permissions */
  $route['panel/groupes/creer'] = 'groups/admin_add';
  $route['panel/groupes/gestion'] = 'groups/admin_manage';
  $route['panel/groupes/editer/(:num)'] = 'groups/admin_edit/$1';
  $route['panel/groupes/supprimer/(:num)'] = 'groups/admin_delete/$1';
  $route['panel/permissions/gestion/(:num)'] = 'permissions/admin_manage/$1';

  /* Plugins */
  $route['panel/plugins'] = 'plugins/admin_available';
  $route['panel/plugins/gestion'] = 'plugins/admin_manage';
  $route['panel/plugins/activer/(:any)'] = 'plugins/admin_enable/$1';
  $route['panel/plugins/desactiver/(:any)'] = 'plugins/admin_disable/$1';
  $route['panel/plugins/installer/(:any)'] = 'plugins/admin_install/$1';
  $route['panel/plugins/desinstaller/(:any)'] = 'plugins/admin_uninstall/$1';
  $route['panel/plugins/mettre-a-jour/(:any)'] = 'plugins/admin_update/$1';

  $route['panel/plugins/(:any)'] = '$1/admin_index';
  $route['panel/plugins/(:any)/parametres'] = '$1/admin_settings';

  /* Thèmes */
  $route['panel/themes'] = 'themes/admin_available';
  $route['panel/themes/gestion'] = 'themes/admin_manage';
  $route['panel/themes/activer/(:any)'] = 'themes/admin_enable/$1';
  $route['panel/themes/installer/(:any)'] = 'themes/admin_install/$1';
  $route['panel/themes/desinstaller/(:any)'] = 'themes/admin_uninstall/$1';
  $route['panel/themes/mettre-a-jour/(:any)'] = 'themes/admin_update/$1';

  $route['panel/themes/configuration'] = 'themes/admin_config';

  // Paramètres généraux
  $route['panel/parametres'] = 'settings/admin_general';


} else {
  $route['page/(:any)'] = 'pages/view/$1';

  $route['news'] = 'news/listing/1';
  $route['news/page/(:num)'] = 'news/listing/$1';
  $route['news/(:any)'] = 'news/view/$1';

  $route['register'] = 'users/register';
  $route['login'] = 'auth/login';
  $route['logout'] = 'auth/logout';
}
