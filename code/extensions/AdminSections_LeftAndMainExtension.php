<?php
/**
 * Extends {@link LeftAndMain} to add left menu items for main sections from
 * {@link SiteTree}
 */

class AdminSections_LeftAndMainExtension extends LeftAndMainExtension implements PermissionProvider
{
	public static $menu_priority_section = 80;
	public function init() {
		$pages = SiteTree::get()->filter('HasAdminSection', true);

		foreach ($pages as $idx => $page) {
			//higher comes first, so reverse
			$idx++;//increase so starts at 1
			$index = (int)(floor(self::$menu_priority_section/10) . ((10 - $idx) % 10));
			if (Permission::check('VIEW_ADMIN_SECTION_' . $idx)) {
				CMSMenu::add_menu_item($page->URLSegment, $page->Title, 'admin/admin-sections/' . $page->URLSegment . '/', null, $index);
			}
		}
	}

	public function providePermissions() {
		$permissions = array();

		$pages = SiteTree::get()->filter('HasAdminSection', true);

		foreach ($pages as $idx => $page) {
			$permissions['VIEW_ADMIN_SECTION_' . $idx] = array(
				'name' => 'View "' . $page->Title . '" Admin Section',
				'help' => 'Allow viewing of "' . $page->Title . '" Admin Section',
				'category' => 'Admin Sections permissions',
				'sort' => 100
			);
		}

		return $permissions;

	}

}