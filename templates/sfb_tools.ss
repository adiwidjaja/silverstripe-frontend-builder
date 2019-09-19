        <% if not $CanEdit %>
        <div class="fpb sfb-login">
            <a class="sfb-logo" href="$RelativeLink(login)">
                CMS
            </a>&nbsp;
            <a href="$Link(login)">Login</a>
        </div>
        <% end_if %>
        <% if CanEdit %>
        <div class="fpb sfb-nav">
            <% if CurrentVersion == Stage %>
                <div class="sfb-stages">
                    <a class="sfb-logo" href="{$BaseAdminLink}/admin/pages/edit/show/$ID" target="_blank">
                        CMS
                    </a>&nbsp;
                    <div class="btn-group" role="group">
                        <span class="btn btn-info">Bearbeitungsversion</span>
                        <a class="btn btn-default" href="$Link?stage=Live">Liveversion</a>
                    </div>
                </div>
                <div class="sfb-tools">
                    <a class="btn btn-default" href="#" id="sfb-edit-page">Eigenschaften</a>
                    <% if $NewChildClass == "Page" %>
                        <a class="btn btn-default" href="#" id="sfb-new-page">Neue Unterseite</a>
                    <% end_if %>
                    <% if $NewChildClass == "News" %>
                        <a class="btn btn-default" href="#" id="sfb-new-page">Neue News</a>
                    <% end_if %>
                    <% if $NewChildClass == "Event" %>
                        <a class="btn btn-default" href="#" id="sfb-new-page">Neue Veranstaltung</a>
                    <% end_if %>
                </div>
                <div class="sfb-publishing">
                    <% if CurrentVersion == Stage %>
                    <div class="save-status label" id="save-status">
                    </div>
                    <a id="sfb-save-page" class="btn btn-link" href="javascript:;">Speichern</a>
                    <a id="rollback-page" class="btn btn-link" href="$Link(rollback)">Änderungen verwerfen</a>
                    <a id="publish-page" class="btn btn-primary" href="$Link(publish)">Veröffentlichen</a>
                    <% end_if %>
                </div>
            <% else %>
                <div class="sfb-stages">
                    <a class="sfb-logo" href="{$BaseAdminLink}/admin/pages/edit/show/$ID" target="_blank">
                        CMS
                    </a>&nbsp;
                    <div class="btn-group" role="group">
                        <a class="btn btn-default" href="$Link?stage=Stage">Bearbeitungsversion</a>
                        <span class="btn btn-info">Live-Version</span>
                    </div>
                </div>
            <% end_if %>
        </div>
        <script src="silverstripe-frontend-builder/js/sfb.js?v=2"></script>
        <% end_if %>
