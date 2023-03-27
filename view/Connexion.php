<?php
    session_start();
    if (isset($_GET['d'])) {
        session_destroy();
        header('Location: connexion.php');
        exit();
    }
    if (isset($_SESSION['login'])) {
        header('Location: accueil.php');
        exit();
    }

    (string) $ch_username = NULL;
    (string) $ch_pwd = NULL;
    (string) $username = NULL;
    (string) $userpwd = NULL;
    (array) $data = NULL;
    (string) $color_mdp = '#2b3d58';
    
    if (isset($_POST['btn_connexion'])){
        // Utilisateur connecté
        $server = "localhost";
        $db = "sport-team-management";
        $login = "root";
        $mdp = "9dfe351b";
        try {
            $linkpdo = new PDO("mysql:host=$server;dbname=$db", $login, $mdp);
        }
        catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }

        $ch_username = hash("sha256",$_POST['user_id']);
        $ch_pwd = hash("sha256",$_POST['user_pwd']);
        $res = $linkpdo->query('SELECT * FROM user_connect');

        // Affichage des entrées du résultat une à une
        while ($data = $res->fetch()) {
            $userpwd = $data['pass_wd'];
            $username = $data['user_name'];
        }
        $res->closeCursor();
        
        if (!(strcmp($ch_username,$username) == 0 && strcmp($ch_pwd,$userpwd) == 0)) {
            $color_mdp = '#d00412';
        } else{
            $color_mdp = '#2b3d58';
            session_start();
            $_SESSION['login'] = $username;
            header('location: accueil.php');
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
</head>
<body style="background-color: #1E1E1E; color: #E0E0E0; font-family: Arial, sans-serif; font-size: 16px;">

    <div style="width: 400px; margin: 80px auto; background-color: #2C2C2C; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);">
        <div style="padding: 20px; text-align: center; background-color: #3F3F3F; border-radius: 5px 5px 0 0;">
            <h2 style="margin: 0; font-size: 32px;">Connexion</h2>
        </div>
        <form style="padding: 20px; text-align: center;" action="" method="post">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Nom d'utilisateur</label>
                <input type="text" placeholder="Entrez votre nom d'utilisateur" name="user_id" style="padding: 10px; border-radius: 5px; border: none; background-color: #3F3F3F; color: #E0E0E0;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px;">Mot de passe</label>
                <input type="password" placeholder="Entrez votre mot de passe" name="user_pwd" style="padding: 10px; border-radius: 5px; border: none; background-color: #3F3F3F; color: #E0E0E0;">
            </div>
            <input type="submit" value="Se connecter" name="btn_connexion" style="background-color: #4CAF50; color: #E0E0E0; border: none; padding: 1px 13px; border-radius: 5px; cursor: pointer;">
        </form>
    </div>

</body>
</html>

<style>
    @import url('https://fonts.googleapis.com/css?family=Nunito+Sans:400,700&display=swap');

    @import url('https://fonts.googleapis.com/css?family=Noto+Sans+TC&display=swap');

body {
  margin: 0;
  padding: 0;
  background: #2b3d58;
  height: 100vh;
  overflow: hidden;
  font-family: 'Noto Sans TC', sans-serif;
}

.center {
  width: 430px;
  margin: 130px auto;
  position: relative;
}

.center .header {
  font-size: 28px;
  font-weight: bold;
  color: white;
  padding: 25px 0 30px 0;
  background: #7c8594;
  border-bottom: 1px solid #7c8594;
  border-radius: 2px 2px 0 0;
  text-align: center; /* Centrer le texte horizontalement */
}

.center form {
  position: absolute;
  background: white;
  width: 100%;
  padding: 50px 10px 0 30px;
  box-sizing: border-box;
  border: 1px solid #eeeee4;
  border-radius: 0 0 2px 2px;
}

form input {
  height: 50px;
  width: 100%;
  max-width: 350px; /* Ajout d'une largeur maximale */
  margin: 0 auto; /* Centrer horizontalement */
  padding: 0 10px;
  border-radius: 3px;
  border: 1px solid silver;
  font-size: 18px;
  outline: none;
}

form i {
  position: absolute;
  font-size: 25px;
  color: grey;
  margin: 15px 0 0 -45px;
}

i.fa-lock {
  margin-top: 35px;
}

form input[type="submit"] {
  margin-top: 40px;
  margin-bottom: 40px;
  width: 130px;
  height: 45px;
  color: white;
  cursor: pointer;
  line-height: 45px;
  border-radius: 45px;
  border-radius: 5px;
  margin-left: 30%;
  margin-right: 30%;
}

form input[type="submit"]:hover {
  background: #0e1d35;
  transition: .5s;
}

form a {
  text-decoration: none;
  font-size: 18px;
  color: #2b3d58;
  padding: 0 0 0 20px;
}

form input[type="submit"] {
    margin-top: 40px;
    margin-bottom: 40px;
    width: 130px;
    height: 45px;
    color: white;
    cursor: pointer;
    line-height: 45px;
    border-radius: 45px;
    border-radius: 5px;
    margin-left: 30%;
    margin-right: 30%;
    background-color: #ed7e0e;
    border: none;
    text-align: center;
}

</style>
</html>
