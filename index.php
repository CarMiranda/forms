<?php
    $name = "Untitled Form";
    $owner = "Myself";
    $group = "Group";
?><!DOCTYPE html>

<html>
    
    <head>
        <meta charset="utf-8" />
        <meta name="description" content="Creation de formulaires AJIR" />
        <meta name="kerywords" content="AJIR,Junior-Entreprise,INSA,ROUEN,FORMULAIRES" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Creation de formulaire | Association Junior-Entreprise de l'INSA de Rouen</title>
        <link rel="stylesheet" type="text/css" href="style.css" />
        <script src="createForm.js"></script>
        <script src="saveForm.js"></script>
    </head>
    <body>
        <div id="topTools">
            <div>
                <textarea id="formTitle" rows="1"><?php echo $name ?></textarea>

                <textarea id="formOwner" rows="1"><?php echo $owner ?></textarea>

                <textarea id="formGroup" rows="1"><?php echo $group ?></textarea>
            </div>
        </div>
        <div id="formContainer">
            <div id="sideTools">
                <div>
                    <img id="newQuestion" src="media/plus.png" title="Add a Question" onclick="addQuestion();" alt="Add a Question"/>
                </div>
                <div>
                    <img id="newTitle" src="media/title.png" title="Add a Title" onclick="addTitle();" alt="Add a Title"/>
                </div>
                <div>
                    <img id="newImage" src="media/image.png" title="Add an Image" onclick="addImage();" alt="Add an Image" />
                </div>
                <div>
                    <img id="newVideo" src="media/video.png" title="Add a Video" onclick="addImage();" alt="Add a Video" />
                </div>
                <div>
                    <img id="newSection" src="media/section.png" title="Add a Section" onclick="addSection();" alt="Add a Section" />
                </div>
            </div>
        </div>
    </body>
</html>