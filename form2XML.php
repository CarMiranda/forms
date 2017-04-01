<?php 

    $xml = new DOMDocument();
    $xml->formatOutput = true;
    $form = $xml->createElement("form");

    for ($i = 1; $i <= $_POST["nbSections"]; ++$i) {
        $section = $xml->createElement("section");
        
        $title = $xml->createElement("title", urldecode($_POST["title" . $i]));
        $description = $xml->createElement("description", urldecode($_POST["description" . $i]));
        $section->appendChild($title);
        $section->appendChild($description);
        
        $qNb = $_POST["qNb" . $i];
        $attr = $xml->createAttribute("qNb");
        $attr->value = $qNb;
        $section->appendChild($attr);
        for ($j = 1; $j <= $qNb; ++$j) {
            $question = $xml->createElement("qcontainer");
            $question->appendChild($xml->createElement("question", urldecode($_POST["question" . $i . $j])));
            
            $aNb = $_POST["aNb" . $i . $j];
            $attr = $xml->createAttribute("aNb");
            $attr->value = $aNb;
            $question->appendChild($attr);
            
            for ($k = 1; $k <= $aNb; ++$k) {
                $question->appendChild($xml->createElement("answer", urldecode($_POST["answer" . $i . $j . $k])));
            }
            $section->appendChild($question);
        }
        $form->appendChild($section);
    }
    $xml->appendChild($form);
    $xml->save("./formsxml/test.xml");

    # echo var_dump($_POST);
?>