/*jslint plusplus : true*/

/* Function to get the current highlighted section */
function getHLS() {
    "use strict";
    var i = 0, sections;
    sections = document.getElementById("hls-container").getElementsByTagName("input");
    while (!(sections[i].checked) && i < sections.length) {
        i++;
    }
    return (i >= sections.length ? -1 : i + 1);
}

function reId(sNb, qNb, caNb) {
    var theAnswers = document.getElementsByName("answers" + sNb + qNb), aNb, i, j;
    aNb = theAnswers.length;
    for (i = caNb, j = caNb + 1; i <= aNb; ++i, ++j) {
        document.getElementById("acont" + sNb + qNb + j).id = "acont" + sNb + qNb + i; 
        document.getElementById("answer" + sNb + qNb + j).id = "answer" + sNb + qNb + i;
        document.getElementById("label" + sNb + qNb + j).id = "label" + sNb + qNb + i; 
        document.getElementById("minus" + sNb + qNb + j).id = "minus" + sNb + qNb + i; 
    }
}

/* Function to highlight a section */
function highlight(sNb) {
    "use strict";
    document.getElementById("hls" + sNb).checked = true;
}

/* Function to add a new Option element to the form preview */
function addOption(sNb, qNb) {
    "use strict";
    
    // Variables declaration
    var theAContainer, theQContainer, aNb, theAnswer, theLabel, thePlus, theMinus;

    theAContainer = document.createElement("div");
    theQContainer = document.getElementById("qcont" + sNb + qNb);
    theLabel = document.createElement("textarea");
    theAnswer = document.createElement("div");
    thePlus = document.getElementById("plus" + sNb + qNb);
    theMinus = document.createElement("img");
    aNb = (theQContainer.children.length - 1) / 2;

    theAnswer.id = "answer" + sNb + qNb + aNb;
    theAnswer.setAttribute("class", "answers");
    theAnswer.setAttribute("value", "opt" + aNb);

    theLabel.id = "label" + sNb + qNb + aNb;
    theLabel.name = "answers" + sNb + qNb;
    theLabel.innerHTML = "Option " + aNb;
    theLabel.setAttribute("placeholder", "Option");
    theLabel.setAttribute("class", "labels");
    theLabel.setAttribute("rows", 1);

    theMinus.id = "minus" + sNb + qNb + aNb;
    theMinus.src = "./media/minus.svg";
    theMinus.style.cssText = "width: 10px; position: relative;";
    theMinus.setAttribute("onclick", "removeOption(this.parentNode);");

    theAContainer.id = "acont" + sNb + qNb + aNb;
    theAContainer.setAttribute("class", "acont");
    theAContainer.appendChild(theAnswer);
    theAContainer.appendChild(theLabel);
    theAContainer.appendChild(theMinus);
    theQContainer.insertBefore(theAContainer, thePlus);
    theQContainer.insertBefore(document.createElement("br"), thePlus);
}

/* Function to add a new Question element to the form preview */
function addQuestion() {
    "use strict";

    // Variables declaration
    var sNb, qNb, theSection, theQContainer, theQuestion, thePlus;

    // Variables initialization
    sNb = getHLS();
    theSection = document.getElementById("section" + sNb);
    theQContainer = document.createElement("div");
    theQuestion = document.createElement("textarea");
    thePlus = document.createElement("img");
    qNb = (theSection.children.length - 1) / 2;

    // Setting up the question container
    theQContainer.id = "qcont" + sNb + qNb;

    // Setting up the question
    theQuestion.id = "question" + sNb + qNb;
    theQuestion.name = "questions" + sNb;
    theQuestion.setAttribute("placeholder", "Question");
    theQuestion.setAttribute("rows", 1);

    // Setting up the "plus" icon to add new options/answers
    thePlus.id = "plus" + sNb + qNb;
    thePlus.src = "./media/plus.svg";
    thePlus.style.cssText = "width: 10px; height: 10px;";
    thePlus.setAttribute("onclick", "addOption(" + sNb + "," + qNb + ");");

    // Adding the "plus" icon and the question to the question container, and the question container to the section
    theQContainer.appendChild(theQuestion);
    theQContainer.setAttribute("class", "qcont");
    theQContainer.innerHTML += "<br>";
    theQContainer.appendChild(thePlus);
    theSection.appendChild(theQContainer);
    theSection.insertBefore(document.createElement("br"), theQContainer);

    // Adding an option/answer
    addOption(sNb, qNb);
}

/* Function to add a new Section element to the form preview */
function addSection() {
    "use strict";

    // Variables declaration
    var theForm, theSection, theTitle, theDescription, hlscont;

    // Section counter (private variable)
    addSection.cnt = ++addSection.cnt || 1;

    // Variables initialization
    theForm = document.getElementById("right-container"); // Form container
    theSection = document.createElement("section"); // New section element for the new section
    theTitle = document.createElement("textarea"); // New textarea element for the new title
    theDescription = document.createElement("textarea"); // New textarea element for the new description
    hlscont = document.getElementById("hls-container"); // Highlight section container

    // Setting up the titles attributes
    theTitle.id = "section-title" + addSection.cnt;
    theTitle.name = "section-title";
    theTitle.setAttribute("class", "section-title");
    theTitle.setAttribute("placeholder", "Title");
    theTitle.setAttribute("rows", "1");
    theTitle.setAttribute("required", "required");

    // Setting up the description attributes
    theDescription.id = "section-description" + addSection.cnt;
    theDescription.name = "section-description";
    theDescription.setAttribute("class", "section-description");
    theDescription.setAttribute("placeholder", "Description (optional)");

    // Setting up the new section attributes and appending title and description
    theSection.id = "section" + addSection.cnt;
    theSection.name = "section";
    theSection.appendChild(theTitle);
    theSection.innerHTML += "<br>";
    theSection.appendChild(theDescription);

    // Adding section to hls section and changing the current highlighted section to the new section
    hlscont.innerHTML += "<label for='hls" + addSection.cnt + "'>" + addSection.cnt + "</label> : <input type='radio' name='hls' id='hls" + addSection.cnt + "' value='" + addSection.cnt + "' checked />";

    // Bind the section to onclick event
    theSection.setAttribute("onclick", "highlight(" + addSection.cnt + ")");

    // Appending to the container
    theForm.appendChild(theSection);
    theForm.appendChild(document.createElement("hr"));

    // Adding a question to the section
    addQuestion();
}

/* Function to remove an Option element of the form preview */
function removeOption(e) {
    "use strict";
    var id = e.id.substr(5, 3).split('');
    e.parentNode.removeChild(e.nextElementSibling);
    e.parentNode.removeChild(e);
    reId(parseInt(id[0]), parseInt(id[1]), parseInt(id[2]));
}

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