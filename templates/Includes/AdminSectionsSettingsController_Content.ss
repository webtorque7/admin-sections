<div id="pages-controller-cms-content" class="cms-content center cms-tabset $BaseCSSClasses" data-layout-type="border" data-pjax-fragment="Content" data-ignore-tab-state="true">

	<div class="cms-content-header north">
		<div class="cms-content-header-info">
			<h2>
				<% include AdminSectionsBreadcrumbs %>
			</h2>
		</div>
	
		<div class="cms-content-header-tabs">
			<ul>
				<li class="content-treeview">
					<a href="$LinkPageEdit" class="cms-panel-link" title="Form_EditForm" data-href="$LinkPageEdit">
						<% _t('CMSMain.TabContent', 'Content') %>
					</a>
				</li>
				<li class="content-listview ui-tabs-active">
					<a href="$LinkPageSettings" class="cms-panel-link" title="Form_EditForm" data-href="$LinkPageSettings">
						<% _t('CMSMain.TabSettings', 'Settings') %>
					</a>
				</li>
				<li class="content-listview">
					<a href="$LinkPageHistory" class="cms-panel-link" title="Form_EditForm" data-href="$LinkPageHistory">
						<% _t('CMSMain.TabHistory', 'History') %>
					</a>
				</li>
			</ul>
		</div>
	</div>

	$EditForm
	
</div>