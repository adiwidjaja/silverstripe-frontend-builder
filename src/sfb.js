require("./scss/sfb-styles.scss");
import FrontendPageBuilder from "frontend-pagebuilder";

import $ from "jquery";

//TODO: Move
var editorconf = {
    elements: {
        intro: {
            name: 'Intro',
            template: `<section class="section section--intro">
                    <div class="section_content">
                        <h1 data-sfb-value="headline" data-sfb-valuetype="string">{{headline}}{{^headline}}Headline{{/headline}}</h1>
                        <div class="section_content_text" data-sfb-value="text" data-sfb-valuetype="rich">
                        {{& text}}{{^text}}<p>Lorem ipsum dolor sit amet</p>{{/text}}
                        </div>
                    </div>
                </section>`,
            formdef: {
              "title": "Intro: Bearbeiten",
              "description": "",
              "type": "object",
              "required": [
                "headline",
                "text"
              ],
              "properties": {
                "headline": {
                  "type": "string",
                  "title": "Headline"
                },
                "text": {
                  "type": "string",
                  "title": "Text"
                }
              }
            }
        },
        image: {
            name: 'Großes Bild',
            preview: 'mysite/sections/bigimage.png',
            template: `<section class="section section--image">
                <div class="section_content">
                    <img src="mysite/photos/2016_06_08_TechnoZ-571.jpg"/>
                    <div class="section_overlay">
                        <div class="section_overlay_content">
                            <h2 data-sfb-value="headline" data-sfb-valuetype="string">{{headline}}{{^headline}}Headline{{/headline}}</h2>
                            <div data-sfb-value="text" data-sfb-valuetype="rich">
                            {{text}}{{^text}}<p>Lorem ipsum dolor sit amet</p>{{/text}}
                            </div>
                        </div>
                    </div>
                    {{#disrupter}}
                    <div class="section_disrupter">
                        <div class="section_disrupter_content" data-sfb-value="disruptertext" data-sfb-valuetype="rich">
                            {{disruptertext}}{{^disruptertext}}<h2>Störertext<br/><a href="#">weiter</a></h2>{{/disruptertext}}
                        </div>
                    </div>
                    {{/disrupter}}
                </div>
            </section>`,
            formdef: {
              // "title": "Großes Bild: Bearbeiten",
              // "description": "",
              "type": "object",
              "required": [
              ],
              "properties": {
                "image": {
                  "type": "string",
                  "format": "data-url",
                  "title": "Bild"
                },
                "disrupter": {
                  "type": "boolean",
                  "title": "Störer anzeigen",
                  "default": false
                }
              }
            }
        }
    }
};


const fpb = new FrontendPageBuilder();
// console.log(content);
// console.log(JSON.parse(content));
fpb.init("[data-fpb-content]", JSON.parse(content), editorconf);

window.document.getElementById("sfb-save-page").addEventListener("click", e => {
    const content = JSON.stringify({
            "sections": fpb.getContent()
        });
    const data = {
        'PageContent': content
    };
    // var request = new XMLHttpRequest();
    // request.open('post', baseurl+"savecontent", true);
    // request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    // request.send({
    //     PageContent: content
    // });
    // request.onreadystatechange = function () {
    //     if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
    //         alert("Ok");
    //     }
    // };
    $.post(baseurl+"savecontent", data, function() {
        alert("Ok");
    });
});
