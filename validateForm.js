// Translates the HTML elements in the form preview to HTTP parameters
function html2Params() {
    "use strict";
    var theSections = document.getElementsByTagName("section"), params = "", i;
    params += "nbSections=" + theSections.length;
    for (i = 1; i <= theSections.length; i += 1) {
        params += "&title" + i + "=" + encodeURIComponent(document.getElementById("section-title" + i).value);
        params += "&description" + i + "=" + encodeURIComponent(document.getElementById("section-description" + i).value);
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
    var getThroughWithThisShit = validateForm(), doc, lel;
    if (getThroughWithThisShit === 0) {
        doc = new XMLHttpRequest();
        lel = html2Params();
        doc.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("esto").innerHTML = doc.responseText;
            }
        };
        doc.open("POST", "validate.php", true);
        doc.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        doc.send(lel);
    } else {
        if (getThroughWithThisShit === 1) {
            window.alert("Missing title");
        } else if (getThroughWithThisShit === 2) {
            window.alert("Missing question");
        }
    }
}

/* var aSection = [];

function Section(sNb) {
    this.id : "section" + sNb;
    this.title : "";
    this.description : "";
    this.questions = new aQuestions(sNb);

    this.setTitle = function (sTitle) {
        this.title = sTitle;
    }

    this.setDescription = function (sDescription) {
        this.description = sDescription;
    }

    this.addQuestion = function (e) {
        this.questions.push(new Question(e));
    }
}

function Question(sNb, qNb) {

}*/