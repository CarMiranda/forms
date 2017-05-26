<?php

    /*
    if (session_status() == PHP_SESSION_NONE) {
        header("Location: login.php");
    }
    */

    require_once('Form.php');
    try {
        $form = new Form($_GET['fId']);
        $_SESSION['status'] = 0;
    } catch (InternalError $e) {
        $_SESSION['status'] = 1;
    } catch (IOException $e) {
        $_SESSION['status'] = 2;
    } catch (Exception $e) {
        $_SESSION['status'] = 3;
    }

?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="style.css" />
    </head>
    <body>
        <?php echo ($_SESSION['status'] === 0 ? $form->toHTML() : "Error"); ?>
    </body>
</html>