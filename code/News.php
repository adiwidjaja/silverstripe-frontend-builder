<?php
class News extends Page {
    private static $db = [
        "Date" => "Date",
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
        // $fields->addFieldToTab("Root.Main", new UploadField('Image'));
        return $fields;
    }

    function EditFields() {
        $fields = parent::EditFields();
        $fields->insertBefore('MetaTitle', new DateField('Date','Datum'));
        $fields->insertBefore('MetaTitle', new TextareaField('Description', 'Kurzbeschreibung') );
        // $fields->insertBefore('MetaTitle', new UploadField('Image'));
        return $fields;
    }

}

class News_Controller extends Page_Controller {
}