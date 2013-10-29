<div id="pages-controller-cms-content" class="cms-content center cms-tabset $BaseCSSClasses" data-layout-type="border" data-pjax-fragment="Content">

    <div class="cms-content-header north">
        <div class="cms-content-header-info">
            <h2>
		   <% include AdminSectionsBreadcrumbs %>
            </h2>
        </div>

    </div>

    $Tools

    <div class="cms-content-fields center ui-widget-content cms-panel-padded">

        <div class="cms-content-view" id="cms-content-view">
            $ListViewForm
        </div>
        <!--
        <div id="cms-content-galleryview">
            <i>Not implemented yet</i>
        </div>
        -->

    </div>

</div>