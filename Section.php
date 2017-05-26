<?php

require_once('Exceptions.php');
require_once('IConversion.php');
require_once('Question.php');

interface ISection {
    /* Start: Constructors */
    public function __construct($sNb = null, $nbQ = null, $sTitle = null, $sDescription = null);
    /* End: Constructors */

    /* Start: Questions manipulation methods */
    public function addQuestion($txt = null);
    public function removeQuestion($i);
    public function removeQuestionById($id);
    /* End: Questions manipulation methods */

    /* Start: Getters and Setters */
    public function getSNb();
    public function setSNb($s);
    public function getNbQ();
    public function getSTitle();
    public function setSTitle($str);
    public function getSDescription();
    public function setSDescription($str);
    public function getQuestion($i); //
    /* End: Getters and Setters */

}

class Section implements ISection, IConversion {

    /* Start: Instance variables */
    private $sNb;
    private $nbQ;
    private $sTitle;
    private $sDescription;
    private $questions = [];
    /* End: Instance variables */

    /* Start: Constructors */
    public function __construct($sNb = null, $nbQ = null, $sTitle = null, $sDescription = null) {
        $this->setSNb($sNb);
        $this->setNbQ($nbQ);
        $this->setSTitle($sTitle);
        $this->setSDescription($sDescription);
        $this->initQuestions($nbQ);
    }

    private function initQuestions($nbQ) {
        if ($nbQ === null) {
            throw new InternalError("Could not create Section object. Number of questions parameter was empty. Section {$this->sNb}.");
        } else if (!is_int($nbQ)) {
            throw new InternalError("Wrong argument value for number of questions on Section object creation. Value: {$nbQ}. Section {$this->sNb}.");
        } else if ($nbQ < 1) {
            throw new InternalError("Wrong value for number of questions parameter. Value: {$nbQ}. Section {$this->sNb}.");
        }
        
        try {
            for ($i = 0; $i < $nbQ; ++$i) {
                $this->questions[$i] = new Question();
            }
        } catch (InternalError $e) {
            throw new InternalError ($e->__toString() . " , section {$this->sNb}");
        }
    }
    /* End: Constructors */

    /* Start: Conversion methods */
    public function toHTML() {
        $str = "<section id='{$this->SNb}'>";
        $str .= "<h2>{$this->sTitle}</h2>";
        $str .= "<h4>{$this->sDescription}</h4>";
        for ($i = 0; $i < $this->nbQ; ++$i) {
            $str .= $this->questions[$i]->toHTML();
        }
        $str .= "</section>";

        return $str;
    }

    public function toXML($xml) {
        /* Add questions' text to questions file */
        /*if (!file_exists("/tmp/{$formId}-questions.xml")) {
            $xmlQuestions = new DOMDocument("1.0", "UTF-8");
            $xmlQuestions->formatOutput = true;
            $container = $xmlQuestions->createElement("question");
            $pastQuestionsId = [];
            for ($i = 0, $j = 0; $i < $this->nbQ; ++$i) {
                if (!in_array($this->getQuestionId($i), $pastQuestionsId)) {
                    $question = $xmlOptions->createElement("question", $this->getQuestionText($i));
                    $question->setAttribute("id", $this->getQuestionId($i));
                    $container->appendChild($question);
                    $pastQuestionsId[$j++] = $this->getQuestionId($i);
                }
            }
            $xmlQuestions->appendChild($container);
            $xmlQuestions->save("/tmp/{$formId}-questions.xml");
        }*/

        $section = $xml->createElement("section");
        $section->setAttribute("nbQ", $this->nbQ);
        $section->appendChild($xml->createElement("sTitle", $this->sTitle));
        $section->appendChild($xml->createElement("sDescription", $this->sDescription));
        for ($i = 0; $i < $this->nbQ; ++$i) {
            $section->appendChild($this->questions[$i]->toXML($xml));
        }
        return $section;
    }
    /* End: Conversion methods */

    /* Start: Questions manipulation methods */
    public function addQuestion($txt = null) {
        $this->questions[$this->nbQ] = new Question($txt);
    }

    public function removeQuestion($i) {
        if ($i >= $this->nbQ) {
            throw new InternalError("Index out of bounds.");
        }
        unset($this->questions[$i]);
        $this->nbQ--;
        for ($j = $i; $j < $this->nbQ; ++$j) {
            $this->questions[$j] = $this->questions[$j + 1];
        }
    }

    public function removeQuestionById($id) {
        try {
            $idx = $this->indexOf($id);
            unset($this->questions[$idx]);
            $this->nbQ--;
            for ($i = $idx; $i < $this->nbQ; ++$i) {
                $this->questions[$i] = $this->questions[$i + 1];
            }
        } catch (InternalError $e) {
            throw new InternalError($e->getMessage() . ", section {$this->id}");
        }
    }
    /* End: Questions manipulation methods */

    /* Start: Implementation methods */
    private function indexOf($id) {
        for ($i = 0; $i < $this->nbQ; ++$i) {
            if ($this->options[$i]->getOptionId() === $id) {
                return $i;
            }
        }
        throw new InternalError("Option with id {$id} not found.");
    }
    /* End: Implementation methods */

    /* Start: Getters and Setters */
    public function getSNb() {
        return $this->sNb;
    }

    public function setSNb($s) {
        if ($s === null) {
            throw new InternalError("Could not create Section object. Section number parameter was empty. Section {$this->sNb}");
        } else if (!is_int($s)) {
            throw new InternalError("Wrong parameter type for section number on Section object creation. Value: {$s}. Section {$this->sNb}");
        }
        $this->sNb = $s;
    }

    public function getNbQ() {
        return (int)$this->nbQ;
    }

    private function setNbQ($nb) {
        if ($nb === null) {
            throw new InternalError("Could not create Section object. Number of questions parameter was empty. Section {$this->sNb}");
        } else if (!is_int($nb)) {
            throw new InternalError("Wrong parameter type for number of questions on Section object creation. Value: {$nb}. Section {$this->sNb}");
        }
        $this->nbQ = $nb;
    }

    public function getSTitle() {
        return $this->sTitle;
    }

    public function setSTitle($str) {
        if ($str === null) {
            $str = "";
        } else if (!is_string($str)) {
            throw new InternalError("Wrong argument type for section title on Section object creation. Value: {$str}. Section {$this->sNb}");
        }
        $this->sTitle = $str;
    }

    public function getSDescription() {
        return $this->sDescription;
    }

    public function setSDescription($str) {
        if ($str === null) {
            $str = "";
        } else if (!is_string($str)) {
            throw new InternalError("Wrong argument type for section description on Section object creation. Value: {$str}. Section {$this->sNb}");
        }
        $this->sDescription = $str;
    }

    public function getQuestion($i) {
        return $this->questions[$i];
    }
    /* End: Getters and Setters */
}

?>