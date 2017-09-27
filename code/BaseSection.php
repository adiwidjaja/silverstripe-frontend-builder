<?php

use EVought\DataUri\DataUri;

class BaseSection {

    public function __construct($type, $conf, $section=null) {
        $this->type = $type;
        $this->section = $section;
        $this->conf = $conf;
    }

    //Abstract factory
    //Needs conf to create subobjects
    public static function create($section, $conf) {
        $class = "BaseSection";

        if(array_key_exists("class", $conf[$section->type])) {
            $class = $conf[$section->type]["class"];
        }
        return new $class($section->type, $conf, $section);
    }

    //Create without instance
    public static function createabstract($type, $conf) {
        $class = "BaseSection";

        if(array_key_exists("class", $conf[$type])) {
            $class = $conf[$type]["class"];
        }
        return new $class($type, $conf);
    }

    public function getTemplate() {
        $base_path = Director::baseFolder();
        $moduleconf = $this->conf[$this->type];

        $templatefile = $base_path."/".$moduleconf["template"];
        $handle = fopen($templatefile, "r");
        $template = fread($handle, filesize($templatefile));
        fclose($handle);
        return $template;
    }

    public function getFormDef() {
        $base_path = Director::baseFolder();
        $moduleconf = $this->conf[$this->type];

        if(array_key_exists("formdef", $moduleconf)) {
            $formdeffile = $base_path."/".$moduleconf["formdef"];
            $handle = fopen($formdeffile, "r");
            $formdef = fread($handle, filesize($formdeffile));
            fclose($handle);
            $formdef_json = json_decode($formdef, true);
        } else {
            $formdef_json = null;
        }
        return $formdef_json;
    }

    public function getPreview() {
        $moduleconf = $this->conf[$this->type];
        $base_url = Director::baseURL();

        if(array_key_exists("preview", $moduleconf)) {
            return $moduleconf["preview"];
        }
        return;
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

    public function replaceImages($content, $ignore=[]) {
        //Save, replace images
        foreach($content as $name => $value) {
            if(in_array($name, $ignore)) {
                //Skip children
            } else {
                if(is_array($value)) {
                    $newvalue = [];
                    foreach($value as $subcontent) {
                         $newcontent = $this->replaceImages($subcontent); //No ignore
                         $newvalue[] = $newcontent;
                    }
                    $content->$name = $newvalue;
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
                            $savename = str_replace("load_", "save_", $name);
                            unset($content->$name);
                            $content->$savename = "image:".$image->ID;
                        } else {
                            $file = new File(array(
                                "Filename" => $relfilepath,
                                "ParentID" => $folder->ID,
                                "Name" => $filename,
                                "Title" => $filename
                            ));
                            $file->write();
                            $realname = str_replace("load_", "", $name);
                            $savename = str_replace("load_", "save_", $name);
                            unset($content->$name);
                            $content->$savename = "file:".$file->ID;
                        }
                    }
                }
            }
        }
        return $content;
    }

    public function beforeSave($content) {
        $conf = $this->conf;

        $subsection_names = [];

        foreach($this->getSubSections() as $name => $listcontent) {
            $subsection_names[] = $name;
            $preparedlist = [];
            foreach($listcontent as $subsection) {
                 $subsection->content = BaseSection::create($subsection, $conf)->beforeSave($subsection->content);
                 $preparedlist[] = $subsection;
            }
            $content->$name = $preparedlist;
        }

        $content = $this->replaceImages($content, $subsection_names);

        return $content;
    }

    public function getSubSections() {
        $section = $this->section;
        $conf = $this->conf;

        $subsections = [];
        if(array_key_exists("subsections", $conf[$section->type])) {
            $subsectionlists = $conf[$section->type]["subsections"];
            foreach($subsectionlists as $subsectionlistname) {
                $listcontent = $section->content->$subsectionlistname?$section->content->$subsectionlistname:[];
                $subsections[$subsectionlistname] = $listcontent;
            }
        }
        return $subsections;
    }

    public function getImageInfos() {
        $conf = $this->conf[$this->section->type];
        if(!isset($conf["images"]))
            return [];
        return $conf["images"];
    }

    public function renderImages($content, $ignore=[]) {
        $image_infos = $this->getImageInfos();

        foreach($content as $name => $value) {
            if(in_array($name, $ignore)) {
                continue; //Skip subsections
            } else {
                if(is_array($value)) {
                    $newvalue = [];
                    foreach($value as $subcontent) {
                        $newvalue[] = $this->renderImages($subcontent, []);
                    }
                    $content->$name = $newvalue;
                } else {
                    if(strpos($value, "image:") === 0) {
                        $id = intval(str_replace("image:", "", $value));
                        $image = Image::get()->byId($id);

                        $realname = str_replace("save_", "", $name);

                        if($image_infos && isset($image_infos[$realname])) {
                            $info = $image_infos[$realname];
                            if($image)
                                $image = $image->getFormattedImage($info["method"], isset($info["width"])?$info["width"]:0, isset($info["height"])?$info["height"]:0);
                        }
                        if($image) {
                            $content->$realname = $image->URL;
                        }
                    }
                }
            }
        }
        return $content;
    }

    public function beforeRender($content, $prepareChildren=false) {

        $subsection_names = [];
        $section = $this->section;
        $conf = $this->conf;
        //Subsections

        foreach($this->getSubSections() as $name => $listcontent) {
            $preparedlist = [];
            $subsection_names[] = $name;
            if($prepareChildren) {
                foreach($listcontent as $subsection) {
                     $subsection->content = BaseSection::create($subsection, $conf)->beforeRender($subsection->content);
                     $preparedlist[] = $subsection;
                }
            }
            $content->$name = $preparedlist;
        }

        $content = $this->renderImages($content, $subsection_names);

        return $content;
    }

    public function render() {
        $engine = new Mustache_Engine;
        $section = $this->section;
        $conf = $this->conf;

        $templatesrc = $conf[$section->type]["template"];
        $base_path = Director::baseFolder();
        $template = file_get_contents($base_path."/".$templatesrc);

        $content = $this->beforeRender($section->content, true);

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