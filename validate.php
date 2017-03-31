<?php 

    $xml = new DOMDocument();
    $xml->formatOutput = true;
    $form = $xml->createElement("form");

    for ($i = 1; $i <= $_REQUEST["nbSections"]; ++$i) {
        $section = $xml->createElement("section");
        $title = $xml->createElement("title", $_POST["title" . $i]);
        $description = $xml->createElement("description", $_POST["description" . $i]);
        $section->appendChild($title);
        $section->appendChild($description);
        $form->appendChild($section);
    }
    $xml->appendChild($form);
    $xml->save("./formsxml/test.xml");

    echo var_dump($_POST);
?>