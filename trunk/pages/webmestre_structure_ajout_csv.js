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

		// Variable à définir globalement en dehors des fonctions
		var courriel_envoi = -1 ;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Réagir au clic sur un bouton pour uploader un fichier csv à importer
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Envoi du fichier avec jquery.ajaxupload.js
		new AjaxUpload
		('#bouton_form_csv',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'f_action':'importer_csv'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier_csv,
				onSubmit: verifier_fichier_csv,
				onComplete: retourner_fichier_csv
			}
		);

		function changer_fichier_csv(fichier_nom,fichier_extension)
		{
			$('#ajax_msg_csv').removeAttr("class").html('&nbsp;');
			return true;
		}

		function verifier_fichier_csv(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('#ajax_msg_csv').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
				return false;
			}
			else if ('.csv.txt.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
			{
				$('#ajax_msg_csv').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas une extension "csv" ou "txt".');
				return false;
			}
			else
			{
				$('button').prop('disabled',true);
				$('#ajax_msg_csv').removeAttr("class").addClass("loader").html('Fichier envoyé... Veuillez patienter.');
				return true;
			}
		}

		function retourner_fichier_csv(fichier_nom,responseHTML)	// Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
		{
			$('button').prop('disabled',false);
			var tab_infos = responseHTML.split(']¤[');
			if( (tab_infos.length!=2) || (tab_infos[0]!='') )
			{
				$('#ajax_msg_csv').removeAttr("class").addClass("alerte").html(responseHTML);
				$('#div_import , #div_info_import , #structures').hide('fast');
			}
			else
			{
				initialiser_compteur();
				$('#ajax_msg_csv').removeAttr("class").addClass("valide").html("Fichier bien reçu ; "+tab_infos[1]+".");
				$('#div_info_import , #structures').hide('fast');
				$('#ajax_msg_import').removeAttr("class").html('&nbsp;');
				$('#div_import').show('fast');
				$('#ajax_import_num').html(1);
				$('#ajax_import_max').html(parseInt(tab_infos[1]));
			}
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Demande d'import du csv => soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#bouton_importer').click
		(
			function()
			{
				courriel_envoi = $('#f_courriel_envoi').is(':checked') ? 1 : 0 ;
				if( courriel_envoi || confirm("Le mot de passe du premier administrateur, non récupérable ultérieurement, ne sera pas transmis !\nConfirmez-vous ne pas vouloir envoyer le courriel d'inscription ?") )
				{
					$("button").prop('disabled',true);
					var num = $('#ajax_import_num').html();
					var max = $('#ajax_import_max').html();
					$('#ajax_msg_import').removeAttr("class").addClass("loader").html('Import en cours : étape ' + num + ' sur ' + max + '...');
					$('#puce_info_import').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
					$('#div_info_import').show('fast');
					$('#structures').hide('fast').children('#transfert').children('tbody').html('');
					importer();
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Demande d'import du csv => étapes du traitement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function importer()
		{
			var num = parseInt( $('#ajax_import_num').html() );
			var max = parseInt( $('#ajax_import_max').html() );
			// Appel en ajax
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action=' + 'ajouter' + '&num=' + num + '&max=' + max + '&courriel_envoi=' + courriel_envoi,
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg_import').removeAttr("class").addClass("alerte").html('Echec lors de la connexion au serveur !');
						$('#puce_info_import').html('<li><a id="a_reprise_import" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a></li>');
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						var tab_infos = responseHTML.split(']¤[');
						if( (tab_infos.length==2) && (tab_infos[0]=='') )
						{
							num++;
							$('#structures tbody').append(tab_infos[1]);
							if(num > max)	// Utilisation de parseInt obligatoire sinon la comparaison des valeurs pose ici pb
							{
								$('#ajax_msg_import').removeAttr("class").addClass("valide").html('');
								$('#puce_info_import').html('<li>Import terminé !</li>');
								$('#ajax_msg_csv , #ajax_msg_import').removeAttr("class").html('&nbsp;');
								$('#div_import').hide('fast');
								$('#structures').show('fast');
								$("button").prop('disabled',false);
							}
							else
							{
								$('#ajax_import_num').html(num);
								$('#ajax_msg_import').removeAttr("class").addClass("loader").html('Import en cours : étape ' + num + ' sur ' + max + '...');
								$('#puce_info_import').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
								importer();
							}
						}
						else
						{
							$('#ajax_msg_import').removeAttr("class").addClass("alerte").html(tab_infos[0]);
							$('#puce_info_import').html('<li><a id="a_reprise_import" href="#">Reprendre la procédure à l\'étape ' + num + ' sur ' + max + '.</a></li>');
						}
					}
				}
			);
		}

		// live est utilisé pour prendre en compte les nouveaux éléments html créés

		$('#a_reprise_import').live
		('click',
			function()
			{
				num = $('#ajax_import_num').html();
				max = $('#ajax_import_max').html();
				$('#ajax_msg_import').removeAttr("class").addClass("loader").html('Import en cours : étape ' + num + ' sur ' + max + '...');
				$('#puce_info_import').html('<li>Ne pas interrompre la procédure avant la fin du traitement !</li>');
				importer();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Éléments dynamiques du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Tout cocher ou tout décocher
		$('#all_check').click
		(
			function()
			{
				$('#structures input[type=checkbox]').prop('checked',true);
				return false;
			}
		);
		$('#all_uncheck').click
		(
			function()
			{
				$('#structures input[type=checkbox]').prop('checked',false);
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur une cellule (remplace un champ label, impossible à définir sur plusieurs colonnes)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('td.label').live
		('click',
			function()
			{
				$(this).parent().find("input[type=checkbox]").click();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur un bouton pour effectuer une action sur les structures cochées
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var supprimer_structures_cochees = function(listing_id)
		{
			$("button").prop('disabled',true);
			// afficher_masquer_images_action('hide');
			$('#ajax_supprimer').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action=supprimer&f_listing_id='+listing_id,
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_supprimer').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
						$("button").prop('disabled',false);
						// afficher_masquer_images_action('show');
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML!='<ok>')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_supprimer').removeAttr("class").addClass("alerte").html(responseHTML);
						}
						else
						{
							$("input[type=checkbox]:checked").each
							(
								function()
								{
									$('#f_base option[value='+$(this).val()+']').remove();
									$(this).parent().parent().remove();
								}
							);
							$('#ajax_supprimer').removeAttr("class").html('&nbsp;');
							$("button").prop('disabled',false);
							// afficher_masquer_images_action('show');
						}
					}
				}
			);
		};

		$('#zone_actions button').click
		(
			function()
			{
				var listing_id = new Array(); $("input[type=checkbox]:checked").each(function(){listing_id.push($(this).val());});
				if(!listing_id.length)
				{
					$('#ajax_supprimer').removeAttr("class").addClass("erreur").html("Aucune structure cochée !");
					return false;
				}
				$('#ajax_supprimer').removeAttr("class").html('&nbsp;');
				var id = $(this).attr('id');
				if(id=='bouton_supprimer')
				{
					if(confirm("Toutes les bases des structures cochées seront supprimées !\nConfirmez-vous vouloir effacer les données de ces structures ?"))
					{
						supprimer_structures_cochees(listing_id);
					}
				}
				else
				{
					$('#listing_ids').val(listing_id);
					var tab = new Array;
					tab['bouton_newsletter'] = "webmestre_newsletter";
					// tab['bouton_stats']      = "webmestre_statistiques";
					// tab['bouton_transfert']  = "webmestre_structure_transfert";
					var page = tab[id];
					var form = document.getElementById('structures');
					form.action = './index.php?page='+page;
					form.method = 'post';
					// form.target = '_blank';
					form.submit();
				}
			}
		);

	}
);
