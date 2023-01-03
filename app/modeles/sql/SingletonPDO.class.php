<?php
 
class SingletonPDO extends PDO
{
    private static $instance = null;

    const DB_SERVEUR  = "us-cdbr-east-06.cleardb.net" ; //'localhost';
    const DB_NOM      = "heroku_7c851397e4d5f5c"; //'cinema';
    const DB_DSN      = 'mysql:host='. self::DB_SERVEUR .';dbname='. self::DB_NOM.';charset=utf8'; 
    const DB_LOGIN    = "bdc75edba29ccd"; //'root';
    const DB_PASSWORD = "0c7e5cff"; //'root';

    private function __construct() {
        
      $options = [
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION, // Gestion des erreurs par des exceptions de la classe PDOException
        PDO::ATTR_EMULATE_PREPARES  => false                   // Préparation des requêtes non émulée
      ];
      parent::__construct(self::DB_DSN, self::DB_LOGIN, self::DB_PASSWORD, $options);
    }
  
    private function __clone (){}  

    public static function getInstance() {  
      if(is_null(self::$instance))
      {
        self::$instance = new SingletonPDO();
      }
      return self::$instance;
    }
}
