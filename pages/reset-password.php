<?php
if(!isset($_SESSION['loggdin']) || $_SESSION['loggdin'] !== true){
    header("Location: ../../index.php");
    exit;
}
require_once "config.php";

$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){
    // Validation password
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Entrer votre nouveau password";
    } elseif(strlen(trim($_POST["new_password"])) < 6){
        $new_password_err = "Password doit depasser 6 caracteres";
    } else{
        $new_password = trim($_POST["new_password"]);
    }
    // Validation confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Entrer votre nouveau password";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Password Different";
        }
    }

    if(empty($new_password_err) && empty($confirm_password_err)){

        $sql = "UPDATE users SET password=? WHERE id=?";

        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);

            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Password updated successfully. Destroy the session, and redirect to login page
                session_destroy(); if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);

            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_id = $_SESSION["id"];

            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Password updated successfully. Destroy the session, and redirect to login page
                session_destroy();
                header("Location: ../index.php");
                exit();
            } else{
                echo "Oops! Errerur";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
                header("Location: ../index.php");
                exit();
            } else{
                echo "Oops! Erreu";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}