<?php
namespace App\Controllers;

use CodeIgniter\Controller;
Use App\Models\Annonce_Model;
use App\Models\Uti_Model;
use CodeIgniter\Session\Session;

class Annonce extends Controller
{

    //Fonction qui permet de charger les annonces par block de 15
    public function seemore(){
        $session = \Config\Services::session();
        $selectedbutton = $this->request->getPost('button');
        $nbr = $this->request->getPost('more');
        if (empty($nbr)) {
            $nbr = 0;
            $session->set('page', $nbr);
        }
        if ($selectedbutton === 'Moins' && $nbr > 15)
        $session->set('page', $nbr-15);
        elseif ($selectedbutton === 'Plus')
            $session->set('page', $nbr+15);

       return redirect()->to('Annonces');

    }
    //Ajouter une annonce
    public function ajouterAnnonce()
    {
        $session = \Config\Services::session();
        $model = new Annonce_Model();
        $modelUti = new Uti_Model();
        $mail = $session->get('mail');

        //Récupération des données du formulaire
        $titre = $this->request->getPost('title');
        $coutlocation = $this->request->getPost('coutlocation');
        $coutcharges = $this->request->getPost('coutcharges');
        $type = $this->request->getPost('typeselect');
        $superficie = $this->request->getPost('superficie');
        $typechauffage = $this->request->getPost('typechauffageselect');

        //Traitement type de chauffage
        if($typechauffage === "Collectif") $modeenergie = '4'; //Si chauffage collectif, énergie : '4' càd non renseigné
        else $modeenergie = $this->request->getPost('modeenergieselect');

        $adresse = $this->request->getPost('adresse');
        $ville = $this->request->getPost('ville');
        $region = $this->request->getPost('region');
        $codepostal = $this->request->getPost('codepostal');
        $description = $this->request->getPost('description');
        $selectedbutton = $this->request->getPost('button');
        $dossier = ROOTPATH."public/images/annonces/"; //Fichier de destination pour les images
        $verifEtat = $modelUti->verifEtat($session->get('mail')); //Requête qui renvoie l'état de l'utilisateur s'il est bloqué

        //Si l'utilisateur n'est pas bloqué
        if(empty($verifEtat)) {
            //Si l'utilisateur choisit de publier directement l'annonce
            if ($selectedbutton === "publiée") {
                $insert = $model->insertAnnonce($mail, $titre, $coutlocation, $coutcharges, $type, $superficie, $typechauffage, $modeenergie, $adresse, $ville, $region, $codepostal, $description, "publiée");
            } else { //Sinon l'annonce est mise en brouillon
                $insert = $model->insertAnnonce($mail, $titre, $coutlocation, $coutcharges, $type, $superficie, $typechauffage, $modeenergie, $adresse, $ville, $region, $codepostal, $description, "brouillon");
            }

            //Upload image sur le serveur
            for ($i = 1; $i <= 5; $i++) {
                ${"fichier" . $i} = basename($_FILES["image" . "$i"]["name"]);
                ${"image" . $i} = 'image' . $i;
                $idAnnonce = $model->getLastAnnonce($mail);

                if (!empty(${"fichier" . $i})) $this->uploadImage(${"image" . $i}, $dossier, $idAnnonce, $i);
            }

            //Si la requête a été exécutée
            if ($this->request->getMethod() === 'post' && $insert) {
                $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-succes" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>SUCCÈS </strong><i class="fas fa-check"></i> L\'annonce a été ajoutée !</div>');
                return redirect()->to('Mes-annonces');
            } else {
                $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> L\'ajout de l\'annonce a échoué.</div>');
            }
        }
        else{
            $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> Vous êtes bloqué.</div>');
            return redirect()->to('Mes-annonces');
        }
    }

    //Méthode pour enregistrer les images de l'annonce
    public function uploadImage($image, $dossier, $idAnnonce, $i){
        $model = new Annonce_Model();
        $temp = explode(".", $_FILES[$image]["name"]);
        $newfilename = $i . '-' . current($temp) . '-' . current($idAnnonce) . '.' . end($temp);

        //Si l'image a bien été enregistrée
        if(move_uploaded_file($_FILES[$image]['tmp_name'], $dossier . $newfilename))
            $model->insertImageAnnonce($newfilename,current($idAnnonce));
        else
            echo 'Echec de l\'upload !';
    }

    //Méthode intervenant avant le processus de modification d'une annonce
    public function avantModifierAnnonce ($page = 'Mes-annonces'){
        $session = \Config\Services::session();
        $model = new Annonce_Model();
        $modelUti = new Uti_Model();

        $verifUser =  $model->verifAnnonce($session->get('mail'),$page);
        $etat = mysqli_fetch_array($model->getEtatAnnonce($page)); //Renvoie l'état de l'annonce

        //Empêche la modification si l'annonce est bloquée
        if ($etat['A_etat'] == "bloquée") {
            $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> Modification impossible car l\'annonce est bloquée. </div>');
            return redirect()->to('/Mes-annonces');
        }
        else if(!empty($verifUser)) {
            if (!empty($session->get('mail'))){
                $data = ['mail' => $session->get('mail'),
                    'pseudo' => $session->get('pseudo'),
                    'nom' => $session->get('nom'),
                    'prenom' => $session->get('prenom')
                ];
            }
            $data['title'] = ucfirst($page);
            echo view('templates/header.tpl',$data);

            if (!empty($session->get('mail'))) echo view('templates/navbar-connected.tpl',$data);
            else echo view('templates/navbar.tpl',$data);

            if (!empty($session->getFlashdata('warning'))) echo $session->getFlashdata('warning');

            $session->setFlashdata("id",$page);

            echo view('pages/Modifier-une-annonce.php',$data);

            if (!empty($session->get('mail'))) {
                if ($modelUti->getIsAdmin($_SESSION['mail'])['U_isAdmin'] == 1) echo view('templates/admin.tpl', $data);
            }

            echo view('templates/footer.tpl',$data);
        }else{
            $session->setFlashdata('warning','<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> Vous n\'êtes pas le propriétaire de cette annonce. </div>');
            return redirect()->to('/Mes-annonces');
        }

    }

    //Méthode pour modifier une annonce
    public function modifierAnnonce()
    {
        $session = \Config\Services::session();
        $annonceModel = new Annonce_Model();

        $id = $session->getFlashdata('id'); //Récupère l'id de l'annonce

        //Si on clique sur modifier
        if ($this->request->getPost('button')) {
            //Récupère les données du formulaire
            $titre = $this->request->getPost('title');
            $coutlocation = $this->request->getPost('coutlocation');
            $coutcharges = $this->request->getPost('coutcharges');
            $type = $this->request->getPost('typeselect');
            $superficie = $this->request->getPost('superficie');
            $typeChauffage = $this->request->getPost('typechauffageselect');
            $modeEnergie = $this->request->getPost('modeenergieselect');
            $adresse = $this->request->getPost('adresse');
            $ville = $this->request->getPost('ville');
            $codepostal = $this->request->getPost('codepostal');
            $region = $this->request->getPost('region');
            $description = $this->request->getPost('description');
            $etat = $this->request->getPost('etat');

            //Exécution de la requête
            $updateAnnonce = $annonceModel->updateAnnonce($id, $titre, $coutlocation, $coutcharges, $type, $superficie, $typeChauffage, $modeEnergie, $adresse, $ville, $region, $codepostal, $description, $etat);

            //Si la requête a bien été exécutée
            if ($this->request->getMethod() === 'post' && $updateAnnonce) {
                $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-succes" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>SUCCÈS </strong><i class="fas fa-check"></i> Les modifications ont bien été prises en compte !</div>');
                return redirect()->to('/Mes-annonces');
            } else {
                $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> La modification des informations a échoué.</div>');
                return redirect()->to('/Mes-annonces');
            }

        }

        //Si on clique sur supprimer les photos
        if($this->request->getPost('buttondeletephoto')){
            $idphoto = $this->request->getPost('deletePhoto[]');
            $nombrePhoto = mysqli_fetch_array($annonceModel->getHowManyPhotos($id));

            if (!empty($idphoto)) {
                if ($nombrePhoto['nbrphoto'] == sizeof($idphoto)) {
                    $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> Vous ne pouvez pas supprimer toutes vos photos.</div>');
                    return redirect()->to('');
                } else {
                    if ($nombrePhoto['nbrphoto'] == 1) {
                        $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> Vous ne pouvez pas supprimer l\'unique photo de l\'annnonce.</div>');
                        return redirect()->to('');
                    }
                    for ($i = 0; $i < sizeof($idphoto); $i++) {
                        $annonceModel->deletePhoto($idphoto[$i]);
                    }
                    $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-succes" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>SUCCÈS </strong><i class="fas fa-check"></i> La suppression a bien été prise en compte.</div>');
                    return redirect()->to('');
                }
            }else{
                $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> Vous n\'avez sélectionné aucune photo.</div>');
                return redirect()->to('');}
        }

        //Si on clique sur Ajouter une photo
        if($this->request->getPost('buttonaddphoto')) {
            $nombrePhoto = mysqli_fetch_array($annonceModel->getHowManyPhotos($id));
            //$posPhoto = $annonceModel->getPosPhotos($id);
            if ($nombrePhoto['nbrphoto'] >= 5){
                $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> Vous ne pouvez pas ajouter plus de photos à cette annonce (maximum 5).</div>');
                return redirect()->to('');
            }else{
                $fichier = basename($_FILES["image"]["name"]);
                $image = 'image';

                $rand = rand(1,50);
                $dossier = ROOTPATH."public/images/annonces/";

                if (!empty($fichier)) {
                    $temp = explode(".", $_FILES[$image]["name"]);
                    $newfilename = $rand . '-' . current($temp) . '-' . $id . '.' . end($temp);

                    if(move_uploaded_file($_FILES[$image]['tmp_name'], $dossier . $newfilename)) //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
                    {
                        $annonceModel->insertImageAnnonce($newfilename,$id);
                        $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-succes" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>SUCCÈS </strong><i class="fas fa-check"></i> L\'image a bien été ajoutée.</div>');
                    }
                    else //Sinon (la fonction renvoie FALSE).
                    {
                        $session->setFlashdata('warning','<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> L\'ajout de l\'image a échoué.</div>');
                        return redirect()->to('');
                    }
                }else{
                    $session->setFlashdata('warning','<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> L\'ajout de l\'image a échoué.</div>');
                    return redirect()->to('');
                }
            }
        }
        return redirect()->to('');
    }

    //Méthode pour supprimer une annonce
    public function supprimerAnnonce($id){
        $session = \Config\Services::session();
        $model = new Annonce_Model();
        $utimodel = new Uti_Model();

        $verifUser =  $model->verifAnnonce($session->get('mail'),$id);
        if (!empty($verifUser)) {

            $utimodel->deletePhoto($id);
            $model->deleteOneAnnonce($session->get('mail'), $id);
            $session->setFlashdata('warning', '<div id="flashdata" class="alerte alerte-succes" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>SUCCÈS </strong><i class="fas fa-check"></i> L\'annonce a bien été supprimée !</div>');
            //TODO delete message
            return redirect()->to('/Mes-annonces');
        }else{
            $session->setFlashdata('warning','<div id="flashdata" class="alerte alerte-echec" onclick="document.getElementById(\'flashdata\').style.display=\'none\';"><strong>ERREUR </strong><i class="fas fa-exclamation-triangle"></i> Vous n\'êtes pas le propriétaire de cette annonce. </div>');
            return redirect()->to('/Mes-annonces');
        }
    }
    
    //Formulaire de premier contact sur une annonce
    public function contact(){
        $session = \Config\Services::session();

        $modelUti = new Uti_Model();
        $modelAnnonce = new Annonce_Model();
        $texte = $this->request->getPost('message');

        $page = $_SERVER['REQUEST_URI'];
        $id  = (int)filter_var($page, FILTER_SANITIZE_NUMBER_INT);

        $mailReceiver = $modelAnnonce->getMailAnnonce(abs($id));
        $mailSender = $session->get('mail');

        $modelUti->sendMessage($mailReceiver['A_U_mail'],$mailSender,$texte,abs($id));

        return redirect()->to('Mes-messages');
    }
}