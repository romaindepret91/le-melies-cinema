<?php

/**
 * Classe Contrôleur des requêtes de l'interface frontend
 * 
 */

class Frontend extends Routeur {

  private $film_id;
  
  /**
   * Constructeur qui initialise des propriétés à partir du query string
   * et la propriété oRequetesSQL déclarée dans la classe Routeur
   * 
   */
  public function __construct() {
    $this->film_id = $_GET['film_id'] ?? null; 
    $this->oRequetesSQL = new RequetesSQL;
  }


  /**
   * Lister les films à l'affiche
   * 
   */  
  public function listerAlaffiche() {
    $films = $this->oRequetesSQL->getFilms('enSalle');
    (new Vue)->generer("vListeFilms",
            array(
              'titre'  => "À l'affiche",
              'films' => $films
            ),
            "gabarit-frontend");
  }

  /**
   * Lister les films à l'affiche
   * 
   */  
  public function listerProchainement() {
    $films = $this->oRequetesSQL->getFilms('prochainement');
    (new Vue)->generer("vListeFilms",
            array(
              'titre'  => "Prochainement",
              'films' => $films
            ),
            "gabarit-frontend");
  }

  /**
   * Voir les informations d'un film
   * 
   */  
  public function voirFilm() {
    $film = false;
    if (!is_null($this->film_id)) {
      $film = $this->oRequetesSQL->getFilm($this->film_id);
      $realisateurs = $this->oRequetesSQL->getRealisateursFilm($this->film_id);
      $pays         = $this->oRequetesSQL->getPaysFilm($this->film_id);
      $acteurs      = $this->oRequetesSQL->getActeursFilm($this->film_id);
      $seances      = $this->oRequetesSQL->getSeancesFilm($this->film_id);
      // echo "<pre>".print_r($seances, true)."</pre>"; exit;
    }
    if (!$film) throw new Exception("Film inexistant.");

    (new Vue)->generer("vFilm",
            array(
              'titre'        => $film['film_titre'],
              'film'         => $film,
              'realisateurs' => $realisateurs,
              'pays'         => $pays,
              'acteurs'      => $acteurs,
              'seances'      => $seances
            ),
            "gabarit-frontend");
  }
}