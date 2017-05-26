<?php

require_once('Exceptions.php');
require_once('IConversion.php');

interface IOption {

    /* Start: Constructors */
        public function __construct($txt = null);
    /* End: Constructors */

    /* Start: Getters and Setters */
        /* fn() : string */
        public function getId();

        /* fn() : string */
        public function getText();

        /* fn(string) : void */
        public function setText($txt);
    /* End: Getters and Setters */
}

class Option implements IOption {

    /* Start: Instance variables */
        private $id;
        protected $text;
    /* End: Instance variables*/

    /* Start: Constructors */
        public function __construct($txt = null) {
            $this->setText($txt);
        }
    /* End: Constructors */

    /* Start: Getters and Setters */
        // fn() : string
        public function getId() {
            return $this->id;
        }

        // fn(string) : void
        protected function setId($txt) {
            if (empty($txt)) {
                $this->id = "";
            } else if (!is_string($txt)) {
                throw new InternalError("Cannot set 'id' property of Option object using element of type " . gettype($txt) . ". Value passed {$txt}.");
            } else {
                $this->id = hash("crc32", $txt);
            }
        }

        // fn() : string
        public function getText() {
            return $this->text;
        }

        // fn(string) : void
        public function setText($txt) {
            if (empty($txt)) {
                $txt = "";
            } else if (!is_string($txt)) {
                throw new InternalError("Cannot set 'text' property of Option object to element of type " . gettype($txt) . ". Value passed {$txt}.");
            }
            $this->text = $txt;
            $this->setId($txt);
        }
    /* End: Getters and Setters */
}

class Pattern extends Option {

    /* Start: Instance variables */
        protected $text;
    /* End: Instance variables*/

    /* Start: Constructors */
        public function __construct($txt = null) {
            $this->setText($txt);
        }
    /* End: Constructors */

    // fn() : string
    private function toRegExp() {
        $regexp = "";
        preg_match_all('/([A]+|[\#]+|[\-]+)/', $this->text, $aregexp);
        $aregexp = $aregexp[0];

        for ($i = 0; $i < count($aregexp); ++$i) {
            if ($aregexp[$i][0] === '#') {
                $regexp .= '[0-9]{' . strlen($aregexp[$i]) . '}';
            } else if ($aregexp[$i][0] === 'A') {
                $regexp .= '[a-zA-Z]{' . strlen($aregexp[$i]) . '}';
            } else {
                $regexp .= '[\-]{' . strlen($aregexp[$i]) . '}';
            }
        }
        return $regexp;
    }

    /* Start: Getters and Setters */
        public function setText($str) {
            if (!preg_match('/^[A\#\-]*$/', $str)) {
                throw new InternalError('Wrong pattern.');
            }
            $this->text = $str;
            $this->setId($str);
        }

        public function getText() {
            return $this->text;
        }

        // fn() : string
        public function getRegExp() {
            return $this->toRegExp();
        }
    /* End: Getters and Setters */
}

?>