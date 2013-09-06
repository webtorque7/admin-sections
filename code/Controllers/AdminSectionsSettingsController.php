<?php
/**
 * Handler for {@link SiteTree} settings in {@link AdminSectionsMain}
 * @todo Add extra security
 */
class AdminSectionsSettingsController extends AdminSectionsMain {

	static $url_segment = 'admin-sections/settings';
	//static $url_rule = '/$URLSegment/$Action/$ID/$VersionID/$OtherVersionID';
	static $url_priority = 53;
	static $menu_title = 'Settings';
	static $session_namespace = 'AdminSectionsMain';

	public static $url_handlers = array(
		'/$URLSegment/EditForm' => 'EditForm',
		'/$URLSegment/$Action/$ID/$VersionID/$OtherVersionID' => '$Action'
	);

	public function getActionTemplate() {
		return $this->renderWith($this->getTemplatesWithSuffix('_Content'));
	}

	public function getEditForm($id = null, $fields = null) {
		$record = $this->getRecord($id ? $id : $this->currentPageID());

		return parent::getEditForm($record, ($record) ? $record->getSettingsFields() : null);
	}

}