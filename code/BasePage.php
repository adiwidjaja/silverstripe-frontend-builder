<?php
class BasePage extends SiteTree {
    private static $db = [
        "PageContent" => "Text"
    ];
    private static $defaults = [
        "Title" => "Neue Unterseite"
    ];

    public function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->removeByName("Content");
        $fields->addFieldToTab("Root.Main", new TextareaField("PageContent", "Inhalt"));
        // $fields->replaceField("Content", new TextareaField("PageContent", "Inhalt"));
        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    public function CurrentLocaleInformation() {
        return $this->LocaleInformation($this->CurrentLocale());
    }

    public function CurrentLanguage() {
        return explode("_", $this->CurrentLocale())[0];
    }

    function EditFields() {
        $fields = new FieldList();
        $fields->push( new TextField( 'Title', $this->fieldLabel('Title') ) );
        $fields->push( new TextField( 'MenuTitle', $this->fieldLabel('MenuTitle') ) );
        $fields->push( new CheckboxField( 'ShowInMenus', $this->fieldLabel('ShowInMenus') ) );
        //$fields->push( new TextField( 'MetaTitle', $this->fieldLabel('MetaTitle') ) );
        $fields->push( new TextareaField( 'MetaDescription', $this->fieldLabel('MetaDescription') ) );
        return $fields;
    }

    public function NewChildClass() {
        if(sizeof($this->data()->stat('allowed_children')) == 0)
            return null;
        return $this->data()->stat('default_child');
    }

}
class BasePage_Controller extends ContentController {

    private static $allowed_actions = array(
        'publish',
        'rollback',
        'savecontent',
        'editorconf',
        'login',
        'saveform',
        "EditForm",
        "edit",
        "AddForm",
        "add"
    );
    function CurrentVersion() {
        return Versioned::current_stage();
    }

    function BaseAdminLink() {
        return Config::inst()->get("silverstripe-frontend-builder", "base-admin-link");
    }

    function EditMode() {
        return $this->CurrentVersion() == "Stage" && $this->CanEdit();
    }

    function login() {
        Session::set("BackURL", $this->Link());
        $this->redirect("Security/login");
    }

    function RenderSection($section, $conf) {
        return BaseSection::create($section, $conf)->render();
    }

    //Apply pre-render hooks
    function PrepareSection($section) {
        $conf = Config::inst()->get("silverstripe-frontend-builder", "modules");
        $section->content = BaseSection::create($section, $conf)->beforeRender($section->content, true);
        return $section;
    }

    function RenderContent() {
        $conf = Config::inst()->get("silverstripe-frontend-builder", "modules");
        $content = json_decode($this->PageContent);
        if(!$content || !$content->sections) {
            return "";
        } else {
            $result = "";
            foreach($content->sections as $section) {
                $rendered = $this->RenderSection($section, $conf);
                $result.=$rendered;
            }
        }
        return $result;
    }

    function prepareJsonContent($content) {
        $content = json_decode($content);
        foreach($content->sections as $section) {
            $section = $this->PrepareSection($section);
        }
        return json_encode($content);
    }

    function JsonContent() {
        if($this->PageContent) {
            $content = $this->prepareJsonContent($this->PageContent);
            return json_encode($content, JSON_FORCE_OBJECT);
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
        $this->redirect($this->Link()."?stage=Stage");
    }

    function prepareSaveContent($content) {
        $prepared_sections = [];
        foreach($content->sections as $section) {
            $prepared_sections[] = $this->PrepareSaveSection($section);
        }
        $content->sections = $prepared_sections;
        return $content;
    }

    //Apply pre-save hooks
    function PrepareSaveSection($section) {
        $conf = Config::inst()->get("silverstripe-frontend-builder", "modules");
        $section->content = BaseSection::create($section, $conf)->beforeSave($section->content);
        return $section;
    }

    function savecontent() {
        $content = $this->getRequest()->postVar("PageContent");

        $content = json_encode($this->prepareSaveContent(json_decode($content)));

        $page = $this->data();
        $page->PageContent = $content;
        $page->write();

        //Return modified json
        $content = $this->prepareJsonContent($content);
        $this->getResponse()->addHeader('Content-Type', 'application/json');
        return json_encode($content);
    }

    function saveform() {
        $newpage = $this->getRequest()->postVar("NewPage");
        $content = $this->getRequest()->postVar("FormData");

        if($newpage) {
            $item = new Page();
            $item->ParentID = $this->ID;
            $item->URLSegment = $content['Title'];
        } else {
            $item = $this->data();
        }
        foreach($content as $name => $value) {
            $item->$name = $value;
        }
        $item->write();


        return $item->Link();
    }

    //Move to global controller function
    function editorconf() {
        $groups = Config::inst()->get("silverstripe-frontend-builder", "groups");
        $modules = Config::inst()->get("silverstripe-frontend-builder", "modules");
        $base_path = Director::baseFolder();
        $data = array(
            // "editform" => [
            //     "title" => "Seite bearbeiten",
            //     "description" => "",
            //     "type" => "object",
            //     "required" => [
            //         "Title"
            //     ],
            //     "properties" => [
            //         "Title" => [
            //             "type" => "string",
            //             "title"=> "Titel"
            //         ],
            //         "MenuTitle" => [
            //             "type" => "string",
            //             "title"=> "Menü-Titel"
            //         ],
            //         "URLSegment" => [
            //             "type" => "string",
            //             "title"=> "URL-Segment"
            //         ],
            //         "ShowInMenus" => [
            //             "type" => "boolean",
            //             "title"=> "In Menüs anzeigen",
            //             "default" => true
            //         ],
            //         "MetaTitle" => [
            //             "type" => "string",
            //             "title"=> "Meta-Titel",
            //             "default" => ""
            //         ],
            //         "MetaDescription" => [
            //             "type" => "string",
            //             "title"=> "Meta-Description",
            //             "default" => ""
            //         ]
            //     ]
            // ],
            // "pagedata" => [
            //     "Title" => $this->Title,
            //     "MenuTitle" => $this->Title,
            //     "ShowInMenus" => $this->ShowInMenus?true:false,
            //     "URLSegment" => $this->URLSegment,
            //     "MetaTitle" => $this->MetaTitle?$this->MetaTitle:"",
            //     "MetaDescription" => $this->MetaDescription?$this->MetaDescription:""
            // ],
            "groups" => $groups,
            "elements" => array()
        );

        foreach($modules as $name => $moduleconf) {
            $section = BaseSection::createabstract($name, $modules);
            $template = $section->getTemplate();
            $formdef = $section->getFormDef();
            $preview = $section->getPreview();

            $data["elements"][$name] = array(
                "name" => $moduleconf["name"],
                "template" => $template,
                "formdef" => $formdef,
                "preview" => $preview,
                "size" => $section->getSize()
            );
        }
        $this->getResponse()->addHeader('Content-Type', 'application/json');
        return json_encode($data);
    }

    function EditForm() {
        $fields = $this->EditFields();
        $actions = new FieldList(
            FormAction::create('save', _t("Page.SAVE","Sichern"))->setStyle("primary")
        );
        $validator = new RequiredFields('Title');
        $form = BootstrapForm::create($this, 'EditForm',$fields, $actions,$validator);
        $form->loadDataFrom($this);
        return $form;
    }

    function save($data, $form) {
        $item = $this->data();
        $form->saveInto($item);
        $item->write();
        $this->redirect($this->Link()."?stage=Stage");
    }

    function edit() {
        $template = SSViewer::fromString("\$Form");
        return $this->renderWith($template, [ "Form" => $this->EditForm() ]);
    }

    function AddForm() {
        $addclass = $this->data()->stat('default_child');

        $addmodel = singleton($addclass);

        $fields = $addmodel->EditFields();
        $actions = new FieldList(
            FormAction::create('saveAdd', _t("Page.SAVE","Sichern"))->setStyle("primary")
        );
        $validator = new RequiredFields('Title');
        $form = BootstrapForm::create($this, 'AddForm',$fields, $actions,$validator);
        $form->loadDataFrom($addmodel);
        return $form;
    }

    function saveAdd($data, $form) {
        $addclass = $this->data()->stat('default_child');
        $item = $addclass::create();
        $form->saveInto($item);
        $item->ParentID = $this->ID;
        $item->write();
        $this->redirect($item->Link()."?stage=Stage");
    }

    function add() {
        $template = SSViewer::fromString("\$Form");
        return $this->renderWith($template, [ "Form" => $this->AddForm() ]);
    }

}
