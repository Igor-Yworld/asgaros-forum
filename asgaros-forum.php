<?php

/*
  Plugin Name: Asgaros Forum
  Plugin URI: https://github.com/Asgaros/asgaros-forum
  Description: Asgaros Forum is the best forum solution for WordPress! It comes with dozens of features in a beautiful design and stays lightweight, easy to use and ultra fast.
  Version: 1.3.0
  Author: Thomas Belser
  Author URI: http://thomasbelser.net
  License: GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Text Domain: asgaros-forum
  Domain Path: /languages

  Asgaros Forum is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 2 of the License, or
  any later version.

  Asgaros Forum is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Asgaros Forum. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if (!defined('ABSPATH')) exit;

require('includes/forum.php');
require('includes/forum-database.php');
require('includes/forum-taxonomies.php');
require('includes/forum-permissions.php');
require('includes/forum-insert.php');
require('includes/forum-notifications.php');
require('includes/forum-widgets.php');
require('includes/forum-thememanager.php');
require('includes/forum-unread.php');
require('includes/forum-uploads.php');
require('includes/forum-search.php');
require('admin/admin.php');

AsgarosForumDatabase::createInstance();
$asgarosforum = new AsgarosForum();
AsgarosForumPermissions::createInstance();
AsgarosForumThemeManager::createInstance();

if (is_admin()) {
    $asgarosforum_admin = new asgarosforum_admin();
}

?>
