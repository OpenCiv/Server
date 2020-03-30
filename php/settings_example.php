<?php
/**
 * Only contains static security sensitive variables
 */
class settings {

   /**
    * The allowed origin for access control
    */
   static $origin = 'https://openciv.eu';

   /**
    * The location of the MySQL instance
    */
   static $dbhost = 'localhost';

   /**
    * The login username for the MySQL instance
    */
   static $dbuser = 'dbuser';

   /**
    * The login password for the MySQL instance
    */
   static $dbpass = 'dbpass';

   /**
    * The name of the MySQL database
    */
   static $dbname = 'openciv';

   /**
    * The e-mail address from which e-mails are sent
    */
   static $email = 'Open Civ <noreply@openciv.eu>';
}
?>