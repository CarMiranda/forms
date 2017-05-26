<?php

require_once('Exceptions.php');

class Validation {

    private static $answerTypes = ['short', 'long', 'pattern', 'radio', 'check', 'dropdown', 'date', 'dateInt'];

    private static function keysLike($data, $key) {
        /* Check if parameters are valid */
        if (!is_array($data) || !is_string($key)) {
            return null;
        }

        $cnt = 0;
        $len = count($data);
        $keys = array_keys($data);
        for ($i = 0; $i < $len; ++$i) {
            if (strpos($keys[$i], $key) !== FALSE) {
                $cnt++;
            }
        }
        return $cnt;
    }

    /* Checks for basic form information */
    private static function checkFormInfo($data) {

        /* Fields to check */
        $fields = [
            'fId' => 'Form ID',
            'fTitle' => 'Form Title',
            'fOwner' => 'Form Owner',
            'fGroup' => 'Form Group',
        ]; // 'formAccess', 'staDate', 'endDate'];

        foreach ($fields as $key => $value) {
            if (!isset($data[$key])) {
                throw new HTTPException('No ' . $key . ' parameter found.');
            } else if (empty($data[$key])) {
                throw new HTPPException($value . ' is empty.');
            }
        }

        /* + validation against db values:
        * Repetition of formId?
        * Possible change of owner/group?
        * ...
        */
    }

    /* Returns max section number based on sTitle and sDescription params */
    private static function getNbSections($data) {

        $max = 0;
        foreach ($data as $key => $value) {
            if (strpos($key, 'sTitle') !== FALSE || strpos($key, 'sDescription') !== FALSE) {
                if ((int)substr($key, -1, 1) > $max) {
                    $max = (int)substr($key, -1, 1);
                }
            }
        }
        return ++$max;
    }

    /* Checks for errors in sections (real number of sections vs. nbS parameter) and returns real number of sections */
    private static function checkSections($nbS, $rNbS) {
        if (empty($nbS)) {
            throw new HTTPException('nbS parameter not found.');
        }

        if ((int)$nbS !== $rNbS) {
            throw new HTTPException('nbS parameter and real section number mismatch.' . $nbS . " != " . $rNbS);
        }

        return $rNbS;
    }

    /* Checks questions for errors */
    private static function checkQuestions($nbS, $data) {

        /* Check if number of nbQ parameters is equal to number of sections */
        if (self::keysLike($data, 'nbQ') !== $nbS) {
            throw new HTTPException('Number of nbQ parameters and value of nbS parameter mismatch.', 24);
        }
        
        for ($i = 0; $i < $nbS; ++$i) {

            /* Check if nbQ_i exists and if it is valid (> 0) */
            if (empty($data["nbQ{$i}"])) {
                throw new HTTPException("Empty nbQ parameter for section {$i}.");
            } else if ((int)$data["nbQ{$i}"] < 1) {
                throw new HTTPException("Invalid nbQ parameter value (" . $data["nbQ{$i}"] . ") for section {$i}.");
            }

            $nbQ = (int)$data["nbQ{$i}"];

            /* Check if nbQ_i value is equal to number of question for section i */
            if ($nbQ !== self::keysLike($data, "question{$i}")) {
                throw new HTTPException("nbQ parameter value ({$nbQ}) and number of questions mismatch for section {$i}.", 25);
            }

            /* Check if sTitle_i exists and if it is valid (not empty string) */
            if (empty($data["sTitle{$i}"])) {
                throw new HTTPException("Empty sTitle parameter for section {$i}.");
            }

            for ($j = 0; $j < $nbQ; ++$j) {

                /* Check if question_ij exists and if it is valid (not empty string) */
                if (empty($data["question{$i}_{$j}"])) {
                    throw new HTTPException("Empty question parameter for question {$j} in section {$i}.");
                }

                /* Check if required_ij exists */
                if (!isset($data["required{$i}_{$j}"])) {
                    throw new HTTPException("Empty required parameter for question {$j} in section {$i}.");
                }

                /* Check if answerType_ij exists */
                if (empty($data["answerType{$i}_{$j}"])) {
                    throw new HTTPException("Empty answer type parameter for question {$j} in section {$i}.");
                }

                $answerType = $data["answerType{$i}_{$j}"];

                /* Check if answerType_ij is valid (is one of valid answer types) */
                if (!in_array($answerType, self::$answerTypes)) {
                    throw new HTTPException("Invalid answer type parameter value '{$answerType}' found for question {$j} in section {$i}.");
                }

                if ($answerType === 'check' || $answerType === 'radio' || $answerType === 'dropdown') {
                    /* Check if nbO_ij exists and is valid (> 0) */
                    if (empty($data["nbO{$i}_{$j}"])) {
                        throw new HTTPException("Empty nbO parameter for question {$j} in section {$i}.");
                    } else if ((int)$data["nbO{$i}_{$j}"] < 1) {
                        throw new HTTPException("Invalid nbO parameter value (" . $data["nbQ{$i}"] . ") for question {$j} in section {$i}.");
                    }

                    $nbO = (int)$data["nbO{$i}_{$j}"];

                    /* Check if nbO_ij value is equal to number of question for section i */
                    if ($nbO !== self::keysLike($data, "option{$i}_{$j}")) {
                        throw new HTTPException("nbO parameter value ({$nbO}) and number of options mismatch for question {$j} in section {$i}.", 25);
                    }

                    for ($k = 0; $k < $nbO; ++$k) {
                        /* Check if question_ij exists and if it is valid (not empty string) */
                        if (empty($data["option{$i}_{$j}_{$k}"])) {
                            throw new HTTPException("Empty option ({$k}) for question {$j} in section {$i}.");
                        }
                    }

                    if ($answerType !== 'dropdown') {
                        /* Check if other_ij exists and if it is valid (not empty string) */
                        if (!isset($data["other{$i}_{$j}"])) {
                            throw new HTTPException("Empty other parameter for question {$j} in section {$i}.");
                        }
                        /* Here we use isset rather than empty since empty('0') === true */
                    }
                } else if ($answerType === 'pattern') {
                    if (empty($data["pattern{$i}_{$j}"])) {
                        throw new HTTPException("Empty pattern parameter for question {$j} in section {$i}.");
                    }  else if (!preg_match('/^[A\-\#]*$/', $data["pattern{$i}{$j}"])) {
                        throw new HTTPException("Pattern is invalid for question {$j} in section {$i}.");
                    }
                }
            }
        }

    }

    /* Validate the data received from the Form Creator */
    public static function requestValidation($request) {

        try {
            /* Check for basic form information */
            self::checkFormInfo($request);
            
            /* Check if nbS parameter == real section number */
            $rNbS = self::getNbSections($request);
            $nbS = $request['nbS'];
            self::checkSections($nbS, $rNbS);
            self::checkQuestions((int)$nbS, $request);
            
        } catch (HTTPException $e) {
            return false;
        }

        return true;
    }

    /* Parse the HTTP request data */
    public static function parseData(&$data) {
        foreach ($data as $key => $component) {
            $data[$key] = urldecode($component);
            $data[$key] = trim($component);
            $data[$key] = stripslashes($component);
            $data[$key] = htmlspecialchars($component);
        }
    }
}

?>