<?php

/**
 * Classe des requêtes SQL
 *
 */
class RequetesSQL extends RequetesPDO {

  /**
   * Récupération des films à l'affiche ou prochainement
   * @param  string $critere
   * @return array tableau des lignes produites par la select   
   */ 
  public function getFilms($critere = 'enSalle') {
    // $oAujourdhui = ENV === "DEV" ? new mocks\DateTime() : new DateTime(); // utilisation d'une classe mock DateTime
    $oAujourdhui = ENV === "DEV" ? new DateTime(MOCK_NOW) : new DateTime(); // utilisation directe de la constante MOCK_NOW
    $aujourdhui  = $oAujourdhui->format('Y-m-d');
    $dernierJour = $oAujourdhui->modify('+6 day')->format('Y-m-d');
    $this->sql = "
      SELECT film_id, film_titre, film_duree, film_annee_sortie, film_resume,
             film_affiche, film_bande_annonce, film_statut, genre_nom
      FROM film
      INNER JOIN genre ON genre_id = film_genre_id
      WHERE film_statut = ".Film::STATUT_VISIBLE;

      switch($critere) {
        case 'enSalle':
          $this->sql .= " AND film_id IN (SELECT DISTINCT seance_film_id FROM seance
                                         WHERE seance_date >='$aujourdhui' AND seance_date <= '$dernierJour')";
          break;
        case 'prochainement':
          $this->sql .= " AND film_id NOT IN (SELECT DISTINCT seance_film_id FROM seance
                                             WHERE seance_date <= '$dernierJour')";
          break;
      }      
    return $this->getLignes();
  }

  /**
   * Récupération d'un film
   * @param int $film_id, clé du film 
   * @return array|false tableau associatif de la ligne produite par la select, false si aucune ligne  
   */ 
  public function getFilm($film_id) {
    $this->sql = "
      SELECT film_id, film_titre, film_duree, film_annee_sortie, film_resume,
             film_affiche, film_bande_annonce, film_statut, genre_nom
      FROM film
      INNER JOIN genre ON genre_id = film_genre_id
      WHERE film_id = :film_id";

    return $this->getLignes(['film_id' => $film_id], RequetesPDO::UNE_SEULE_LIGNE);
  }

  /**
   * Récupération des réalisateurs d'un film
   * @param int $film_id, clé du film
   * @return array tableau des lignes produites par la select 
   */ 
  public function getRealisateursFilm($film_id) {
    $this->sql = "
      SELECT realisateur_nom, realisateur_prenom
      FROM realisateur
      INNER JOIN film_realisateur ON f_r_realisateur_id = realisateur_id
      WHERE f_r_film_id = :film_id";

    return $this->getLignes(['film_id' => $film_id]);
  }

  /**
   * Récupération des pays d'un film
   * @param int $film_id, clé du film
   * @return array tableau des lignes produites par la select 
   */ 
  public function getPaysFilm($film_id) {
    $this->sql = "
      SELECT pays_nom
      FROM pays
      INNER JOIN film_pays ON f_p_pays_id = pays_id
      WHERE f_p_film_id = :film_id";

    return $this->getLignes(['film_id' => $film_id]);
  }

  /**
   * Récupération des acteurs d'un film
   * @param int $film_id, clé du film
   * @return array tableau des lignes produites par la select 
   */ 
  public function getActeursFilm($film_id) {
    $this->sql = "
      SELECT acteur_nom, acteur_prenom
      FROM acteur
      INNER JOIN film_acteur ON f_a_acteur_id = acteur_id
      WHERE f_a_film_id = :film_id
      ORDER BY f_a_priorite ASC";

    return $this->getLignes(['film_id' => $film_id]);
  }

  /**
   * Récupération des séances d'un film
   * @param int $film_id, clé du film
   * @return array tableau des lignes produites par la select 
   */ 
  public function getSeancesFilm($film_id) {
    // $oAujourdhui = ENV === "DEV" ? new mocks\DateTime() : new DateTime(); // utilisation d'une classe mock DateTime
    $oAujourdhui = ENV === "DEV" ? new DateTime(MOCK_NOW) : new DateTime(); // utilisation directe de la constante MOCK_NOW
    $aujourdhui  = $oAujourdhui->format('Y-m-d');
    $dernierJour = $oAujourdhui->modify('+6 day')->format('Y-m-d');
    $this->sql = "
      SELECT seance_date, seance_heure
      FROM seance
      INNER JOIN film ON seance_film_id = film_id
      WHERE seance_film_id = :film_id AND seance_date >='$aujourdhui' AND seance_date <= '$dernierJour'";

    return $this->getLignes(['film_id' => $film_id]);
  }

  /* GESTION DES UTILISATEURS 
     ======================== */

  /**
   * Connecter un utilisateur
   * @param array $champs, tableau avec les champs utilisateur_courriel et utilisateur_mdp  
   * @return array|false ligne de la table, false sinon 
   */ 
  public function connecter($champs) {
    $this->sql = "
      SELECT utilisateur_id, utilisateur_nom, utilisateur_prenom, utilisateur_courriel, utilisateur_profil
      FROM utilisateur
      WHERE utilisateur_courriel = :utilisateur_courriel AND utilisateur_mdp = SHA2(:utilisateur_mdp, 512)";

    return $this->getLignes($champs, RequetesPDO::UNE_SEULE_LIGNE);
  } 

    /**
   * Récupération des utilisateurs
   * @return array tableau des lignes produites par la select  
   */ 
  public function getUtilisateurs() {
    $this->sql = "
    SELECT * FROM `utilisateur`";

    return $this->getLignes();
  }

    /**
   * Récupération d'un utilisateur
   * @param int $utilisateur_id, clé du utilisateur 
   * @return array|false tableau associatif de la ligne produite par la select, false si aucune ligne  
   */ 
  public function getUtilisateur($utilisateur_id) {
    $this->sql = "
      SELECT *
      FROM utilisateur
      WHERE utilisateur_id = :utilisateur_id";

    return $this->getLignes(['utilisateur_id' => $utilisateur_id], RequetesPDO::UNE_SEULE_LIGNE);
  }

   /**
   * Ajout d'un utilisateur
   * @param array tableau des champs entrés pour cet utilisateur
   * @return boolean|string retourne l'id de l'utilisateur inséré si l'ajout a eu lieu
   */ 
  public function ajouterUtilisateur($champs) {
    $this->sql = '
      INSERT INTO utilisateur 
      SET utilisateur_id = :utilisateur_id, utilisateur_nom = :utilisateur_nom, utilisateur_prenom = :utilisateur_prenom, utilisateur_courriel = :utilisateur_courriel, utilisateur_mdp = SHA2(:utilisateur_mdp, 512), utilisateur_profil = :utilisateur_profil';
    return $this->CUDLigne($champs); 
  }

   /**
   * Suppression d'un utilisateur
   * @param int id de l'utilisateur à supprimer
   * @return boolean true si suppression effectuée, false sinon
   */ 
  public function supprimerUtilisateur($utilisateur_id) {
    $this->sql = '
    DELETE FROM utilisateur WHERE utilisateur_id = :utilisateur_id';
  return $this->CUDLigne(['utilisateur_id' => $utilisateur_id]); 
  }

  /**
   * Modifier un utilisateur
   * @param array $champs tableau avec les champs à modifier et la clé utilisateur_id
   * @return boolean true si modification effectuée, false sinon
   */ 
  public function modifierUtilisateur($champs) {
    if($champs['utilisateur_mdp']) {
      $this->sql = '
      UPDATE utilisateur 
      SET utilisateur_nom = :utilisateur_nom, utilisateur_prenom = :utilisateur_prenom, utilisateur_courriel = :utilisateur_courriel, utilisateur_mdp = SHA2(:utilisateur_mdp, 512), utilisateur_profil = :utilisateur_profil
      WHERE utilisateur_id = :utilisateur_id';
    }
    else {
      $this->sql = '
      UPDATE utilisateur 
      SET utilisateur_nom = :utilisateur_nom, utilisateur_prenom = :utilisateur_prenom, utilisateur_courriel = :utilisateur_courriel, utilisateur_profil = :utilisateur_profil
      WHERE utilisateur_id = :utilisateur_id';
    }
    return $this->CUDLigne($champs);
  }
}