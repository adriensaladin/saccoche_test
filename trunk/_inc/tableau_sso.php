<?php
/**
 * @version $Id: tableau_sso.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 * Tableau avec les différents modes d'identification possibles
 * 
 */

$tab_sso = array();
$tab_sso['normal']                  = array('txt'=>'Mode de connexion normal avec les identifiants enregistrés dans SACoche.' , 'doc'=>false);
$tab_sso['ent-auth.ac-bordeaux.fr'] = array('doc'=>'academie_Bordeaux' , 'nom'=>3 , 'prenom'=>4 , 'id_ent'=>1 , 'txt'=>'ENT Argos de l\'académie de Bordeaux (départements 24,33,40,47).');
$tab_sso['cas.argos64.fr']          = array('doc'=>'academie_Bordeaux' , 'nom'=>3 , 'prenom'=>4 , 'id_ent'=>1 , 'txt'=>'ENT Argos64 (département des Pyrénées-Atlantiques).');
$tab_sso['cas.entmip.fr']           = array('doc'=>'academie_Toulouse' , 'nom'=>4 , 'prenom'=>3 , 'id_ent'=>1 , 'txt'=>'ENT Midi-Pyrénées K-d\'Ecole de l\'académie de Toulouse.');
$tab_sso['cas.cybercolleges42.fr']  = array('doc'=>'departement_Loire' , 'nom'=>4 , 'prenom'=>3 , 'id_ent'=>1 , 'txt'=>'ENT Cybercollège 42 du département de la Loire.');

?>