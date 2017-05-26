/*jslint plusplus : true*/
/*jslint devel: true */

function sTitleParam(sNb, str) {
    "use strict";
    return "&sTitle" + sNb + "=" + encodeURIComponent(str).replace("%20", "+");
}

function sDescriptionParam(sNb, str) {
    "use strict";
    return "&sDescription" + sNb + "=" + encodeURIComponent(str).replace("%20", "+");
}

function questionParam(sNb, qNb, str) {
    "use strict";
    return "&question" + sNb + "_" + qNb + "=" + encodeURIComponent(str).replace("%20", "+");
}

function descriptionParam(sNb, qNb, str) {
    "use strict";
    return "&description" + sNb + "_" + qNb + "=" + encodeURIComponent(str).replace("%20", "+");
}

function aTypeParam(sNb, qNb, str) {
    "use strict";
    return "&answerType" + sNb + "_" + qNb + "=" + encodeURIComponent(str).replace("%20", "+");
}

function requiredParam(sNb, qNb, str) {
    "use strict";
    return "&required" + sNb + "_" + qNb + "=" + (encodeURIComponent(str).replace("%20", "+") === "true" ? 1 : 0);
}

function patternParam(sNb, qNb, str) {
    "use strict";
    return "&pattern" + sNb + "_" + qNb + "=" + encodeURIComponent(str).replace("%20", "+");
}

function optionParam(sNb, qNb, oNb, str) {
    "use strict";
    return "&option" + sNb + "_" + qNb + "_" + oNb + "=" + encodeURIComponent(str).replace("%20", "+");
}

// Translates the HTML elements in the form preview to HTTP parameters
function formConversion() {
    "use strict";
    var fTitle, fOwner, fGroup, sections, questions, descriptions, aContainer, aType, options, nbS, nbQ, nbO, params, i, ri, j, rj, k, rk;
    sections = document.getElementsByTagName("section");
    nbS = sections.length;
    fTitle = document.getElementById("formTitle").value;
    fOwner = document.getElementById("formOwner").value;
    fGroup = document.getElementById("formGroup").value;
    
    params = "fId=1&fTitle=" + encodeURIComponent(fTitle).replace("%20", "+") + "&fOwner=" + encodeURIComponent(fOwner).replace("%20", "+") + "&fGroup=" + encodeURIComponent(fGroup).replace("%20", "+") + "&nbS=" + nbS;
    for (i = 0; i < nbS; ++i) {
        params += sTitleParam(i, sections[i].querySelector(".sectionTitles").value);
        params += sDescriptionParam(i, sections[i].querySelector(".sectionDescriptions").value);
        
        questions = sections[i].querySelectorAll(".questions");
        descriptions = sections[i].querySelectorAll(".descriptions");
        aContainer = sections[i].querySelectorAll(".answerContainers");
        nbQ = questions.length;
        
        params += "&nbQ" + i + "=" + nbQ;
        for (j = 0; j < nbQ; ++j) {
            params += questionParam(i, j, questions[j].value);
            params += descriptionParam(i, j, descriptions[j].value);
            aType = aContainer[j].dataset.type;
            params += aTypeParam(i, j, aType);
            params += requiredParam(i, j, aContainer[j].dataset.required);
            
            if (["radio", "check", "dropdown"].indexOf(aType) > -1) {
                options = aContainer[j].querySelectorAll(".labels");
                nbO = options.length;

                params += "&nbO" + i + j + "=" + nbO;
                for (k = 0; k < nbO; ++k) {
                    params += optionParam(i, j, k, options[k].value);
                }
                if (aContainer[j].lastChild.previousSibling.dataset.isset) {
                    params += "&other" + i + j + "=1";
                } else {
                    params += "&other" + i + j + "=0";
                }
            } else if (aType === "pattern") {
                params += patternParam(i, j, aContainer[j].firstChild.value);
            }
        }
    }
    return params;
}

function formValidation() {
    "use strict";
    var valid, fTitle, fOwner, fGroup, sTitles, sDescriptions, questions, descriptions, str;
    valid = 0;
    fTitle = document.getElementById("formTitle");
    fOwner = document.getElementById("formOwner");
    fGroup = document.getElementById("formGroup");
    sTitles = document.getElementsByName("sectionTitles");
    sDescriptions = document.getElementsByName("sectionDescriptions");
    questions = document.querySelectorAll(".questions");
    descriptions = document.getElementsByName("descriptions");
    //patterns = document.getElementsByName("patterns");
    //options = document.getElementsByName("options");
    if (fTitle.value === "") {
        valid++;
    }
    if (fOwner.value === "") {
        valid++;
    }
    if (fGroup.value === "") {
        valid++;
    }
    for (str of sTitles) {
        if (str.value === "") {
            str.className += " missing";
            valid++;
        }
    }
    for (str of sDescriptions) {
        if (str.value === "") {
            str.className += " missing";
            valid++;
        }
    }
    for (str of questions) {
        if (str.value === "") {
            str.className += " missing";
            valid++;
        }
    }
    for (str of descriptions) {
        if (str.value === "") {
            str.className += " missing";
            valid++;
        }
    }
    /*for (str of patterns) {
        if (str.value === "") {
            valid++;
        }
    }*/
    /*for (str of options) {
        if (str.value === "") {
            valid++;
        }
    }*/

    return valid;
}

// Saves the form, triggering validateForm and sending the HTTP data to a PHP script
function saveForm() {
    "use strict";
    var isValid = formValidation(), doc, HTTPParams, status;
    if (isValid === 0) {
        doc = new XMLHttpRequest();
        HTTPParams = formConversion();
        doc.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                    switch (this.responseText) {
                        case "0" :
                            status = "Form saved.";
                        break;
                        case "1" :
                            status = "Internal error. Please resubmit the form or contact an administrator.";
                        break;
                        case "2" :
                            status = "There was an error in your form data. Please check your input and resubmit.";
                        break;
                        case "3" :
                            status = "Unknown error. Form could not be saved. Please contact an administrator";
                        break;
                    }
                    document.getElementById("status").innerHTML = status;
            }
        };
        doc.open("POST", "saveForm.php", true);
        doc.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        doc.send(HTTPParams);
    } else {
        if (isValid > 0) {
            window.alert(`There where ${isValid} errors found.`);
        }
    }
}