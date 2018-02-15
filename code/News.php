<?php
class News extends Page {
    private static $db = [
        "Date" => "Datetime",
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
    private static $default_sort = "Date DESC";

    /**
     * Sets the Date field to the current date.
     */
    public function populateDefaults() {
        $this->Date = date('Y-m-d');
        parent::populateDefaults();
    }

    function getCMSFields() {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab("Root.Main", new DateField('Date','Datum'));
        $fields->addFieldToTab("Root.Main", new TextareaField('Description', 'Kurzbeschreibung'));
        // $fields->addFieldToTab("Root.Main", new TextField('Tags', 'Tags (comma seperated)'));
        $fields->addFieldToTab("Root.Main", new UploadField('Image'));
        return $fields;
    }

    function EditFields() {
        $fields = parent::EditFields();
        $fields->removeByName("ClassName");
        $fields->insertBefore('MetaDescription', new DateField('Date','Datum'));
        $fields->insertBefore('MetaDescription', new TextareaField('Description', 'Kurzbeschreibung') );
        $fields->insertBefore('MetaDescription', new FileField('Image', 'Vorschaubild'));
        return $fields;
    }

}

class News_Controller extends Page_Controller {
}