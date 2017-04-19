/*jslint plusplus : true*/
/*jslint devel: true */

// Language variable
var lang = "en";

// Variable containing the help text
var helpText = {
    "en" : {
        "short" : "Enter a question and a description (optional). The user will be asked to input a short answer (max. 100 characters).",
        "long" : "Enter a question and a description (optional). The user will be asked to input a long answer (max. 1500 characters).",
        "radio" : "Enter a question and a description (optional). The user will have to choose exactly one option.",
        "check" : "Enter a question and a description (optional). The user will have to choose one or more options.",
        "pattern" : "Enter a question and a description (optional). The user will have to provide an input matching a pattern.<br>Pattern instructions: # is a digit, A is a letter, - is the field separator.<br>E.g. 12-LE21 matches the pattern ##-AA##.",
        "dropdown" : "Enter a question and a description (optional). The user will have to choose exactly one option.",
        "date" : "Enter a question and a description (optional). The user will have to provide a valid date.",
        "dateInt" : "Enter a question and a description (optional). The user will have to provide a valid date interval."
    },
    
    "fr" : {
        "short" : "Entrez une question et une description (facultative). L'utilisateur devra écrire une réponse courte (max. 100 caractères).",
        "long" : "Entrez une question et une description (facultative). L'utilisateur devra écrire une réponse longue (max. 1500 caractères).",
        "radio" : "Entrez une question et une description (facultative). L'utilisateur devra choisir exactement une réponse.",
        "check" : "Entrez une question et une description (facultative). L'utilisateur devra choisir au moins une réponse.",
        "pattern" : "Entrez une question et une description (facultative). L'utilisateur devra écrire une réponse validant le motif. Consignes pour le motif: # est un chiffre, A est une lettre, - est le séparateur de champs. E.g. 12-LE21 valide le motif ##-AA##.",
        "dropdown" : "Entrez une question et une description (facultative). L'utilisateur devra choisir exactement une réponse.",
        "date" : "Entrez une question et une description (facultative). L'utilisateur devra renseigner une date valide.",
        "dateInt" : "Entrez une question et une description (facultative). L'utilisateur devra renseigner un intervalle de dates valide."
    }
};

// Variable containing the placeholders
var placeholdersText = {
    "en" : {
        "title" : "Title",
        "description" : "Description (optional)",
        "question" : "Question",
        "short" : "Short answer...",
        "long" : "Long answer...",
        "pattern" : "Enter pattern",
        "another" : "Add another option"
    },
    "fr" : {
        "title" : "Titre",
        "description" : "Description (facultative)",
        "question" : "Question",
        "short" : "Réponse courte...",
        "long" : "Réponse longue...",
        "pattern" : "Entrez un motif",
        "another" : "Ajouter une option"
    }
};

/* fn(Element obj, string type, bool useCapture, function callback) : int ErrCode
 * Cross-browser function to add events.
 * Returns 0 if everything went well, 1 if element passed is null or undefined.
 */
var addEvent = function (obj, type, useCapture, callback) {
    "use strict";
    
    if (obj === null || typeof (obj) === "undefined") {
        return 1;
    }
    if (obj.addEventListener) {
        obj.addEventListener(type, callback, useCapture);
    } else if (obj.attachEvent) {
        obj.attachEvent("on" + type, callback);
    } else {
        obj["on" + type] = callback;
    }
    
    return 0;
};

// Adds a function to String.prototype to capitalize a word or sentence, i.e. capitalizes first letter of the string.
String.prototype.capitalize = function () {
    "use strict";
    
    return this.charAt(0).toUpperCase() + this.slice(1);
};

// Adds a function to Error.prototype to write an error.
Error.prototype.toString = function () {
    "use strict";
    
    return this.name + " " + this.message;
};

/* fn(Element e) : int nbElements
 * Activates/highlights an element.
 * Returns 0 if everything went well, 1 if type passed is invalid.
 */
function activate(type, e) {
    "use strict";
    
    var currentActive, theClass, theActiveClass;
    
    theClass = type + "s"; // ClassName of the element (non active)
    theActiveClass = "active" + type.capitalize(); // ClassName of the element (active)
    switch (type) {
    case "section":
        currentActive = document.querySelector(".activeSection");
        break;
    case "questionContainer":
        currentActive = document.querySelector(".activeSection > .activeQuestionContainer");
        break;
    case "answerType":
        currentActive = e.parentNode.querySelector(".activeAnswerType");
        break;
    default: // Type is invalid
        return 1;
    }

    if (currentActive !== null) { // There is not active element of that type in the required scope
        currentActive.className = theClass; // Deactivates element
    }

    e.className = theClass + " " + theActiveClass; // Activates element
    
    return 0;
}

/* fn(Element e) : int nbElements
 * Empty an element.
 * Returns the number of children (0 if everything went well).
 */
function cleanElement(e) {
    "use strict";
    
    while (e.firstChild) {
        e.removeChild(e.firstChild);
    }
    
    return e.children.length;
}

/* fn(string type, Element theAContainer) : int ErrCode
 * Sets an Answer to a Text Answer (type short, long or pattern).
 * Returns 0 if everything went well, 1 if type passed is invalid.
 */
function textAnswer(type, theAContainer) {
    "use strict";
    
    var theText;
    
    theText = document.createElement("textarea");
    
    switch (type) {
    case "short":
        theText.value = "Short answer...";
        theText.rows = 1;
        theText.setAttribute("readonly", "");
        break;
    case "long":
        theText.value = "Long answer...";
        theText.rows = 3;
        theText.setAttribute("readonly", "");
        break;
    case "pattern":
        theText.placeholder = "Enter pattern...";
        theText.rows = 1;
        break;
    default: // Type is invalid
        return 1;
    }

    theAContainer.appendChild(theText);
    
    return 0;
}

/* fn(string type, Element theAContainer) : int ErrCode
 * Adds a new Multiple Choice Option (type radio, check or dropdown).
 * Returns 0 if everything went well.
 */
function addMCOption(type, theAContainer) {
    "use strict";
    
    var theOContainer, theButton, oNb, theLabel, theMinus;

    theButton = document.createElement("div"); // Leftmost, button-like element
    theLabel = document.createElement("textarea"); // Modifiable label for the option
    theOContainer = document.createElement("div"); // Option container
    
    // Get the id number for this option
    oNb = theAContainer.children.length - (document.getElementById("otherContainer" + theAContainer.id.substring(15)).dataset.isset === "1" ? 1 : 0); // Number of options
    oNb = theAContainer.id.substr(15) + oNb; // Id number

    // Setting up the button-like element
    theButton.id = type + oNb;
    theButton.className = type + "s";
    
    // Setting up the label element
    theLabel.id = "label" + oNb;
    theLabel.textContent = "Option " + oNb.substring(oNb.length - 1);
    theLabel.placeholder = "Option";
    theLabel.className = "labels";
    theLabel.rows = 1;

    // Setting up the option container element
    theOContainer.id = "optionContainer" + oNb;
    theOContainer.className = "optionContainers";
    theOContainer.appendChild(theButton);
    theOContainer.appendChild(theLabel);
    
    // Insertion before the "add other" option and focus
    theAContainer.insertBefore(theOContainer, document.getElementById("otherContainer" + oNb.slice(0, -1)));
    theLabel.focus();
    
    return 0;
}

/* fn(Element theOther) : int ErrCode
 * Adds the "Other..." option to a Multiple Choice Answer.
 * Returns 0 if everything went well.
 */
function addMCOtherOption(type, theAContainer) {
    "use strict";
    
    var theOContainer, theAddOption, theAddOptionLabel;
    
    addMCOption(type, theAContainer);
    
    theOContainer = theAContainer.lastChild;
    theAddOption = theOContainer.previousSibling;
    theAddOptionLabel = theAddOption.lastElementChild;
    
    theOContainer.removeChild(theOContainer.lastChild);
    theOContainer.removeChild(theOContainer.lastChild);
    theOContainer.lastChild.textContent = "Other...";
    theOContainer.lastChild.setAttribute("disabled", "");
    theOContainer.dataset.isset = 1;
    
    theAddOption.id = "addOption" + theAContainer.id.substr(15);
    theAddOptionLabel.textContent = "Add another option";
    theAddOptionLabel.removeAttribute("class");
    theAddOptionLabel.setAttribute("readonly", "");
    theAContainer.appendChild(theAddOption);
    addEvent(theAddOptionLabel, "click", false, function () { addMCOption(type, theAContainer); });

}

/* fn(string type, Element theAContainer) : int ErrCode
 * Sets an Answer to a Multiple Choice Answer (type radio, check or dropdown).
 * Returns 0 if everything went well, 1 if type passed is invalid.
 */
function mcAnswer(type, theAContainer) {
    "use strict";
    
    // Variables declaration
    var theOtherButton, theOtherContainer, theOtherText, theOtherClick, aId;

    // Variables initialization
    theOtherButton = document.createElement("div");
    theOtherContainer = document.createElement("div");
    theOtherText = document.createElement("textarea");
    theOtherClick = document.createElement("span");
    aId = theAContainer.id.substr(15);
    
    // Setting up the button-like element
    theOtherButton.id = "other" + aId;
    theOtherButton.className = type + "s";
    
    // Setting up the other option
    theOtherText.id = "otherText" + aId;
    theOtherText.rows = 1;
    theOtherText.className = "otherTexts";
    theOtherText.setAttribute("readonly", "");
    theOtherText.innerHTML = "Add another option";
    addEvent(theOtherText, "focus", false, function hola() { addMCOption(type, theAContainer); });
    
    theOtherClick.className = "otherClicks";
    theOtherClick.textContent = "add OTHER";
    addEvent(theOtherClick, "click", false, function () { addMCOtherOption(type, theAContainer); });
    
    // Setting up the Other Container
    theOtherContainer.id = "otherContainer" + aId;
    theOtherContainer.setAttribute("data-isset", "0");
    
    theOtherContainer.appendChild(theOtherButton);
    theOtherContainer.appendChild(theOtherText);
    if (type !== "dropdown") {
        theOtherContainer.appendChild(document.createTextNode(" or "));
        theOtherContainer.appendChild(theOtherClick);
    }
    
    // Appending a radio option and the other option
    theAContainer.appendChild(theOtherContainer);
    addMCOption(type, theAContainer);
    
    return 0;
}

/* fn(string type, Element theAContainer) : int ErrCode
 * Sets an Answer to a Date Answer (type date or dateInt).
 * Returns 0 if everything went well, 1 if type passed is invalid.
 */
function dateAnswer(type, theAContainer) {
    "use strict";
    
    var staDate, staLabel, endDate, endLabel;
    staDate = document.createElement("input");
    staLabel = document.createElement("label");
    
    staDate.type = "date";
    staDate.setAttribute("readonly", "");
    staLabel.textContent = "Start Date : ";
    theAContainer.appendChild(staLabel);
    theAContainer.appendChild(staDate);
    
    if (type === "dateInt") {
        endDate = document.createElement("input");
        endLabel = document.createElement("label");
        endDate.type = "date";
        endDate.setAttribute("readonly", "");
        endLabel.textContent = "End Date : ";
        theAContainer.appendChild(document.createElement("br"));
        theAContainer.appendChild(endLabel);
        theAContainer.appendChild(endDate);
    }
    
    return 0;
    
}

/* fn(string type) : int ErrCode
 * Replaces the content the active Answer Container based on the value of type.
 * Returns 0 if everything went well, 1 if type passed is invalid.
 */
function replaceElement(type) {
    "use strict";
    
    var theAContainer;
    theAContainer = document.querySelector(".activeSection .activeQuestionContainer .answerContainers");
    
    switch (type) {
    case "short":
    case "long":
    case "pattern":
        cleanElement(theAContainer);
        textAnswer(type, theAContainer);
        break;
    case "radio":
    case "check":
    case "dropdown":
        cleanElement(theAContainer);
        mcAnswer(type, theAContainer);
        break;
    case "date":
    case "dateInt":
        cleanElement(theAContainer);
        dateAnswer(type, theAContainer);
        break;
    default:
        return 1;
    }
    theAContainer.dataset.type = type;
    theAContainer.parentNode.querySelector(".helpText").textContent = helpText[lang][type];
    
    return 0;
}

/* fn() : int ErrCode
 * Adds a question to the active Section (short by default).
 * Returns 0 if everything went well.
 */
function addQuestion() {
    "use strict";
    
    // Variables declaration
    var sNb, qNb, theSection, theQContainer, theHeader, theFooter, theQuestion, theAContainer, theDescription, theDropDown, answerTypeContainer, answerTypes, answerTypesLabels, i, helpLabel, helpContent, aSeparator, theRequired;
    
    // Variables initialization
    theSection = document.querySelector(".activeSection");
    sNb = theSection.id.substring(7);
    theHeader = document.createElement("header");
    theFooter = document.createElement("footer");
    theQContainer = document.createElement("div");
    theQuestion = document.createElement("textarea");
    theDescription = document.createElement("textarea");
    theAContainer = document.createElement("div");
    theDropDown = document.createElement("div");
    qNb = sNb + (theSection.children.length - 1);
    helpLabel = document.createElement("div");
    helpContent = document.createElement("dialog");
    theRequired = document.createElement("div");
    answerTypes = ["short", "long", "pattern", "radio", "check", "dropdown", "date", "dateInt"];
    answerTypesLabels = ["Short", "Long", "Pattern", "Radio", "Check", "Dropdown", "Date", "DateInt"];

    // Setting up the question container
    theQContainer.id = "questionContainer" + qNb;
    theQContainer.className = "questionContainers";
    
    // Setting up the header
    theHeader.id = "header" + qNb;
    theHeader.className = "headers";
    
    // Setting up the footer
    theFooter.id = "footer" + qNb;
    theFooter.className = "footers";

    // Setting up the question
    theQuestion.id = "question" + qNb;
    theQuestion.className = "questions";
    theQuestion.name = "questions" + sNb;
    theQuestion.placeholder = "Question";
    theQuestion.rows = 1;
    
    // Setting up the description
    theDescription.id = "description" + qNb;
    theDescription.className = "descriptions";
    theDescription.name = "descriptions" + sNb;
    theDescription.placeholder = "Description (optional)";
    theDescription.rows = 1;
    
    // Setting up the Answer Container
    theAContainer.id = "answerContainer" + qNb;
    theAContainer.className = "answerContainers";
    theAContainer.setAttribute("data-type", "");
    theAContainer.setAttribute("data-required", true);
    
    // Setting up the dropdown list
    theDropDown.id = "answerTypeDropdown" + qNb;
    theDropDown.className = "answerTypeDropdowns";
    
    for (i = 0; i < 8; i++) {
        answerTypeContainer = (function (answerType) {
            var answerTypeContainer;
            
            answerTypeContainer = document.createElement("div");
            answerTypeContainer.className = "answerTypes";
            answerTypeContainer.textContent = answerTypesLabels[i];
            addEvent(answerTypeContainer, "click", false, function () { activate("answerType", answerTypeContainer); replaceElement(answerType); });
            return answerTypeContainer;
        }(answerTypes[i]));
        theDropDown.appendChild(answerTypeContainer);
        if (i === 2 || i === 5) {
            aSeparator = document.createElement("hr");
            aSeparator.className = "answerTypes";
            theDropDown.appendChild(aSeparator);
        }
    }
    
    // Setting up the help
    helpLabel.className = "help";
    helpLabel.textContent = "Help";
    helpContent.className = "helpText";
    helpContent.textContent = helpText.short;
    
    // Setting up the required button
    theRequired.id = "required" + qNb;
    theRequired.className = "requiredOn";
    theRequired.textContent = " Required";
    theRequired.onclick = (function (theAContainer) {
        return function () {
            if (this.className === "requiredOn") {
                this.className = "requiredOff";
            } else {
                this.className = "requiredOn";
            }
            theAContainer.dataset.required = theAContainer.dataset.required !== "true";
        };
    }(theAContainer));
    
    // Appending everything
    theSection.insertBefore(theQContainer, theSection.lastElementChild);
    
    theQContainer.appendChild(theHeader);
    theQContainer.appendChild(document.createElement("br"));
    theQContainer.appendChild(theAContainer);
    theQContainer.appendChild(document.createElement("br"));
    theQContainer.appendChild(theFooter);
    
    theHeader.appendChild(theDropDown);
    theHeader.appendChild(theQuestion);
    theHeader.appendChild(document.createElement("br"));
    theHeader.appendChild(theDescription);
    
    theFooter.appendChild(helpLabel);
    theFooter.appendChild(helpContent);
    theFooter.appendChild(theRequired);
    
    addEvent(theQContainer, "click", true, function () { activate("questionContainer", theQContainer); });
    activate("questionContainer", theQContainer);
    activate("answerType", theDropDown.firstElementChild);
    
    // Adding an option/answer
    replaceElement("short");
    
    return 0;
}

/* fn() : int ErrCode
 * Adds a section with a question.
 * Returns 0 if everything went well.
 */
function addSection() {
    "use strict";

    // Variables declaration
    var theForm, theSection, theHeader, theFooter, theTitle, theDescription;

    // Section counter
    addSection.cnt = ++addSection.cnt || 1;

    // Variables initialization
    theForm = document.getElementById("formContainer"); // Form container
    theSection = document.createElement("section");
    theHeader = document.createElement("header");
    theFooter = document.createElement("footer");
    theTitle = document.createElement("textarea");
    theDescription = document.createElement("textarea");

    // Setting up the title
    theTitle.id = "sectionTitle" + addSection.cnt;
    theTitle.className = "sectionTitles";
    theTitle.name = "sectionTitles";
    theTitle.placeholder = "Title";
    theTitle.rows = 1;
    theTitle.setAttribute("required", "");

    // Setting up the description
    theDescription.id = "sectionDescription" + addSection.cnt;
    theDescription.className = "sectionDescriptions";
    theDescription.name = "sectionDescriptions";
    theDescription.placeholder = "Description (optional)";
    theDescription.rows = 2;
    
    // Setting up the header
    theHeader.id = "sectionHeader" + addSection.cnt;
    theHeader.className = "sectionHeaders";
    
    // Setting up the footer
    theFooter.id = "sectionFooter" + addSection.cnt;
    theFooter.className = "sectionFooters";
    
    // Setting up the new section
    theSection.id = "section" + addSection.cnt;
    theSection.className = "sections";
    
    // Appending everything    
    theForm.appendChild(theSection);
    theForm.appendChild(document.createElement("br"));
    
    theSection.appendChild(theHeader);
    theSection.appendChild(theFooter);
    
    theHeader.appendChild(theTitle);
    theHeader.appendChild(document.createElement("br"));
    theHeader.appendChild(document.createElement("br"));
    theHeader.appendChild(theDescription);

    // Bind the section to onclick event
    addEvent(theSection, "click", true, function () { activate("section", theSection); });
    activate("section", theSection);

    // Adding a question to the section
    addQuestion();
    
    return 0;
}

/* fn()
 * Slides the side toolbar when scroll.
 */
function slideToolBar() {
    "use strict";
    
    var lastPos, ticking;
    lastPos = 0;
    ticking = false;

    function scrollTools(scrollPos) {
        document.getElementById("sideTools").style.top = scrollPos + "px";
    }

    window.addEventListener("scroll", function (e) {
        lastPos = 100 + window.pageYOffset;
        if (!ticking) {
            window.requestAnimationFrame(function () {
                scrollTools(lastPos);
                ticking = false;
            });
        }
        ticking = true;
    });
}

/*************************************************************************************************************/

/* Function to remove an Option element of the form preview */
/* function removeOption(e) {
    "use strict";
    var id = e.id.substr(5, 3).split('');
    e.parentNode.removeChild(e.nextElementSibling);
    e.parentNode.removeChild(e);
    // reId(parseInt(id[0], 10), parseInt(id[1], 10), parseInt(id[2], 10));
}*/

/* Function to add a new Title element to the form preview */
function addTitle() {
    "use strict";
}

/* Function to add a new Image element to the form preview */
function addImage() {
    "use strict";
}

/* Function to add a new Video element to the form preview */
function addVideo() {
    "use strict";
}

/* Adds a section on window load */
addEvent(window, "load", false, addSection);

/* Adds an event listener for the side toolbar */
addEvent(window, "load", false, function () { "use strict"; return (slideToolBar()); });