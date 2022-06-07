<?php

/**
 * Classe Contrôleur des requêtes de l'application admin
 */

class Admin extends Routeur {

  private $entite;
  private $action;
  private $utilisateur_id;

  private $methodes = [
    'utilisateur'   => [
      'd'                 => ['nom' => 'deconnecter', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]],
      'l'                 => ['nom' => 'listerUtilisateurs', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR]],
      'a'                 => ['nom' => 'ajouterUtilisateur', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR]],
      'm'                 => ['nom' => 'modifierUtilisateur', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR]],
      's'                 => ['nom' => 'supprimerUtilisateur', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR]],
      'generer_mdp'       => ['nom' => 'genererMdp', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR]],
    ],
    'film'          => [
      'l'                 => ['nom' => 'listerFilms', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
    ],
    'genre'         => [
      'l'                 => ['nom' => 'listerGenres', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
    ],
    'realisateur'   => [
      'l'                 => ['nom' => 'listerRealisateurs', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
    ],
    'acteur'        => [
      'l'                 => ['nom' => 'listerActeurs', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
    ],
    'pays'          => [
      'l'                 => ['nom' => 'listerPays', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
    ],
    'seance'        => [
      'l'                 => ['nom' => 'listerSeances', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
    ],
    'salle'         => [
      'l'                 => ['nom' => 'listerSalles', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
    ]
  ];

  private $classRetour = "fait";
  private $messageRetourAction = "";

  /**
   * Constructeur qui initialise le contexte du contrôleur  
   */  
  public function __construct() {
    $this->entite    = $_REQUEST['entite']    ?? 'film';
    $this->action    = $_REQUEST['action']    ?? 'l';
    $this->utilisateur_id = $_REQUEST['utilisateur_id'] ?? null;
    $this->oRequetesSQL = new RequetesSQL;
  }

   /**
   * Gérer l'interface d'administration 
   */  
  public function gererAdmin() {
    if (isset($_SESSION['oUtilisateur'])) {
      $this->oUtilisateur = $_SESSION['oUtilisateur'];
      if (isset($this->methodes[$this->entite])) {
        if (isset($this->methodes[$this->entite][$this->action])) {
          $autorise = false;
          foreach( $this->methodes[$this->entite][$this->action]['droits'] as $droit) {
            if($this->oUtilisateur->utilisateur_profil === $droit) {
              $autorise = true;
              break;
            }
          }
          if($autorise) {
            $methode = $this->methodes[$this->entite][$this->action]['nom'];
            $this->$methode();
          }
          else throw new \Exception(self::ERROR_FORBIDDEN);
        } 
        else throw new Exception("L'action $this->action de l'entité $this->entite n'existe pas.");
      } 
      else throw new Exception("L'entité $this->entite n'existe pas.");
    } 
    else $this->connecter();
  }

  /**
   * Connecter un utilisateur
   */
  public function connecter() {
    $messageErreurConnexion = ""; 
    if (count($_POST) !== 0) {
      $utilisateur = $this->oRequetesSQL->connecter($_POST);
      if ($utilisateur !== false) {
        if($utilisateur['utilisateur_profil'] === Utilisateur::PROFIL_ADMINISTRATEUR || $utilisateur['utilisateur_profil'] === Utilisateur::PROFIL_EDITEUR) {
          $_SESSION['oUtilisateur'] = new Utilisateur($utilisateur);
          $this->oUtilisateur = $_SESSION['oUtilisateur'];
          $this->listerFilms();
          exit;  
        }
        else  throw new \Exception(self::ERROR_FORBIDDEN);
      } 
      else {
        $messageErreurConnexion = "Courriel ou mot de passe incorrect.";
      }
    }
    $utilisateur = [];
    
    (new Vue)->generer('vAdminUtilisateurConnecter',
            array(
              'titre'                  => 'Connexion',
              'utilisateur'            => $utilisateur,
              'messageErreurConnexion' => $messageErreurConnexion
            ),
            'gabarit-admin-min');
  }

  /**
   * Déconnecter un utilisateur
   */
  public function deconnecter() {
    unset ($_SESSION['oUtilisateur']);
    $this->connecter();
  }

  /**
   * Lister les utilisateurs
   */
  public function listerUtilisateurs() {
    $utilisateurs = $this->oRequetesSQL->getUtilisateurs();

    (new Vue)->generer('vAdminUtilisateurs',
            array(
              'titre'               => 'Gestion des Utilisateurs',
              'oUtilisateur'        => $this->oUtilisateur,
              'utilisateurs'        => $utilisateurs,
              'classRetour'         => $this->classRetour, 
              'messageRetourAction' => $this->messageRetourAction
            ),
            'gabarit-admin');
  }

  /**
   * Ajouter un utilisateur
   */
  public function ajouterUtilisateur() {
    $utilisateur  = [];
    $erreurs = [];
    if (count($_POST) !== 0) {
      $utilisateur = $_POST;
      $oUtilisateur = new Utilisateur($utilisateur);
      $erreurs = $oUtilisateur->erreurs;
      if (count($erreurs) === 0) { 
        $oUtilisateur->genererMdp();
        $utilisateur_id = $this->oRequetesSQL->ajouterUtilisateur([
          'utilisateur_id'         => $oUtilisateur->utilisateur_id,
          'utilisateur_nom'        => $oUtilisateur->utilisateur_nom,
          'utilisateur_prenom'     => $oUtilisateur->utilisateur_prenom,
          'utilisateur_courriel'   => $oUtilisateur->utilisateur_courriel,
          'utilisateur_mdp'        => $oUtilisateur->utilisateur_mdp,
          'utilisateur_profil'     => $oUtilisateur->utilisateur_profil
        ]);
        if ( $utilisateur_id > 0) { 
          $oGestionCourrielSimuler = new GestionCourrielSimuler;
          $oGestionCourrielSimuler->envoyerMdp($oUtilisateur);
          $this->messageRetourAction = "Ajout de l'utilisateur numéro $utilisateur_id effectué. Un mail avec les identifiants à été envoyé à l'adresse $oUtilisateur->utilisateur_courriel.";
        } else {
          $this->classRetour = "erreur";
          $this->messageRetourAction = "Ajout de l'utilisateur non effectué.";
        }
        $this->listerUtilisateurs(); 
        exit;
      }
    }
    
    (new Vue)->generer('vAdminUtilisateurAjouter',
            array(
              'titre'   => 'Ajouter un utilisateur',
              'utilisateur'  => $utilisateur,
              'erreurs' => $erreurs
            ),
            'gabarit-admin');
  }

  /**
   * Modifier un utilisateur 
   */
  public function modifierUtilisateur() {
    if (count($_POST) !== 0) {
      $utilisateur = $_POST;
      $oUtilisateur = new Utilisateur($utilisateur);
      $erreurs = $oUtilisateur->erreurs;
      if (count($erreurs) === 0) {
        if($this->oRequetesSQL->modifierUtilisateur([
          'utilisateur_id'          => $this->utilisateur_id,
          'utilisateur_nom'         => $oUtilisateur->utilisateur_nom,
          'utilisateur_prenom'      => $oUtilisateur->utilisateur_prenom,
          'utilisateur_courriel'    => $oUtilisateur->utilisateur_courriel,
          'utilisateur_profil'      => $oUtilisateur->utilisateur_profil,
        ])) {
          $this->messageRetourAction = "Modification de l'utilisateur numéro $this->utilisateur_id effectuée.";
        } else {
          $this->classRetour = "erreur";
          $this->messageRetourAction = "modification de l'utilisateur numéro $this->utilisateur_id non effectuée.";
        }
        $this->listerUtilisateurs();
        exit;
      }
    } else {
      $utilisateur  = $this->oRequetesSQL->getUtilisateur($this->utilisateur_id);
      $erreurs = [];
    }
    
    (new Vue)->generer('vAdminUtilisateurModifier',
            array(
              'titre'         => "Modifier l'utilisateur numéro $this->utilisateur_id",
              'utilisateur'   => $utilisateur,
              'erreurs'       => $erreurs
            ),
            'gabarit-admin');
  }
  
  /**
   * Supprimer un utilisateur
   */
  public function supprimerUtilisateur() {
    if ($this->oRequetesSQL->supprimerUtilisateur($this->utilisateur_id)) {
      $this->messageRetourAction = "Suppression de l'utilisateur numéro $this->utilisateur_id effectuée.";
    } else {
      $this->classRetour = "erreur";
      $this->messageRetourAction = "Suppression de l'utilisateur numéro $this->utilisateur_id non effectuée.";
    }
    $this->listerUtilisateurs();
  }

  /**
   * Générer un nouveau mot de passe 
   */
  public function genererMdp() {
    $utilisateur = $this->oRequetesSQL->getUtilisateur($this->utilisateur_id);
    if($utilisateur) {
      $oUtilisateur = new Utilisateur($utilisateur);
      $oUtilisateur->genererMdp();
      if($this->oRequetesSQL->modifierUtilisateur([
        'utilisateur_id'          => $this->utilisateur_id,
        'utilisateur_nom'         => $oUtilisateur->utilisateur_nom,
        'utilisateur_prenom'      => $oUtilisateur->utilisateur_prenom,
        'utilisateur_courriel'    => $oUtilisateur->utilisateur_courriel,
        'utilisateur_mdp'         => $oUtilisateur->utilisateur_mdp,
        'utilisateur_profil'      => $oUtilisateur->utilisateur_profil,
      ])) {
        $oGestionCourrielSimuler = new GestionCourrielSimuler;
        $oGestionCourrielSimuler->envoyerMdp($oUtilisateur);
        $this->messageRetourAction = "Génération d'un nouveau mot de passe effectuée pour l'utilisateur $this->utilisateur_id. Nouveau mot de passe envoyé à l'adresse $oUtilisateur->utilisateur_courriel";
      }
      else {
        $this->classRetour = "erreur";
        $this->messageRetourAction = "Génération du nouveau mot de passe échouée.";
      }
    }
    else {
      $this->classRetour = "erreur";
      $this->messageRetourAction = "Utilisateur inexistant dans la base de données.";
    }
    $this->listerUtilisateurs();

  }

  // Fonctions restantes à développer (affichent une vue générique "en développement")
  public function listerFilms() {

    (new Vue)->generer('vAdminEnDeveloppement',
                        array(
                          'titre' => 'Gestion des Films',
                          'oUtilisateur'        => $this->oUtilisateur,
                        ),
                        'gabarit-admin');
  }

  public function listerGenres() {

    (new Vue)->generer('vAdminEnDeveloppement',
                        array(
                          'titre' => 'Gestion des Genres',
                          'oUtilisateur'        => $this->oUtilisateur,
                        ),
                        'gabarit-admin');
  }
 
  public function listerRealisateurs() {
 
    (new Vue)->generer('vAdminEnDeveloppement',
                        array(
                          'titre' => 'Gestion des Réalisateurs',
                          'oUtilisateur'        => $this->oUtilisateur,
                        ),
                        'gabarit-admin');
  }
  
  public function listerActeurs() {
  
    (new Vue)->generer('vAdminEnDeveloppement',
                        array(
                          'titre' => 'Gestion des Acteurs',
                          'oUtilisateur'        => $this->oUtilisateur,
                        ),
                        'gabarit-admin');
  }

  public function listerPays() {

    (new Vue)->generer('vAdminEnDeveloppement',
                        array(
                          'titre' => 'Gestion des Pays',
                          'oUtilisateur'        => $this->oUtilisateur,
                        ),
                        'gabarit-admin');
  }

  public function listerSeances() {
 
    (new Vue)->generer('vAdminEnDeveloppement',
                        array(
                          'titre' => 'Gestion des Séances',
                          'oUtilisateur'        => $this->oUtilisateur,
                        ),
                        'gabarit-admin');
  }

  public function listerSalles() {
  
    (new Vue)->generer('vAdminEnDeveloppement',
                        array(
                          'titre' => 'Gestion des Salles',
                          'oUtilisateur'        => $this->oUtilisateur,
                        ),
                        'gabarit-admin');
  }
}