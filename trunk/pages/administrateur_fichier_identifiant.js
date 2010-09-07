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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Réagir au changement dans le premier formulaire (choix principal)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#f_choix_principal").change
		(
			function()
			{
				// Masquer tout
				$('#p_eleves , #p_professeurs_directeurs , #td_bouton').hide(0); // bug mystérieux si on échange avec la ligne suivante...
				$('#fieldset_init_loginmdp , #fieldset_import_loginmdp , #fieldset_import_id_ent_normal , #fieldset_import_id_ent_cas , #fieldset_import_id_gepi').hide(0);
				// Puis afficher ce qu'il faut
				var objet = $(this).val();
				switch (objet)
				{
					case 'init_loginmdp_eleves':                 $('#fieldset_init_loginmdp , #p_eleves , #td_bouton').show(); break;
					case 'init_loginmdp_professeurs_directeurs': $('#fieldset_init_loginmdp , #p_professeurs_directeurs , #td_bouton').show(); break;
					case 'import_loginmdp':                      $('#fieldset_import_loginmdp').show(); break;
					case 'import_id_ent_normal':                 $('#fieldset_import_id_ent_normal').show(); break;
					case 'import_id_ent_cas':                    $('#fieldset_import_id_ent_cas').show(); break;
					case 'import_id_gepi':                       $('#fieldset_import_id_gepi').show(); break;
				}
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
				$('#ajax_retour').html("&nbsp;");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Charger le select_professeurs_directeurs en ajax
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function maj_professeur_directeur()
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page=_maj_select_professeurs_directeurs',
					data : 'f_statut=1',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						maj_clock(1);
						if(responseHTML.substring(0,4)=='<opt')	// option ou optgroup !
						{
							$('#ajax_msg').removeAttr("class").addClass("valide").html("Affichage actualisé !");
							$('#select_professeurs_directeurs').html(responseHTML);
						}
						else
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Charger le select_eleves en ajax
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function maj_eleve(groupe_id,groupe_type)
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page=_maj_select_eleves',
					data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_statut=1',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						maj_clock(1);
						if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_msg').removeAttr("class").addClass("valide").html("Affichage actualisé !");
							$('#select_eleves').html(responseHTML).show();
						}
						else
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}
		function changer_groupe()
		{
			$("#select_eleves").html('<option value=""></option>').hide();
			var groupe_val = $("#f_groupe").val();
			if(groupe_val)
			{
				// type = $("#f_groupe option:selected").parent().attr('label');
				groupe_type = groupe_val.substring(0,1);
				groupe_id   = groupe_val.substring(1);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
				maj_eleve(groupe_id,groupe_type);
			}
			else
			{
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
			}
		}
		$("#f_groupe").change
		(
			function()
			{
				changer_groupe();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Réagir au clic dans un select multiple
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('select[multiple]').click
		(
			function()
			{
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Réagir au clic sur un bouton pour demander un export csv de la base (user_ent -> user_export)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#user_export').click
		(
			function()
			{
				$('button').attr('disabled','disabled');
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=' + 'user_export',
						dataType : "html",
						error : function(msg,string)
						{
							$('button').removeAttr('disabled');
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,3)!='<ul')
							{
								$('button').removeAttr('disabled');
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('button').removeAttr('disabled');
								$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
								$('#ajax_retour').html(responseHTML);
								format_liens('#ajax_retour');
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Soumission du formulaire - choix 1 et 2
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#init_login , #init_mdp').click
		(
			function()
			{
				var action = $(this).attr('id');
				// Récupérer le profil
				var choix = $('#f_choix_principal option:selected').val();
				if( (typeof(choix)=='undefined') || ((choix!='init_loginmdp_eleves')&&(choix!='init_loginmdp_professeurs_directeurs')) )	// normalement impossible, sauf si par exemple on triche avec la barre d'outils Web Developer...
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Anomalie avec le premier formulaire !");
					return(false);
				}
				var profil = choix.substring(14);
				// grouper les select multiples => normalement pas besoin si name de la forme nom[], mais ça plante curieusement sur le serveur competences.sesamath.net
				// alors j'ai remplacé le $("form").serialize() par les tableaux maison et mis un explode dans le fichier ajax
				if( $("#select_"+profil+" option:selected").length==0 )
				{
					$('#ajax_msg').removeAttr("class").addClass("erreur").html("Sélectionnez au moins un utilisateur !");
					return(false);
				}
				else
				{
					var select_users = new Array(); $("#select_"+profil+" option:selected").each(function(){select_users.push($(this).val());});
				}
				$('button').attr('disabled','disabled');
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=' + action + '&' + 'profil=' + profil + '&' + 'select_users=' + select_users,
						dataType : "html",
						error : function(msg,string)
						{
							$('button').removeAttr('disabled');
							$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							$('button').removeAttr('disabled');
							if(responseHTML.substring(0,3)!='<ul')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg').removeAttr("class").addClass("valide").html('Demande réalisée.');
								$('#ajax_retour').html(responseHTML);
								format_liens('#ajax_retour');
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Réagir au clic sur un bouton pour envoyer un import csv afin de forcer les logins ou/et mdp élèves (user_ent -> user_import)
// Réagir au clic sur le bouton pour envoyer un csv issu de l'ENT
// Réagir au clic sur un bouton pour envoyer un csv issu de Gepi
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Envoi du fichier avec jquery.ajaxupload.js
		new AjaxUpload
		('#import_loginmdp',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'action':'import_loginmdp'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier,
				onComplete: retourner_fichier
			}
		);
		new AjaxUpload
		('#import_ent',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'action':'import_ent'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier,
				onComplete: retourner_fichier
			}
		);
		new AjaxUpload
		('#import_gepi_eleves',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'action':'import_gepi_eleves'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier,
				onComplete: retourner_fichier
			}
		);
		new AjaxUpload
		('#import_gepi_profs',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'action':'import_gepi_profs'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier,
				onComplete: retourner_fichier
			}
		);

		function changer_fichier(fichier_nom,fichier_extension)
		{
			$('#ajax_msg').removeAttr("class").html('&nbsp;');
			$('#ajax_retour').html("&nbsp;");
			return true;
		}

		function verifier_fichier(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('#ajax_msg').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
				return false;
			}
			else if ('.csv.txt.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
			{
				$('#ajax_msg').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
				return false;
			}
			else
			{
				$('button').attr('disabled','disabled');
				$('#ajax_msg').removeAttr("class").addClass("loader").html('Fichier envoyé... Veuillez patienter.');
				return true;
			}
		}

		function retourner_fichier(fichier_nom,responseHTML)	// Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
		{
			$('button').removeAttr('disabled');
			if( (responseHTML.substring(0,3)!='<ul') && (responseHTML.substring(0,3)!='<UL') )
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				maj_clock(1);
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
				$('#ajax_retour').html(responseHTML);
				format_liens('#ajax_retour');
			}
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Réagir au clic sur un bouton afin de demander la duplication d'un champ
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('button[name=dupliquer]').click
		(
			function()
			{
				var action = $(this).attr('id');
				$('#ajax_retour').html('&nbsp;');
				$('button').attr('disabled','disabled');
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'action=' + action,
						dataType : "html",
						error : function(msg,string)
						{
							$('button').removeAttr('disabled');
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML!='ok')
							{
								$('button').removeAttr('disabled');
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('button').removeAttr('disabled');
								$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
							}
						}
					}
				);
			}
		);

	}
);
