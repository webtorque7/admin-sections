<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 7
 * Date: 7/06/13
 * Time: 8:07 AM
 * To change this template use File | Settings | File Templates.
 */
class AdminSectionsMain extends CMSMain
{
	public static $url_segment = 'admin-sections';

	public static $url_rule = '/$URLSegment/$Action/$ID';

	public static $url_priority = 50;

	public static $session_namespace = 'AdminSectionsMain';

	public static $url_handlers = array(
		'$URLSegment/EditForm' => 'EditForm',
		'$URLSegment/$Action/$ID' => '$Action',
		'$URLSegment' => 'index'
	);

	public static $menu_title = '';

	public static $allowed_actions = array(
		'index',
		'show',
		'history',
		'settings',
		'EditForm'
	);


	protected $section;

	public function init() {
		parent::init();

		$this->section = SiteTree::get()->filter(array(
			'URLSegment' => $this->request->param('URLSegment'),
			'HasAdminSection' => true
		))->first();

		if (!$this->request->param('Action')) {
			$this->setCurrentPageID(null);
		}

		Requirements::css(ADMIN_SECTIONS_DIR . '/css/AdminSections.css');
	}

	public function index($request) {
		// In case we're not showing a specific record, explicitly remove any session state,
		// to avoid it being highlighted in the tree, and causing an edit form to show.
		if(!$request->param('Action')) $this->setCurrentPageId(null);



		if (!$this->section) {
			return Security::permissionFailure($this);
		}

		return parent::index($request);
	}

	public function show($request) {

		if (!$this->checkSection()) {
			return Security::permissionFailure($this);
		}
		return parent::show($request);
	}

	/**
	 * Check we are still in the section, prevents user
	 * from putting in an ID to access other pages
	 * @return bool
	 */
	public function checkSection($id = null) {
		if ($id) {
			$page = SiteTree::get()->byID($id);
		}
		else {
			$page = $this->currentPage();
		}

		if ($page->URLSegment == $this->section->URLSegment) {
			return true;
		}

		$parent = $page->Parent();
		while ($parent && $parent->exists()) {
			if ($parent->URLSegment == $this->section->URLSegment) {
				return true;
			}
			$parent = $parent->Parent();
		}

		return false;
	}

	/**
	 * @return ArrayList
	 */
	public function Breadcrumbs($unlinked = false) {
		$items = LeftAndMain::Breadcrumbs($unlinked);

		// The root element should point to the pages tree view,
		// rather than the actual controller (which would just show an empty edit form)
		//$defaultTitle = $this->section->Title;
		$items[0]->Title = 'Show All';
		$items[0]->Link = $this->Link();

		return $items;
	}

	/**
	 * Recursively get parent ids to limit search to within this section
	 * @param null $section
	 * @return array
	 */
	public function getSectionIDs($section = null) {
		$ids = array();

		if (!$section) {
			$section = $this->section;
			$ids[] = $section->ID;
		}

		$children = $section->AllChildren();

		if ($children->count()) foreach ($children as $child) {
			$ids = array_merge($ids, $this->getSectionIDs($child));
		}

		return array_merge($ids, array_keys($children->map('ID', 'Title')->toArray()));

	}

	/**
	 * Returns the pages meet a certain criteria as {@see CMSSiteTreeFilter} or the subpages of a parent page
	 * defaulting to no filter and show all pages in first level.
	 * Doubles as search results, if any search parameters are set through {@link SearchForm()}.
	 *
	 * @param Array Search filter criteria
	 * @param Int Optional parent node to filter on (can't be combined with other search criteria)
	 * @return SS_List
	 */
	public function getList($params, $parentID = 0) {
		$list = new DataList($this->stat('tree_class'));
		$filter = null;
		$ids = array();
		if(isset($params['FilterClass']) && $filterClass = $params['FilterClass']){
			if(!is_subclass_of($filterClass, 'CMSSiteTreeFilter')) {
				throw new Exception(sprintf('Invalid filter class passed: %s', $filterClass));
			}
			$filter = new $filterClass($params);
			$filterOn = true;
			foreach($pages=$filter->pagesIncluded() as $pageMap){
				$ids[] = $pageMap['ID'];
			}
			if(count($ids)) $list = $list->where('"'.$this->stat('tree_class').'"."ID" IN ('.implode(",", $ids).')');
			$list = $list->filter('ID', $this->getSectionIDs());
		} else {
			$list = $list->filter("ParentID", is_numeric($parentID) ? $parentID : 0);
		}

		return $list;
	}

	public function ListViewForm() {

		$params = $this->request->requestVar('q');
		$parentID = ($this->request->requestVar('ParentID')) ? $this->request->requestVar('ParentID') : $this->section->ID;

		if (!$this->checkSection($parentID)) {
			return Security::permissionFailure($this);
		}

		$list = $this->getList($params, $parentID);
		$gridFieldConfig = GridFieldConfig::create()->addComponents(
			new GridFieldSortableHeader(),
			new GridFieldDataColumns(),
			new GridFieldPaginator(15)
		);
		if($parentID != $this->section->ID){
			$gridFieldConfig->addComponent(
				GridFieldLevelup::create($parentID)
					->setLinkSpec('?ParentID=%d')
					->setAttributes(array('data-pjax' => 'CurrentForm,Breadcrumbs'))
			);
		}
		$gridField = new GridField('Page','Pages', $list, $gridFieldConfig);
		$columns = $gridField->getConfig()->getComponentByType('GridFieldDataColumns');

		// Don't allow navigating into children nodes on filtered lists
		$fields = array(
			'getTreeTitle' => _t('SiteTree.PAGETITLE', 'Page Title'),
			'Created' => _t('SiteTree.CREATED', 'Date Created'),
			'LastEdited' => _t('SiteTree.LASTUPDATED', 'Last Updated'),
		);
		$gridField->getConfig()->getComponentByType('GridFieldSortableHeader')->setFieldSorting(array('getTreeTitle' => 'Title'));

		if(!$params) {
			$fields = array_merge(array('listChildrenLink' => ''), $fields);
		}

		$columns->setDisplayFields($fields);
		$columns->setFieldCasting(array(
			'Created' => 'Datetime->Ago',
			'LastEdited' => 'Datetime->Ago',
			'getTreeTitle' => 'HTMLText'
		));

		$controller = $this;
		$columns->setFieldFormatting(array(
			'listChildrenLink' => function($value, &$item) use($controller) {
				$num = $item ? $item->numChildren() : null;
				if($num) {
					return sprintf(
						'<a class="cms-panel-link list-children-link" data-pjax-target="CurrentForm,Breadcrumbs" href="%s">%s</a>',
						Controller::join_links($controller->Link(), "?ParentID={$item->ID}"),
						$num
					);
				}
			},
			'getTreeTitle' => function($value, &$item) use($controller) {
				return '<a class="action-detail" href="' . $controller->Link('show') . '/' . $item->ID . '">' . $item->TreeTitle . '</a>';
			}
		));

		$listview = new Form(
			$this,
			'ListViewForm',
			new FieldList($gridField),
			new FieldList()
		);
		$listview->setAttribute('data-pjax-fragment', 'CurrentForm');

		$this->extend('updateListView', $listview);

		$listview->disableSecurityToken();
		return $listview;
	}


	public function getEditForm($id = null, $fields = null) {
		//load list view if current page not set
		if(!$id && !$this->currentPageID()) {
			return $this->ListViewForm();
		}

		return parent::getEditForm($id, $fields);

	}
	/**
	 * Overwrite to allow templates for actions
	 * @return string
	 */
	public function Content() {
		return $this->getActionTemplate();
	}

	public function getActionTemplate() {
		return ($action = $this->request->param('Action')) ? $this->renderWith($this->getTemplatesWithSuffix('_' . $action . '_Content')) : $this->renderWith($this->getTemplatesWithSuffix('_Content'));
	}

	/**
	 * Override {@link LeftAndMain} Link to allow URLSegment for section.
	 *
	 * @return string
	 */
	public function Link($action = null) {
		$link = Controller::join_links(
			$this->stat('url_base', true),
			$this->stat('url_segment', true), // in case we want to change the segment
			$this->section->URLSegment,
			'/', // trailing slash needed if $action is null!
			"$action"
		);
		$this->extend('updateLink', $link);
		return $link;
	}

	public function LinkPageEdit($id = null) {
		if(!$id) $id = $this->currentPageID();
		return $this->LinkWithSearch(
			Controller::join_links(
				$this->stat('url_base'),
				'admin-sections',
				$this->section->URLSegment,
				'show',
				$id
			)
		);
	}

	public function LinkPageSettings() {
		if($id = $this->currentPageID()) {
			return $this->LinkWithSearch(
				Controller::join_links(
					$this->stat('url_base'),
					'admin-sections',
					'settings',
					$this->section->URLSegment,
					'show',
					$id
				)
			);
		}
	}

	public function LinkPageHistory() {
		if($id = $this->currentPageID()) {
			return $this->LinkWithSearch(
				Controller::join_links(
					$this->stat('url_base'),
					'admin-sections',
					'history',
					$this->section->URLSegment,
					'show',
					$id
				)
			);
		}
	}


	/**
	 * Prevent CMSMain falling back to home page
	 * @return int|mixed
	 */
	public function currentPageID() {
		$id = LeftAndMain::currentPageID();

		return $id;
	}


	/**
	 * Caution: Volatile API.
	 *
	 * @return PjaxResponseNegotiator
	 */
	public function getResponseNegotiator() {
		if(!$this->responseNegotiator) {
			$controller = $this;
			$this->responseNegotiator = new PjaxResponseNegotiator(
				array(
					'CurrentForm' => function() use(&$controller) {
						return $controller->getEditForm()->forTemplate();
					},
					'Content' => function() use(&$controller) {
						return $controller->getActionTemplate();
					},
					'Breadcrumbs' => function() use (&$controller) {
						return $controller->renderWith('CMSBreadcrumbs');
					},
					'default' => function() use(&$controller) {
						return $controller->renderWith($controller->getViewer('show'));
					}
				),
				$this->response
			);
		}
		return $this->responseNegotiator;
	}

}