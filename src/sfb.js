require("./scss/sfb-styles.scss");
import Sortable from "sortablejs";
var tinymce = window.tinymce;

function ready(fn) {
  if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading"){
    fn();
  } else {
    document.addEventListener('DOMContentLoaded', fn);
  }
}

// forEach method, could be shipped as part of an Object Literal/Module
var forEach = function (array, callback, scope) {
  for (var i = 0; i < array.length; i++) {
    callback.call(scope, i, array[i]); // passes back stuff we need
  }
};

tinymce.init({
    selector: '[data-sfb-valuetype="string"]',
    inline: true,
    toolbar: 'undo redo',
    menubar: false
});

tinymce.init({
    selector: '[data-sfb-valuetype="rich"]',
    inline: true,
    // toolbar: 'undo redo',
    menubar: false
});

function serializeContent() {
    var areas = document.querySelectorAll("[data-sfb-content]");
    forEach(areas, function(i, area){
        var data = [];
        var sections = area.querySelectorAll("[data-sfb-section]");
        forEach(sections, function(i, section){
            var sectiondata = {};
            var fields = section.querySelectorAll("[data-sfb-value]");
            forEach(fields, function(i, field) {
                var name = field.getAttribute("data-sfb-value");
                var type = field.getAttribute("data-sfb-valuetype");
                var value = field.innerHTML;
                sectiondata[name] = value;
            });
            data.push(sectiondata);
        });
        console.log(JSON.stringify(data));
    });
}

var buttonrow = '<ul class="sfb-tools"><li class="sfb-tools_handle"></li><li class="sfb-tools_edit"></li><li class="sfb-tools_trash"></li><li class="sfb-tools_show"></li></ul><div class="sfb-overlay"><span></span><span></span><span></span><span></span></div>'

ready(function() {

    var areas = document.querySelectorAll("[data-sfb-content]");
    forEach(areas, function(i, area){
        Sortable.create(area, {
            animation: 150,
            // draggable: "[data-sfb-section]",
            handle: ".sfb-tools_handle"
        });
        var sections = area.querySelectorAll("[data-sfb-section]");
        forEach(sections, function(i, section){
            section.insertAdjacentHTML('afterbegin', buttonrow);
            // section.classList.add("sfb-draggable");
        });
    });

    document.getElementById("sfb-save").addEventListener("click", function(e) {
        serializeContent();
    });
});