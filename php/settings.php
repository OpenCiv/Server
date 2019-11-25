<?php
/**
 * Only contains static security sensitive variables
 */
class settings {
   /**
    * The working title
    */
   static $title = 'Open Civ';

   /**
    * The allowed origin for access control
    */
   static $origin = '*';

   /**
    * The location of the MySQL instance
    */
   static $dbhost = 'localhost';

   /**
    * The login username for the MySQL instance
    */
   static $dbuser = 'username';

   /**
    * The login password for the MySQL instance
    */
   static $dbpass = 'password';

   /**
    * The name of the MySQL database
    */
   static $dbname = 'openciv';

   /**
    * The e-mail address from which e-mails are sent
    */
   static $email = 'user@domain.com';

   /**
    * The location of the client
    */
   static $baseurl = 'http://localhost:5000/';
}
?>