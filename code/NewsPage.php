<?php
class NewsPage extends Page {
    static $db = array(
    );
    static $has_one = array(
    );
    private static $allowed_children = array("News");
    private static $default_child = "News";
}

class NewsPage_Controller extends Page_Controller {

    public function GetNews() {
        $news = News::get()->filter("ParentID", $this->ID)->sort("Date", "DESC");

        //TODO Paging

        return $news;
    }
}