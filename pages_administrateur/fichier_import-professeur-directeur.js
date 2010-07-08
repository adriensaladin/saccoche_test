/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre GPL 3 <http://www.rodage.org/gpl-3.0.fr.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Générale Publique GNU pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Générale Publique GNU avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

// jQuery !
$(document).ready
(
	function()
	{

		$("#step1").addClass("on");

		// ********************
		// * Étape 0 -> Étape 1
		// ********************

		// Envoi du fichier avec jquery.ajaxupload.js
		new AjaxUpload
		('#bouton_sconet',
			{
				action: 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
				name: 'userfile',
				data: {'f_step':1,'f_action':'sconet'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier_sconet,
				onComplete: retourner_fichier_sconet
			}
		);
		new AjaxUpload
		('#bouton_tableur',
			{
				action: 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
				name: 'userfile',
				data: {'f_step':1,'f_action':'tableur'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier_tableur,
				onComplete: retourner_fichier_tableur
			}
		);

		function changer_fichier(fichier_nom,fichier_extension)
		{
			$('button').attr('disabled','disabled');
			$('button').next('label').removeAttr("class").html('&nbsp;');
			return true;
		}

		function verifier_fichier_sconet(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('button').removeAttr('disabled');
				$('#ajax_msg_sconet').removeAttr("class").addClass("erreur").html('Chemin de fichier incorrect.');
				return false;
			}
			else if ('.xml.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
			{
				$('button').removeAttr('disabled');
				$('#ajax_msg_sconet').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension "zip" ou "xml".');
				return false;
			}
			else
			{
				$('#ajax_msg_sconet').removeAttr("class").addClass("loader").html('Fichier envoyé... Veuillez patienter.');
				return true;
			}
		}
		function verifier_fichier_tableur(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('button').removeAttr('disabled');
				$('#ajax_msg_tableur').removeAttr("class").addClass("erreur").html('Chemin de fichier incorrect.');
				return false;
			}
			else if ('.csv.txt.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
			{
				$('button').removeAttr('disabled');
				$('#ajax_msg_tableur').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension "csv" ou "txt".');
				return false;
			}
			else
			{
				$('#ajax_msg_tableur').removeAttr("class").addClass("loader").html('Fichier envoyé... Veuillez patienter.');
				return true;
			}
		}

		function retourner_fichier_sconet(fichier_nom,responseHTML)
		{
			if(responseHTML.substring(0,13)!='<div id="ok">')
			{
				$('button').removeAttr('disabled');
				$('#ajax_msg_sconet').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				maj_clock(1);
				$('button').next('label').removeAttr("class").html('&nbsp;');
				$('#ajax').html(responseHTML);
			}
		}
		function retourner_fichier_tableur(fichier_nom,responseHTML)
		{
			if(responseHTML.substring(0,13)!='<div id="ok">')
			{
				$('button').removeAttr('disabled');
				$('#ajax_msg_tableur').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				maj_clock(1);
				$('button').next('label').removeAttr("class").html('&nbsp;');
				$('#ajax').html(responseHTML);
			}
		}

		// ********************
		// * Étape 1 -> Étape 2
		// ********************

		$('a.step2').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$("#step li").removeAttr("class");
				$("#step2").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=2',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,13)!='<div id="ok">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#ajax').html(responseHTML);
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 2 -> Étape 3
		// ********************

		$('a.step3').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#ajax table').hide('fast');
				$("#step li").removeAttr("class");
				$("#step3").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=3',
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,13)!='<div id="ok">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#ajax').html(responseHTML);
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 3 -> Étape 4
		// ********************

		$('a.step4').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#ajax table').hide('fast');
				$("#step li").removeAttr("class");
				$("#step4").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=4&'+$("form").serialize(),
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,13)!='<div id="ok">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#ajax').html('&nbsp;');
								$('#ajax').html(responseHTML);
							}
						}
					}
				);
			}
		);

		// ********************
		// * Étape 4 -> Étape 5
		// ********************

		$('a.step5').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#ajax table').hide('fast');
				$("#step li").removeAttr("class");
				$("#step5").addClass("on");
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_step=5&'+$("form").serialize(),
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,13)!='<div id="ok">')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").html('&nbsp;');
								$('#ajax').html(responseHTML);
								format_liens('#ajax');
							}
						}
					}
				);
			}
		);

	}
);
