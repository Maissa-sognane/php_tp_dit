<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("Location: ../../index.php");
    exit;
}
include_once "readUser.php";
include_once '../../pages/config.php';

//Initialisation des passwords
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
$alerte = "";

if(isset($_POST['enregistrer_password'])){
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
                // Lier les variables
                mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);
                // Crypter les variables
                $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                $param_id = $_SESSION["id"];
                // Excution
                if(mysqli_stmt_execute($stmt)){
                    // Modification du password
                    session_destroy(); if($stmt = mysqli_prepare($link, $sql)){
                        mysqli_stmt_bind_param($stmt, "si", $param_password, $param_id);
                        $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $param_id = $_SESSION["id"];
                        if(mysqli_stmt_execute($stmt)){
                            session_destroy();
                            header("Location: ../../index.php");
                            exit();
                        } else{
                            echo "Oops! Errerur";
                        }
                        mysqli_stmt_close($stmt);
                    }
                    header("Location: ../../index.php");
                    exit();
                } else{
                    echo "Oops! Erreu";
                }
                mysqli_stmt_close($stmt);
            }
        }
        mysqli_close($link);
    }
}


//Initialisation Des attributs de l'utilisateurs

$username  = $prenom = $nom = $date_naissance = "";
$username_err = $prenom_err = $nom_err = $date_naissance_err = "";
if (isset($_POST['modification'])){
if($_SERVER['REQUEST_METHOD'] == "POST"){
    //Validation
    if(empty(trim($_POST['prenom']))){
      //  $prenom_err = "Entrer votre prenom";
        $prenom = $_SESSION['prenom'];
    }else{
        $prenom = trim($_POST['prenom']);
    }
    //Validation de Nom
    if(empty(trim($_POST['nom']))){
       // $nom_err = "Entrer votre nom";
        $nom = $_SESSION['nom'];
    }else{
        $nom = trim($_POST['nom']);
    }

    //Validation de username
    if(empty(trim($_POST['username']))){
       // $username_err = 'Entrer email correct';
        $username = $_SESSION['username'];
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
                    $username = $_SESSION['username'];
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

    //Date de naissance

    if(empty(trim($_POST['date_naissance']))){
       // $date_naissance_err = "Entrer votre date de naissance";
          $date_naissance = $_SESSION['datenaissance'];
    }else{
        $date_naissance = trim($_POST['date_naissance']);
    }

        $sql = "UPDATE users SET prenom=?, nom=?, username=?, datenaissance=? WHERE id=?";
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "ssssi", $param_prenom, $param_nom,
                $param_username,  $param_datenaissance, $param_id);

            $param_prenom = $prenom;
            $param_nom = $nom;
            $param_username = $username;
            $param_datenaissance = $date_naissance;
            $param_id = $_SESSION['id'];

            if(mysqli_stmt_execute($stmt)){
                //Redirection apres creation
                //session_destroy();
               //  header("Location: ../../index.php");
                header("Refresh:1");
                $_SESSION['prenom'] = $prenom;
                $_SESSION['nom'] = $nom;
              // exit;
            }else{
                echo 'Erreur, Reéssayer';
            }
            //Fermerture
            mysqli_stmt_close($stmt);
        }

    }
}

if (isset($_POST['avatar'])){
if($_SERVER['REQUEST_METHOD'] == "POST") {
    if(!empty($_FILES['avatar'])){
        $tailleMax = 2097152;
        $extentionsValides = array('jpeg', 'jpg', 'gif', 'png');
        $extensionUpload = strtolower(substr(strrchr($_FILES['avatar']['name'], '.'), 1));
        if(in_array($extensionUpload, $extentionsValides)){
            $_SESSION['extensionUpload'] = $extensionUpload;
            $chemin = "../../pages/imagesuser/".$_SESSION['username'].".".$_SESSION['extensionUpload'];
            $resultat = move_uploaded_file($_FILES['avatar']['tmp_name'], $chemin);
            if($resultat){
              //  $_SESSION['photo'] = $_SESSION['username'].".".$_SESSION['extensionUpload'];
                $photo = $_SESSION['username'].".".$_SESSION['extensionUpload'];
            }
        }
        $sql = "UPDATE users SET avatar=? WHERE id=?";
        if($stmt = mysqli_prepare($link, $sql)){
            // Lier les variables
            mysqli_stmt_bind_param($stmt, "si", $param_avatar, $param_id);
            // Crypter les variables
            $param_avatar = $photo;
            $param_id = $_SESSION["id"];
            // Excution
            if(mysqli_stmt_execute($stmt)){
                header("Refresh:1");
                $_SESSION['avatar'] = $photo;
            }
        }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="images/favicon.ico" type="image/ico" />

    <title>INNOV</title>

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">
	
    <!-- bootstrap-progressbar -->
    <link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- JQVMap -->
    <link href="../vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
    <!-- bootstrap-daterangepicker -->
    <link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="../build/css/custom.min.css" rel="stylesheet">
      <link rel="stylesheet" href="css/style.css">

  </head>
  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">
              <a href="#" class="site_title"><i class="fa fa-paw"></i> <span>INNOV</span></a>
            </div>
            <div class="clearfix"></div>
            <!-- menu profile quick info -->
            <div class="profile clearfix">
              <div class="profile_pic">
                <?php
                echo '<img  class="img-circle profile_img" style = "
                        width: 46px;
                        height: 48px;
                        border-radius: 50%;
                        background: #fff;
                        margin-left: 42%;
                        z-index: 1000;
                        position: inherit;
                        margin-top: 27px;
                        border: 1pxsolidrgba(52,73,94,0.44);
                        padding: 4px;
                        " src="../../pages/imagesuser/'.$_SESSION['avatar'].'">';
                ?>
              </div>
              <div class="profile_info">
                <span>Bienvenue,</span>
                <h2><?php echo htmlspecialchars($_SESSION["prenom"]);?> <?php echo htmlspecialchars($_SESSION["nom"]);?></h2>
              </div>
            </div>
            <!-- /menu profile quick info -->
            <br />
            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              <div class="menu_section">
                <h3>General</h3>
                <ul class="nav side-menu">
                  <li><a><i class="fa fa-home"></i> Home <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="#">Utilisateur</a></li>
                      <li><a href="#">Gestion</a></li>
                      <li><a href="#">Gestion Roles</a></li>
                    </ul>
                  </li>
                </ul>
              </div>
            </div>
            <div class="sidebar-footer hidden-small">
              <a data-toggle="tooltip" data-placement="top" title="parametre">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="FullScreen">
                <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Lock">
                <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Logout" href="../../pages/logout.php">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
              </a>
            </div>
            <!-- /menu footer buttons -->
          </div>
        </div>
        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
              <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
              </div>
              <nav class="nav navbar-nav">
              <ul class=" navbar-right">
                <li class="nav-item dropdown open" style="padding-left: 15px;">
                  <a href="javascript:;" class="user-profile dropdown-toggle" aria-haspopup="true" id="navbarDropdown" data-toggle="dropdown" aria-expanded="false">
                      <?php
                      echo '<img  src="../../pages/imagesuser/'.$_SESSION['avatar'].'">';
                      ?><?php echo htmlspecialchars($_SESSION["prenom"]);?> <?php echo htmlspecialchars($_SESSION["nom"]);?>
                  </a>
                  <div class="dropdown-menu dropdown-usermenu pull-right" aria-labelledby="navbarDropdown">
                      <a class="dropdown-item"  href="javascript:;" data-toggle="modal" data-target=".bs-example-modal-lg_avatar">
                          <i class="fa fa-camera pull-right"></i>changer Photo
                      </a>
                        <a class="dropdown-item"  href="javascript:;" data-toggle="modal" data-target=".bs-example-modal-lg">profile
                        </a>
                      <a class="dropdown-item"  href="javascript:;" data-toggle="modal" data-target=".bs-example-modal-lg_password">
                          Changer de password
                      </a>
                    <a class="dropdown-item"  href="../../pages/logout.php"><i class="fa fa-sign-out pull-right"></i>Deconnexion</a>
                  </div>
                </li>
              </ul>
            </nav>
          </div>
        </div>
        <div class="right_col" role="main">
          <!-- top tiles -->
          <div class="row" style="display: inline-block;" >
          <div class="tile_count">
            <div class="col-md-2 col-sm-4  tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Total Users</span>
              <div class="count">
                  <?php
                        echo $nbUsers;
                  ?>
              </div>
              <span class="count_bottom"><i class="green">4% </i> </span>
            </div>
            <div class="col-md-2 col-sm-4  tile_stats_count">
              <span class="count_top"><i class="fa fa-clock-o"></i> Average Time</span>
            </div>
            <div class="col-md-2 col-sm-4  tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Total Males</span>
            </div>
            <div class="col-md-2 col-sm-4  tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Total Females</span>
            </div>
            <div class="col-md-2 col-sm-4  tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Total Collections</span>
            </div>
            <div class="col-md-2 col-sm-4  tile_stats_count">
              <span class="count_top"><i class="fa fa-user"></i> Total Connections</span>
            </div>
          </div>
        </div>
          <!-- /top tiles -->
            <div class="container-xl">
                <div class="table-responsive">
                    <div class="table-wrapper">
                        <div class="table-title" style="background: #2A3F54">
                            <div class="row">
                                <div class="col-sm-5">
                                    <h2>User <b>Management</b></h2>
                                </div>
                            </div>
                        </div>
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Prenom</th>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>date Naissance</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i=1;
                            foreach($users as $user){
                            ?>
                            <tr>
                                <td><?= $i  ?></td>
                                <td><a href="#"><?php
                                        echo '<img class="avatar" alt="Avatar"  src="../../pages/imagesuser/'.$user['avatar'].'">';
                                        ?><?= $user['prenom'] ?></a></td>
                                <td><?= $user['nom'] ?></td>
                                <td><?= $user['username'] ?></td>
                                <td><?= $user['datenaissance'] ?></td>
                                <td><span class="status text-success">&bull;</span> Active</td>
                                <td>
                                    <a href="#" class="settings" title="Settings" data-toggle="tooltip"><i class="material-icons">&#xE8B8;</i></a>
                                    <a href="#" class="delete" title="Delete" data-toggle="tooltip"><i class="material-icons">&#xE5C9;</i></a>
                                </td>
                            </tr>
                                <?php
                                $i++;
                            }
                            ?>
                            </tbody>
                        </table>
                        <div class="clearfix">
                            <div class="hint-text">Showing <b>5</b> out of <b>25</b> entries</div>
                            <ul class="pagination">
                                <li class="page-item <?= ($currentPage == 1) ? "disabled" : "" ?>">
                                    <a href="./?page=<?= $currentPage - 1 ?>" class="page-link">Précédente</a>
                                </li>
                                <?php for($page = 1; $page <= $pages; $page++): ?>
                                    <!-- Lien vers chacune des pages (activé si on se trouve sur la page correspondante) -->
                                    <li class="page-item <?= ($currentPage == $page) ? "active" : "" ?>">
                                        <a href="./?page=<?= $page ?>" class="page-link"><?= $page ?></a>
                                    </li>
                                <?php endfor ?>
                                <!-- Lien vers la page suivante (désactivé si on se trouve sur la dernière page) -->
                                <li class="page-item <?= ($currentPage == $pages) ? "disabled" : "" ?>">
                                    <a href="./?page=<?= $currentPage + 1 ?>" class="page-link">Suivante</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
          <br />
          </div>
        </div>

        <!-- /page content -->

        <!-- footer content -->
        <footer>
          <div class="pull-right">
            INNOV - SOGNANE
          </div>
        </footer>
        <!-- /footer content -->
      </div>

    <!-- Modification User -->
    <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Profile</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="x_content">
                        <br />
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'])?>" id="demo-form2" data-parsley-validate class="form-horizontal form-label-left">

                            <div class="item form-group">
                                <label class="col-form-label col-md-3 col-sm-3 label-align" for="prenom">Prenom <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 ">
                                    <input type="text" id="prenom" name="prenom" class="form-control " value="<?php echo $_SESSION['prenom']; ?>">
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="col-form-label col-md-3 col-sm-3 label-align" for="nom">Nom <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 ">
                                    <input type="text" id="nom" name="nom"  class="form-control" value="<?php echo $_SESSION['nom']; ?>">
                                </div>
                            </div>
                            <div class="item form-group">
                                <label for="username" class="col-form-label col-md-3 col-sm-3 label-align">Email</label>
                                <div class="col-md-6 col-sm-6 ">
                                    <input id="username" class="form-control" type="text" name="username" value="<?php echo $_SESSION['username']; ?>">
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="col-form-label col-md-3 col-sm-3 label-align">Date Naissance <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 ">
                                    <input id="date_naissance" name="date_naissance" class="date-picker form-control"
                                           placeholder="dd-mm-yyyy" type="text"  type="text"
                                           onfocus="this.type='date'" onmouseover="this.type='date'" onclick="this.type='date'"
                                           onblur="this.type='text'" onmouseout="timeFunctionLong(this)" value="<?php echo $_SESSION['datenaissance']; ?>">
                                    <script>
                                        function timeFunctionLong(input) {
                                            setTimeout(function() {
                                                input.type = 'text';
                                            }, 60000);
                                        }
                                    </script>
                                </div>
                            </div>
                            <div class="ln_solid"></div>
                            <div class="item form-group">
                                <div class="col-md-6 col-sm-6 offset-md-3">
                                    <button name="modification" type="submit" class="btn btn-success">Enregistrer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
                -->
            </div>
        </div>
    </div>

    <!-- Modification Password-->

    <div class="modal fade bs-example-modal-lg bs-example-modal-lg_password" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Password</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="x_content">
                        <br />
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="demo-form2" method="post" data-parsley-validate class="form-horizontal form-label-left">

                            <div class="item form-group">
                                <label class="col-form-label col-md-3 col-sm-3 label-align" for="new_password">Password <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 ">
                                    <input type="password" id="new_password" name="new_password"  class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>"
                                           value="<?php echo $new_password; ?>">
                                    <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="col-form-label col-md-3 col-sm-3 label-align" for="confirm_password">Confirm Password <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 ">
                                    <input type="password" id="confirm_password" name="confirm_password"  class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>"
                                           value="<?php echo $confirm_password; ?>">
                                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                                </div>
                            </div>
                            <div class="ln_solid"></div>
                            <div class="item form-group">
                                <div class="col-md-6 col-sm-6 offset-md-3">
                                    <button name="enregistrer_password" type="submit" class="btn btn-success">Enregistrer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    // Avatar

    <div class="modal fade bs-example-modal-lg bs-example-modal-lg_avatar" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">Changer de Photo</h4>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="x_content">
                        <br />
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="demo-form2"
                              method="post" data-parsley-validate class="form-horizontal form-label-left" enctype="multipart/form-data">
                            <div class="item form-group">
                                <label class="col-form-label col-md-3 col-sm-3 label-align" for="confirm_password">Avatar<span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 ">
                                    <input type="file" id="avatar" name="avatar"  class="form-control">
                                </div>
                            </div>
                            <div class="ln_solid"></div>
                            <div class="item form-group">
                                <div class="col-md-6 col-sm-6 offset-md-3">
                                    <button name="avatar" type="submit" class="btn btn-success">Enregistrer</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- jQuery -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- FastClick -->
    <script src="../vendors/fastclick/lib/fastclick.js"></script>
    <!-- NProgress -->
    <script src="../vendors/nprogress/nprogress.js"></script>
    <!-- Chart.js -->
    <script src="../vendors/Chart.js/dist/Chart.min.js"></script>
    <!-- gauge.js -->
    <script src="../vendors/gauge.js/dist/gauge.min.js"></script>
    <!-- bootstrap-progressbar -->
    <script src="../vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>
    <!-- iCheck -->
    <script src="../vendors/iCheck/icheck.min.js"></script>
    <!-- Skycons -->
    <script src="../vendors/skycons/skycons.js"></script>
    <!-- Flot -->
    <script src="../vendors/Flot/jquery.flot.js"></script>
    <script src="../vendors/Flot/jquery.flot.pie.js"></script>
    <script src="../vendors/Flot/jquery.flot.time.js"></script>
    <script src="../vendors/Flot/jquery.flot.stack.js"></script>
    <script src="../vendors/Flot/jquery.flot.resize.js"></script>
    <!-- Flot plugins -->
    <script src="../vendors/flot.orderbars/js/jquery.flot.orderBars.js"></script>
    <script src="../vendors/flot-spline/js/jquery.flot.spline.min.js"></script>
    <script src="../vendors/flot.curvedlines/curvedLines.js"></script>
    <!-- DateJS -->
    <script src="../vendors/DateJS/build/date.js"></script>
    <!-- JQVMap -->
    <script src="../vendors/jqvmap/dist/jquery.vmap.js"></script>
    <script src="../vendors/jqvmap/dist/maps/jquery.vmap.world.js"></script>
    <script src="../vendors/jqvmap/examples/js/jquery.vmap.sampledata.js"></script>
    <!-- bootstrap-daterangepicker -->
    <script src="../vendors/moment/min/moment.min.js"></script>
    <script src="../vendors/bootstrap-daterangepicker/daterangepicker.js"></script>

    <!-- Custom Theme Scripts -->
    <script src="../build/js/custom.min.js"></script>
	
  </body>
</html>
