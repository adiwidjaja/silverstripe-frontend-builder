<?php
class BasePage extends SiteTree {
    private static $db = [
        "PageContent" => "Text"
    ];

    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->removeByName("Content");
        $fields->addFieldToTab("Root.Main", new TextareaField("PageContent", "Inhalt"));
        // $fields->replaceField("Content", new TextareaField("PageContent", "Inhalt"));
        return $fields;
    }

}
class BasePage_Controller extends ContentController {

    private static $allowed_actions = array(
        'publish',
        'rollback',
        'savecontent',
    );
    function CurrentVersion() {
        return Versioned::current_stage();
    }

    function EditMode() {
        return $this->CurrentVersion() == "Stage" && $this->CanEdit();
    }

    function RenderContent() {
    }

    function JsonContent() {
        if($this->PageContent) {
            return json_encode($this->PageContent, JSON_FORCE_OBJECT);
        } else {
            return "\"{}\"";
        }
    }

    function publish() {
        //TODO Access? Only allow on Stage
        Versioned::reading_stage("Stage");
        $item = $this->data();
        $item->doPublish();

        $this->redirect($this->Link()."?stage=Live");
    }

    function performRollback($class, $id, $version) {
        $record = DataObject::get_by_id($class, $id);
        if($record && !$record->canEdit()) return Security::permissionFailure($this);

        $record->doRollbackTo($version);
        return $record;
    }

    function rollback() {
        $this->performRollback($this->ClassName, $this->ID, "Live");
        Versioned::reading_stage('Live');
        foreach($this->getComponents("Widgets") as $widget) {
            $this->performRollback($widget->ClassName, $widget->ID, "Live");
        }
        $this->redirect($this->Link()."?stage=Stage");
    }

    function savecontent() {
        $content = $this->getRequest()->postVar("PageContent");
        $page = $this->data();
        $page->PageContent = $content;
        $page->write();
    }

}