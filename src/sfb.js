require("./scss/sfb-styles.scss");
import FrontendPageBuilder from "frontend-pagebuilder";

import $ from "jquery";

$.get(baseurl+"editorconf", function(conf) {
    let savetimeout = null;
    let editordata = JSON.parse(content);
    const editform = conf.editform;
    const pagedata = conf.pagedata;

    const fpb = new FrontendPageBuilder(function(neweditordata, instantsave) {
        editordata = neweditordata;

        $("#save-status").removeClass("label-warning label-success").addClass("label-danger").text("Geändert");

        if(savetimeout) {
            clearTimeout(savetimeout);
        }


        if(instantsave) {
            saveContent();
        } else {
            console.log("Saving in 5sec");
            savetimeout = setTimeout(function() {
                saveContent();
            }, 5000);
        }

    });

    const saveContent = function() {
        const content = JSON.stringify({
            "sections": editordata
        });
        const data = {
            'PageContent': content
        };
        if(savetimeout) {
            clearTimeout(savetimeout);
        }
        $("#save-status").removeClass("label-danger label-success").addClass("label-warning").text("Speichert...");
        $.post(baseurl+"savecontent", data, function(feedback) {
            fpb.setContent(JSON.parse(feedback));
            // alert("Ok");
            $("#save-status").removeClass("label-danger label-warning").addClass("label-success").text("Gespeichert");
        }, "json");
    }

    fpb.init("[data-fpb-content]", editordata, conf, editmode);

    $("#sfb-save-page").click(function(e) {
        e.preventDefault();
        saveContent();
    })

    $("#sfb-edit-page").click(function(e) {
        e.preventDefault();
        fpb.getModal().showForm(editform, pagedata, "Seiteneigenschaften", function(formdata) {
            const data = {
                'FormData': formdata
            }
            $.post(baseurl+"saveform", data, function(newurl) {
                if(newurl == window.location.href)
                    window.location.reload();
                else
                    window.location.href = newurl;
            })
        });
    })
    $("#sfb-new-page").click(function(e) {
        e.preventDefault();
        fpb.getModal().showForm(editform, {}, "Seiteneigenschaften", function(formdata) {
            const data = {
                'NewPage': 1,
                'FormData': formdata
            }
            $.post(baseurl+"saveform", data, function(newurl) {
                if(newurl == window.location.href)
                    window.location.reload();
                else
                    window.location.href = newurl;
            })
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
