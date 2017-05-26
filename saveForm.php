<?php

    //session_start();

    require_once('Form.php');
    try {
        $form = new Form($_POST);
        $form->saveXML();
        $_SESSION['status'] = 0;
    } catch (InternalError $e) {
        $_SESSION['status'] = 1;
    } catch (IOException $e) {
        $_SESSION['status'] = 2;
    } catch (Exception $e) {
        $_SESSION['status'] = 3;
    } finally {
        echo $_SESSION['status'];
    }

?>