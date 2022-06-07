<?php

/**
 * Classe GestionCourrielSimuler
 *
 */
class GestionCourrielSimuler {

  /**
   * Simuler l'envoi d'un courriel à l'utilisateur pour lui communiquer
   * son identifiant de connexion et son mot de passe
   * en l'écrivant dans un fichier
   * @param object $oUtilisateur utilisateur destinataire  
   */
  public function envoyerMdp(Utilisateur $oUtilisateur) {
    $dateEnvoi     = date("Y-m-d H-i-s");
    $destinataire  = $oUtilisateur->utilisateur_courriel; 
    $message       = (new Vue)->generer('cMdp',
                                        array(
                                          'titre'        => 'Information',
                                          'oUtilisateur' => $oUtilisateur
                                        ),
                                        'gabarit-admin-min', true);
  
    $nfile = fopen("app/mocks/courriels/$dateEnvoi-$destinataire.html", "w");
    fwrite($nfile, $message);
    fclose($nfile); 
    return true;
  }
}