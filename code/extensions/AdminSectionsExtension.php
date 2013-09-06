<?php
/**
 * Adds a field to {@link SiteTree} objects in CMS to create a main
 * section in left nav
 */

class AdminSectionsExtension extends SiteTreeExtension
{
	public static $db = array(
		'HasAdminSection' => 'Boolean'
	);

	public function updateSettingsFields(FieldList $fields) {
		$fields->insertAfter(new CheckboxField('HasAdminSection', 'Create main section from this page and it\'s children? (requires reload of page to see)'), 'CanEditType');
	}
}