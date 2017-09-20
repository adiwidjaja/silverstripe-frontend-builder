require("./scss/sfb-styles.scss");
import FrontendPageBuilder from "frontend-pagebuilder";

import $ from "jquery";

$.get(baseurl+"editorconf", function(conf) {
    const fpb = new FrontendPageBuilder();
    // console.log(content);
    // console.log(JSON.parse(content));
    fpb.init("[data-fpb-content]", JSON.parse(content), conf);

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

})
