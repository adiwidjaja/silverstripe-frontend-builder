require("./scss/sfb-styles.scss");
import FrontendPageBuilder from "frontend-pagebuilder";

import $ from "jquery";

$.get(baseurl+"editorconf", function(conf) {
    let savetimeout = null;
    let contentjson = JSON.parse(content);
    let saving = false;

    if(!contentjson.sections)
        contentjson.sections = [];
    let editordata = contentjson.sections;

    const fpb = new FrontendPageBuilder(function(neweditordata, instantsave) {
        editordata = neweditordata;

        $("#save-status").removeClass("label-warning label-success").addClass("label-danger").text("Geändert");

        if(savetimeout) {
            // console.log("clear timeout");
            clearTimeout(savetimeout);
        }


        if(instantsave) {
            // console.log("Saving in 0.5 sec");
            savetimeout = setTimeout(function() {
                saveContent();
            }, 500);
        } else {
        }

    });

    const saveContent = function() {
        const content = JSON.stringify({
            "sections": editordata
        });
        const data = {
            'PageContent': content
        };
        if(saving)
            return;
        if(savetimeout) {
            clearTimeout(savetimeout);
        }
        $("#save-status").removeClass("label-danger label-success").addClass("label-warning").text("Speichert...");
        saving = true;

        $.post(baseurl+"savecontent", data, function(feedback) {
            saving = false;
            const contentjson = JSON.parse(feedback);
            fpb.setContent(contentjson.sections);
            // alert("Ok");
            $("#save-status").removeClass("label-danger label-warning").addClass("label-success").text("Gespeichert");
        }, "json");
    }

    fpb.init("[data-fpb-content]", editordata, conf, editmode);

    $("#sfb-save-page").click(function(e) {
        e.preventDefault();
        saveContent();
    })

    // JSON based forms?
    // $("#sfb-edit-page").click(function(e) {
    //     e.preventDefault();
    //     fpb.getModal().showForm(editform, pagedata, "Seiteneigenschaften", function(formdata) {
    //         const data = {
    //             'FormData': formdata
    //         }
    //         $.post(baseurl+"saveform", data, function(newurl) {
    //             if(newurl == window.location.href)
    //                 window.location.reload();
    //             else
    //                 window.location.href = newurl;
    //         })
    //     });
    // })
    $("#sfb-edit-page").click(function(e) {
        e.preventDefault();
        $.get(baseurl+"edit", function(loadedform) {
            fpb.getModal().showModal(loadedform, "Seiteneigenschaften", false);
        });
    })
    // $("#sfb-new-page").click(function(e) {
    //     e.preventDefault();
    //     fpb.getModal().showForm(editform, {}, "Seiteneigenschaften", function(formdata) {
    //         const data = {
    //             'NewPage': 1,
    //             'FormData': formdata
    //         }
    //         $.post(baseurl+"saveform", data, function(newurl) {
    //             if(newurl == window.location.href)
    //                 window.location.reload();
    //             else
    //                 window.location.href = newurl;
    //         })
    //     });
    // })

    $("#sfb-new-page").click(function(e) {
        e.preventDefault();
        $.get(baseurl+"add", function(loadedform) {
            fpb.getModal().showModal(loadedform, "Neue Seite", false);
        });
    })

    // window.document.getElementById("sfb-save-page").addEventListener("click", e => {
    //     const content = JSON.stringify({
    //             "sections": fpb.getContent()
    //         });
    //     const data = {
    //         'PageContent': content
    //     };
    //     $.post(baseurl+"savecontent", data, function(feedback) {
    //         fpb.setContent(JSON.parse(feedback));
    //         // alert("Ok");
    //     }, "json");
    // });

})
