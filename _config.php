<?php
define('ADMIN_SECTIONS_DIR', basename(__DIR__));

CMSMenu::remove_menu_item('AdminSectionsMain');
CMSMenu::remove_menu_item('AdminSectionsSettingsController');
CMSMenu::remove_menu_item('AdminSectionsHistoryController');