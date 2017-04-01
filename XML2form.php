<?php 
    $formId = $_REQUEST["formId"];
    $xml = new DOMDocument();
    $xml->preserveWhiteSpace = false;
    $xml->load("./formsxml/" . $formId . ".xml");
?>
<html>
    <head>
    </head>
    <body>
        <form>
            <?php
                $sections = $xml->getElementsByTagName("section");
                $sNb = $sections->length;
                $theSection = "";
                $section = $sections->item(0);
                for ($i = 0; $i < $sNb; ++$i) {
                    
                    $theSection .= "<section>";
                    $title = $section->firstChild;
                    $description = $title->nextSibling;
                    $theSection .= "<h1>" . $title->textContent . "</h1>"; 
                    $theSection .= "<h3>" . $description->textContent . "</h3><br>";
                    
                    $qNb = $section->getAttribute("qNb");
                    for ($j = 0; $j < $qNb; ++$j) {
                        $theQuestion = $description->nextSibling->firstChild;
                        $theSection .= "<div name='qcont'><div name='question'>" . $theQuestion->textContent . "</div>";
                        $anAnswer = $theQuestion->nextSibling;
                        $aNb = $theQuestion->parentNode->getAttribute("aNb");
                        for ($k = 0; $k < $aNb; ++$k) {
                            $theSection .= "<input type='radio' name='answer" . $i . $j . "' id='" . $i . $j . $k . "'/> <label for='answer" . $i . $j . $k . "'>" . $anAnswer->textContent . "</label><br>";
                            $anAnswer = $anAnswer->nextSibling;
                        }
                        $theSection .= "</div>";
                    }
                    
                    $theSection .= "</section><br>";
                    $section = $section->nextSibling;
                }
                echo $theSection;
            ?>
        </form>
    </body>
</html>