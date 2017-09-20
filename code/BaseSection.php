<?php

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

    public function beforeSave($content) {
        //Save, replace images. Shortcodes?
        foreach($content as $name => $value) {
            if(is_array($value)) {
                //Children
            } else {
                if(strpos($value, "data:image") === 0) {
                    // print "YEAH";die();
                    // DataUri::tryParse($value, $out);
                    // print "HUHU";
                    // print_r($out);die();
                    // preg_match_all("/(url\(data:image\/(jpeg|gif|png);base64,(.*)\))/si", $value, $vdata);
                    // print_r($vdata);
                    // if (count($vdata[0])) {
                    //     for($i=0;$i<count($vdata[0]);$i++) {
                    //         $file = Director::baseFolder()."assets/autoupload".'_tempCSSidata'.RAND(1,10000).'_'.$i.'.'.$idata[2][$i];
                    //         print $file;die();
                    //         //Save to local file
                    //         file_put_contents($file, base64_decode($idata[3][$i]));
                    //         // $CSSstr = str_replace($idata[0][$i], 'url("'.$file.'")', $CSSstr);  // mPDF 5.5.17
                    //     }
                    // }
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