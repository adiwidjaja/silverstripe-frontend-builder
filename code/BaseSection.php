<?php

use EVought\DataUri\DataUri;

class BaseSection {

    public function __construct($section, $conf) {
        $this->section = $section;
        $this->conf = $conf;
    }

    //Abstract factory
    public static function create($section, $conf) {
        $class = "BaseSection";

        if(array_key_exists("class", $conf[$section->type])) {
            $class = $conf[$section->type]["class"];
        }
        //TODO: Lookup section classes
        return new $class($section, $conf);
    }

    public function getFormDef() {
        //Fetch dynamic lists
    }

    function getExtension($mimetype) {
        if(empty($mimetype)) return "";
        switch($mimetype)
        {
            case 'image/gif': return '.gif';
            case 'image/jpeg': return '.jpg';
            case 'image/png': return '.png';
            case 'application/pdf': return '.pdf';
            default:
                $mimearray = explode("/", $mimetype);
                return array_pop($mimearray);
        }
    }

    public function isImage($mimetype) {

        if(empty($mimetype)) return false;

        if(in_array($mimetype, array('image/gif', 'image/jpeg', 'image/png')))
            return true;
        return false;

    }

    public function beforeSave($content) {
        $conf = $this->conf;

        foreach($this->getSubSections() as $name => $listcontent) {
                $preparedlist = [];
                foreach($listcontent as $subsection) {
                     $subsection->content = BaseSection::create($subsection, $conf)->beforeSave($subsection->content);
                     $preparedlist[] = $subsection;
                }
                $content->$name = $preparedlist;
        }

        //Save, replace images
        foreach($content as $name => $value) {
            if(is_array($value)) {
                //Skip children
            } else {
                if(strpos($value, "data:image") === 0) {
                    DataUri::tryParse($value, $data);

                    $mediaType = explode(";", $data->getMediaType());
                    $mime = $mediaType[0];

                    $filetype = $this->getExtension($mime);

                    $folder = Folder::find_or_make("autoupload");
                    $filename = 'upload_'.RAND(1,10000).$filetype;
                    $filepath = Director::baseFolder()."/assets/autoupload/".$filename;
                    $relfilepath = "assets/autoupload/".$filename;
                    file_put_contents($filepath, base64_decode($data->getEncodedData()));

                    if($this->isImage($mime)) {
                        $image = new Image(array(
                            "Filename" => $relfilepath,
                            "ParentID" => $folder->ID,
                            "Name" => $filename,
                            "Title" => $filename
                        ));
                        $image->write();

                        $realname = str_replace("load_", "", $name);
                        unset($content->$name);
                        $content->$realname = "image:".$image->ID;
                    } else {
                        $file = new File(array(
                            "Filename" => $relfilepath,
                            "ParentID" => $folder->ID,
                            "Name" => $filename,
                            "Title" => $filename
                        ));
                        $file->write();
                        $realname = str_replace("load_", "", $name);
                        unset($content->$name);
                        $content->$realname = "file:".$file->ID;
                    }
                }
            }
        }
        return $content;
    }

    public function getSubSections() {
        $section = $this->section;
        $conf = $this->conf;

        $subsections = [];
        if(array_key_exists("subsections", $conf[$section->type])) {
            $subsectionlists = $conf[$section->type]["subsections"];
            foreach($subsectionlists as $subsectionlistname) {
                $listcontent = $section->content->$subsectionlistname;
                $subsections[$subsectionlistname] = $listcontent;
            }
        }
        return $subsections;
    }

    public function beforeRender($content, $prepareChildren=false) {

        if($prepareChildren) {
            $section = $this->section;
            $conf = $this->conf;
            //Subsections

            foreach($this->getSubSections() as $name => $listcontent) {
                    $preparedlist = [];
                    foreach($listcontent as $subsection) {
                         $subsection->content = BaseSection::create($subsection, $conf)->beforeRender($subsection->content);
                         $preparedlist[] = $subsection;
                    }
                    $content->$name = $preparedlist;
            }
        }

        //Render images here
        foreach($content as $name => $value) {
            if(is_array($value)) {
                continue;
            }
            if(strpos($value, "image:") === 0) {
                $id = intval(str_replace("image:", "", $value));
                $image = Image::get()->byId($id);

                //Resize here
                $content->$name = $image->URL;
            }
        }

        return $content;
    }

    public function render() {
        $engine = new Mustache_Engine;
        $section = $this->section;
        $conf = $this->conf;

        $templatesrc = $conf[$section->type]["template"];
        $base_path = Director::baseFolder();
        $template = file_get_contents($base_path."/".$templatesrc);

        $content = $this->beforeRender($section->content, false);

        //Subsections
        foreach($this->getSubSections() as $name => $listcontent) {
                $rendered_subcontent = "";
                foreach($listcontent as $subsection) {
                    $rendered_subcontent .= BaseSection::create($subsection, $conf)->render();
                }
                $content->$name = $rendered_subcontent;
        }

        return $engine->render($template, $content);

    }
}