<?php

/**
 * Classe de l'entité Utilisateur
 *
 */
class Utilisateur
{
  private $utilisateur_id;
  private $utilisateur_nom;
  private $utilisateur_prenom;
  private $utilisateur_courriel;
  private $utilisateur_mdp;
  private $utilisateur_profil;

  const PROFIL_ADMINISTRATEUR = "administrateur";
  const PROFIL_EDITEUR        = "editeur";
  const PROFIL_UTILISATEUR    = "utilisateur";

  private $erreurs = array();

  /**
   * Constructeur de la classe
   * @param array $proprietes, tableau associatif des propriétés 
   *
   */ 
  public function __construct($proprietes = []) {
    $t = array_keys($proprietes);
    foreach ($t as $nom_propriete) {
      $this->__set($nom_propriete, $proprietes[$nom_propriete]);
    } 
  }

  /**
   * Accesseur magique d'une propriété de l'objet
   * @param string $prop, nom de la propriété
   * @return property value
   */     
  public function __get($prop) {
    return $this->$prop;
  }

  public function getUtilisateur_id()       { return $this->utilisateur_id; }
  public function getUtilisateur_nom()      { return $this->utilisateur_nom; }
  public function getUtilisateur_prenom()   { return $this->utilisateur_prenom; }
  public function getUtilisateur_courriel() { return $this->utilisateur_courriel; }
  public function getUtilisateur_mdp()      { return $this->utilisateur_mdp; }
  public function getUtilisateur_profil()   { return $this->utilisateur_profil; }
  public function getErreurs()              { return $this->erreurs; }
  
  /**
   * Mutateur magique qui exécute le mutateur de la propriété en paramètre 
   * @param string $prop, nom de la propriété
   * @param $val, contenu de la propriété à mettre à jour    
   */   
  public function __set($prop, $val) {
    $setProperty = 'set'.ucfirst($prop);
    $this->$setProperty($val);
  }

  /**
   * Mutateur de la propriété utilisateur_id 
   * @param int $utilisateur_id
   * @return $this
   */    
  public function setUtilisateur_id($utilisateur_id) {
    unset($this->erreurs['utilisateur_id']);
    $regExp = '/^[1-9]\d*$/';
    if (!preg_match($regExp, $utilisateur_id)) {
      $this->erreurs['utilisateur_id'] = "Numéro d'utilisateur incorrect.";
    }
    $this->utilisateur_id = $utilisateur_id;
    return $this;
  }    

  /**
   * Mutateur de la propriété utilisateur_nom 
   * @param string $utilisateur_nom
   * @return $this
   */    
  public function setUtilisateur_nom($utilisateur_nom) {
    unset($this->erreurs['utilisateur_nom']);
    $utilisateur_nom = trim($utilisateur_nom);
    $regExp = '/^\p{L}{2,}( \p{L}{2,})*$/ui';
    if (!preg_match($regExp, $utilisateur_nom)) {
      $this->erreurs['utilisateur_nom'] = "Au moins 2 caractères alphabétiques pour chaque mot.";
    }
    $this->utilisateur_nom = $utilisateur_nom;
    return $this;
  }

  /**
   * Mutateur de la propriété utilisateur_prenom 
   * @param string $utilisateur_prenom
   * @return $this
   */    
  public function setUtilisateur_prenom($utilisateur_prenom) {
    unset($this->erreurs['utilisateur_prenom']);
    $utilisateur_prenom = trim($utilisateur_prenom);
    $regExp = '/^\p{L}{2,}( \p{L}{2,})*$/ui';
    if (!preg_match($regExp, $utilisateur_prenom)) {
      $this->erreurs['utilisateur_prenom'] = "Au moins 2 caractères alphabétiques pour chaque mot.";
    }
    $this->utilisateur_prenom = $utilisateur_prenom;
    return $this;
  }

  /**
   * Mutateur de la propriété utilisateur_courriel
   * @param string $utilisateur_courriel
   * @return $this
   */    
  public function setUtilisateur_courriel($utilisateur_courriel) {
    unset($this->erreurs['utilisateur_courriel']);
    $utilisateur_courriel = trim(strtolower($utilisateur_courriel));
    if (!filter_var($utilisateur_courriel, FILTER_VALIDATE_EMAIL)) {
      $this->erreurs['utilisateur_courriel'] = "Format de courriel invalide.";
    }
    $this->utilisateur_courriel = $utilisateur_courriel;
    return $this;
  }

  /**
   * Mutateur de la propriété utilisateur_profil
   * @param string $utilisateur_profil
   * @return $this
   */    
  public function setUtilisateur_profil($utilisateur_profil) {
    unset($this->erreurs['utilisateur_profil']);
    $utilisateur_profil = trim(strtolower($utilisateur_profil));
    if ($utilisateur_profil !== self::PROFIL_ADMINISTRATEUR && $utilisateur_profil !== self::PROFIL_UTILISATEUR && $utilisateur_profil !== self::PROFIL_EDITEUR) {
      $this->erreurs['utilisateur_profil'] = "Type de profil invalide.";
    }
    $this->utilisateur_profil = $utilisateur_profil; 
    return $this;
  }

   /**
   * Mutateur de la propriété utilisateur_mdp
   * @param string $utilisateur_mdp
   * @return $this
   */    
  public function setUtilisateur_mdp($utilisateur_mdp) {
    unset($this->erreurs['utilisateur_mdp']);
    $utilisateur_mdp = trim(strtolower($utilisateur_mdp));
    $regExp = '/^[A-Za-z0-9&#@+=$%!]{12,}$/ui';
    if (!preg_match($regExp, $utilisateur_mdp)) {
      $this->erreurs['utilisateur_mdp'] = "Le mot de passe doit contenir au moins 12 caractères (lettres, chiffres, et les caractères &#@+=$%! sont acceptés.";
    }
    $this->utilisateur_mdp = $utilisateur_mdp; 
    return $this;
  }

  /**
   * Génération d'un mot de passe aléatoire dans la propriété utilisateur_mdp
   * @return $this
   */    
  public function genererMdp() {
    $mdp = random_bytes(12);
    $mdp = bin2hex($mdp);
    $this->utilisateur_mdp = $mdp;
    return $this;
  }
}