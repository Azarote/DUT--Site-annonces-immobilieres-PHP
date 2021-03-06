<?php
    $session = \Config\Services::session();

    use App\Models\Uti_Model;
    $modelUti = new Uti_Model();

    if(!isset($_SESSION['mail'])){
        header('Location:Connexion');
        exit;
    }elseif ( $modelUti->getIsAdmin($_SESSION['mail'])['U_isAdmin'] != 1){
        header('Location:/');
        $session->setFlashdata('warning','<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> Vous n\'avez pas la permission.</div>');
        exit;
    }
    else {
        $mail = $session->getFlashData("mailUti");
        $utilisateur = $modelUti->getUserInfo($mail);

        service('SmartyEngine')->assign('utilisateur', $utilisateur);
        echo service('SmartyEngine')->view('../pages/Modifier-utilisateur.tpl');
    }