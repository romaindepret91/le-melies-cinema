<?php

/**
 * Classe de l'entité Seance
 *
 */
class Seance {
  private $seance_film_id;
  private $seance_salle_numero;
  private $seance_date;
  private $seance_heure;
  
  private $erreurs = array();

  /**
   * Constructeur de la classe 
   * @param array $proprietes, tableau associatif des propriétés 
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
   * Mutateur de la propriété seance_film_id 
   * @param int $seance_film_id
   * @return $this
   */    
  public function setSeance_film_id($seance_film_id) {
    unset($this->erreurs['seance_film_id']);
    $regExp = '/^[1-9]\d*$/';
    if (!preg_match($regExp, $seance_film_id)) {
      $this->erreurs['seance_film_id'] = 'Numéro de film incorrect.';
    }
    $this->film_id = $seance_film_id;
    return $this;
  }

  /**
   * Mutateur de la propriété seance_salle_numero 
   * @param int $seance_salle_numero
   * @return $this
   */    
  public function setSeance_salle_numero($seance_salle_numero) {
    unset($this->erreurs['seance_salle_numero']);
    $regExp = '/^[1-9]\d*$/';
    if (!preg_match($regExp, $seance_salle_numero)) {
      $this->erreurs['seance_salle_numero'] = 'Numéro de salle incorrect.';
    }
    $this->film_id = $seance_salle_numero;
    return $this;
  }       

  /**
   * Mutateur de la propriété seance_date 
   * @param string $seance_date
   * @return $this
   */    
  public function setSeance_date($seance_date) {
    unset($this->erreurs['seance_date']);
    $seance_date = trim($seance_date);
    $this->seance_date = $seance_date;
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $seance_date)) {
      $t = explode('-', $seance_date);
      if (count($t) === 3 && checkdate($t[1], $t[2], $t[0])) {
        return $this;
      }
    }
    $this->erreurs['seance_date'] = 'Date invalide.';
    return $this;
  }

  /**
   * Mutateur de la propriété seance_heure 
   * @param string seance_heure
   * @return $this
   */    
  public function setSeance_heure($seance_heure) {
    unset($this->erreurs['seance_heure']);
    $seance_heure = trim($seance_heure);
    $this->seance_heure = $seance_heure;
    if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $seance_heure)) {
      $t = explode(':', $seance_heure);
      if (count($t) === 3 && $t[0] >= 0 && $t[0] <24 && $t[1] >= 0 && $t[1] < 60 && $t[2] >= 0 && $t[2] < 60) {
          return $this;
      }
    }
    $this->erreurs['seance_heure'] = 'Heure invalide.';
    return $this;
  }

  /**
   * Jour de la semaine de la date en français
   * @return string
   */    
  public static function getJourSemaine($seance_date) {
    $joursSemaine = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    $date = new DateTime($seance_date);
    return $joursSemaine[$date->format('w')];
  }  
}