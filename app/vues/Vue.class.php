<?php

/**
 * Classe Vue qui gère la génération de toutes les pages html en utilisant le moteur de templates Twig
 * 
 */

class Vue {

  /**
   * Génération et affichage de la page html complète associée à la vue avec le moteur de templates Twig
   * ---------------------------------------------------------------------------------------------------
   * @param string $vue,     nom du fichier de la vue sans le suffixe twig 
   * @param array $donnees,  variables à insérer dans la page
   * @param string $gabarit, nom du fichier gabarit de la page html sans le suffixe twig, dans lequel est insérée la vue 
   * @param boolean $courriel
   */
  public function generer($vue, $donnees = array(), $gabarit, $courriel = false) {

    require_once 'app/vues/vendor/autoload.php';
    $loader = new \Twig\Loader\FilesystemLoader('app/vues/templates');
    $twig   = new \Twig\Environment(
                                    $loader,
                                    // ['cache' => 'app/vues/templates/cache']
                                   );
    
    $donnees['templateMain'] = "$vue.twig";

    $twig->addFunction(
      new \Twig\TwigFunction(
        'getJourSemaine',
        function($seance_date) {
          return Seance::getJourSemaine($seance_date);
        }
      )
    );
    
    $html = $twig->render("$gabarit.twig", $donnees);
    if ($courriel) return $html; 
    echo $html;
  }


}