<?php

require_once('Exceptions.php');
require_once('IConversion.php');
require_once('Option.php');

class QuestionUtils {

}

interface IQuestion {

    /* Start: Constructors */
        public function __construct($question = null, $description = null, $type = null, $required = null, $other = null, $nbO = null);
    /* End: Constructors */

    /* Start: Options manipulation methods */
        // fn(string) : void
        public function addOption($txt = null);

        // fn(int) : void
        public function removeOption($i);

        // fn(string) : void
        public function removeOptionById($id);
    /* End: Option manipulation methods */

    /* Start: Getters and Setters */
        // fn() : string
        public function getId();

        // fn() : int
        public function getNbO();

        // fn() : string
        public function getQuestion();

        // fn(string) : void
        public function setQuestion($txt);

        // fn() : string
        public function getDescription();

        // fn(string) : void
        public function setDescription($txt);

        // fn() : string
        public function getType();

        // fn(string) : void
        public function setType($type);

        // fn() : boolean
        public function getRequired();

        // fn(boolean) : void
        public function setRequired($ir);

        // fn() : boolean
        public function getOther();

        // fn(boolean) : void
        public function setOther($ho);

        /* Start: Options manipulation */
            /* Start: By index */
            /* These functions should be used only when manipulating the whole options array */
                // fn(int) : Option
                public function getOption($i); //

                // fn(int, string) : void
                public function setOption($i, $txt);

                // fn(int) : string
                public function getOptionId($i);

                // fn(int) : string
                public function getOptionText($i);

                // fn(int, string) : void
                public function setOptionText($i, $txt);
            /* End: By index */

            /* Start: By Id */
            /* These function should only be used when manipulating specific options */
                // fn(string) : Option
                public function getOptionById($id); //

                // fn(string, string) : void
                public function setOptionById($id, $txt);

                // fn(string) : string
                public function getOptionTextById($id);

                // fn(string, string) : void
                public function setOptionTextById($id, $txt);
            /* End: By Id */
        /* End: Options manipulation */

    /* End: Getters and Setters */
}

class Question implements IQuestion, IConversion {

    /* Start: Instance variables */
    private $id;
    private $nbO;
    private $question;
    private $description;
    private $type;
    private $required;
    private $other;
    private $options = [];
    /* End: Instance variables */

    /* Start: Constructors */
    public function __construct($question = null, $description = null, $type = null, $required = null, $other = null, $nbO = null) {
        $this->setQuestion($question);
        $this->setDescription($description);
        $this->setType($type);
        $this->setRequired($required);
        $this->setOther($other);
        if ($nbO == 1 && $type === 'pattern') {
            $this->setNbO(1);
            $this->options[0] = new Pattern();
        } else if ($nbO !== null) {
            $this->setNbO($nbO);
            $this->initOptions($nbO);
        } else {
            $this->setNbO(0);
        }
    }

    private function initOptions($nbO) {
        if (!is_int($nbO) || $nb < 0) {
            throw new InternalError("Wrong argument value for number of options on Question object creation. Value: {$nbO}. Question {$this->id}");
        }
        
        try {
            for ($i = 0; $i < $nbO; ++$i) {
                $this->options[$i] = new Option();
            }
        } catch (Exception $e) {
            throw new Exception ($e->__toString() . ", question {$this->id}");
        } 
    }
    /* End: Constructors */

    /* Start: Conversion methods */
    public function toHTML() {
        $str = "<div class='questionContainer'><div class='question'>{$this->question}</div>";
        if ($this->description) {
            $str .= "<div class='description'>{$this->description}</div>";
        }
        switch ($this->type) {
            case 'short':
                $str .= "<input type='text' " . ($this->required ? "required" : "") . "/><br>";
            break;
            case 'long':
                $str .= "<textarea " . ($this->required ? "required" : "") . "></textarea><br>";
            break;
            case 'pattern':
                $str .= "<input type='pattern' placeholder='" . $this->getOption(0)->getText() . "' pattern='" . $this->getOption(0)->getRegExp() . "' " . ($this->required ? "required" : "") . "/><br>";
            break;
            case 'radio':
                for ($k = 0; $k < $this->nbO; ++$k) {
                    $str .= "<input type='radio' id='" . $this->id . $this->getOptionId($k) . "' name='{$this->id}' " . ($this->required ? "required" : "") . "/>";
                    $str .= "<label for='" . $this->id . $this->getOptionId($k) . "'>" . $this->getOptionText($k) . "</label><br>";
                }
                if ($this->other) {
                    $str .= "<input type='radio' id='" . $this->id . "o' name='{$this->id}' " . ($this->required ? "required" : "") . "/>";
                    $str .= "<label for='" . $this->id . "o'><input type='text' /></label><br>";
                }
            break;
            case 'check':
                for ($k = 0; $k < $this->nbO; ++$k) {
                    "<input type='check' id='" . $this->id . $this->getOptionId($k) . "' name='{$this->id}'/>";
                    $str .= "<label for='" . $this->id . $this->getOptionId($k) . "'>" . $this->getOptionText($k) . "</label><br>";
                }
                if ($this->other) {
                    $str .= "<input type='check' id='" . $this->id . "o' name='{$this->id}'/>";
                    $str .= "<label for='" . $this->id . "o'><input type='text' /></label><br>";
                }
            break;
            case 'dropdown':
                $str .= "<select " . ($this->required ? "required" : "") . ">";
                for ($k = 0; $k < $this->nbO; ++$k) {
                    $str .= "<option>" . $this->getOptionText($k) . "</option>";
                }
                $str .= "</select><br>";
            break;
            case 'date':
                $str .= "<input type='date' " . ($this->required ? "required" : "") . "/><br>";
            break;
            case 'dateInt':
                $str .= "<label>Start date: </label><input type='date' " . ($this->required ? "required" : "") . "/><br>";
                $str .= "<label>End date: </label><input type='date' " . ($this->required ? "required" : "") . "/><br>";
            break;
            default:
                throw new InternalError('Wrong answer type.');
        }
        $str .= '</div>';
        return $str;
    }

    public function toXML($xml) {

        /* Add options' text to options file */
        /*if (!file_exists("/tmp/{$formId}-options.xml")) {
            $xmlOptions = new DOMDocument("1.0", "UTF-8");
            $xmlOptions->formatOutput = true;
            $container = $xmlOptions->createElement("options");
            $pastOptionsId = [];
            for ($i = 0, $j = 0; $i < $this->nbO; ++$i) {
                if (!in_array($this->getOptionId($i), $pastOptionsId)) {
                    $option = $xmlOptions->createElement("option", $this->getOptionText($i));
                    $option->setAttribute("id", $this->getOptionId($i));
                    $container->appendChild($option);
                    $pastOptionsId[$j++] = $this->getOptionId($i);
                }
            }
            $xmlOptions->appendChild($container);
            $xmlOptions->save("/tmp/{$formId}-options.xml");
        } /*else {}*/

        $questionContainer = $xml->createElement("questionContainer");
        $question = $xml->createElement("question", $this->question);
        $question->setAttribute("id", $this->id);
        $description = $xml->createElement("description", $this->description);
        $questionContainer->appendChild($question);
        $questionContainer->appendChild($description);

        $answerContainer = $xml->createElement("answerContainer");
        $answerContainer->setAttribute("type", $this->type);
        $answerContainer->setAttribute("required", (int)$this->required);

        switch ($this->type) {
            case "radio": case "check" : case "dropdown" :
                $answerContainer->setAttribute("nbO", $this->nbO);
                for ($k = 0; $k < $this->nbO; ++$k) {
                    $option = $xml->createElement("option", $this->getOptionText($k));
                    $option->setAttribute("id", $this->getOptionId($k));
                    $answerContainer->appendChild($option);
                }
                $answerContainer->setAttribute("other", $this->other);
            break;
            case "pattern" :
                $answerContainer->appendChild($xml->createElement("pattern", $this->getOptionText(0)));
            break;
        }

        $questionContainer->appendChild($answerContainer);
        return $questionContainer;
    }
    /* End: Conversion methods */

    /* Start: Options manipulation methods */
    public function addOption($txt = null, $ip = null) {
        if ($ip !== null) {
            $this->options[$this->nbO] = new Pattern($txt);
        } else {
            $this->options[$this->nbO] = new Option($txt);
        }
        $this->nbO++;
    }

    public function removeOption($i) {
        if ($i >= $this->nbO) {
            throw new InternalError("Index out of bounds.");
        }
        unset($this->options[$i]);
        $this->nbO--;
        for ($j = $i; $j < $this->nbO; ++$j) {
            $this->options[$j] = $this->options[$j + 1];
        }
    }

    public function removeOptionById($id) {
        try {
            $idx = $this->indexOf($id);
            unset($this->options[$idx]);
            $this->nbO--;
            for ($i = $idx; $i < $this->nbO; ++$i) {
                $this->options[$i] = $this->options[$i + 1];
            }
        } catch (InternalError $e) {
            throw new InternalError($e->getMessage() . ", question {$this->id}");
        }
    }
    /* End: Options manipulation methods */

    /* Start: Implementation methods */
    private function indexOf($id) {
        for ($i = 0; $i < $this->nbO; ++$i) {
            if ($this->options[$i]->getOptionId() === $id) {
                return $i;
            }
        }
        throw new InternalError("Option with id {$id} not found.");
    }
    /* End: Implementation methods */

    /* Start: Getters and Setters */
    public function getId() {
        return $this->id;
    }

    private function setId($txt) {
        if (empty($txt)) {
            $txt = "";
        } else if (!is_string($txt)) {
            throw new InternalError("Cannot set 'id' property of Question object using element of type " . gettype($txt) . ". Value passed {$txt}.");
        } else {
            $this->id = hash("crc32", $txt);
        }
    }

    public function getNbO() {
        return $this->nbO;
    }

    private function setNbO($nb) {
        if ($nb === null) {
            $nb = 0;
        } else if (!is_int($nb)) {
            throw new InternalError("Wrong argument type for number of option on Question object creation. Value: {$nb}. Question {$this->id}");
        }
        $this->nbO = $nb;
    }

    public function getQuestion() {
        return $this->question;
    }

    public function setQuestion($txt) {
        if (empty($txt)) {
            $txt = "";
        } else if (!is_string($txt)) {
            throw new InternalError("Wrong argument type for question on Question object creation. Value: {$txt}. Question {$this->id}");
        }
        $this->question = $txt;
        $this->setId($txt);
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($txt) {
        if ($txt === null) {
            $txt = "";
        } else if (!is_string($txt)) {
            throw new InternalError("Wrong argument type for description on Question object creation. Value: {$txt}. Question {$this->id}");
        }
        $this->description = $txt;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($txt) {
        if ($txt === null) {
            $txt = "";
        } else if (!is_string($txt)) {
            throw new InternalError("Wrong argument type for type on Question object creation. Value: {$txt}. Question {$this->id}");
        }
        $this->type = $txt;
    }

    public function getRequired() {
        return $this->required;
    }

    public function setRequired($ir) {
        if ($ir === null) {
            $ir = FALSE;
        } else if (!is_bool($ir)) {
            throw new InternalError("Wrong argument type for question on Question object creation. Value: {$ir}. Question {$this->id}");
        }
        $this->required = $ir;
    }

    public function getOther() {
        return $this->other;
    }

    public function setOther($ho) {
        if ($ho === null) {
            $ho = FALSE;
        } else if (!is_bool($ho)) {
            throw new InternalError("Wrong argument type for other on Question object creation. Value: {$ho}. Question {$this->id}");
        }
        $this->other = $ho;
    }

    public function getOption($i) {
        if (empty($this->options)) {
            throw new InternalError('Options array is empty.');
        } else if (count($this->options) < $i) {
            throw new InternalError('Index out of bounds.');
        }
        return $this->options[$i];
    }

    public function setOption($i, $txt) {
        try {
            $this->option[$i]->setText($txt);
        } catch (InternalError $e) {
            throw new InternalError($e->__toString() . ", question {$this->id}");
        } catch  (Exception $e) {
            throw new InternalError("Index out of bounds.");
        }   
    }

    public function getOptionId($i) {
        return $this->getOption($i)->getId();
    }

    public function setOptionId($i, $txt) {
        try {
            $this->option[$i]->setId($txt);
        } catch (InternalError $e) {
            throw new InternalError($e->__toString() . ", question {$this->id}");
        } catch  (Exception $e) {
            throw new InternalError("Index out of bounds.");
        }
    }

    public function getOptionText($i) {
        return $this->getOption($i)->getText();
    }

    public function setOptionText($i, $txt) {
        try {
            $this->option[$i]->setText($txt);
        } catch (InternalError $e) {
            throw new InternalError($e->__toString() . ", question {$this->id}");
        } catch  (Exception $e) {
            throw new InternalError("Index out of bounds.");
        }
    }

    public function getOptionById($id) {
        return $this->getOption($this->indexOf($id));
    }

    public function setOptionById($id, $txt) {
        $this->setOption($this->indexOf($id), $txt);
    }

    public function getOptionTextById($id) {
        return $this->getOptionById($id)->getText();
    }

    public function setOptionTextById($id, $txt) {
        $this->setOptionById($this->indexOf($id), $txt);
    }
    /* End: Getters and Setters */
}

?>