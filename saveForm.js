/*jslint plusplus : true*/

// Translates the HTML elements in the form preview to HTTP parameters
function html2Params() {
    "use strict";
    var theSections, theQuestions, aQuestion, theAnswers, sNb, qNb, aNb, params, i, j, k;
    theSections = document.getElementsByTagName("section");
    sNb = theSections.length;
    params = "nbSections=" + sNb;
    for (i = 1; i <= sNb; ++i) {
        params += "&title" + i + "=" + encodeURIComponent(document.getElementById("section-title" + i).value);
        params += "&description" + i + "=" + encodeURIComponent(document.getElementById("section-description" + i).value);
        
        theQuestions = document.getElementsByName("questions" + i);
        qNb = theQuestions.length;
        
        params += "&qNb" + i + "=" + qNb;
        for (j = 1; j <= qNb; ++j) {
            params += "&question" + i + j + "=" + encodeURIComponent(theQuestions[j - 1].value);
            
            theAnswers = document.getElementsByName("answers" + i + j);
            aNb = theAnswers.length;
            
            params += "&aNb" + i + j + "=" + aNb;
            for (k = 1; k <= aNb; ++k) {
                params += "&answer" + i + j + k + "=" + encodeURIComponent(document.getElementById("label" + i + j + k).value);
            }
        }
    }
    return params;
}

// Checks form validity
function validateForm() {
    "use strict";
    var titles = document.getElementsByName("title"), questions = document.getElementsByName("question"), i, len = titles.length;
    for (i = 0; i < len; i += 1) {
        if (titles[i].value === "") {
            // title.class += " missing";
            return 1;
        }
    }
    len = questions.length;
    for (i = 0; i < len; i += 1) {
        if (questions[i].value === "") {
            return 2;
        }
    }
    return 0;
}

// Saves the form, triggering validateForm and sending the HTTP data to a PHP script
function saveForm() {
    "use strict";
    var isValid = validateForm(), doc, HTTPParams;
    if (isValid === 0) {
        doc = new XMLHttpRequest();
        HTTPParams = html2Params();
        doc.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                console.log("Saved successfuly.");
            }
        };
        doc.open("POST", "form2XML.php", true);
        doc.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        doc.send(HTTPParams);
    } else {
        if (isValid === 1) {
            window.alert("Missing title");
        } else if (isValid === 2) {
            window.alert("Missing question");
        }
    }
}