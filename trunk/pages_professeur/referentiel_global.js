/**
 * @version $Id: referentiel_global.js 8 2009-10-30 20:56:02Z thomas $
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

// jQuery !
$(document).ready
(
	function()
	{

		var select_partage = '<select id="f_partage" name="f_partage"><option value="oui">Rendre ce référentiel accessible par d\'autres établissements.</option><option value="bof">Référentiel dont le partage sans intérêt (pas novateur).</option><option value="non">Cacher ce référentiel aux autres établissements.</option></select>';

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Voir un référentiel de son établissement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('table.comp_view q.voir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				ids = $(this).parent().attr('id');
				afficher_masquer_images_action('hide');
				new_label = '<label for="'+ids+'" class="loader">Demande envoyée... Veuillez patienter.</label>';
				$(this).after(new_label);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'action=Voir&ids='+ids,
						dataType : "html",
						error : function(msg,string)
						{
							$('label[for='+ids+']').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.').fadeOut(2000,function(){$('label[for='+ids+']').remove();afficher_masquer_images_action('show');});
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,18)!='<ul class="ul_m1">')
							{
								$('label[for='+ids+']').removeAttr("class").addClass("alerte").html(responseHTML).fadeOut(2000,function(){$('label[for='+ids+']').remove();afficher_masquer_images_action('show');});
							}
							else
							{
								$('#voir_referentiel').html(responseHTML+'<p />');
								infobulle();
								$('label[for='+ids+']').removeAttr("class").addClass("valide").html("Contenu affiché ci-dessous !").fadeOut(2000,function(){$('label[for='+ids+']').remove();afficher_masquer_images_action('show');});
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Modifier le partage d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('q.partager').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				afficher_masquer_images_action('hide');
				ids     = $(this).parent().attr('id');
				partage = $(this).parent().prev().attr('lang');
				new_span = '<span>'+select_partage.replace('"'+partage+'"','"'+partage+'" selected="selected"')+'<q class="valider" lang="partager" title="Valider les modifications du partage de ce référentiel."></q><q class="annuler" title="Annuler la modification du partage de ce référentiel."></q> <label id="ajax_msg">&nbsp;</label></span>';
				$(this).after(new_span);
				infobulle();
				mode = 'partager';
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Valider la modification du partage d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('q.valider[lang=partager]').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				ids = $(this).parent().parent().attr('id');
				partage = $('#f_partage').val();
				$('#ajax_msg').removeAttr("class").addClass("loader").html('Demande envoyée... Veuillez patienter.');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'action=Partager&ids='+ids+'&partage='+partage,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,10)!='<img title')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#'+ids).prev().attr('lang',partage).html('Référentiel présent. '+responseHTML);
								$('#ajax_msg').parent().remove();
								afficher_masquer_images_action('show');
								infobulle();
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Retirer un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('q.supprimer').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				afficher_masquer_images_action('hide');
				ids = $(this).parent().attr('id');
				new_span = '<span class="danger">Tous les items et les résultats associés des élèves seront perdus !<q class="valider" lang="retirer" title="Confirmer la suppression de ce référentiel."></q><q class="annuler" title="Annuler la suppression de ce référentiel."></q> <label id="ajax_msg">&nbsp;</label></span>';
				$(this).after(new_span);
				infobulle();
				mode = 'retirer';
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Valider la suppression d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('q.valider[lang=retirer]').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				ids = $(this).parent().parent().attr('id');
				$('#ajax_msg').removeAttr("class").addClass("loader").html('Demande envoyée... Veuillez patienter.');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'action=Retirer&ids='+ids,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML!='ok')
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								proposition = (ids.substring(0,5)=='ids_1') ? '' : ' ou importer un référentiel existant' ;
								$('#'+ids).html('<q class="ajouter" title="Créer un référentiel vierge'+proposition+'."></q>');
								$('#'+ids).prev().removeAttr("class").addClass("r").html('Absence de référentiel.');
								afficher_masquer_images_action('show');
								infobulle();
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Ajouter un référentiel ; cas d'une matière spécifique à l'établissement traité, ou chargement de choisir_referentiel sinon
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('q.ajouter').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				ids = $(this).parent().attr('id');
				if(ids.substring(0,5)=='ids_1')
				{
					// C'est une matière spécifique à l'établissement : on ne peut que créer un nouveau référentiel
					afficher_masquer_images_action('hide');
					new_span = '<span><input id="succes" name="succes" type="hidden" value="0" /><label for="'+ids+'" class="loader">Demande envoyée... Veuillez patienter.</label></span>';
					$(this).after(new_span);
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
							data : 'action=Ajouter&ids='+ids,
							dataType : "html",
							error : function(msg,string)
							{
								$('label[for='+ids+']').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.').fadeOut(2000,function(){$('label[for='+ids+']').remove();afficher_masquer_images_action('show');});
								return false;
							},
							success : function(responseHTML)
							{
								maj_clock(1);
								if(responseHTML!='ok')
								{
									$('label[for='+ids+']').removeAttr("class").addClass("alerte").html(responseHTML).fadeOut(2000,function(){$('label[for='+ids+']').remove();afficher_masquer_images_action('show');});
								}
								else
								{
									$('#'+ids).html('<q class="voir" title="Voir le détail de ce référentiel."></q><q class="partager_non" title="Le référentiel d\'une matière spécifique à l\'établissement ne peut être partagé."></q><q class="supprimer" title="Supprimer ce référentiel."></q>');
									$('#'+ids).prev().removeAttr("class").addClass("v").attr('lang','hs').html('Référentiel présent. <img title="Référentiel dont le partage est sans objet (matière spécifique)." src="./_img/partage0.gif" />');
									afficher_masquer_images_action('show');
									infobulle();
								}
							}
						}
					);
				}
				else
				{
					// C'est une matière commune : on propose d'importer un référentiel partagé existant
					$('#voir_referentiel').html("&nbsp;");
					afficher_masquer_images_action('hide');
					new_span = '<span><input id="succes" name="succes" type="hidden" value="" /><label for="'+ids+'" class="loader">Demande envoyée... Veuillez patienter.</label></span>';
					$(this).after(new_span);
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
							data : 'action=Lister&ids='+ids,
							dataType : "html",
							error : function(msg,string)
							{
								$('label[for='+ids+']').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.').fadeOut(2000,function(){$('label[for='+ids+']').remove();afficher_masquer_images_action('show');});
								return false;
							},
							success : function(responseHTML)
							{
								maj_clock(1);
								if(responseHTML.substring(0,4)!='<li>')
								{
									$('label[for='+ids+']').removeAttr("class").addClass("alerte").html(responseHTML).fadeOut(2000,function(){$('label[for='+ids+']').remove();afficher_masquer_images_action('show');});
								}
								else
								{
									$('label[for='+ids+']').removeAttr("class").addClass("valide").html("Référentiels disponibles affichés ci-dessous...");
									tab_ids = ids.split('_');
									$('#f_matiere').val(tab_ids[2]);
									$('#f_niveau').val(tab_ids[3]);
									$('#ajax_msg_choisir').html("&nbsp;");
									$('#choisir_referentiel ul').html('<li><input id="etabl_0" name="donneur" type="radio" value="0" /><label for="etabl_0"> Référentiel vierge.</label></li>'+responseHTML);
									$('#choisir_referentiel').show();
									infobulle();
								}
							}
						}
					);
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Changement de matière -> desactiver les niveaux classiques en cas de matière transversale
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#f_matiere').change
		(
			function()
			{
				modif_niveau_selected = 0; // 0 = pas besoin modifier / 1 = à modifier / 2 = déjà modifié
				matiere_id = $('#f_matiere').val();
				$("#f_niveau option").each
				(
					function()
					{
						niveau_id = $(this).val();
						findme = '.'+niveau_id+'.';
						// Les niveaux "paliers" sont tout le temps accessibles
						if('..46.47.48.49.'.indexOf(findme)==-1)
						{
							// matière classique -> tous niveaux actifs
							if(matiere_id!=99)
							{
								$(this).removeAttr('disabled');
							}
							// matière transversale -> desactiver les autres niveaux
							else
							{
								$(this).attr('disabled',true);
								modif_niveau_selected = Math.max(modif_niveau_selected,1);
							}
						}
						// C'est un niveau palier ; le sélectionner si besoin
						else if(modif_niveau_selected==1)
						{
							$(this).attr('selected',true);
							modif_niveau_selected = 2;
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour chercher des référentiels partagés sur d'autres niveaux ou matières
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#f_submit_lister').click
		(
			function()
			{
				$('#voir_referentiel').html("&nbsp;");
				matiere_id = $('#f_matiere').val();
				niveau_id  = $('#f_niveau').val();
				if(!matiere_id)
				{
					$('#ajax_msg_actualiser').removeAttr("class").addClass("erreur").html("Il faut choisir une matière !");
					return false;
				}
				else if(!niveau_id)
				{
					$('#ajax_msg_actualiser').removeAttr("class").addClass("erreur").html("Il faut choisir un niveau !");
					return false;
				}
				$('#ajax_msg_actualiser').removeAttr("class").addClass("loader").html('Demande envoyée... Veuillez patienter.');
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'action=Lister&ids='+ids+'&matiere_id='+matiere_id+'&niveau_id='+niveau_id,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg_actualiser').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,4)!='<li>')
							{
								$('#ajax_msg_actualiser').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_actualiser').removeAttr("class").html("&nbsp;");
								$('#choisir_referentiel ul').html('<li><input id="etabl_0" name="donneur" type="radio" value="0" /><label for="etabl_0"> Référentiel vierge.</label></li>'+responseHTML);
								infobulle();
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Voir un référentiel d'un autre établissement
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#choisir_referentiel q.voir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				ids = $('#succes').parent().parent().attr('id');
				matiere_id = $('#f_matiere').val();
				niveau_id  = $('#f_niveau').val();
				matiere = $('#f_matiere option:selected').text();
				niveau = $('#f_niveau option:selected').text();
				donneur = $(this).parent().children('input').val();
				etabl   = $(this).parent().children('label').text();
				new_label = '<label id="temp" class="loader">Demande envoyée... Veuillez patienter.</label>';
				$(this).after(new_label);
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'action=Voir&ids='+ids+'&donneur='+donneur+'&matiere_id='+matiere_id+'&niveau_id='+niveau_id,
						dataType : "html",
						error : function(msg,string)
						{
							$('label[id=temp]').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.').fadeOut(2000,function(){$('label[id=temp]').remove();});
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,18)!='<ul class="ul_m1">')
							{
								$('label[id=temp]').removeAttr("class").addClass("alerte").html(responseHTML).fadeOut(2000,function(){$('label[id=temp]').remove();});
							}
							else
							{
								$('#voir_referentiel').html('<h2>'+etabl+'</h2>'+responseHTML+'<p />');
								infobulle();
								$('label[id=temp]').removeAttr("class").addClass("valide").html("Contenu affiché ci-dessous !").fadeOut(2000,function(){$('label[id=temp]').remove();});
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Valider le choix d'un referentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('a.Valider_choisir').click
		(
			function()
			{
				ids = $('#succes').parent().parent().attr('id');
				matiere_id = $('#f_matiere').val();
				niveau_id  = $('#f_niveau').val();
				donneur = $("#choisir_referentiel input[type=radio]:checked").val();
				if(isNaN(donneur))	// normalement impossible, sauf si par exemple on triche avec la barre d'outils Web Developer...
				{
					$('#ajax_msg_choisir').removeAttr("class").addClass("erreur").html("Veuillez cocher un bouton !");
					return false;
				}
				$('#ajax_msg_choisir').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'action=Ajouter&ids='+ids+'&donneur='+donneur+'&matiere_id='+matiere_id+'&niveau_id='+niveau_id,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg_choisir').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_choisir').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#'+ids).html('<q class="voir" title="Voir le détail de ce référentiel."></q><q class="partager" title="Modifier le partage de ce référentiel."></q><q class="supprimer" title="Supprimer ce référentiel."></q>');
								if(donneur>0)
								{
									$('#'+ids).prev().removeAttr("class").addClass("v").attr('lang','bof').html('Référentiel présent. <img title="Référentiel dont le partage est sans intérêt (pas novateur)." src="./_img/partage0.gif" />');
								}
								else
								{
									$('#'+ids).prev().removeAttr("class").addClass("v").attr('lang','non').html('Référentiel présent. <img title="Référentiel caché aux autres établissements." src="./_img/partage0.gif" />');
								}
								infobulle();
								$('a.Annuler_choisir').click();
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Annuler le choix d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('a.Annuler_choisir').click
		(
			function()
			{
				$('#voir_referentiel').html("&nbsp;");
				$('#choisir_referentiel').hide();
				$('#choisir_referentiel ul.ul_m2').html("&nbsp;");
				$('#ajax_msg_choisir').removeAttr("class").html("&nbsp;");
				$('#succes').parent().remove();
				afficher_masquer_images_action('show');
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur l'image pour Annuler la suppression ou la modification du partage d'un référentiel
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('q.annuler').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$(this).parent().remove();
				afficher_masquer_images_action('show');
			}
		);

	}
);
