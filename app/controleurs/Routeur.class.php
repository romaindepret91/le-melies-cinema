<?php

/**
 * Classe Routeur
 * analyse l'uri et exécute la méthode associée  
 *
 */

class Routeur {

  private $routes = [
    // uri, classe, méthode
    // --------------------
    ["admin",         "Admin",    "gererAdmin"],
    ["",              "Frontend", "listerAlaffiche"],
    ["prochainement", "Frontend", "listerProchainement"],
    ["film",          "Frontend", "voirFilm"]
  ];

  protected $oRequetesSQL; // objet RequetesSQL utilisé par tous les contrôleurs

  const ERROR_NOT_FOUND  = "HTTP 404";
  const ERROR_FORBIDDEN  = "HTTP 403";
  
  /**
   * Constructeur qui valide l'URI
   * et instancie la méthode du contrôleur correspondante
   *
   */
  public function __construct() {
    try {
      $baseUri = substr($_SERVER['PHP_SELF'], 0, -9);
      
      $uri =  $_SERVER['REQUEST_URI'];
      if (strpos($uri, '?')) $uri = strstr($uri, '?', true);

      foreach ($this->routes as $route) {

        $routeUri     = $baseUri.$route[0];
        $routeClasse  = $route[1];
        $routeMethode = $route[2];
        
        if ($routeUri ===  $uri) {
          
          $oRouteClasse = new $routeClasse;
          $oRouteClasse->$routeMethode();  
          exit;
        }
      }
     
      throw new \Exception(self::ERROR_NOT_FOUND);
    }
    catch (\Error | \Exception $e) {
      $this->erreur($e->getMessage(), $e->getFile(), $e->getLine());
    }
  }

  /**
   * Méthode qui envoie un compte-rendu d'erreur
   * @param string $erreur, message d'erreur ou code d'erreur HTTP 
   *
   */
  public static function erreur($erreur, $fichier, $ligne) {
    $message = '';
    if ($erreur == self::ERROR_FORBIDDEN) {
      header('HTTP/1.1 403 Forbidden');
    } else if ($erreur == self::ERROR_NOT_FOUND) {
      header('HTTP/1.1 404 Not Found');
      (new Vue)->generer('vErreur404', [], 'gabarit-erreur');
    } else {
      header('HTTP/1.1 500 Internal Server Error');
      $message = $erreur;
      (new Vue)->generer("vErreur500", ['message' => $message, 'fichier' => $fichier, 'ligne' => $ligne], 'gabarit-erreur');
    }
    exit;
  }
}