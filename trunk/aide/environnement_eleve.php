<?php
/**
 * @version $Id: environnement_eleve.php 8 2009-10-30 20:56:02Z thomas $
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009
 * 
 * ****************************************************************************************************
 * SACoche [http://competences.sesamath.net] - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath [http://www.sesamath.net]
 * Distribution sous licence libre prévue pour l'été 2010.
 * ****************************************************************************************************
 * 
 */

if(!defined('SACoche')) {exit('Ce fichier ne peut être appelé directement !');}
$TITRE = "L'environnement élève";
?>
<h2>Connexion</h2>
<p>
	Pour se connecter comme élève, il faut sélectionner son établissement, saisir son nom d'utilisateur et son mot de passe (seul l'administrateur de l'établissement peut générer l'inscription de l'élève et lui communiquer ses paramètres).
</p>

<h2>Changer son mot de passe</h2>
<p>
	Les élèves peuvent modifier leur mot de passe (sauf si la connexion est dépendante d'un service extérieur, tel un ENT).<br />
	Les mots de passe sont cryptés et ne peuvent pas être renvoyés. En cas d'oubli du mot de passe élève, contacter l'administrateur qui est seul habilité à en générer un nouveau (ou un professeur qui transmettra la demande).
</p>

<h2>Fonctionnalités</h2>
<p>L'élève peut :</p>
<ul class="puce">
	<li>générer des grilles de compétences</li>
	<li>établir des bilans de compétences</li>
	<li>estimer son attestation de maîtrise du socle (si autorisé)</li>
</ul>
