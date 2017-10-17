<?php
class EventPage extends NewsPage {
    static $db = array(
    );
    static $has_one = array(
    );
    private static $allowed_children = array("Event");
    private static $default_child = "Event";
}

class EventPage_Controller extends NewsPage_Controller {

    public function GetNews() {
        $news = Event::get()
            ->where("DATE(StartDate) >= CURDATE() AND (DATE(EndDate) > CURDATE() OR EndDate IS NULL)")
            ->sort("StartDate", "ASC");

        return $news;
    }
}