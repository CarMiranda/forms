<?php

//session_start();

require_once('Exceptions.php');
require_once('Validation.php');
require_once('Section.php');
$config = json_decode(file_get_contents('includes/config.json'), true);


$formsDir = $config['formsDir'];
$response = "";

interface IForm {
    /* */
    public function __construct($data);
    /* */
}

class Form implements IForm, IConversion {

    protected $formId;
    protected $title;
    protected $owner;
    protected $group;
    protected $nbS;
    protected $isActive;
    protected $sections = [];

    /* Start:  Constructors
    *   There are two implicit constructors: 
    *       - if data is an array, process it as if it is an HTTP request array (data corresponds to the POST superglobal array)
    *       - if data is a string, process it as if loading from an XML '$data.xml' (data corresponds to a form id)
    */      
    public function __construct($data) {
        
        if (is_array($data)) { /* Case of HTTP request  */
            /* Check for submission method */
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new HTTPException('Expected request method POST, got ' . $_SERVER['REQUEST_METHOD'] . ' instead.');
            }

            /* Check if submitted data is valid*/
            $isValid = Validation::requestValidation($data);
            if ($isValid === TRUE) {
                /* Parse the data */
                Validation::parseData($data);
                $this->fromRequest($data);
                $response .= "Form object successfully created.\n";
            } else {
                $response = "Request error: please submit the form again.\n";
                throw new HTTPException('Request data is not valid.');
            }
        } else if (is_string($data)) { /* Case of XML loading */
            if (file_exists("{$GLOBALS['formsDir']}/{$data}.xml")) {
                $this->fromXML($data);
            } else {
                $response = "Form with id '{$data}' does not exist.";
                throw new IOException("Form with id '{$data}' does not exist. Path: {$GLOBALS['formsDir']}/{$data}");
            }
        } else {
            throw new Exception("Could not construct new Form object from argument: {$data}.\nExpected array or string, found " . gettype($data) . " instead.");
        }
    }

    /* End: Constructors */

    /* Start: Load methods */
    private function fromXML($id) {
        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        // Load owner, group and activity from db.
        // Check for user permissions
        $xml->load("{$GLOBALS['formsDir']}/{$id}.xml");

        $this->formId = $id;
        $this->title = $xml->firstChild->firstChild->textContent;
        
        $xmlSections = $xml->getElementsByTagName("section");
        $this->nbS = $xmlSections->length;

        $xmlSection = $xmlSections->item(0);
        for ($i = 0; $i < $this->nbS; ++$i) {
            $nbQ = (int)$xmlSection->getAttribute("nbQ");
            $xmlSTitle = $xmlSection->firstChild;
            $xmlSDescription = $xmlSTitle->nextSibling;
            $this->sections[$i] = new Section($i, $nbQ, $xmlSTitle->textContent, $xmlSDescription->textContent);

            $section = $this->sections[$i];
            $xmlQuestionContainer = $xmlSDescription->nextSibling;
            for ($j = 0; $j < $nbQ; ++$j) {
                $xmlQuestion = $xmlQuestionContainer->firstChild;
                $xmlDescription = $xmlQuestion->nextSibling;
                $question = $section->getQuestion($j);
                $question->setQuestion($xmlQuestion->textContent);
                $question->setDescription($xmlDescription->textContent);

                $xmlAnswerContainer = $xmlDescription->nextSibling;
                $type = $xmlAnswerContainer->getAttribute("type");
                $question->setType($type);
                $question->setRequired((bool)$xmlAnswerContainer->getAttribute("required"));

                switch($type) {
                    case 'pattern' :
                        $question->addOption($xmlAnswerContainer->firstChild->textContent, 1);
                    break;
                    case 'radio' : case 'check' : case 'dropdown' :
                        $nbO = (int)$xmlAnswerContainer->getAttribute("nbO");
                        $io = (bool)$xmlAnswerContainer->getAttribute("other");
                        $question->setOther($io);
                        $xmlOption = $xmlAnswerContainer->firstChild;
                        for ($k = 0; $k < $nbO; ++$k) {
                            $question->addOption($xmlOption->textContent);
                            $xmlOption = $xmlOption->nextSibling;
                        }
                    break;
                }
                $xmlQuestionContainer = $xmlQuestionContainer->nextSibling;
            }
            $xmlSection = $xmlSection->nextSibling;
        }
    }

    private function fromRequest($data) {
        $this->formId = $data["fId"];
        $this->setTitle($data["fTitle"]);
        $this->setOwner($data["fOwner"]);
        $this->setGroup($data["fGroup"]);
        $nbS = (int)$data["nbS"];
        $this->setNbS($nbS);
        for ($i = 0; $i < $nbS; ++$i) {
            $nbQ = (int)$data["nbQ{$i}"];
            $this->sections[$i] = new Section($i, $nbQ, $data["sTitle{$i}"], $data["sDescription{$i}"]);
            $section = $this->sections[$i];
            for ($j = 0; $j < $nbQ; ++$j) {
                $question = $section->getQuestion($j);
                $question->setQuestion($data["question{$i}_{$j}"]);
                $question->setDescription($data["description{$i}_{$j}"]);
                $aType = $data["answerType{$i}_{$j}"];
                $question->setType($aType);
                $question->setRequired((bool)$data["required{$i}_{$j}"]);
                switch ($aType) {
                    case "radio" : case "check" : case "dropdown" :
                        $nbO = (int)$data["nbO{$i}_{$j}"];
                        for ($k = 0; $k < $nbO; ++$k) {
                            $question->addOption($data["option{$i}_{$j}_{$k}"]);
                        }
                        if (isset($data["other{$i}{$j}"]) && $data["other{$i}_{$j}"]) {
                            $question->setOther(TRUE);
                        }
                    break;
                    case "pattern" :
                        $question->addOption($data["pattern{$i}_{$j}"], 1);
                    break;
                }
            }
        }
    }
    /* End: Load methods */

    /* Start: Conversion methods */
    public function toHTML() {
        $str = "<form name='$this->formId'>";
        $str .= "<h1>{$this->title}</h1>";
        for ($i = 0; $i < $this->nbS; ++$i) {
            $str .= $this->sections[$i]->toHTML();
        }
        $str .= "<input type='submit' value='Submit' />";
        $str .= "</form>";

        return $str;
    }

    public function toXML($xml) {
        $form = $xml->createElement("form");
        $form->appendChild($xml->createElement("title", $this->title));
        for ($i = 0; $i < $this->nbS; ++$i) {
            $form->appendChild($this->sections[$i]->toXML($xml));
        }
        return $form;
    }
    /* End: Conversion methods */

    /* Start: Save methods */
    public function saveHTML() {

    }

    private function saveDB() {

    }

    public function saveXML() {
        $xml = new DOMDocument("1.0", "UTF-8");
        $xml->formatOutput = true;
        $xml->appendChild($this->toXML($xml));
        $xml->save("{$GLOBALS['formsDir']}/{$this->formId}.xml");
    }

    public function savePDF() {

    }
    /* End: Save Methods */

    /* Start: Getters and setters */

    /* Form id */
    public function getId() {
        return $this->formId;
    }

    public function setId($id) {
        $this->formId = $id;
    }

    /* Form title */
    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    /* Form owner */
    public function getOwner() {
        return $this->owner;
    }

    public function setOwner($owner) {
        $this->owner = $owner;
    }

    /* Form group */
    public function getGroup() {
        return $this->group;
    }

    public function setGroup($group) {
        $this->group = $group;
    }

    /* Form activity status */
    public function getActive() {
        return $this->isActive;
    }

    public function setActive($isActive) {
        $this->isActive = $isActive;
    }

    /* Number of sections */
    public function getNbS() {
        return $this->nbS;
    }

    public function setNbS($nbS) {
        $this->nbS = $nbS;
    }

    /* End: Getters and setters */
}

?>