<?php
//Inclure Config.php

include_once 'config.php';

//Definir les variables
$username = $password = $confirm_password = $prenom = $nom = $date_naissance = "";
$username_err = $password_err = $confirm_password_err = $prenom_err = $nom_err = $date_naissance_err = "";

//Procedure

if($_SERVER['REQUEST_METHOD'] == "POST"){

    //Validation Prenom
    if(empty(trim($_POST['prenom']))){
        $prenom_err = "Entrer votre prenom";
    }else{
        $prenom = trim($_POST['prenom']);
    }
    //Validation de Nom
    if(empty(trim($_POST['nom']))){
        $nom_err = "Entrer votre nom";
    }else{
        $nom = trim($_POST['nom']);
    }
    //Validation de username
    if(empty(trim($_POST['username']))){
        $username_err = 'Entrer email correct';
    }/*elseif (!preg_match('/^[a-zA-Z0-9]+s/', trim($_POST['username']))){
        $username_err = 'Veuillez respecter les caracteres';
    }*/else{
        //Verifier dans la DB
        $sql = "SELECT id FROM users where username = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            //Lier les variables
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = trim($_POST['username']);

            // Executer la requete
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                if(mysqli_stmt_num_rows($stmt) == 1){
                    $username_err = 'Le login existe deja';
                }else{
                    $username = trim($_POST['username']);
                }
            }else{
                echo 'Incorrect';
            }

            //Fermerture
            mysqli_stmt_close($stmt);
        }
    }
    //Validation Password
    if(empty(trim($_POST['password']))){
        $password_err = 'Entrer votre password';
    }elseif (strlen(trim($_POST['password'])) < 6){
        $password_err = "Le password doit depasser 6 caractéres ";
    }else{
        $password = trim($_POST['password']);
    }
    //Validation Confirm_Password
    if(empty(trim($_POST['confirm_password']))){
        $confirm_password_err = "Veuillez confirmer votre password";
    }else{
        $confirm_password = trim($_POST['confirm_password']);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "password différent";
        }
    }
    //Validation de date naissance
    if(empty(trim($_POST['date_naissance']))){
        $date_naissance_err = "Entrer votre date de naissance";
    }else{
        $date_naissance = trim($_POST['date_naissance']);
    }

    //Configuration avatar


    //Inserer dans la base de donnée
    if(empty($prenom_err) && empty($nom_err) && empty($username_err) && empty($password_err) && empty($date_naissance_err)){
        if (isset($_FILES['avatar'])){
            $tailleMax = 2097152;
            $extentionsValides = array('jpeg', 'jpg', 'gif', 'png');
            $extensionUpload = strtolower(substr(strrchr($_FILES['avatar']['name'], '.'), 1));
            if(in_array($extensionUpload, $extentionsValides)){
                $_SESSION['extensionUpload'] = $extensionUpload;
                $chemin = "imagesuser/".$username.".".$_SESSION['extensionUpload'];
                $resultat = move_uploaded_file($_FILES['avatar']['tmp_name'], $chemin);
                if($resultat){
                    $_SESSION['photo'] = $username.".".$_SESSION['extensionUpload'];
                    $photo = $username.".".$_SESSION['extensionUpload'];
                }
            }
        }
        //Preparation du requete SQL
        $sql = "INSERT INTO users (prenom,nom,username,password,datenaissance,avatar) VALUES (?,?,?,?,?,?)";
        if($stmt = mysqli_prepare($link, $sql)){
            //Liaison des variables
            mysqli_stmt_bind_param($stmt, "ssssss", $param_prenom, $param_nom, $param_username, $param_password, $param_datenaissance, $param_avatar);
            //Insersion variable
            $param_prenom = $prenom;
            $param_nom = $nom;
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); //Cryptage du mot de pass
            $param_datenaissance = $date_naissance;
            $param_avatar = $photo;

            if(mysqli_stmt_execute($stmt)){
                //Redirection apres creation
                header("Location: ../index.php");
            }else{
                echo 'Erreur, Reéssayer';
            }
            //Fermerture
            mysqli_stmt_close($stmt);
        }
    }
    //Fermerture
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="icon" href="../dashbord/production/images/favicon.ico" type="image/ico" />
    <title>Inscription</title>
    <!-- Mobile Specific Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
    <!-- Font-->
    <link rel="stylesheet" type="text/css" href="../css/roboto-font.css">
    <link rel="stylesheet" type="text/css" href="../css/fontawesome-all.min.css">
    <!-- Main Style Css -->
    <link rel="stylesheet" href="../css/styleinscription.css"/>
</head>
<body class="form-v5" style="background-image: url(../images/2h-media-4blzwmR6q3o-unsplash.jpg); background-size: cover">
<div class="page-content">
    <div class="form-v5-content">
        <form class="form-detail" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>" method="post" enctype="multipart/form-data">
            <a  href="../index.php">
                <button type="button" class="btn-close btn-close-primary"  aria-label="Close"></button>
            </a>
            <h2>Inscription</h2>
            <div class="form-row">
                <label for="prenom">Prenom</label>
                <input type="text" name="prenom" id="prenom" class="input-text <?php echo (!empty($prenom_err)) ? 'is-invalid' : ''; ?>"
                       placeholder="Votre Prenom" value="<?php echo $prenom?>">
                <span class="invalid-feedback"><?php echo $prenom_err; ?></span>
            </div>
            <div class="form-row">
                <label for="nom">Nom</label>
                <input type="text" name="nom" id="nom" class="input-text <?php echo (!empty($nom_err)) ? 'is-invalid' : ''; ?>"
                       placeholder="Votre Nom" value="<?php echo $nom?>">
                <span class="invalid-feedback"><?php echo $nom_err; ?></span>
            </div>
            <div class="form-row">
                <label for="username">Email</label>
                <input type="text" name="username" id="username" class="input-text <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>"
                       placeholder="Votre Email"  pattern="[^@]+@[^@]+.[a-zA-Z]{2,6}" value="<?php echo $username?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>
            <div class="form-row">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="input-text <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                       placeholder="Password" value="<?php echo $password?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-row">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="input-text <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>"
                       placeholder="Confirm Password" value="<?php echo $confirm_password?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-row">
                <label for="date_naissance">date Naissance</label>
                <input type="date" name="date_naissance" id="date_naissance" class="input-text <?php echo (!empty($date_naissance_err)) ? 'is-invalid' : ''; ?>"
                       placeholder="Entrer votre date" value="<?php echo $date_naissance?>">
                <span class="invalid-feedback"><?php echo $date_naissance_err; ?></span>
            </div>
            <div class="form-row">
                <label for="avatar">Avatar</label>
                <input type="file" name="avatar" class="input-text" value="Choisir un avatar">
            </div>
            <div class="row g-3">
                <div class="col">
                    <input  type="submit" name="register" class="register" value="Register">
                </div>
                <!--
                <div class="col">
                    <input  type="button" href="../login.php" name="register" class="register" value="Register">
                </div>
                -->
            </div>
        </form>
    </div>
</div>


</body><!-- This templates was made by Colorlib (https://colorlib.com) -->

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.5/dist/umd/popper.min.js"
        integrity="sha384-Xe+8cL9oJa6tN/veChSP7q+mnSPaj5Bcu9mPX5F5xIGE0DVittaqT5lorf0EI7Vk" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.min.js"
        integrity="sha384-kjU+l4N0Yf4ZOJErLsIcvOU2qSb74wXpOhqTvwVx3OElZRweTnQ6d31fXEoRD1Jy" crossorigin="anonymous"></script>
</html>