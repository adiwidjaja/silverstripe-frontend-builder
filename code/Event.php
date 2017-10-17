<?php
class Event extends Page {
    private static $db = [
        "StartDate" => "Datetime",
        "EndDate" => "Datetime",
        "Description" => "Text",
        "Tags" => "Text"
    ];
    private static $has_one = [
        "Image" => "Image",
    ];
    private static $defaults = [
        "Title" => "Neue News"
    ];
    private static $allowed_children = [];
    private static $default_sort = "StartDate ASC";

    /**
     * Sets the Date field to the current date.
     */
    public function populateDefaults() {
        $this->StartDate = date('Y-m-d');
        parent::populateDefaults();
    }

    function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab("Root.Main", new DateField('StartDate','Stadtdatum'));
        $fields->addFieldToTab("Root.Main", new DateField('EndDate','Enddatum'));
        $fields->addFieldToTab("Root.Main", new TextareaField('Description', 'Kurzbeschreibung'));
        // $fields->addFieldToTab("Root.Main", new TextField('Tags', 'Tags (comma seperated)'));
        $fields->addFieldToTab("Root.Main", new UploadField('Image'));
        return $fields;
    }

    function EditFields() {
        $fields = parent::EditFields();
        $fields->insertBefore('MetaDescription', new DateField('StartDate','Startdatum'));
        $fields->insertBefore('MetaDescription', new DateField('EndDate','Enddatum'));
        $fields->insertBefore('MetaDescription', new TextareaField('Description', 'Kurzbeschreibung') );
        $fields->insertBefore('MetaDescription', new UploadField('Image', 'Vorschaubild'));
        return $fields;
    }

}

class Event_Controller extends Page_Controller {
}