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
//	Traitement du premier formulaire pour afficher le tableau avec les états de validations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire0 = $('#zone_choix');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation0 = formulaire0.validate
		(
			{
				rules :
				{
					f_palier : { required:true },
					f_groupe : { required:true }
				},
				messages :
				{
					f_palier : { required:"palier manquant" },
					f_groupe : { required:"classe / groupe manquant" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element){element.after(error);}
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions0 =
		{
			type : 'POST',
			url : 'ajax.php?page='+PAGE,
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg_choix",
			beforeSubmit : test_form_avant_envoi0,
			error : retour_form_erreur0,
			success : retour_form_valide0
		};

		// Envoi du formulaire (avec jquery.form.js)
		formulaire0.submit
		(
			function()
			{
				var groupe_val = $("#f_groupe").val();
				if(groupe_val)
				{
					// récupération du type du groupe
					type = $("#f_groupe option:selected").parent().attr('label');
					$('#f_groupe_type').val( type );
				}
				$(this).ajaxSubmit(ajaxOptions0);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi0(formData, jqForm, options)
		{
			$('#ajax_msg_choix').removeAttr("class").html("&nbsp;");
			var readytogo = validation0.form();
			if(readytogo)
			{
				$("button").attr('disabled','disabled');
				$('#zone_bilan').html("&nbsp;").show();
				$('#zone_validation').hide('fast');
				$('#ajax_msg_choix').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur0(msg,string)
		{
			$("button").removeAttr('disabled');
			$('#ajax_msg_choix').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide0(responseHTML)
		{
			maj_clock(1);
			$("button").removeAttr('disabled');
			if( (responseHTML.substring(0,6)!='<table') && (responseHTML!='') )
			{
				$('#ajax_msg_choix').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#ajax_msg_choix').removeAttr("class").addClass("valide").html("Affichage réalisé !").fadeOut(3000,function(){$(this).removeAttr("class").html("").show();});
				$('#zone_bilan').html(responseHTML);
				infobulle();
			}
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Trois variables utiles pour la suite (à définir globalement)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var etat_valid = '';
		var user_id = 0;
		var item_id = 0;

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Fonction pour mettre à jour et afficher la zone de validation personnelle
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function maj_zone_validation()
		{
			$('#ajax_msg_validation').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action=Afficher_validation&f_user='+user_id+'&f_item='+item_id,
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg_validation').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
						return false;
					},
					success : function(responseHTML)
					{
						maj_clock(1);
						if(responseHTML.substring(0,5)!='<span')
						{
							$('#ajax_msg_validation').removeAttr("class").addClass("alerte").html(responseHTML);
						}
						else
						{
							$('#ajax_msg_validation').removeAttr("class").addClass("valide").html("Affichage réalisé !");
							$('#identite').html( $('#U'+user_id).children('img').attr('alt') );
							contenu = $('#E'+item_id).html();
							$('#E0').html( contenu );
							contenu = $('#E'+item_id).parent().parent().html();
							$('#S0').html( contenu.substring(0,contenu.indexOf('<ul')) );
							contenu = $('#E'+item_id).parent().parent().parent().parent().html();
							$('#P0').html( contenu.substring(0,contenu.indexOf('<ul')) );
							$('input[type=radio]').removeAttr('checked');
							$('#'+etat_valid).attr('checked','checked');
							texte_stats = responseHTML.substring(0,responseHTML.indexOf('@'));
							texte_items = responseHTML.substring(responseHTML.indexOf('@')+1);
							$('#stats').html(texte_stats);
							$('#items').html(texte_items);
							maj_formulaire();
						}
					}
				}
			);
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Fonction pour chercher les ids des élèves/items précédents/suivants et (des)activer les options du select correspondant
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function maj_formulaire()
		{
			// élève suivant
			var cellule = $('#U'+user_id+'E'+item_id).next('th');
			if(cellule.length)
			{
				var new_id = cellule.attr('id');
				$('#next_user').val( parseInt(new_id.substring(1,new_id.indexOf('E'))) );
				$('#f_ensuite option[value=next_user]').removeAttr('disabled');
			}
			else
			{
				$('#next_user').val(0);
				$('#f_ensuite option[value=next_user]').attr('disabled','disabled');
				if($('#f_ensuite option[value=next_user]:selected').length)
				{
					$('#f_ensuite option[value=retour_menu]').attr('selected','selected');
				}
			}
			// élève précédent
			var cellule = $('#U'+user_id+'E'+item_id).prev('th');
			if(cellule.length)
			{
				var new_id = cellule.attr('id');
				$('#prev_user').val( parseInt(new_id.substring(1,new_id.indexOf('E'))) );
				$('#f_ensuite option[value=prev_user]').removeAttr('disabled');
			}
			else
			{
				$('#prev_user').val(0);
				$('#f_ensuite option[value=prev_user]').attr('disabled','disabled');
				if($('#f_ensuite option[value=prev_user]:selected').length)
				{
					$('#f_ensuite option[value=retour_menu]').attr('selected','selected');
				}
			}
			// item suivant
			var cellule = $('#U'+user_id+'E'+item_id).parent().next('tr').children('th:eq(0)');
			if(cellule.length)
			{
				var new_id = cellule.attr('id');
				$('#next_item').val( parseInt(new_id.substring(new_id.indexOf('E')+1)) );
				$('#f_ensuite option[value=next_item]').removeAttr('disabled');
			}
			else
			{
				$('#next_item').val(0);
				$('#f_ensuite option[value=next_item]').attr('disabled','disabled');
				if($('#f_ensuite option[value=next_item]:selected').length)
				{
					$('#f_ensuite option[value=retour_menu]').attr('selected','selected');
				}
			}
			// item précédent
			var cellule = $('#U'+user_id+'E'+item_id).parent().prev('tr').children('th:eq(0)');
			if(cellule.length)
			{
				var new_id = cellule.attr('id');
				$('#prev_item').val( parseInt(new_id.substring(new_id.indexOf('E')+1)) );
				$('#f_ensuite option[value=prev_item]').removeAttr('disabled');
			}
			else
			{
				$('#prev_item').val(0);
				$('#f_ensuite option[value=prev_item]').attr('disabled','disabled');
				if($('#f_ensuite option[value=prev_item]:selected').length)
				{
					$('#f_ensuite option[value=retour_menu]').attr('selected','selected');
				}
			}
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur une image de la zone bilan général => Charger / Afficher la zone de validation personnelle
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#zone_bilan tbody th').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var id = $(this).attr('id');
				user_id = parseInt(id.substring(1,id.indexOf('E')));
				item_id = parseInt(id.substring(id.indexOf('E')+1));
				etat_valid = $(this).attr('class');
				$('#zone_bilan').hide('fast');
				$('#fieldset_validation').hide('fast');
				$('#zone_validation').show('fast');
				maj_zone_validation();
				$('#fieldset_validation').show('fast');
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer la zone de validation personnelle
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#fermer_zone_validation').click
		(
			function()
			{
				$('#zone_validation').hide('fast');
				$('#zone_bilan').show('fast');
				// On vide en plus les contenus sinon on les voit apparaitre ensuite durant le temps de chargement malgré les précautions prises
				$('#identite').html('');
				$('input[type=radio]').removeAttr('checked');
				$('#E0').html('');
				$('#S0').html('');
				$('#P0').html('');
				$('#stats').html('');
				$('#items').html('');
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour soumettre une validation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function action_suivante()
		{
			// Enchaîner sur l'action suivante demandée
			var ensuite = $('#f_ensuite option:selected').val();
			if( (ensuite=='next_user') && ($('#next_user').val()!=0) )
			{
				user_id = $('#next_user').val();
				etat_valid = $('#U'+user_id+'E'+item_id).attr('class');
				maj_zone_validation();
				return false;
			}
			if( (ensuite=='prev_user') && ($('#prev_user').val()!=0) )
			{
				user_id = $('#prev_user').val();
				etat_valid = $('#U'+user_id+'E'+item_id).attr('class');
				maj_zone_validation();
				return false;
			}
			if( (ensuite=='next_item') && ($('#next_item').val()!=0) )
			{
				item_id = $('#next_item').val();
				etat_valid = $('#U'+user_id+'E'+item_id).attr('class');
				maj_zone_validation();
				return false;
			}
			if( (ensuite=='prev_item') && ($('#prev_item').val()!=0) )
			{
				item_id = $('#prev_item').val();
				etat_valid = $('#U'+user_id+'E'+item_id).attr('class');
				maj_zone_validation();
				return false;
			}
			// On n'arrive là que si aucune des 4 possibilités précédente ne s'est déroulée.
			$('#fermer_zone_validation').click();
		}

		$('#Enregistrer_validation').click
		(
			function()
			{
				// On cherche s'il y a eu une modification concernant l'état de validation
				if( $('input[name=f_vi]').is(":checked")!=true )	// normalement impossible, sauf si par exemple on triche avec la barre d'outils Web Developer...
				{
					$('#ajax_msg_validation').removeAttr("class").addClass("erreur").html("Cocher un état de validation !");
					return(false);
				}
				var etat_valid_new = $('input[name=f_vi]:checked').val();
				if(etat_valid_new!=etat_valid)
				{
					// Envoyer en ajax la décision avec etat_valid_new / user_id / item_id
					$("bouton").attr('disabled','disabled');
					$('#ajax_msg_validation').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?page='+PAGE,
							data : 'f_action=Enregistrer_validation&f_user='+user_id+'&f_item='+item_id+'&f_etat='+etat_valid_new,
							dataType : "html",
							error : function(msg,string)
							{
								$("#bouton_valider").removeAttr('disabled');
								$('#ajax_msg_validation').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
								return false;
							},
							success : function(responseHTML)
							{
								maj_clock(1);
								$("#bouton_valider").removeAttr('disabled');
								if(responseHTML.substring(0,2)!='OK')
								{
									$('#ajax_msg_validation').removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{
									$('#ajax_msg_validation').removeAttr("class").addClass("valide").html("Demande enregistrée !");
									// Mettre à jour le tableau de synthèse
									$('#U'+user_id+'E'+item_id).removeAttr("class").attr('class',etat_valid_new).attr('title','bla bla bla');
									infobulle();
									action_suivante();
								}
							}
						}
					);
				}
				else
				{
					action_suivante();
				}
			}
		);

	}
);

