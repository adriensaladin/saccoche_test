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

// Variable globale Highcharts
var graphique;
var ChartOptions;

// jQuery !
$(document).ready
(
	function()
	{

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic pour tout cocher ou tout décocher
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#table_bilans input[name=all_check]').click
		(
			function()
			{
				var id_mask = $(this).attr('id').replace('_deb1_','^=').replace('_deb2_','^=').replace('_fin1_','$=').replace('_fin2_','$=');
				$('input['+id_mask+']').prop('checked',true);
			}
		);
		$('#table_bilans input[name=all_uncheck]').click
		(
			function()
			{
				var id_mask = $(this).attr('id').replace('_deb1_','^=').replace('_deb2_','^=').replace('_fin1_','$=').replace('_fin2_','$=');
				$('input['+id_mask+']').prop('checked',false);
			}
		);

		$('#rubrique_check_all').click
		(
			function()
			{
				$('#zone_chx_rubriques input[type=checkbox]').prop('checked',true);
				return false;
			}
		);
		$('#rubrique_uncheck_all').click
		(
			function()
			{
				$('#zone_chx_rubriques input[type=checkbox]').prop('checked',false);
				return false;
			}
		);

		$('#eleve_check_all').click
		(
			function()
			{
				$('#form_choix_eleves input[type=checkbox]').prop('checked',true);
				return false;
			}
		);
		$('#eleve_uncheck_all').click
		(
			function()
			{
				$('#form_choix_eleves input[type=checkbox]').prop('checked',false);
				return false;
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur une cellule (remplace un champ label, impossible à définir sur plusieurs colonnes)
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('td.label').live
		('click',
			function()
			{
				$(this).parent().find("input[type=checkbox]").click();
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Enregistrer les modifications de types et/ou d'accès
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#bouton_valider').click
		(
			function()
			{
				if(!$('#cadre_statut input[type=radio]:checked').length)
				{
					$('#ajax_msg_gestion').removeAttr("class").addClass("erreur").html("Aucun statut coché !");
					return false;
				}
				var listing_id = new Array(); $("#table_bilans input[type=checkbox]:checked").each(function(){listing_id.push($(this).attr('id'));});
				if(!listing_id.length)
				{
					$('#ajax_msg_gestion').removeAttr("class").addClass("erreur").html("Aucune case du tableau cochée !");
					return false;
				}
				$('#ajax_msg_gestion').removeAttr("class").addClass("loader").html("Envoi&hellip;"); // volontairement court
				$('#listing_ids').val(listing_id);
				$('#csrf').val(CSRF);
				var form = document.getElementById('cadre_statut');
				form.action = './index.php?page=officiel&section=accueil_'+BILAN_TYPE;
				form.method = 'post';
				form.submit();
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Initialisation de variables utiles accessibles depuis toute fonction
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		var memo_objet         = '';
		var memo_section       = '';
		var memo_classe        = 0;
		var memo_groupe        = 0;
		var memo_periode       = 0;
		var memo_eleve         = 0;
		var memo_rubrique_nom  = 0;
		var memo_rubrique_type = '';
		var memo_rubrique_id   = 0;
		var memo_html          = '';
		var memo_long_max      = '';
		var memo_auto_next     = false;
		var memo_auto_prev     = false;
		var memo_eleve_first   = 0;
		var memo_eleve_last    = 0;
		var memo_classe_first  = 0;
		var memo_classe_last   = 0;

		var tab_classe_action_to_section = new Array();
		tab_classe_action_to_section['modifier']     = 'officiel_saisir';
		tab_classe_action_to_section['tamponner']    = 'officiel_saisir';
		tab_classe_action_to_section['detailler']    = 'officiel_examiner';
		tab_classe_action_to_section['voir']         = 'officiel_consulter';
		tab_classe_action_to_section['imprimer']     = 'officiel_imprimer';
		tab_classe_action_to_section['voir_archive'] = 'officiel_imprimer';

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur une image action
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#table_bilans q').click
		(
			function()
			{
				memo_objet = $(this).attr('class');
				memo_section = tab_classe_action_to_section[memo_objet];
				if(typeof(memo_section)!='undefined')
				{
					var tab_ids = $(this).parent().attr('id').split('_');
					memo_classe  = tab_ids[1];
					memo_groupe  = tab_ids[2];
					memo_periode = tab_ids[3];
					$('#f_objet').val(memo_objet);
					if( (memo_section=='officiel_saisir') || (memo_section=='officiel_consulter') )
					{
						// Masquer le tableau ; Afficher la zone action et charger son contenu
						$('#cadre_statut , #table_bilans , #puces_secondaires').hide(0);
						$('#zone_action_eleve').html('<label class="loader">Envoi en cours&hellip;</label>').show(0);
						$.ajax
						(
							{
								type : 'POST',
								url : 'ajax.php?page='+PAGE,
								data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'initialiser'+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
								dataType : "html",
								error : function(jqXHR, textStatus, errorThrown)
								{
									var message = (jqXHR.status!=500) ? 'Échec de la connexion !' : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
									$('#zone_action_eleve').html('<label class="alerte">'+message+' <button id="fermer_zone_action_eleve" type="button" class="retourner">Retour</button></label>');
									return false;
								},
								success : function(responseHTML)
								{
									initialiser_compteur();
									if(responseHTML.substring(0,4)!='<h2>')
									{
										$('#zone_action_eleve').html('<label class="alerte">'+responseHTML+' <button id="fermer_zone_action_eleve" type="button" class="retourner">Retour</button></label>');
									}
									else
									{
										$('#zone_action_eleve').html(responseHTML);
										infobulle();
										memo_eleve       = $('#go_selection_eleve option:selected').val();
										memo_eleve_first = $('#go_selection_eleve option:first').val();
										memo_eleve_last  = $('#go_selection_eleve option:last').val();
										masquer_element_navigation_choix_eleve();
										if($('#voir_photo').length==0)
										{
											charger_photo_eleve();
										}
										$('#cadre_photo').show(0);
									}
								}
							}
						);
					}
					else if(memo_section=='officiel_examiner')
					{
						// Masquer le tableau ; Afficher la zone de choix des rubriques
						$('#cadre_statut , #table_bilans , #puces_secondaires').hide(0);
						$('#zone_action_classe h2').html('Recherche de saisies manquantes');
						$('#zone_chx_rubriques').show(0);
					}
					else if(memo_section=='officiel_imprimer')
					{
						// Masquer le tableau ; Afficher la zone de choix des élèves, et si les bulletins sont déjà imprimés
						var titre = (memo_objet=='imprimer') ? 'Imprimer le bilan (PDF)' : 'Consulter un bilan imprimé (PDF)' ;
						configurer_form_choix_classe();
						$('#cadre_statut , #table_bilans , #puces_secondaires').hide(0);
						$('#zone_action_classe h2').html(titre);
						$('#report_periode').html( $('#periode_'+memo_periode).text()+' :' );
						$('#zone_action_classe , #zone_'+memo_objet).show(0);
						charger_formulaire_imprimer();
					}
				}
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le bouton pour fermer la zone action_eleve
		// Clic sur le bouton pour fermer la zone de choix des rubriques
		// Clic sur le bouton pour fermer la zone zone_action_classe
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#fermer_zone_action_eleve').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#zone_action_eleve').html("&nbsp;").hide(0);
				$('#cadre_photo').hide(0);
				$('#cadre_statut , #table_bilans , #puces_secondaires').show(0);
				return(false);
			}
		);

		$('#fermer_zone_chx_rubriques').click
		(
			function()
			{
				$('#zone_chx_rubriques').hide(0);
				$('#cadre_statut , #table_bilans , #puces_secondaires').show(0);
				return(false);
			}
		);

		$('#fermer_zone_action_classe').click
		(
			function()
			{
				$('#zone_resultat_classe').html("&nbsp;");
				$('#imprimer_liens').html('');
				var colspan = (memo_objet=='imprimer') ? 3 : 2 ;
				$('#zone_'+memo_objet+' table tbody').html('<tr><td class="nu" colspan="'+colspan+'"></td></tr>');
				$('#zone_action_classe , #zone_imprimer , #zone_voir_archive').css('display','none'); // .hide(0) ne fonctionne pas bien ici...
				$('#ajax_msg_imprimer , #ajax_msg_voir_archive').removeAttr("class").html("");
				$('#cadre_statut , #table_bilans , #puces_secondaires').show(0);
				return(false);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir|officiel_consulter] Navigation d'un élève à un autre
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		function charger_nouvel_eleve(eleve_id,reload)
		{
			if( (eleve_id==memo_eleve) && (!reload) )
			{
				return false;
			}
			memo_eleve = eleve_id;
			$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
			$('#zone_resultat_eleve').html('<label class="loader">Envoi en cours&hellip;</label>');
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'charger'+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&'+$('#form_hidden').serialize(),
					dataType : "html",
					error : function(jqXHR, textStatus, errorThrown)
					{
						$('#zone_resultat_eleve').html('<label class="alerte">Échec de la connexion !</label>');
						$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
						if( (responseHTML.substring(0,4)!='<div') && (responseHTML.substring(0,4)!='<h3>') )
						{
							$('#zone_resultat_eleve').html('<label class="alerte">'+responseHTML+'</label>');
						}
						else
						{
							$('#go_selection_eleve option[value='+memo_eleve+']').prop('selected',true);
							masquer_element_navigation_choix_eleve();
							if($('#voir_photo').length==0)
							{
								charger_photo_eleve();
							}
							var position_script = responseHTML.lastIndexOf('<SCRIPT>');
							if(position_script==-1)
							{
								$('#zone_resultat_eleve').html(responseHTML);
							}
							else
							{
								$('#zone_resultat_eleve').html( responseHTML.substring(0,position_script) );
								eval( responseHTML.substring(position_script+8) );
							}
							infobulle();
							if(memo_auto_next || memo_auto_prev)
							{
								memo_auto_next = false;
								memo_auto_prev = false;
								$('#'+memo_rubrique_nom).find('button').click();
							}
						}
					}
				}
			);
		}

		function masquer_element_navigation_choix_eleve()
		{
			$('#form_choix_eleve button').css('visibility','visible');
			if(memo_eleve==memo_eleve_first)
			{
				$('#go_premier_eleve , #go_precedent_eleve').css('visibility','hidden');
			}
			if(memo_eleve==memo_eleve_last)
			{
				$('#go_dernier_eleve , #go_suivant_eleve').css('visibility','hidden');
			}
		}

		$('#go_premier_eleve').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var eleve_id = $('#go_selection_eleve option:first').val();
				charger_nouvel_eleve(eleve_id,false);
			}
		);

		$('#go_dernier_eleve').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var eleve_id = $('#go_selection_eleve option:last').val();
				charger_nouvel_eleve(eleve_id,false);
			}
		);

		$('#go_precedent_eleve').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if( $('#go_selection_eleve option:selected').prev().length )
				{
					var eleve_id = $('#go_selection_eleve option:selected').prev().val();
					charger_nouvel_eleve(eleve_id,false);
				}
			}
		);

		$('#go_suivant_eleve').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if( $('#go_selection_eleve option:selected').next().length )
				{
					var eleve_id = $('#go_selection_eleve option:selected').next().val();
					charger_nouvel_eleve(eleve_id,false);
				}
			}
		);

		$('#go_selection_eleve').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('change',
			function()
			{
				var eleve_id = $('#go_selection_eleve option:selected').val();
				charger_nouvel_eleve(eleve_id,false);
			}
		);

		$('#change_mode').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if($('#f_mode').val()=='texte')
				{
					$('#change_mode').removeAttr("class").addClass("texte").html('Interface détaillée');
					$('#f_mode').val('graphique');
				}
				else
				{
					$('#change_mode').removeAttr("class").addClass("stats").html('Interface graphique');
					$('#f_mode').val('texte');
				}
				var eleve_id = $('#go_selection_eleve option:selected').val();
				charger_nouvel_eleve(eleve_id,true);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir|officiel_consulter] Clic sur le bouton pour imprimer ses appréciations
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#imprimer_appreciations_perso , #imprimer_appreciations_all').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var f_action = $(this).attr('id');
				$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_action='+f_action+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$.fancybox( '<label class="alerte">'+'Échec de la connexion !\nVeuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
							$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
							if(responseHTML.substring(0,4)!='<ul ')
							{
								$.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
							}
							else
							{
								// Mis dans le div bilan et pas balancé directement dans le fancybox sinon le format_lien() nécessite un peu plus de largeur que le fancybox ne recalcule pas (et $.fancybox.update(); ne change rien).
								// Malgré tout, pour Chrome par exemple, la largeur est mal clculée et provoque des retours à la ligne, d'où le minWidth ajouté.
								$('#bilan').html('<div class="noprint">Afin de préserver l\'environnement, n\'imprimer qu\'en cas de nécessité !</div><p />'+responseHTML);
								format_liens('#bilan');
								$.fancybox( { 'href':'#bilan' , onClosed:function(){$('#bilan').html("");} , 'centerOnScroll':true , 'minWidth':550 } );
							}
						}
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir] Clic sur le bouton pour ajouter une appréciation (une note de s'ajoute pas, mais elle peut se modifier ou se recalculer si NULL ou se recalculer)
		// [officiel_saisir] Clic sur le bouton pour modifier une note ou une saisie d'appréciation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		function afficher_textarea_appreciation_ou_input_moyenne(obj_lieu,champ_contenu)
		{
			// fabriquer le formulare textarea ou input
			if(memo_rubrique_type=='appr')
			{
				memo_html = obj_lieu.closest('td').html();
				memo_long_max = (memo_rubrique_id) ? APP_RUBRIQUE : APP_GENERALE ;
				var nb_lignes = parseInt(memo_long_max/100,10);
				var formulaire_saisie = '<div class="ti"><b>Appréciation / Conseils pour progresser [ '+$('#go_selection_eleve option:selected').text()+' ] :</b></div>'
															+ '<div class="ti"><textarea id="f_appreciation" name="f_appreciation" rows="'+nb_lignes+'" cols="100"></textarea></div>'
															+ '<div class="ti"><label id="f_appreciation_reste"></label></div>'
															+ '<div class="ti"><button id="valider_appr_precedent" type="button" class="valider_prev">Précédent</button> <button id="valider_appr" type="button" class="valider">Valider</button> <button id="valider_appr_suivant" type="button" class="valider_next">Suivant</button> <button id="annuler_appr_precedent" type="button" class="annuler_prev">Précédent</button> <button id="annuler_appr" type="button" class="annuler">Annuler</button> <button id="annuler_appr_suivant" type="button" class="annuler_next">Suivant</button><label id="ajax_msg_appr">&nbsp;</label></div>';
			}
			if(memo_rubrique_type=='note')
			{
				memo_html = obj_lieu.closest('tr').html();
				var pourcent = (CONVERSION_SUR_20) ? '' : '%' ;
				var texte    = (CONVERSION_SUR_20) ? 'en note sur 20' : 'en pourcentage' ;
				var formulaire_saisie = '<div><b>Moyenne '+texte+' [ '+$('#go_selection_eleve option:selected').text()+' ] :</b> <input id="f_moyenne" name="f_moyenne" type="text" size="3" value="" />'+pourcent+'</div>'
															+ '<div><button id="valider_note_precedent" type="button" class="valider_prev">Précédent</button> <button id="valider_note" type="button" class="valider">Valider</button> <button id="valider_note_suivant" type="button" class="valider_next">Suivant</button> <button id="annuler_note_precedent" type="button" class="annuler_prev">Précédent</button> <button id="annuler_note" type="button" class="annuler">Annuler</button> <button id="annuler_note_suivant" type="button" class="annuler_next">Suivant</button><label id="ajax_msg_note">&nbsp;</label></div>';
			}
			// modif affichage
			$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
			obj_lieu.closest('td').html(formulaire_saisie);
			if(memo_eleve==memo_eleve_first)
			{
				$('#valider_'+memo_rubrique_type+'_precedent , #annuler_'+memo_rubrique_type+'_precedent').css('visibility','hidden');
			}
			if(memo_eleve==memo_eleve_last)
			{
				$('#valider_'+memo_rubrique_type+'_suivant , #annuler_'+memo_rubrique_type+'_suivant').css('visibility','hidden');
			}
			// finalisation (remplissage et focus)
			if(memo_rubrique_type=='appr')
			{
				$('#f_appreciation').focus().html(champ_contenu);
				afficher_textarea_reste( $('#f_appreciation') , memo_long_max );
				window.scrollBy(0,100); // Pour avoir à l'écran les bouton de validation et d'annulation situés en dessous du textarea
			}
			if(memo_rubrique_type=='note')
			{
				var valeur = (CONVERSION_SUR_20) ? parseFloat(champ_contenu,10) : parseInt(champ_contenu.substr(0,champ_contenu.length-1),10) ;
				valeur = (isNaN(valeur)) ? '' : valeur ;
				$('#f_moyenne').focus().val(valeur);
			}
		}

		$('#zone_resultat_eleve button.ajouter , #zone_resultat_eleve button.modifier').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				memo_rubrique_nom  = $(this).closest('tr').attr('id');
				var tab_ids = memo_rubrique_nom.split('_');
				memo_rubrique_type = tab_ids[0]; // note | appr
				memo_rubrique_id   = parseInt( tab_ids[1] , 10 );
				if($(this).attr('class')=='modifier')
				{
					var contenu = (memo_rubrique_type=='appr') ? $(this).parent().next().html() : $(this).closest('td').prev().html() ;
				}
				else
				{
					var contenu = '' ;
				}
				afficher_textarea_appreciation_ou_input_moyenne( $(this) , contenu );
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir] Indiquer le nombre de caractères restant autorisés dans le textarea
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#f_appreciation').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('keyup',
			function()
			{
				afficher_textarea_reste($(this),memo_long_max);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir] Clic sur un bouton pour annuler une saisie de note ou d'appréciation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#annuler_appr , #annuler_appr_suivant , #annuler_appr_precedent , #annuler_note , #annuler_note_suivant , #annuler_note_precedent').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if(memo_rubrique_type=='appr')
				{
					$(this).closest('td').html(memo_html);
				}
				else if(memo_rubrique_type=='note')
				{
					$(this).closest('tr').html(memo_html);
				}
				$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
				memo_auto_next = ($(this).attr('id')=='annuler_'+memo_rubrique_type+'_suivant')   ? true : false ;
				memo_auto_prev = ($(this).attr('id')=='annuler_'+memo_rubrique_type+'_precedent') ? true : false ;
				if(memo_auto_next) { $('#go_suivant_eleve').click(); }
				if(memo_auto_prev) { $('#go_precedent_eleve').click(); }
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir] Clic sur un bouton pour valider une saisie de note ou d'appréciation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#valider_appr , #valider_appr_suivant , #valider_appr_precedent , #valider_note , #valider_note_suivant , #valider_note_precedent').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if(memo_rubrique_type=='appr')
				{
					if( !$('#f_appreciation').val().trim().length )
					{
						$('#ajax_msg_'+memo_rubrique_type).removeAttr("class").addClass("erreur").html("Absence d'appréciation !");
						$('#f_appreciation').focus();
						return false;
					}
				}
				if(memo_rubrique_type=='note')
				{
					var note = parseFloat($('#f_moyenne').val(),10);
					if( isNaN(note) )
					{
						$('#ajax_msg_'+memo_rubrique_type).removeAttr("class").addClass("erreur").html("Moyenne incorrecte !");
						$('#f_moyenne').focus();
						return false;
					}
					if( (note<0) || ((note>40)&&(CONVERSION_SUR_20)) || ((note>200)&&(!CONVERSION_SUR_20)) ) // Le code VV pouvant être configuré jusqu'à 200, des moyennes peuvent théoriquement atteindre des sommets...
					{
						$('#ajax_msg_'+memo_rubrique_type).removeAttr("class").addClass("erreur").html("Valeur incorrecte !");
						$('#f_moyenne').focus();
						return false;
					}
				}
				memo_auto_next = ($(this).attr('id')=='valider_'+memo_rubrique_type+'_suivant')   ? true : false ;
				memo_auto_prev = ($(this).attr('id')=='valider_'+memo_rubrique_type+'_precedent') ? true : false ;
				$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
				$('#ajax_msg_'+memo_rubrique_type).removeAttr("class").addClass("loader").html("Envoi en cours&hellip;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'enregistrer_'+memo_rubrique_type+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&f_rubrique='+memo_rubrique_id+'&'+$('#form_hidden').serialize()+'&'+$('#zone_resultat_eleve').serialize(),
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('#ajax_msg_'+memo_rubrique_type).removeAttr("class").addClass("alerte").html("Échec de la connexion !");
							$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
							if( (responseHTML.substring(0,4)!='<div') && (responseHTML.substring(0,4)!='<td ') )
							{
								$('#ajax_msg_'+memo_rubrique_type).removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								if(memo_rubrique_type=='appr')
								{
									$('#ajax_msg_'+memo_rubrique_type).closest('td').html(responseHTML);
								}
								else if(memo_rubrique_type=='note')
								{
									$('#ajax_msg_'+memo_rubrique_type).closest('tr').html(responseHTML);
								}
								if(memo_auto_next) { $('#go_suivant_eleve').click(); }
								if(memo_auto_prev) { $('#go_precedent_eleve').click(); }
							}
						}
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir] Clic sur le bouton pour supprimer une saisie de note ou d'appréciation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#zone_resultat_eleve button.supprimer').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var obj_bouton = $(this);
				memo_rubrique_nom  = $(this).closest('tr').attr('id');
				var tab_ids = memo_rubrique_nom.split('_');
				memo_rubrique_type = tab_ids[0]; // note | appr
				memo_rubrique_id   = parseInt( tab_ids[1] , 10 );
				$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'supprimer_'+memo_rubrique_type+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&f_rubrique='+memo_rubrique_id+'&'+$('#form_hidden').serialize(),
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$.fancybox( '<label class="alerte">'+'Échec de la connexion !\nVeuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
							$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
							if( (responseHTML.substring(0,4)!='<div') && (responseHTML.substring(0,4)!='<td ') )
							{
								$.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
							}
							else
							{
								if(memo_rubrique_type=='appr')
								{
									obj_bouton.closest('td').html(responseHTML);
								}
								else if(memo_rubrique_type=='note')
								{
									obj_bouton.closest('tr').html(responseHTML);
								}
							}
						}
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir] Clic sur le bouton pour recalculer une note (soit effacée - NULL - soit figée car reportée manuellement)
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#zone_resultat_eleve button.nettoyer').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var obj_bouton = $(this);
				memo_rubrique_nom  = $(this).closest('tr').attr('id');
				var tab_ids = memo_rubrique_nom.split('_');
				memo_rubrique_type = tab_ids[0]; // note | appr
				memo_rubrique_id   = parseInt( tab_ids[1] , 10 );
				$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',true);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'recalculer_note'+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&f_user='+memo_eleve+'&f_rubrique='+memo_rubrique_id+'&'+$('#form_hidden').serialize(),
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$.fancybox( '<label class="alerte">'+'Échec de la connexion !\nVeuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
							$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('#form_choix_eleve button , #form_choix_eleve select , #zone_resultat_eleve button').prop('disabled',false);
							if( (responseHTML.substring(0,4)!='<div') && (responseHTML.substring(0,4)!='<td ') )
							{
								$.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
							}
							else
							{
								obj_bouton.closest('tr').html(responseHTML);
							}
						}
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_examiner] Charger le contenu (résultat de l'examen pour une classe)
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#lancer_recherche').click
		(
			function()
			{
				var listing_id = new Array(); $("#zone_chx_rubriques input[type=checkbox]:enabled:checked").each(function(){listing_id.push($(this).val());});
				if(!listing_id.length)
				{
					$('#ajax_msg_recherche').removeAttr("class").addClass("erreur").html("Aucune rubrique cochée !");
					return false;
				}
				$('#f_listing_rubriques').val(listing_id);
				$('#zone_chx_rubriques button').prop('disabled',true);
				$('#ajax_msg_recherche').removeAttr("class").addClass("loader").html("Envoi en cours&hellip;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							var message = (jqXHR.status!=500) ? 'Échec de la connexion !' : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
							$('#ajax_msg_recherche').removeAttr("class").addClass("alerte").html(message);
							$('#zone_chx_rubriques button').prop('disabled',false);
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('#zone_chx_rubriques button').prop('disabled',false);
							if(responseHTML.substring(0,14)!='<p class="ti">')
							{
								$('#ajax_msg_recherche').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								configurer_form_choix_classe();
								masquer_element_navigation_choix_classe();
								$('#ajax_msg_recherche').removeAttr("class").html('');
								$('#report_periode').html( $('#periode_'+memo_periode).text()+' :' );
								$('#zone_resultat_classe').html(responseHTML);
								$('#zone_chx_rubriques').hide(0);
								$('#zone_action_classe').show(0);
							}
						}
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_imprimer] Lancer l'impression pour une liste d'élèves
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		function imprimer(etape)
		{
			$('#ajax_msg_imprimer').removeAttr("class").addClass("loader").html("Envoi en cours&hellip; Étape "+etape+"/4.");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'imprimer'+'&f_etape='+etape+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
					dataType : "html",
					error : function(jqXHR, textStatus, errorThrown)
					{
						var message = (jqXHR.status!=500) ? 'Échec de la connexion !' : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
						$('#ajax_msg_imprimer').removeAttr("class").addClass("alerte").html(message);
						$('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',false);
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if( ( (etape<4) && (responseHTML!='ok') ) || ( (etape==4) && (responseHTML.substring(0,4)!='<ul ') ) )
						{
							$('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',false);
							$('#ajax_msg_imprimer').removeAttr("class").addClass("alerte").html(responseHTML);
						}
						else if(etape<4)
						{
							etape++;
							imprimer(etape);
						}
						else
						{
							$('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',false);
							tab_listing_id = $('#f_listing_eleves').val().split(',');
							for ( var key in tab_listing_id )
							{
								$('#id_'+tab_listing_id[key]).children('td:first').children('input').prop('checked',false);
								$('#id_'+tab_listing_id[key]).children('td:last').html('Oui, le '+TODAY_FR);
							}
							$('#ajax_msg_imprimer').removeAttr("class").addClass("valide").html("Documents ci-dessous.");
							$('#imprimer_liens').html(responseHTML);
							format_liens('#imprimer_liens');
						}
					}
				}
			);
		}

		$('#valider_imprimer').click
		(
			function()
			{
				$('#imprimer_liens').html('');
				var listing_id = new Array(); $("#form_choix_eleves input[type=checkbox]:checked").each(function(){listing_id.push($(this).val());});
				if(!listing_id.length)
				{
					$('#ajax_msg_imprimer').removeAttr("class").addClass("erreur").html("Aucun élève coché !");
					return false;
				}
				$('#f_listing_eleves').val(listing_id);
				$('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',true);
				imprimer(1);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_imprimer] Charger la liste de choix des élèves, et si les bulletins sont déjà imprimés
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		function charger_formulaire_imprimer()
		{
			$('#imprimer_liens').html('');
			var colspan = (memo_objet=='imprimer') ? 3 : 2 ;
			$('#zone_'+memo_objet+' table tbody').html('<tr><td class="nu" colspan="'+colspan+'"></td></tr>');
			$('#zone_voir_archive table tbody').html('<tr><td class="nu" colspan="2"></td></tr>');
			$('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',true);
			$('#ajax_msg_'+memo_objet).removeAttr("class").addClass("loader").html("Envoi en cours&hellip;");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_action='+'initialiser'+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
					dataType : "html",
					error : function(jqXHR, textStatus, errorThrown)
					{
						$('#ajax_msg_'+memo_objet).removeAttr("class").addClass("alerte").html("Échec de la connexion !");
						$('#form_choix_classe button , #form_choix_classe select').prop('disabled',false);
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,3)!='<tr')
						{
							$('#ajax_msg_'+memo_objet).removeAttr("class").addClass("alerte").html(responseHTML);
							$('#form_choix_classe button , #form_choix_classe select').prop('disabled',false);
						}
						else
						{
							masquer_element_navigation_choix_classe();
							$('#zone_'+memo_objet+' table tbody').html(responseHTML);
							$('#ajax_msg_'+memo_objet).removeAttr("class").html("");
							$('#form_choix_classe button , #form_choix_classe select , #valider_imprimer').prop('disabled',false);
							format_liens('#zone_voir_archive');
						}
					}
				}
			);
		}

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_examiner|officiel_imprimer] Actualiser l'état enabled/disabled des options du formulaire de navigation dans les classes, masquer les boutons de navigation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		function masquer_element_navigation_choix_classe()
		{
			$('#go_selection_classe option[value='+memo_classe+'_'+memo_groupe+']').prop('selected',true);
			$('#form_choix_classe button').css('visibility','visible');
			if( memo_classe+'_'+memo_groupe == memo_classe_first )
			{
				$('#go_precedent_classe').css('visibility','hidden');
			}
			if( memo_classe+'_'+memo_groupe == memo_classe_last )
			{
				$('#go_suivant_classe').css('visibility','hidden');
			}
		}

		// La recherche de la bonne option après appui sur "classe précédente" ou "classe suivante" n'est pas évident à cause des options désactivées.
		// D'où la mise en place de deux tableaux supplémentaires :
		var tab_id_option_to_numero = new Array();
		var tab_numero_to_id_option = new Array();

		function configurer_form_choix_classe()
		{
			var numero = 0;
			tab_id_option_to_numero = new Array();
			tab_numero_to_id_option = new Array();
			var indice = (memo_section=='officiel_examiner') ? 'examiner' : 'imprimer' ;
			$('#go_selection_classe option').each
			(
				function()
				{
					var id_option = $(this).val();
					var etat = tab_disabled[indice][id_option+'_'+memo_periode];
					$(this).prop( 'disabled' , etat );
					if(etat==false)
					{
						numero++;
						tab_id_option_to_numero[id_option] = [numero];
						tab_numero_to_id_option[numero] = [id_option];
					}
				}
			);
			memo_classe_first = tab_numero_to_id_option[1];
			memo_classe_last = tab_numero_to_id_option[numero];
		}

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_examiner|officiel_imprimer] Navigation d'une classe à une autre
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		function charger_nouvelle_classe(classe_groupe_id)
		{
			if( classe_groupe_id == memo_classe+'_'+memo_groupe )
			{
				return false;
			}
			var tab_indices = classe_groupe_id.toString().split('_'); // Sans toString() on obtient "error: split is not a function"
			memo_classe = tab_indices[0];
			memo_groupe = tab_indices[1];
			if(memo_section=='officiel_imprimer')
			{
				charger_formulaire_imprimer();
			}
			else if(memo_section=='officiel_examiner')
			{
				$('#form_choix_classe button , #form_choix_classe select').prop('disabled',true);
				$('#zone_resultat_classe').html('<label class="loader">Envoi en cours&hellip;</label>');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_section='+memo_section+'&f_page='+BILAN_TYPE+'&f_bilan_type='+BILAN_TYPE+'&f_classe='+memo_classe+'&f_groupe='+memo_groupe+'&f_periode='+memo_periode+'&'+$('#form_hidden').serialize(),
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('#zone_resultat_classe').html('<label class="alerte">Échec de la connexion !</label>');
							$('#form_choix_classe button , #form_choix_classe select').prop('disabled',false);
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('#form_choix_classe button , #form_choix_classe select').prop('disabled',false);
							if(responseHTML.substring(0,14)!='<p class="ti">')
							{
								$('#zone_resultat_classe').html('<label class="alerte">'+responseHTML+'</label>');
							}
							else
							{
								masquer_element_navigation_choix_classe();
								$('#zone_resultat_classe').html(responseHTML);
							}
						}
					}
				);
			}
		}

		$('#go_precedent_classe').click
		(
			function()
			{
				var id_option = $('#go_selection_classe option:selected').val();
				var numero = tab_id_option_to_numero[id_option];
				numero--;
				if( tab_numero_to_id_option[numero].length )
				{
					charger_nouvelle_classe( tab_numero_to_id_option[numero] );
				}
			}
		);

		$('#go_suivant_classe').click
		(
			function()
			{
				var id_option = $('#go_selection_classe option:selected').val();
				var numero = tab_id_option_to_numero[id_option];
				numero++;
				if( tab_numero_to_id_option[numero].length )
				{
					charger_nouvelle_classe( tab_numero_to_id_option[numero] );
				}
			}
		);

		$('#go_selection_classe').change
		(
			function()
			{
				var classe_groupe_id = $('#go_selection_classe option:selected').val();
				charger_nouvelle_classe(classe_groupe_id);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir|officiel_consulter] Afficher le formulaire pour signaler une erreur
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#zone_resultat_eleve button.signaler').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var tab_ids = $(this).closest('tr').attr('id').split('_');
				var prof_id = parseInt( tab_ids[2] , 10 );
				var message_contenu = $('h1').text().substring(2)+' - '+$('#periode_'+memo_periode).text()+' - '+$('#groupe_'+memo_classe+'_'+memo_groupe).text()+"\n"+'Concernant '+$('#go_selection_eleve option:selected').text()+' il y a un souci dans son appréciation : "'+$(this).parent().next().html()+'" &hellip;';
				$('#f_destinataires_liste').val(prof_id);
				// Afficher la zone
				$.fancybox( { 'href':'#zone_signaler' , onStart:function(){$('#zone_signaler').css("display","block");} , onClosed:function(){$('#zone_signaler').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
				$('#f_message_contenu').focus().html(message_contenu);
				afficher_textarea_reste( $('#f_message_contenu') , 255 );
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir|officiel_consulter] Indiquer le nombre de caractères restant autorisés dans le textarea
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#f_message_contenu').keyup
		(
			function()
			{
				afficher_textarea_reste($(this),255);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir|officiel_consulter] Clic sur le bouton pour fermer le cadre de rédaction d'un signalement d'erreur (annuler / retour)
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#annuler_signaler').click
		(
			function()
			{
				$.fancybox.close();
				return(false);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// [officiel_saisir|officiel_consulter] Valider le formulaire pour signaler une erreur
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#valider_signaler').click
		(
			function()
			{
				$('#zone_signaler button').prop('disabled',true);
				$('#ajax_msg_signaler').removeAttr("class").addClass("loader").html("Envoi en cours&hellip;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&'+$('#zone_signaler').serialize(),
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('#ajax_msg_signaler').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
							$('#zone_signaler button').prop('disabled',false);
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('#zone_signaler button').prop('disabled',false);
							if(responseHTML.substring(0,4)!='<tr ')
							{
								$('#ajax_msg_signaler').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_signaler').removeAttr("class").addClass("valide").html('Message enregistré.');
								$('#annuler_signaler').click();
							}
						}
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Options de base pour le graphique : sont complétées ensuite avec les données personnalisées
		// http://www.highcharts.com/documentation/how-to-use
		// http://www.highcharts.com/ref
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		ChartOptions = {
			chart: {
				renderTo: 'div_graphique',
				type: 'column'
			 },
			colors: [
				BACKGROUND_A,
				BACKGROUND_VA,
				BACKGROUND_NA
			],
			title: {
				style: { color: '#333' } ,
				text: null // Pourrait être MAJ ensuite
			},
			xAxis: {
				labels: { style: { color: '#000' } },
				categories: [] // MAJ ensuite
			},
			yAxis: [
				{
					labels: { enabled: false },
					min: 0,
					max: 100,
					title: { style: { color: '#333' } , text: 'Items acquis' }
				}, {} // MAJ ensuite
			],
			tooltip: {
				formatter: function() {
					return this.series.name +' : '+ (this.y);
				}
			},
			plotOptions: {
				column: {
					stacking: 'percent'
				}
			},
			series: [] // MAJ ensuite
			,
			credits: {
				enabled: false
			}
		};

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Afficher / Masquer la photo d'un élève (module bulletin)
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		function charger_photo_eleve()
		{
			$("#cadre_photo").html('<label id="ajax_photo" class="loader">Envoi en cours&hellip;</label>');
			$.ajax
			(
				{
					type : 'GET',
					url : 'ajax.php?page=calque_voir_photo',
					data : 'user_id='+memo_eleve,
					dataType : "html",
					error : function(jqXHR, textStatus, errorThrown)
					{
						$('#ajax_photo').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
						leave_erreur = true;
					},
					success : function(responseHTML)
					{
						if(responseHTML.substring(0,5)=='<img ')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#cadre_photo').html('<div>'+responseHTML+'</div><button id="fermer_calque_photo" type="button" class="annuler">Fermer</button>');
							leave_erreur = false;
						}
						else
						{
							$('#ajax_photo').removeAttr("class").addClass("alerte").html(responseHTML);
							leave_erreur = true;
						}
					}
				}
			);
		}

		$('#voir_photo').live
		('click',
			function()
			{
				charger_photo_eleve();
			}
		);

		$('#fermer_calque_photo').live
		('click',
			function()
			{
				$('#cadre_photo').html('<button id="voir_photo" type="button" class="voir_photo">Photo</button>');
			}
		);

	}
);
