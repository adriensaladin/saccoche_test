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

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Initialisation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		var mode = false;
		var modification = false;
		var memo_pilotage = 'clavier';
		var memo_direction = 'down';
		var memo_input_id = false;
		var colonne = 1;
		var ligne   = 1;
		var nb_colonnes = 1;
		var nb_lignes   = 1;
		// tri du tableau (avec jquery.tablesorter.js).
		if(TYPE=='groupe')
		{
			var sorting = [[0,1],[3,0]];
			$('table.form').tablesorter({ headers:{1:{sorter:false},2:{sorter:false},5:{sorter:false},6:{sorter:false},7:{sorter:false},8:{sorter:false}} });
		}
		else
		{
			var sorting = [[0,1],[4,0]];
			$('table.form').tablesorter({ headers:{1:{sorter:false},2:{sorter:false},3:{sorter:false},5:{sorter:false},6:{sorter:false},7:{sorter:false},8:{sorter:false}} });
		}
		function trier_tableau()
		{
			if($('table.form tbody tr td').length>1)
			{
				$('table.form').trigger('update');
				$('table.form').trigger('sorton',[sorting]);
			}
		}
		trier_tableau();

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Fonctions utilisées
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		function activer_boutons_upload(ref)
		{
			$('#zone_upload button').prop('disabled',false);
			if(!tab_sujets[ref])     {$('#bouton_supprimer_sujet').prop('disabled',true);}
			if(!tab_corriges[ref]) {$('#bouton_supprimer_corrige').prop('disabled',true);}
		}

		/**
		 * Ajouter une évaluation : mise en place du formulaire
		 * @return void
		 */
		var ajouter = function()
		{
			mode = $(this).attr('class');
			// Report des valeurs transmises via un formulaire depuis un tableau de synthèse bilan
			if(reception_todo)
			{
				reception_todo = false;
			}
			else
			{
				reception_users_texte  = 'aucun';
				reception_items_texte = 'aucun';
				reception_users_liste  = '';
				reception_items_liste = '';
			}
			// Fabriquer la ligne avec les éléments de formulaires
			afficher_masquer_images_action('hide');
			$('#form0').css('visibility','hidden');
			var new_tr = '';
			new_tr += '<tr>';
			new_tr += '<td><input id="f_date" name="f_date" size="8" type="text" value="'+input_date+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></td>';
			new_tr += '<td><input id="box_date" type="checkbox" checked style="vertical-align:-3px" /> <span style="vertical-align:-2px">identique</span><span class="hide"><input id="f_date_visible" name="f_date_visible" size="8" type="text" value="'+input_date+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></span></td>';
			new_tr += '<td><input id="box_autoeval" type="checkbox" checked style="vertical-align:-3px" /> <span style="vertical-align:-2px">sans objet</span><span class="hide"><input id="f_date_autoeval" name="f_date_autoeval" size="8" type="text" value="00/00/0000" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></span></td>';
			if(TYPE=='groupe')
			{
				var groupe = $('#f_aff_classe option:selected').val();
				new_tr += '<td><select id="f_groupe" name="f_groupe">'+select_groupe.replace('value="'+groupe+'"','value="'+groupe+'" selected')+'</select></td>';
			}
			else
			{
				new_tr += '<td><input id="f_eleve_nombre" name="f_eleve_nombre" size="6" type="text" value="'+reception_users_texte+'" readonly /><input id="f_eleve_liste" name="f_eleve_liste" type="hidden" value="'+reception_users_liste+'" /><q class="choisir_eleve" title="Voir ou choisir les élèves."></q></td>';
			}
			new_tr += '<td><input id="f_description" name="f_description" size="20" type="text" value="" /></td>';
			new_tr += '<td><input id="f_compet_nombre" name="f_compet_nombre" size="6" type="text" value="'+reception_items_texte+'" readonly /><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="'+reception_items_liste+'" /><q class="choisir_compet" title="Voir ou choisir les items."></q></td>';
			new_tr += '<td><input id="f_prof_nombre" name="f_prof_nombre" size="6" type="text" value="moi seul" readonly /><input id="f_prof_liste" name="f_prof_liste" type="hidden" value="" /><q class="choisir_prof" title="Voir ou choisir les collègues."></q></td>';
			new_tr += '<td><img alt="" src="./_img/document/sujet_non.png" /><input id="f_doc_sujet" name="f_doc_sujet" type="hidden" value="" /><img alt="" src="./_img/document/corrige_non.png" /><input id="f_doc_corrige" name="f_doc_corrige" type="hidden" value="" /></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_type" name="f_type" type="hidden" value="'+TYPE+'" /><q class="valider" title="Valider l\'ajout de cette évaluation."></q><q class="annuler" title="Annuler l\'ajout de cette évaluation."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Ajouter cette nouvelle ligne
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_description').focus();
		};

		/**
		 * Modifier une évaluation : mise en place du formulaire
		 * @return void
		 */
		var modifier = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			$('#form0').css('visibility','hidden');
			// Récupérer les informations de la ligne concernée
			var ref           = $(this).parent().attr('id').substring(7); // "devoir_" + ref
			var date          = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().html();
			var date_visible  = $(this).parent().prev().prev().prev().prev().prev().prev().prev().html();
			var date_autoeval = $(this).parent().prev().prev().prev().prev().prev().prev().html();
			if(TYPE=='groupe')
			{
				var groupe        = $(this).parent().prev().prev().prev().prev().prev().html();
			}
			else
			{
				var eleve_nombre  = $(this).parent().prev().prev().prev().prev().prev().html();
				var eleve_liste   = tab_eleves[ref];
			}
			var description   = $(this).parent().prev().prev().prev().prev().html();
			var compet_nombre = $(this).parent().prev().prev().prev().html();
			var compet_liste  = tab_items[ref];
			var prof_nombre   = $(this).parent().prev().prev().html();
			var prof_liste    = tab_profs[ref];
			var date_fr       = date.substring(17,date.length); // enlever la date mysql cachée
			if(date_visible=='identique') { var date_checked = ' checked'; var date_classe1 = ''; var date_classe2 = ' class="hide"'; date_visible = date_fr; }
			else                          { var date_checked = '';         var date_classe2 = ''; var date_classe1 = ' class="hide"'; }
			if(date_autoeval=='sans objet') { var autoeval_checked = ' checked'; var autoeval_classe1 = ''; var autoeval_classe2 = ' class="hide"'; date_autoeval = '00/00/0000'; }
			else                            { var autoeval_checked = '';         var autoeval_classe2 = ''; var autoeval_classe1 = ' class="hide"'; }
			var img_sujet   = (tab_sujets[ref])   ? '<img alt="" src="./_img/document/sujet_oui.png" />'   : '<img alt="" src="./_img/document/sujet_non.png" />' ;
			var img_corrige = (tab_corriges[ref]) ? '<img alt="" src="./_img/document/corrige_oui.png" />' : '<img alt="" src="./_img/document/corrige_non.png" />' ;
			// Fabriquer la ligne avec les éléments de formulaires
			var new_tr = '';
			new_tr += '<tr>';
			new_tr += '<td><input id="f_date" name="f_date" size="8" type="text" value="'+date_fr+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></td>';
			new_tr += '<td><input id="box_date" type="checkbox"'+date_checked+' style="vertical-align:-3px" /> <span'+date_classe1+' style="vertical-align:-2px">identique</span><span'+date_classe2+'><input id="f_date_visible" name="f_date_visible" size="8" type="text" value="'+date_visible+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></span></td>';
			new_tr += '<td><input id="box_autoeval" type="checkbox"'+autoeval_checked+' style="vertical-align:-3px" /> <span'+autoeval_classe1+' style="vertical-align:-2px">sans objet</span><span'+autoeval_classe2+'><input id="f_date_autoeval" name="f_date_autoeval" size="8" type="text" value="'+date_autoeval+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></span></td>';
			if(TYPE=='groupe')
			{
				new_tr += '<td>'+groupe+'<select id="f_groupe" name="f_groupe" class="hide">'+select_groupe.replace('>'+groupe+'<',' selected>'+groupe+'<')+'</select></td>';
			}
			else
			{
				new_tr += '<td><input id="f_eleve_nombre" name="f_eleve_nombre" size="6" type="text" value="'+eleve_nombre+'" readonly /><input id="f_eleve_liste" name="f_eleve_liste" type="hidden" value="'+eleve_liste+'" /><q class="choisir_eleve" title="Voir ou choisir les élèves."></q></td>';
			}
			new_tr += '<td><input id="f_description" name="f_description" size="'+Math.max(description.length,20)+'" type="text" value="'+escapeQuote(description)+'" /></td>';
			new_tr += '<td><input id="f_compet_nombre" name="f_compet_nombre" size="6" type="text" value="'+compet_nombre+'" readonly /><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="'+compet_liste+'" /><q class="choisir_compet" title="Voir ou choisir les items."></q></td>';
			new_tr += '<td><input id="f_prof_nombre" name="f_prof_nombre" size="6" type="text" value="'+prof_nombre+'" readonly /><input id="f_prof_liste" name="f_prof_liste" type="hidden" value="'+prof_liste+'" /><q class="choisir_prof" title="Voir ou choisir les collègues."></q></td>';
			new_tr += '<td>'+img_sujet+'<input id="f_doc_sujet" name="f_doc_sujet" type="hidden" value="'+tab_sujets[ref]+'" />'+img_corrige+'<input id="f_doc_corrige" name="f_doc_corrige" type="hidden" value="'+tab_corriges[ref]+'" /></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_type" name="f_type" type="hidden" value="'+TYPE+'" /><input id="f_ref" name="f_ref" type="hidden" value="'+ref+'" /><q class="valider" title="Valider les modifications de cette évaluation."></q><q class="annuler" title="Annuler les modifications de cette évaluation."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Cacher la ligne en cours et ajouter la nouvelle
			$(this).parent().parent().hide();
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_description').focus();
		};

		/**
		 * Dupliquer une évaluation : mise en place du formulaire
		 * @return void
		 */
		var dupliquer = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			$('#form0').css('visibility','hidden');
			// Récupérer les informations de la ligne concernée
			var ref           = $(this).parent().attr('id').substring(7); // "devoir_" + ref
			var date          = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().html();
			var date_visible  = $(this).parent().prev().prev().prev().prev().prev().prev().prev().html();
			var date_autoeval = $(this).parent().prev().prev().prev().prev().prev().prev().html();
			if(TYPE=='groupe')
			{
				var groupe        = $(this).parent().prev().prev().prev().prev().prev().html();
			}
			else
			{
				var eleve_nombre  = $(this).parent().prev().prev().prev().prev().prev().html();
				var eleve_liste   = tab_eleves[ref];
			}
			var description   = $(this).parent().prev().prev().prev().prev().html();
			var compet_nombre = $(this).parent().prev().prev().prev().html();
			var compet_liste  = tab_items[ref];
			var prof_nombre   = $(this).parent().prev().prev().html();
			var prof_liste    = tab_profs[ref];
			var date_fr       = date.substring(17,date.length); // enlever la date mysql cachée
			if(date_visible=='identique') { var date_checked = ' checked'; var date_classe1 = ''; var date_classe2 = ' class="hide"'; date_visible = date_fr; }
			else                          { var date_checked = '';         var date_classe2 = ''; var date_classe1 = ' class="hide"'; }
			if(date_autoeval=='sans objet') { var autoeval_checked = ' checked'; var autoeval_classe1 = ''; var autoeval_classe2 = ' class="hide"'; date_autoeval = '00/00/0000'; }
			else                            { var autoeval_checked = '';         var autoeval_classe2 = ''; var autoeval_classe1 = ' class="hide"'; }
			var img_sujet   = (tab_sujets[ref])   ? '<img alt="" src="./_img/document/sujet_oui.png" />'   : '<img alt="" src="./_img/document/sujet_non.png" />' ;
			var img_corrige = (tab_corriges[ref]) ? '<img alt="" src="./_img/document/corrige_oui.png" />' : '<img alt="" src="./_img/document/corrige_non.png" />' ;
			// Fabriquer la ligne avec les éléments de formulaires
			var new_tr = '';
			new_tr += '<tr>';
			new_tr += '<td><input id="f_date" name="f_date" size="8" type="text" value="'+date_fr+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></td>';
			new_tr += '<td><input id="box_date" type="checkbox"'+date_checked+' style="vertical-align:-3px" /> <span'+date_classe1+' style="vertical-align:-2px">identique</span><span'+date_classe2+'><input id="f_date_visible" name="f_date_visible" size="8" type="text" value="'+date_visible+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></span></td>';
			new_tr += '<td><input id="box_autoeval" type="checkbox"'+autoeval_checked+' style="vertical-align:-3px" /> <span'+autoeval_classe1+' style="vertical-align:-2px">sans objet</span><span'+autoeval_classe2+'><input id="f_date_autoeval" name="f_date_autoeval" size="8" type="text" value="'+date_autoeval+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></span></td>';
			if(TYPE=='groupe')
			{
				new_tr += '<td><select id="f_groupe" name="f_groupe">'+select_groupe.replace('>'+groupe+'<',' selected>'+groupe+'<')+'</select></td>';
			}
			else
			{
				new_tr += '<td><input id="f_eleve_nombre" name="f_eleve_nombre" size="6" type="text" value="'+eleve_nombre+'" readonly /><input id="f_eleve_liste" name="f_eleve_liste" type="hidden" value="'+eleve_liste+'" /><q class="choisir_eleve" title="Voir ou choisir les élèves."></q></td>';
			}
			new_tr += '<td><input id="f_description" name="f_description" size="'+Math.max(description.length,20)+'" type="text" value="'+escapeQuote(description)+'" /></td>';
			new_tr += '<td><input id="f_compet_nombre" name="f_compet_nombre" size="6" type="text" value="'+compet_nombre+'" readonly /><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="'+compet_liste+'" /><q class="choisir_compet" title="Voir ou choisir les items."></q></td>';
			new_tr += '<td><input id="f_prof_nombre" name="f_prof_nombre" size="6" type="text" value="'+prof_nombre+'" readonly /><input id="f_prof_liste" name="f_prof_liste" type="hidden" value="'+prof_liste+'" /><q class="choisir_prof" title="Voir ou choisir les collègues."></q></td>';
			new_tr += '<td>'+img_sujet+'<input id="f_doc_sujet" name="f_doc_sujet" type="hidden" value="'+tab_sujets[ref]+'" />'+img_corrige+'<input id="f_doc_corrige" name="f_doc_corrige" type="hidden" value="'+tab_corriges[ref]+'" /></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_type" name="f_type" type="hidden" value="'+TYPE+'" /><input id="f_ref" name="f_ref" type="hidden" value="'+ref+'" /><q class="valider" title="Valider l\'ajout de cette évaluation."></q><q class="annuler" title="Annuler l\'ajout de cette évaluation."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Ajouter cette nouvelle ligne
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_description').focus();
		};

		/**
		 * Supprimer une évaluation : mise en place du formulaire
		 * @return void
		 */
		var supprimer = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			$('#form0').css('visibility','hidden');
			var ref = $(this).parent().attr('id').substring(7); // "devoir_" + ref
			var new_span = '<span class="danger"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_type" name="f_type" type="hidden" value="'+TYPE+'" /><input id="f_ref" name="f_ref" type="hidden" value="'+ref+'" />Les notes seront effacées !<q class="valider" title="Confirmer la suppression de cette évaluation."></q><q class="annuler" title="Annuler la suppression de cette évaluation."></q> <label id="ajax_msg">&nbsp;</label></span>';
			$(this).after(new_span);
			infobulle();
		};

		/**
		 * Imprimer un cartouche d'une évaluation : mise en place du formulaire
		 * @return void
		 */
		var imprimer = function()
		{
			mode = $(this).attr('class');
			// Récupérer les informations de la ligne concernée
			var ref         = $(this).parent().attr('id').substring(7); // "devoir_" + ref
			var date        = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().html();
			var groupe      = $(this).parent().prev().prev().prev().prev().prev().html();
			var description = $(this).parent().prev().prev().prev().prev().html();
			var date_fr = date.substring(17,date.length); // garder la date française
			// Masquer le tableau et Afficher la zone associée
			$('#form0 , #form1').hide('fast');
			$('#zone_imprimer').css("display","block");
			$('#titre_imprimer').html('Imprimer le cartouche d\'une évaluation'+' | '+groupe+' | '+date_fr+' | '+description+'<input id="f_ref" name="f_ref" type="hidden" value="'+ref+'" /><input id="f_date_fr" name="f_date_fr" type="hidden" value="'+date_fr+'" /><input id="f_groupe_nom" name="f_groupe_nom" type="hidden" value="'+escapeQuote(groupe)+'" /><input id="f_description" name="f_description" type="hidden" value="'+escapeQuote(description)+'" />');
		};

		/**
		 * Annuler une action
		 * @return void
		 */
		var annuler = function()
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			switch (mode)
			{
				case 'ajouter':
				case 'dupliquer':
					$(this).parent().parent().remove();
					break;
				case 'modifier':
					$(this).parent().parent().remove();
					$("table.form tr").show(); // $(this).parent().parent().prev().show(); pose pb si tri du tableau entre temps
					break;
				case 'supprimer':
					$(this).parent().remove();
					break;
			}
			afficher_masquer_images_action('show');
			$('#form0').css('visibility','visible');
			mode = false;
		};

		/**
		 * Intercepter la touche entrée ou escape pour valider ou annuler les modifications
		 * @return void
		 */
		function intercepter(e)
		{
			if( (mode=='ajouter') || (mode=='dupliquer') || (mode=='modifier') || (mode=='supprimer') )
			{
				if(e.which==13)	// touche entrée
				{
					$('q.valider').click();
				}
				else if(e.which==27)	// touche escape
				{
					$('q.annuler').click();
				}
			}
		}

		/**
		 * Saisir les items acquis par les élèves à une évaluation : chargement du formulaire
		 * @return void
		 */
		var saisir = function()
		{
			mode = $(this).attr('class');
			// Récupérer les informations de la ligne concernée
			var ref          = $(this).parent().attr('id').substring(7); // "devoir_" + ref
			var date         = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().html();
			var date_visible = $(this).parent().prev().prev().prev().prev().prev().prev().prev().html();
			var groupe       = $(this).parent().prev().prev().prev().prev().prev().html();
			var description  = $(this).parent().prev().prev().prev().prev().html();
			var objet_date   = new Date();
			var date_mysql   = date.substring(3,13); // garder la date mysql
			var date_fr      = date.substring(17,date.length); // garder la date française
			// Masquer le tableau ; Afficher la zone associée et charger son contenu
			$('#form0 , #form1').hide('fast');
			$('#msg_import').removeAttr("class").html('&nbsp;');
			$('#zone_saisir').css("display","block");
			$('#titre_saisir').html('Saisir les acquisitions d\'une évaluation'+' | '+groupe+' | '+date_fr+' | '+description);
			$('#msg_saisir').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'csrf='+CSRF+'&f_action='+mode+'&f_ref='+ref+'&f_date_mysql='+date_mysql+'&f_description='+encodeURIComponent(description)+'&f_date_visible='+date_visible+'&f_groupe_nom='+encodeURIComponent(groupe)+'&f_date_fr='+encodeURIComponent(date_fr),
					dataType : "html",
					error : function(jqXHR, textStatus, errorThrown)
					{
						$('#msg_saisir').removeAttr("class").addClass("alerte").html('Échec de la connexion ! <button id="fermer_zone_saisir" type="button" class="retourner">Retour</button>');
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						var tab_response = responseHTML.split('<SEP>');
						if( (tab_response.length!=2) || (tab_response[0].substring(0,1)!='<') )
						{
							$('#msg_saisir').removeAttr("class").addClass("alerte").html(responseHTML+' <button id="fermer_zone_saisir" type="button" class="retourner">Retour</button>');
						}
						else
						{
							modification = false;
							$('#msg_saisir').removeAttr("class").html('&nbsp;');
							$('#table_saisir').html(tab_response[0]);
							$('#table_saisir tbody tr th img').css('display','none'); // .hide(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
							$('img[title]').tooltip({showURL:false});
							$('#export_file1').attr("href", url_export+'saisie_deportee_'+tab_response[1]+'.zip' );
							$('#export_file4').attr("href", url_export+'tableau_sans_notes_'+tab_response[1]+'.pdf' );
							colorer_cellules();
							format_liens('#table_saisir');
							infobulle();
							$('#radio_'+memo_pilotage).click();
							$('#arrow_continue_'+memo_direction).click();
							if(memo_pilotage=='clavier')
							{
								$('#C'+colonne+'L'+ligne).focus();
							}
							else
							{
								$('#arrow_continue').hide();
							}
							nb_colonnes = $('#table_saisir thead th').length;
							nb_lignes   = $('#table_saisir tbody tr').length;
						}
					}
				}
			);
		};

		/**
		 * Voir les items acquis par les élèves à une évaluation : chargement des données
		 * @return void
		 */
		var voir = function()
		{
			mode = $(this).attr('class');
			// Récupérer les informations de la ligne concernée
			var ref         = $(this).parent().attr('id').substring(7); // "devoir_" + ref
			var date        = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().html();
			var groupe      = $(this).parent().prev().prev().prev().prev().prev().html();
			var description = $(this).parent().prev().prev().prev().prev().html();
			var date_fr     = date.substring(17,date.length); // garder la date française
			var objet_date  = new Date();
			// Masquer le tableau ; Afficher la zone associée et charger son contenu
			$('#form0 , #form1').hide('fast');
			$('#zone_voir').css("display","block");
			$('#titre_voir').html('Voir les acquisitions d\'une évaluation'+' | '+groupe+' | '+date_fr+' | '+description);
			$('#msg_voir').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'csrf='+CSRF+'&f_action='+mode+'&f_ref='+ref+'&f_date_fr='+encodeURIComponent(date_fr)+'&f_description='+encodeURIComponent(description)+'&f_groupe_nom='+encodeURIComponent(groupe),
					dataType : "html",
					error : function(jqXHR, textStatus, errorThrown)
					{
						$('#msg_voir').removeAttr("class").addClass("alerte").html('Échec de la connexion ! <button id="fermer_zone_voir" type="button" class="retourner">Retour</button>');
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						var tab_response = responseHTML.split('<SEP>');
						if( (tab_response.length!=2) || (tab_response[0].substring(0,1)!='<') )
						{
							$('#msg_voir').removeAttr("class").addClass("alerte").html(responseHTML+' <button id="fermer_zone_voir" type="button" class="retourner">Retour</button>');
						}
						else
						{
							$('#msg_voir').removeAttr("class").html('&nbsp;');
							$('#table_voir').html(tab_response[0]);
							$('#table_voir tbody tr th img').css('display','none'); // .hide(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
							format_liens('#table_voir');
							$('#export_file2').attr("href", url_export+'saisie_deportee_'+tab_response[1]+'.zip' );
							$('#export_file3').attr("href", url_export+'tableau_sans_notes_'+tab_response[1]+'.pdf' );
							$('#export_file5').attr("href", url_export+'tableau_avec_notes_couleur_'+tab_response[1]+'.pdf' );
							$('#export_file8').attr("href", url_export+'tableau_avec_notes_monochrome_'+tab_response[1]+'.pdf' );
							$('#table_voir tbody td').css({"background-color":"#DDF","text-align":"center","vertical-align":"middle","font-size":"110%"});
							infobulle();
						}
					}
				}
			);
		};

		/**
		 * Voir les répartitions des élèves à une évaluation : chargement des données
		 * @return void
		 */
		var voir_repart = function()
		{
			mode = $(this).attr('class');
			// Récupérer les informations de la ligne concernée
			var ref         = $(this).parent().attr('id').substring(7); // "devoir_" + ref
			var date        = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().html();
			var groupe      = $(this).parent().prev().prev().prev().prev().prev().html();
			var description = $(this).parent().prev().prev().prev().prev().html();
			var date_fr     = date.substring(17,date.length); // garder la date française
			var objet_date  = new Date();
			$('#form0 , #form1').hide('fast');
			$('#zone_voir_repart').css("display","block");
			$('#titre_voir_repart').html('Voir les répartitions des élèves à une évaluation | '+date_fr+' | '+description);
			$('#msg_voir_repart').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'csrf='+CSRF+'&f_action='+mode+'&f_ref='+ref+'&f_date_fr='+encodeURIComponent(date_fr)+'&f_description='+encodeURIComponent(description)+'&f_groupe_nom='+encodeURIComponent(groupe),
					dataType : "html",
					error : function(jqXHR, textStatus, errorThrown)
					{
						$('#msg_voir_repart').removeAttr("class").addClass("alerte").html('Échec de la connexion ! <button id="fermer_zone_voir_repart" type="button" class="retourner">Retour</button>');
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						var tab_response = responseHTML.split('<SEP>');
						if( tab_response.length!=3 )
						{
							$('#msg_voir_repart').removeAttr("class").addClass("alerte").html(responseHTML+' <button id="fermer_zone_voir_repart" type="button" class="retourner">Retour</button>');
						}
						else
						{
							$('#msg_voir_repart').removeAttr("class").html('<button id="fermer_zone_voir_repart" type="button" class="retourner">Retour</button>');
							$('#table_voir_repart1').html(tab_response[0]);
							$('#table_voir_repart2').html(tab_response[1]);
							format_liens('#zone_voir_repart');
							$('#export_file6').attr("href", url_export+'repartition_quantitative_'+tab_response[2]+'.pdf' );
							$('#export_file7').attr("href", url_export+'repartition_nominative_'+tab_response[2]+'.pdf' );
							$('#table_voir_repart1 tbody td').css({"background-color":"#DDF","font-weight":"normal","text-align":"center"});
							$('#table_voir_repart2 tbody td').css({"background-color":"#DDF","font-weight":"normal","font-size":"85%"});
							infobulle();
						}
					}
				}
			);
		};

		/**
		 * Choisir les items associés à une évaluation : mise en place du formulaire
		 * @return void
		 */
		var choisir_compet = function()
		{
			// Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
			cocher_matieres_items( $('#f_compet_liste').val() );
			// Afficher la zone
			$.fancybox( { 'href':'#zone_matieres_items' , onStart:function(){$('#zone_matieres_items').css("display","block");} , onClosed:function(){$('#zone_matieres_items').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
		};

		/**
		 * Choisir les élèves associés à une évaluation : mise en place du formulaire (uniquement pour des élèves sélectionnés)
		 * @return void
		 */
		var choisir_eleve = function()
		{
			// Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
			$('#zone_eleve q.date_calendrier').show();
			$('#zone_eleve li.li_m1 span.gradient_pourcent').html('');
			$('#msg_indiquer_eleves_deja').removeAttr("class").html('');
			cocher_eleves( $('#f_eleve_liste').val() );
			// Afficher la zone
			$.fancybox( { 'href':'#zone_eleve' , onStart:function(){$('#zone_eleve').css("display","block");} , onClosed:function(){$('#zone_eleve').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
		};

		/**
		 * Réordonner les items associés à une évaluation : mise en place du formulaire
		 * @return void
		 */
		var ordonner = function()
		{
			mode = $(this).attr('class');
			// Récupérer les informations de la ligne concernée
			var ref         = $(this).parent().attr('id').substring(7); // "devoir_" + ref
			var date        = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().html();
			var groupe      = $(this).parent().prev().prev().prev().prev().prev().html();
			var description = $(this).parent().prev().prev().prev().prev().html();
			var date_fr     = date.substring(17,date.length); // garder la date française
			// Masquer le tableau ; Afficher la zone associée et charger son contenu
			$('#form0 , #form1').hide('fast');
			$('#zone_ordonner').css("display","block");
			$('#titre_ordonner').html('Réordonner les items d\'une évaluation'+' | '+groupe+' | '+date_fr+' | '+description);
			$('#msg_ordonner').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'csrf='+CSRF+'&f_action='+mode+'&f_ref='+ref,
					dataType : "html",
					error : function(jqXHR, textStatus, errorThrown)
					{
						$('#msg_ordonner').removeAttr("class").addClass("alerte").html('Échec de la connexion ! <button id="fermer_zone_ordonner" type="button" class="retourner">Retour</button>');
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,18)!='<ul id="sortable">')
						{
							$('#msg_ordonner').removeAttr("class").addClass("alerte").html(responseHTML+' <button id="fermer_zone_ordonner" type="button" class="retourner">Retour</button>');
						}
						else
						{
							modification = false;
							$('#msg_ordonner').removeAttr("class").html('&nbsp;');
							$('#div_ordonner').html(responseHTML);
							$('#sortable').sortable( { cursor:'n-resize' , update:function(event,ui){modif_ordre();} } );
						}
					}
				}
			);
		};
		function modif_ordre()
		{
			if(modification==false)
			{
				$('#fermer_zone_ordonner').removeAttr("class").addClass("annuler").html('Annuler / Retour');
				modification = true;
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
			}
		}

		/**
		 * Choisir les professeurs associés à une évaluation : mise en place du formulaire
		 * @return void
		 */
		var choisir_prof = function()
		{
			cocher_profs( $('#f_prof_liste').val() );
			// Afficher la zone
			$.fancybox( { 'href':'#zone_profs' , onStart:function(){$('#zone_profs').css("display","block");} , onClosed:function(){$('#zone_profs').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
		};

		/**
		 * Uploader les documents associés à une évaluation : mise en place du formulaire
		 * @return void
		 */
		var uploader_doc = function()
		{
			// Récupérer les informations de la ligne concernée
			var ref         = $(this).parent().next().attr('id').substring(7); // "devoir_" + ref // next() en plus
			var date        = $(this).parent().prev().prev().prev().prev().prev().prev().prev().html(); // un prev() de moins
			var groupe      = $(this).parent().prev().prev().prev().prev().html(); // un prev() de moins
			var description = $(this).parent().prev().prev().prev().html(); // un prev() de moins
			var date_fr     = date.substring(17,date.length); // garder la date française
			var img_sujet   = (tab_sujets[ref])   ? '<a href="'+tab_sujets[ref]+'" target="_blank"><img alt="sujet" src="./_img/document/sujet_oui.png" title="Sujet disponible." /></a>' : '<img alt="sujet" src="./_img/document/sujet_non.png" />' ;
			var img_corrige = (tab_corriges[ref]) ? '<a href="'+tab_corriges[ref]+'" target="_blank"><img alt="corrigé" src="./_img/document/corrige_oui.png" title="Corrigé disponible." /></a>' : '<img alt="corrigé" src="./_img/document/corrige_non.png" />' ;
			// Renseigner les champs dynamique affichés
			$('#titre_upload').html(groupe+' | '+date_fr+' | '+description);
			$('#ajax_document_upload').removeAttr("class").html("");
			$('#span_sujet').html(img_sujet);
			$('#span_corrige').html(img_corrige);
			activer_boutons_upload(ref);
			infobulle();
			// maj du paramètre AjaxUpload (les paramètres n'étant pas directement modifiables...)
			uploader_sujet['_settings']['data']['f_ref']   = ref;
			uploader_corrige['_settings']['data']['f_ref'] = ref;
			// Afficher la zone
			$.fancybox( { 'href':'#zone_upload' , onStart:function(){$('#zone_upload').css("display","block");} , onClosed:function(){$('#zone_upload').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
		};

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('q.ajouter').click( ajouter );
		$('q.modifier').live(  'click' , modifier );
		$('q.dupliquer').live( 'click' , dupliquer );
		$('q.supprimer').live( 'click' , supprimer );
		$('q.annuler').live(   'click' , annuler );
		$('q.valider').live(   'click' , function(){formulaire.submit();} );
		$('table.form input , table.form select').live( 'keyup' , function(e){intercepter(e);} );

		$('q.ordonner').live(       'click' , ordonner );
		$('q.imprimer').live(       'click' , imprimer );
		$('q.saisir').live(         'click' , saisir );
		$('q.voir').live(           'click' , voir );
		$('q.voir_repart').live(    'click' , voir_repart );
		$('q.choisir_compet').live( 'click' , choisir_compet );
		$('q.choisir_eleve').live(  'click' , choisir_eleve );
		$('q.choisir_prof').live(   'click' , choisir_prof );
		$('q.uploader_doc').live(   'click' , uploader_doc );

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Cocher / décocher par lot des individus
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#prof_check_all').click
		(
			function()
			{
				$('.prof_liste').find('input:enabled').prop('checked',true);
				return false;
			}
		);
		$('#prof_uncheck_all').click
		(
			function()
			{
				$('.prof_liste').find('input:enabled').prop('checked',false);
				return false;
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le checkbox pour choisir ou non une date visible différente de la date du devoir
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#box_date').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if($(this).is(':checked'))
				{
					$('#f_date_visible').val($('#f_date').val());
					$(this).next().show(0).next().hide(0);
				}
				else
				{
					$(this).next().hide(0).next().show(0);
				}
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le checkbox pour choisir ou non une date d'auto-évaluation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#box_autoeval').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if($(this).is(':checked'))
				{
					$('#f_date_autoeval').val('00/00/0000');
					$(this).next().show(0).next().hide(0);
				}
				else
				{
					$(this).next().hide(0).next().show(0);
					$('#f_date_autoeval').val(input_autoeval);
				}
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Reporter la date visible si modif date du devoir et demande dates identiques
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#f_date').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('change',
			function()
			{
				if($('#box_date').is(':checked'))
				{
					$('#f_date_visible').val($('#f_date').val());
				}
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le bouton pour fermer le cadre des items associés à une évaluation (annuler / retour)
		// Clic sur le bouton pour fermer le cadre des élèves associés à une évaluation (annuler / retour) (uniquement pour des élèves sélectionnés)
		// Clic sur le bouton pour fermer le cadre des professeurs associés à une évaluation (annuler / retour)
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#annuler_compet , #annuler_eleve , #annuler_profs , #fermer_zone_upload').click
		(
			function()
			{
				$.fancybox.close();
				return(false);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le bouton pour fermer le formulaire servant à saisir les acquisitions des élèves à une évaluation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#fermer_zone_saisir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#titre_saisir').html("&nbsp;");
				$('#table_saisir').html("&nbsp;");
				$('#zone_saisir').css("display","none");
				$('#form0 , #form1').show('fast');
				return(false);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le bouton pour fermer le formulaire servant à réordonner les items d'une évaluation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#fermer_zone_ordonner').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#titre_ordonner').html("&nbsp;");
				$('#div_ordonner').html("&nbsp;");
				$('#zone_ordonner').css("display","none");
				$('#form0 , #form1').show('fast');
				return(false);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le bouton pour fermer le bloc pour voir les acquisitions des élèves à une évaluation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#fermer_zone_voir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#titre_voir').html("&nbsp;");
				$('#zone_voir table').html("&nbsp;");
				$('#zone_voir').css("display","none");
				$('#form0 , #form1').show('fast');
				return(false);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le bouton pour fermer le bloc pour voir les répartitions des élèves à une évaluation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#fermer_zone_voir_repart').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$('#titre_voir_repart').html("&nbsp;");
				$('#zone_voir_repart table').html("&nbsp;");
				$('#zone_voir_repart').css("display","none");
				$('#form0 , #form1').show('fast');
				return(false);
			}
		);

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer le formulaire servant à imprimer le cartouche d'une évaluation
// ////////////////////////////////////////////////////////////////////////////////////////////////////
		$('#fermer_zone_imprimer').click
		(
			function()
			{
				$('#titre_imprimer').html("&nbsp;");
				$('#msg_imprimer').removeAttr("class").html("&nbsp;");
				$('#zone_imprimer_retour').html("&nbsp;");
				$('#zone_imprimer').css("display","none");
				$('#form0 , #form1').show('fast');
				return(false);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le bouton pour valider le choix des items associés à une évaluation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#valider_compet').click
		(
			function()
			{
				var liste = '';
				var nombre = 0;
				$("#zone_matieres_items input[type=checkbox]:checked").each
				(
					function()
					{
						liste += $(this).val()+'_';
						nombre++;
					}
				);
				var compet_liste  = liste.substring(0,liste.length-1);
				var compet_nombre = (nombre==0) ? 'aucun' : ( (nombre>1) ? nombre+' items' : nombre+' item' ) ;
				$('#f_compet_liste').val(compet_liste);
				$('#f_compet_nombre').val(compet_nombre);
				$.fancybox.close();
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le bouton pour valider le choix des élèves associés à une évaluation (uniquement pour des élèves sélectionnés)
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#valider_eleve').click
		(
			function()
			{
				var liste = '';
				var nombre = 0;
				var test_doublon = new Array();
				$("#zone_eleve input[type=checkbox]:checked").each
				(
					function()
					{
						var eleve_id = $(this).val();
						if(typeof(test_doublon[eleve_id])=='undefined')
						{
							test_doublon[eleve_id] = true;
							liste += eleve_id+'_';
							nombre++;
						}
					}
				);
				var eleve_liste  = liste.substring(0,liste.length-1);
				var eleve_nombre = (nombre==0) ? 'aucun' : ( (nombre>1) ? nombre+' élèves' : nombre+' élève' ) ;
				$('#f_eleve_liste').val(eleve_liste);
				$('#f_eleve_nombre').val(eleve_nombre);
				$.fancybox.close();
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le bouton pour valider le choix des profs associés à une évaluation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#valider_profs').click
		(
			function()
			{
				var liste = '';
				var nombre = 0;
				$("#zone_profs input[type=checkbox]:checked").each
				(
					function()
					{
						liste += $(this).val()+'_';
						nombre++;
					}
				);
				liste  = (nombre==1) ? '' : liste.substring(0,liste.length-1) ;
				nombre = (nombre==1) ? 'moi seul' : nombre+' profs' ;
				$('#f_prof_liste').val(liste);
				$('#f_prof_nombre').val(nombre);
				$.fancybox.close();
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Demande pour sélectionner d'une liste d'items mémorisés
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#f_selection_items').change
		(
			function()
			{
				cocher_matieres_items( $("#f_selection_items").val() );
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le bouton pour mémoriser un choix d'items
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#f_enregistrer_items').click
		(
			function()
			{
				memoriser_selection_matieres_items( $("#f_liste_items_nom").val() );
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Fonction pour colorer les cases du tableau de saisie des items déjà enregistrés
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		function colorer_cellules()
		{
			$("#table_saisir tbody td input").each
			(
				function ()
				{
					if( ($(this).val()!='X') && ($(this).val()!='REQ') )
					{
						$(this).parent().css("background-color","#AAF");
					}
					else
					{
						$(this).parent().css("background-color","#EEF");
					}
				}
			);
		}

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Validation de la demande de génération d'un cartouche pour une évaluation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#f_submit_imprimer').click
		(
			function()
			{
				$('button').prop('disabled',true);
				$('#msg_imprimer').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
				$('#zone_imprimer_retour').html("&nbsp;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_action=imprimer_cartouche'+'&'+$("#zone_imprimer").serialize(),
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('button').prop('disabled',false);
							$('#msg_imprimer').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('button').prop('disabled',false);
							if(responseHTML.substring(0,6)!='<hr />')
							{
								$('#msg_imprimer').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#msg_imprimer').removeAttr("class").addClass("valide").html("Cartouches générés !");
								$('#zone_imprimer_retour').html(responseHTML);
								format_liens('#zone_imprimer_retour');
								infobulle();
							}
						}
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Demande d'indiquer la liste des élèves associés à une évaluation de même nom (uniquement pour des élèves sélectionnés)
		// Reprise d'un développement initié par Alain Pottier <alain.pottier613@orange.fr>
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#indiquer_eleves_deja').click
		(
			function()
			{
				if(!$('#f_description').val())
				{
					$('#msg_indiquer_eleves_deja').removeAttr("class").addClass("erreur").html('évaluation sans nom');
					return false;
				}
				var f_date_debut = $('#f_date_deja').val();
				if(!f_date_debut)
				{
					$('#msg_indiquer_eleves_deja').removeAttr("class").addClass("erreur").html('date manquante');
					return false;
				}
				if(!test_dateITA(f_date_debut))
				{
					$('#msg_indiquer_eleves_deja').removeAttr("class").addClass("erreur").html('date JJ/MM/AAAA incorrecte');
					return false;
				}
				$('button').prop('disabled',true);
				$('#msg_indiquer_eleves_deja').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_action=indiquer_eleves_deja'+'&f_description='+$('#f_description').val()+'&f_date_debut='+f_date_debut,
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('button').prop('disabled',false);
							$('#msg_indiquer_eleves_deja').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('button').prop('disabled',false);
							if(responseHTML.substring(0,3)!='ok,')
							{
								$('#msg_indiquer_eleves_deja').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								// On récupère les associations élèves -> dates
								var tab_dates   = new Array();
								var tab_groupes = new Array();
								var tab_infos = responseHTML.substring(3).split(',');
								var memo_groupe_id = 0;
								for(i in tab_infos)
								{
									var tab = tab_infos[i].split('_');
									tab_dates[tab[0]] = tab[1];
								}
								// Passer en revue les lignes élève
								$("#zone_eleve input[type=checkbox]").each
								(
									function()
									{
										var tab_ids = $(this).attr('id').split('_');
										var eleve_id  = tab_ids[1];
										var groupe_id = tab_ids[2];
										var eleve_date = tab_dates[eleve_id];
										if(groupe_id!=memo_groupe_id)
										{
											memo_groupe_id = groupe_id;
											tab_groupes[groupe_id] = new Array(0,0);
										}
										$(this).next('label').removeAttr('class').next('span').html('');
										if(typeof(eleve_date)=='undefined')
										{
											tab_groupes[groupe_id][0]++;
										}
										else
										{
											$(this).next('label').addClass('deja grey').next('span').html('<span>'+eleve_date+'</span>');
											tab_groupes[groupe_id][1]++;
										}
									}
								);
								// Passer en revue les bilans par groupe
								for(groupe_id in tab_groupes)
								{
									var nb_eleves = tab_groupes[groupe_id][0]+tab_groupes[groupe_id][1];
									var pourcentage = (nb_eleves) ? (100*tab_groupes[groupe_id][1]/nb_eleves).toFixed(0) : 0 ;
									switch (pourcentage)
									{
										case '0'   : var couleur = '#C00';break;
										case '100' : var couleur = '#080';break;
										default    : var couleur = '#333';break;
									}
									$('#groupe_'+groupe_id).css('color',couleur).html('<span class="gradient_outer"><span class="gradient_inner" style="width:'+pourcentage+'px"></span></span>'+pourcentage+'%');
								}
								$('#msg_indiquer_eleves_deja').removeAttr("class").addClass("valide").html("Affichage actualisé.");
							}
						}
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Choix du mode de pilotage pour la saisie des résultats
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('input[name=mode_saisie]').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				memo_pilotage = $(this).val();
				if(memo_pilotage=='clavier')
				{
					$('#arrow_continue').show(0);
					$('#C'+colonne+'L'+ligne).focus();
				}
				else
				{
					$('#arrow_continue').hide(0);
				}
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Choix du sens de parcours pour la saisie des résultats (si pilotage au clavier)
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('input[name=arrow_continue]').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				memo_direction = $(this).val();
					$('#C'+colonne+'L'+ligne).focus();
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Choix de rétrécir ou pas les colonnes sur #table_saisir
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#table_saisir #check_largeur').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var condense = ($(this).is(':checked')) ? 'v' : 'h' ; // 'h' ou 'v' pour horizontal (non condensé) ou vertical (condensé)
				$('#table_saisir tbody').removeAttr("class").addClass(condense);
				$("#table_saisir thead tr th img").each
				(
					function ()
					{
						img_src_old = $(this).attr('src');
						img_src_new = (condense=='v') ? img_src_old.substring(0,img_src_old.length-3) : img_src_old+'&br' ;
						$(this).attr('src',img_src_new);
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Choix de rétrécir ou pas les colonnes sur #table_voir
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#table_voir #check_largeur').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var condense = ($(this).is(':checked')) ? 'v' : 'h' ; // 'h' ou 'v' pour horizontal (non condensé) ou vertical (condensé)
				$("#table_voir thead tr th img").each
				(
					function ()
					{
						img_src_old = $(this).attr('src');
						img_src_new = (condense=='v') ? img_src_old.substring(0,img_src_old.length-3) : img_src_old+'&br' ;
						$(this).attr('src',img_src_new);
					}
				);
				$("#table_voir tbody tr td img").each
				(
					function ()
					{
						img_src_old = $(this).attr('src');
						img_src_new = (condense=='v') ? img_src_old.replace('/h/','/v/') : img_src_old.replace('/v/','/h/') ; // Pas besoin d'expression régulière car une seule occurence
						$(this).attr('src',img_src_new);
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Choix de rétrécir ou pas les lignes sur #table_saisir ou #table_voir
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#check_hauteur').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var table_id = $(this).closest('table').attr('id');
				if($(this).is(':checked'))
				{
					$("#"+table_id+" tbody tr th div").css('display','none');         // .hide(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
					$("#"+table_id+" tbody tr th img").css('display','inline-block'); // .show(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
				}
				else
				{
					$("#"+table_id+" tbody tr th img").css('display','none');  // .hide(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
					$("#"+table_id+" tbody tr th div").css('display','block'); // .show(0) s'avère bcp plus lent dans FF et pose pb si bcp élèves / items ...
				}
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Gérer la saisie des acquisitions au clavier
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		function focus_cellule_suivante_en_evitant_sortie_tableau()
		{
			if(colonne==0)
			{
				colonne = nb_colonnes;
				ligne = (ligne!=1) ? ligne-1 : nb_lignes ;
			}
			else if(colonne>nb_colonnes)
			{
				colonne = 1;
				ligne = (ligne!=nb_lignes) ? ligne+1 : 1 ;
			}
			else if(ligne==0)
			{
				ligne = nb_lignes;
				colonne = (colonne!=1) ? colonne-1 : nb_colonnes ;
			}
			else if(ligne>nb_lignes)
			{
				ligne = 1;
				colonne = (colonne!=nb_colonnes) ? colonne+1 : 1 ;
			}
			var new_id = 'C'+colonne+'L'+ligne;
			$('#'+new_id).focus();
		}

		$('#table_saisir tbody td input').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('keydown',	// keydown au lieu de keyup permet de laisser appuyer sur la touche pour répéter une action
			function(e)
			{
				if(memo_pilotage=='clavier')
				{
					var id = $(this).attr("id");
					var findme = '.'+e.which+'.';
					var endroit_report_note = 'cellule';
					colonne = parseInt(id.substring(1,id.indexOf('L')),10);
					ligne   = parseInt(id.substring(id.indexOf('L')+1),10);
					if('.8.46.49.50.51.52.65.68.78.97.98.99.100.'.indexOf(findme)!=-1)
					{
						// Une touche d'item a été pressée
						switch (e.which)
						{
							case   8: note = 'X';    break; // backspace
							case  46: note = 'X';    break; // suppr
							case  97: note = 'RR';   break; // 1
							case  49: note = 'RR';   break; // 1 (&)
							case  98: note = 'R';    break; // 2
							case  50: note = 'R';    break; // 2 (é)
							case  99: note = 'V';    break; // 3
							case  51: note = 'V';    break; // 3 (")
							case 100: note = 'VV';   break; // 4
							case  52: note = 'VV';   break; // 4 (')
							case  65: note = 'ABS';  break; // A
							case  78: note = 'NN';   break; // N
							case  68: note = 'DISP'; break; // D
						}
						endroit_report_note = $("input[name=f_endroit_report_note]:checked").val();
						if( (typeof(endroit_report_note)=='undefined') || (endroit_report_note=='cellule') )
						{
							// pour une seule case
							$(this).val(note).removeAttr("class").addClass(note);
							$(this).parent().css("background-color","#F6D");
							if(memo_direction=='down')
							{
								ligne++;
							}
							else
							{
								colonne++;
							}
						}
						else if(endroit_report_note=='tableau')
						{
							// pour toutes les cases vides du tableau
							$("#table_saisir tbody td input").each
							(
								function()
								{
									if($(this).val()=='X')
									{
										$(this).val(note).removeAttr("class").addClass(note);
										$(this).parent().css("background-color","#F6D");
									}
								}
							);
						}
						else if(endroit_report_note=='colonne')
						{
							// pour toutes les cases vides d'une colonne
							$("#table_saisir tbody td input[id^=C"+colonne+"L]").each
							(
								function()
								{
									if($(this).val()=='X')
									{
										$(this).val(note).removeAttr("class").addClass(note);
										$(this).parent().css("background-color","#F6D");
									}
								}
							);
							colonne++;
						}
						else if(endroit_report_note=='ligne')
						{
							// pour toutes les cases vides d'une ligne
							$("#table_saisir tbody td input[id$=L"+ligne+"]").each
							(
								function()
								{
									if($(this).val()=='X')
									{
										$(this).val(note).removeAttr("class").addClass(note);
										$(this).parent().css("background-color","#F6D");
									}
								}
							);
							ligne++;
						}
						if(modification==false)
						{
							$('#fermer_zone_saisir').removeAttr("class").addClass("annuler").html('Annuler / Retour');
							modification = true;
						}
						$('#msg_saisir').removeAttr("class").html("&nbsp;");
						focus_cellule_suivante_en_evitant_sortie_tableau();
						endroit_report_note = 'cellule';
					}
					else if('.37.38.39.40.'.indexOf(findme)!=-1)
					{
						// Une flèche a été pressée
						switch (e.which)
						{
							case 37: colonne--; break; // flèche gauche
							case 38: ligne--;   break; // flèche haut
							case 39: colonne++; break; // flèche droit
							case 40: ligne++;   break; // flèche bas
						}
						focus_cellule_suivante_en_evitant_sortie_tableau();
					}
					else if(e.which==13)	// touche entrée
					{
						// La touche entrée a été pressée
						$('#Enregistrer_saisie').click();
					}
					else if(e.which==27)
					{
						// La touche escape a été pressée
						$('#fermer_zone_saisir').click();
					}
					else if('.67.76.84.'.indexOf(findme)!=-1)
					{
						// Une touche de préparation de modification par lot a été pressée
						switch (e.which)
						{
							case 67: endroit_report_note = 'colonne'; break; // C
							case 76: endroit_report_note = 'ligne';   break; // L
							case 84: endroit_report_note = 'tableau'; break; // T
						}
					}
					else if('.16.17.18.20.144.'.indexOf(findme)!=-1)
					{
						// Une touche Shift / Ctrl / Alt / CapsLock / VerrNum [*] a été pressée
						// [*] 144 est aussi un signal particulier envoyé par un clavier étendu en parallèle à chaque appui sur une touche du pavé numérique pour signaler qu'il est actif
						endroit_report_note = $("input[name=f_endroit_report_note]:checked").val();
					}
					$('#f_report_'+endroit_report_note).prop('checked',true);
					return false; // Evite notamment qu'IE fasse "page précédente" si on appuie sur backspace.
				}
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Gérer la saisie des acquisitions à la souris
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		// Remplacer la cellule par les images de choix
		$("#table_saisir tbody td.td_clavier").live
		('mouseover',
			function()
			{
				if(memo_pilotage=='souris')
				{
					// Test si un précédent td n'a pas été remis en place (js a du mal à suivre le mouseleave sinon)
					if(memo_input_id)
					{
						$("td#td_"+memo_input_id).removeAttr("class").addClass("td_clavier").children("div").remove();
						$("input#"+memo_input_id).show();
						memo_input_id = false;
					}
					else
					{
						// Récupérer les infos associées
						memo_input_id = $(this).children("input").attr("id");
						colonne = parseInt(memo_input_id.substring(1,memo_input_id.indexOf('L')),10);
						ligne   = parseInt(memo_input_id.substring(memo_input_id.indexOf('L')+1),10);
						var valeur = $(this).children("input").val();
						$(this).children("input").hide();
						$(this).removeAttr("class").addClass("td_souris").append( $("#td_souris_container").html() ).find("img[alt="+valeur+"]").addClass("on");
					}
				}
			}
		);

		// Revenir à la cellule initiale ; mouseout ne fonctionne pas à cause des éléments contenus dans le div ; mouseleave est mieux, mais pb qd même avec les select du calendrier
		$("#table_saisir tbody td").live
		('mouseleave',
			function()
			{
				if(memo_pilotage=='souris')
				{
					if(memo_input_id)
					{
						$("td#td_"+memo_input_id).removeAttr("class").addClass("td_clavier").children("div").remove();
						$("input#"+memo_input_id).show();
						memo_input_id = false;
					}
				}
			}
		);

		// Renvoyer l'information dans la ou les cellule(s)
		$("div.td_souris img").live
		('click',
			function()
			{
				var note = $(this).attr("alt");
				endroit_report_note = $("input[name=f_endroit_report_note]:checked").val();
				if( (typeof(endroit_report_note)=='undefined') || (endroit_report_note=='cellule') )
				{
					// pour une seule case
					$("input#"+memo_input_id).val(note).removeAttr("class").addClass(note);
					$(this).parent().children("img").removeAttr("class");
					$(this).addClass("on").parent().parent().css("background-color","#F6D");
				}
				else
				{
					if(endroit_report_note=='tableau')
					{
						// pour toutes les cases vides du tableau
						$("#table_saisir tbody td input").each
						(
							function()
							{
								if($(this).val()=='X')
								{
									$(this).val(note).removeAttr("class").addClass(note);
									$(this).parent().css("background-color","#F6D");
								}
							}
						);
					}
					else if(endroit_report_note=='colonne')
					{
						// pour toutes les cases vides d'une colonne
						$("#table_saisir tbody td input[id^=C"+colonne+"L]").each
						(
							function()
							{
								if($(this).val()=='X')
								{
									$(this).val(note).removeAttr("class").addClass(note);
									$(this).parent().css("background-color","#F6D");
								}
							}
						);
					}
					else if(endroit_report_note=='ligne')
					{
						// pour toutes les cases vides d'une ligne
						$("#table_saisir tbody td input[id$=L"+ligne+"]").each
						(
							function()
							{
								if($(this).val()=='X')
								{
									$(this).val(note).removeAttr("class").addClass(note);
									$(this).parent().css("background-color","#F6D");
								}
							}
						);
					}
				}
				if(modification==false)
				{
					$('#fermer_zone_saisir').removeAttr("class").addClass("annuler").html('Annuler / Retour');
					modification = true;
				}
				$('#msg_saisir').removeAttr("class").html("&nbsp;");
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le lien pour mettre à jour l'ordre des items d'une évaluation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#Enregistrer_ordre').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if(modification==false)
				{
					$('#ajax_msg').removeAttr("class").addClass("alerte").html("Aucune modification effectuée !");
				}
				else
				{
					// On récupère la liste des items dans l'ordre de la page
					var tab_id = new Array();
					$('#sortable').children('li').each
					(
						function()
						{
							var test_id = $(this).attr('id');
							if(typeof(test_id)!='undefined')
							{
								tab_id.push(test_id.substring(1));
							}
						}
					);
					$('button').prop('disabled',true);
					$('#ajax_msg').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?page='+PAGE,
							data : 'csrf='+CSRF+'&f_action=enregistrer_ordre'+'&f_ref='+$('#Enregistrer_ordre').val()+'&tab_id='+tab_id,
							dataType : "html",
							error : function(jqXHR, textStatus, errorThrown)
							{
								$('button').prop('disabled',false);
								$('#ajax_msg').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
								return false;
							},
							success : function(responseHTML)
							{
								initialiser_compteur();
								$('button').prop('disabled',false);
								if(responseHTML.substring(0,1)!='<')
								{
									$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{
									modification = false;
									$('#ajax_msg').removeAttr("class").addClass("valide").html("Ordre enregistré !");
									$('#fermer_zone_ordonner').removeAttr("class").addClass("retourner").html('Retour');
								}
							}
						}
					);
				}
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Clic sur le lien pour mettre à jour les acquisitions des élèves à une évaluation
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#Enregistrer_saisie').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if(modification==false)
				{
					$('#msg_saisir').removeAttr("class").addClass("alerte").html("Aucune modification effectuée !");
				}
				else
				{
					$('button').prop('disabled',true);
					$('#msg_saisir').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
					// Grouper les saisies dans une variable unique afin d'éviter tout problème dû à une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque).
					var f_notes = new Array();
					$("#table_saisir tbody input").each
					(
						function()
						{
							var ids  = $(this).attr('name');
							var note = $(this).val();
							if(note)
							{
								f_notes.push( ids + '_' + note );
							}
						}
					);
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?page='+PAGE,
							data : 'csrf='+CSRF+'&f_action=enregistrer_saisie'+'&f_ref='+$("#f_ref").val()+'&f_date_mysql='+$("#f_date_mysql").val()+'&f_date_visible='+$("#f_date_visible").val()+'&f_notes='+f_notes+'&f_description='+$("#f_description").val(),
							dataType : "html",
							error : function(jqXHR, textStatus, errorThrown)
							{
								$('button').prop('disabled',false);
								$('#msg_saisir').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
								return false;
							},
							success : function(responseHTML)
							{
								initialiser_compteur();
								$('button').prop('disabled',false);
								if(responseHTML.substring(0,1)!='<')
								{
									$('#msg_saisir').removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{
									modification = false;
									$('#msg_saisir').removeAttr("class").addClass("valide").html("Saisies enregistrées !");
									$('#fermer_zone_saisir').removeAttr("class").addClass("retourner").html('Retour');
									colorer_cellules();
								}
							}
						}
					);
				}
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Traitement du formulaire principal
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form1');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					// "required:true" ne fonctionne pas sur "f_eleve_liste" & "f_prof_liste" & "f_compet_liste" car type hidden
					f_date          : { required:true , dateITA:true },
					f_date_visible  : { required:function(){return !$('#box_date').is(':checked');} , dateITA:true },
					f_date_autoeval : { required:function(){return !$('#box_autoeval').is(':checked');} , dateITA:true },
					f_groupe        : { required:true },
					f_eleve_nombre  : { accept:'élève|élèves' },
					f_description   : { required:false , maxlength:60 },
					f_prof_nombre   : { required:false },
					f_compet_nombre : { accept:'item|items' },
					f_doc_sujet     : { required:false , testURL:true },
					f_doc_corrige   : { required:false , testURL:true }
				},
				messages :
				{
					f_date          : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_date_visible  : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_date_autoeval : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_groupe        : { required:"groupe manquant" },
					f_eleve_nombre  : { accept:"élève(s) manquant(s)" },
					f_description   : { maxlength:"60 caractères maximum" },
					f_prof_nombre   : { },
					f_compet_nombre : { accept:"item(s) manquant(s)" },
					f_doc_sujet     : { testURL:" URL sujet invalide" },
					f_doc_corrige   : { testURL:" URL corrigé invalide" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { $('#ajax_msg').after(error); }
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions =
		{
			url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg",
			beforeSerialize : action_form_avant_serialize,
			beforeSubmit : test_form_avant_envoi,
			error : retour_form_erreur,
			success : retour_form_valide
		};

		// Envoi du formulaire (avec jquery.form.js)
		formulaire.submit
		(
			function()
			{
				if (!please_wait)
				{
					$(this).ajaxSubmit(ajaxOptions);
					return false;
				}
				else
				{
					return false;
				}
			}
		); 

		// Fonction précédent le trantement du formulaire (avec jquery.form.js)
		function action_form_avant_serialize(jqForm, options)
		{
			if($('#box_date').is(':checked'))
			{
				// Obligé rajouter le test à ce niveau car si la date a été changé depuis le calendrier, l'événement change() n'a pas été déclenché (et dans test_form_avant_envoi() c'est trop tard).
				$('#f_date_visible').val($('#f_date').val());
			}
			if($('#box_autoeval').is(':checked'))
			{
				$('#f_date_autoeval').val('00/00/0000');
			}
		}

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation.form();
			if(readytogo)
			{
				please_wait = true;
				$('#ajax_msg').parent().children('q').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(jqXHR, textStatus, errorThrown)
		{
			please_wait = false;
			$('#ajax_msg').parent().children('q').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			initialiser_compteur();
			please_wait = false;
			$('#ajax_msg').parent().children('q').show();
			if(responseHTML.substring(0,2)!='<t')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
				var action = $('#f_action').val();
				switch (action)
				{
					case 'ajouter':
						$('table.form tbody tr td[colspan]').parent().remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
					case 'dupliquer':
						var position_script = responseHTML.lastIndexOf('<SCRIPT>');
						var new_td = responseHTML.substring(0,position_script);
						if(TYPE=='groupe')
						{
							var groupe_id = $("#f_groupe option:selected").val();
							var new_td = new_td.replace('<td>{{GROUPE_NOM}}</td>','<td>'+tab_groupe[groupe_id]+'</td>');
						}
						var new_tr = '<tr class="new">'+new_td+'</tr>';
						$('table.form tbody').append(new_tr);
						$('q.valider').parent().parent().remove();
						eval( responseHTML.substring(position_script+8) );
						break;
					case 'modifier':
						var position_script = responseHTML.lastIndexOf('<SCRIPT>');
						var new_td = responseHTML.substring(0,position_script);
						if(TYPE=='groupe')
						{
							var groupe_id = $("#f_groupe option:selected").val();
							var new_td = new_td.replace('<td>{{GROUPE_NOM}}</td>','<td>'+tab_groupe[groupe_id]+'</td>');
						}
						$('q.valider').parent().parent().prev().addClass("new").html(new_td).show();
						$('q.valider').parent().parent().remove();
						eval( responseHTML.substring(position_script+8) );
						break;
					case 'supprimer':
						$('q.valider').closest('tr').remove();
						break;
				}
				trier_tableau();
				afficher_masquer_images_action('show');
				$('#form0').css('visibility','visible');
				infobulle();
			}
		} 

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Traitement du clic sur le bouton pour envoyer un import csv (saisie déportée)
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		// Envoi du fichier avec jquery.ajaxupload.js
		new AjaxUpload
		('#import_file',
			{
				action: 'ajax.php?page='+PAGE+'&f_action=importer_saisie_csv',
				name: 'userfile',
				data : {'csrf':CSRF},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier,
				onSubmit: verifier_fichier,
				onComplete: retourner_fichier
			}
		);

		function changer_fichier(fichier_nom,fichier_extension)
		{
			$('#msg_import').removeAttr("class").html('&nbsp;');
			return true;
		}

		function verifier_fichier(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('#msg_import').removeAttr("class").addClass("erreur").html('"'+fichier_nom+'" n\'est pas un chemin de fichier correct.');
				return false;
			}
			else if ('.csv.txt.'.indexOf('.'+fichier_extension.toLowerCase()+'.')==-1)
			{
				$('#msg_import').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension "csv" ou "txt".');
				return false;
			}
			else
			{
				$('button').prop('disabled',true);
				$('#msg_import').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
				return true;
			}
		}

		function retourner_fichier(fichier_nom,responseHTML)	// Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
		{
			$('button').prop('disabled',false);
			if(responseHTML.substring(0,1)!='|')
			{
				$('#msg_import').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				initialiser_compteur();
				if(responseHTML.length>2)
				{
					responseHTML = responseHTML.substring(1);
					tab_resultat = responseHTML.split('|');
					for (i=0 ; i<tab_resultat.length ; i++)
					{
						tab_valeur = tab_resultat[i].split('.');
						if(tab_valeur.length==3)
						{
							var eleve_id = tab_valeur[0];
							var item_id  = tab_valeur[1];
							var score    = tab_valeur[2];
							champ = $('#table_saisir input[name='+item_id+'x'+eleve_id+']');
							if(champ.length)
							{
								switch (score)
								{
									case '1': champ.val('RR').removeAttr("class").addClass('RR'); break;
									case '2': champ.val('R').removeAttr("class").addClass('R'); break;
									case '3': champ.val('V').removeAttr("class").addClass('V'); break;
									case '4': champ.val('VV').removeAttr("class").addClass('VV'); break;
									case 'A': champ.val('ABS').removeAttr("class").addClass('ABS'); break;
									case 'N': champ.val('NN').removeAttr("class").addClass('NN'); break;
									case 'D': champ.val('DISP').removeAttr("class").addClass('DISP'); break;
								}
								champ.parent().css("background-color","#F6D");
							}
							modification = true;
						}
					}
				}
				$('#msg_import').removeAttr("class").addClass("valide").html("Tableau complété ! N'oubliez pas d'enregistrer...");
			}
		}

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Traitement du clic sur un bouton pour envoyer un sujet ou un corrigé de devoir
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		// Envoi du fichier avec jquery.ajaxupload.js ; on lui donne un nom afin de pouvoir changer dynamiquement le paramètre.
		var uploader_sujet = new AjaxUpload
		('#bouton_uploader_sujet',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'csrf':CSRF,'f_action':'uploader_document','f_doc_objet':'sujet','f_ref':'maj_plus_tard'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier_document,
				onSubmit: verifier_fichier_document,
				onComplete: retourner_fichier_document
			}
		);

		// Envoi du fichier avec jquery.ajaxupload.js ; on lui donne un nom afin de pouvoir changer dynamiquement le paramètre.
		var uploader_corrige = new AjaxUpload
		('#bouton_uploader_corrige',
			{
				action: 'ajax.php?page='+PAGE,
				name: 'userfile',
				data: {'csrf':CSRF,'f_action':'uploader_document','f_doc_objet':'corrige','f_ref':'maj_plus_tard'},
				autoSubmit: true,
				responseType: "html",
				onChange: changer_fichier_document,
				onSubmit: verifier_fichier_document,
				onComplete: retourner_fichier_document
			}
		);

		function changer_fichier_document(fichier_nom,fichier_extension)
		{
			$('#ajax_document_upload').removeAttr("class").html('&nbsp;');
			$('#zone_upload button').prop('disabled',true);
			return true;
		}

		function verifier_fichier_document(fichier_nom,fichier_extension)
		{
			if (fichier_nom==null || fichier_nom.length<5)
			{
				$('#ajax_document_upload').removeAttr("class").addClass("erreur").html('Cliquer sur "Parcourir..." pour indiquer un chemin de fichier correct.');
				activer_boutons_upload(uploader_sujet['_settings']['data']['f_ref']);
				return false;
			}
			else if ( ('.doc.docx.odg.odp.ods.odt.ppt.pptx.rtf.sxc.sxd.sxi.sxw.xls.xlsx.'.indexOf('.'+fichier_extension.toLowerCase()+'.')!=-1) && !confirm('Vous devriez convertir votre fichier au format PDF.\nEtes-vous certain de vouloir l\'envoyer sous ce format ?') )
			{
				$('#ajax_document_upload').removeAttr("class").addClass("erreur").html('Convertissez votre fichier en "pdf".');
				activer_boutons_upload(uploader_sujet['_settings']['data']['f_ref']);
				return false;
			}
			else if ('.bat.com.exe.php.zip.'.indexOf('.'+fichier_extension.toLowerCase()+'.')!=-1)
			{
				$('#ajax_document_upload').removeAttr("class").addClass("erreur").html('Extension non autorisée.');
				activer_boutons_upload(uploader_sujet['_settings']['data']['f_ref']);
				return false;
			}
			else
			{
				$('#zone_upload button').prop('disabled',true);
				$('#ajax_document_upload').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
				return true;
			}
		}

		function retourner_fichier_document(fichier_nom,responseHTML)	// Attention : avec jquery.ajaxupload.js, IE supprime mystérieusement les guillemets et met les éléments en majuscules dans responseHTML.
		{
			var tab_infos = responseHTML.split(']¤[');
			if(tab_infos[0]!='ok')
			{
				$('#ajax_document_upload').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				initialiser_compteur();
				$('#ajax_document_upload').removeAttr("class").addClass("valide").html("Document enregistré.");
				var ref   = tab_infos[1];
				var objet = tab_infos[2];
				var url   = tab_infos[3];
				if(objet=='sujet') { var alt='sujet';   var title='Sujet';   var numero=0; tab_sujets[ref] = url; }
				else               { var alt='corrigé'; var title='Corrigé'; var numero=1; tab_corriges[ref] = url; }
				var lien        = '<a href="'+url+'" target="_blank"><img alt="'+alt+'" src="./_img/document/'+objet+'_oui.png" title="'+title+' disponible." /></a>';
				$('#span_'+objet).html(lien);
				$('#devoir_'+ref).prev().children().eq(numero).replaceWith(lien);
				infobulle();
			}
			activer_boutons_upload(ref);
		}

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Traitement du clic sur un bouton pour retirer un sujet ou un corrigé de devoir
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#bouton_supprimer_sujet , #bouton_supprimer_corrige').click
		(
			function()
			{
				$('#zone_upload button').prop('disabled',true);
				$('#ajax_document_upload').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
				var objet = $(this).attr('id').substring(17);
				var ref   = uploader_sujet['_settings']['data']['f_ref'];
				var url   = (objet=='sujet') ? tab_sujets[ref] : tab_corriges[ref] ;
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'csrf='+CSRF+'&f_action=retirer_document'+'&f_doc_objet='+objet+'&f_ref='+ref+'&f_doc_url='+url,
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('#ajax_document_upload').removeAttr("class").addClass("alerte").html(responseHTML);
							activer_boutons_upload(ref);
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML!='ok')
							{
								$('#ajax_document_upload').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_document_upload').removeAttr("class").addClass("valide").html("Document retiré.");
								if(objet=='sujet') { var alt='sujet';   var numero=0; tab_sujets[ref] = ''; }
								else               { var alt='corrigé'; var numero=1; tab_corriges[ref] = ''; }
								var lien        = '<img alt="'+alt+'" src="./_img/document/'+objet+'_non.png" />';
								$('#span_'+objet).html(lien);
								$('#devoir_'+ref).prev().children().eq(numero).replaceWith(lien);
							}
							activer_boutons_upload(ref);
						}
					}
				);
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Traitement du clic sur un bouton pour référencer un lien de sujet ou corrigé de devoir
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		$('#bouton_referencer_sujet , #bouton_referencer_corrige').click
		(
			function()
			{
				var objet = $(this).attr('id').substring(18);
				var ref   = uploader_sujet['_settings']['data']['f_ref'];
				var url   = $('#f_adresse_'+objet).val();
				if(url == '')
				{
					$('#ajax_document_upload').removeAttr("class").addClass("erreur").html("Adresse manquante !");
					$('#f_adresse_'+objet).focus();
					return false;
				}
				else if(!testURL(url))
				{
					$('#ajax_document_upload').removeAttr("class").addClass("erreur").html("Adresse incorrecte !");
					$('#f_adresse_'+objet).focus();
					return false;
				}
				else
				{
					$('#zone_upload button').prop('disabled',true);
					$('#ajax_document_upload').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?page='+PAGE,
							data : 'csrf='+CSRF+'&f_action=referencer_document'+'&f_doc_objet='+objet+'&f_ref='+ref+'&f_doc_url='+url,
							dataType : "html",
							error : function(jqXHR, textStatus, errorThrown)
							{
								$('#ajax_document_upload').removeAttr("class").addClass("alerte").html(responseHTML);
								activer_boutons_upload(ref);
								return false;
							},
							success : function(responseHTML)
							{
								initialiser_compteur();
								if(responseHTML!='ok')
								{
									$('#ajax_document_upload').removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{

									$('#ajax_document_upload').removeAttr("class").addClass("valide").html("Document référencé.");
									if(objet=='sujet') { var alt='sujet';   var title='Sujet';   var numero=0; tab_sujets[ref] = url; }
									else               { var alt='corrigé'; var title='Corrigé'; var numero=1; tab_corriges[ref] = url; }
									var lien        = '<a href="'+url+'" target="_blank"><img alt="'+alt+'" src="./_img/document/'+objet+'_oui.png" title="'+title+' disponible." /></a>';
									$('#span_'+objet).html(lien);
									$('#devoir_'+ref).prev().children().eq(numero).replaceWith(lien);
									infobulle();
								}
								activer_boutons_upload(ref);
							}
						}
					);
				}
			}
		);

		// ////////////////////////////////////////////////////////////////////////////////////////////////////
		// Traitement du premier formulaire pour afficher le tableau avec la liste des évaluations
		// ////////////////////////////////////////////////////////////////////////////////////////////////////

		// Afficher masquer des options de la grille (uniquement pour un groupe)

		var autoperiode = true; // Tant qu'on ne modifie pas manuellement le choix des périodes, modification automatique du formulaire

		function view_dates_perso()
		{
			var periode_val = $("#f_aff_periode").val();
			if(periode_val!=0)
			{
				$("#dates_perso").attr("class","hide");
			}
			else
			{
				$("#dates_perso").attr("class","show");
			}
		}

		$('#f_aff_periode').change
		(
			function()
			{
				view_dates_perso();
				autoperiode = false;
			}
		);

		// Changement de groupe (uniquement pour un groupe)
		// -> desactiver les périodes prédéfinies en cas de groupe de besoin
		// -> choisir automatiquement la meilleure période et chercher les évaluations si un changement manuel de période n'a jamais été effectué

		function modifier_periodes()
		{
			var groupe_type = $("#f_aff_classe option:selected").parent().attr('label');
			$("#f_aff_periode option").each
			(
				function()
				{
					var periode_id = $(this).val();
					// La période personnalisée est tout le temps accessible
					if(periode_id!=0)
					{
						// groupe de besoin -> desactiver les périodes prédéfinies
						if( (typeof(groupe_type)=='undefined') || (groupe_type=='Besoins') )
						{
							$(this).prop('disabled',true);
						}
						// classe ou groupe classique -> toutes périodes accessibles
						else
						{
							$(this).prop('disabled',false);
						}
					}
				}
			);
			// Sélectionner si besoin la période personnalisée
			if( (typeof(groupe_type)=='undefined') || (groupe_type=='Besoins') )
			{
				$("#f_aff_periode option[value=0]").prop('selected',true);
				$("#dates_perso").attr("class","show");
			}
			// Modification automatique du formulaire
			if(autoperiode)
			{
				if( (groupe_type=='Classes') || (groupe_type=='Groupes') )
				{
					// Rechercher automatiquement la meilleure période
					var id_classe = $('#f_aff_classe option:selected').val().substring(1);
					if(typeof(tab_groupe_periode[id_classe])!='undefined')
					{
						for(var id_periode in tab_groupe_periode[id_classe]) // Parcourir un tableau associatif...
						{
							var tab_split = tab_groupe_periode[id_classe][id_periode].split('_');
							if( (date_mysql>=tab_split[0]) && (date_mysql<=tab_split[1]) )
							{
								$("#f_aff_periode option[value="+id_periode+"]").prop('selected',true);
								view_dates_perso();
								break;
							}
						}
					}
				}
				// Soumettre le formulaire
				if(autoperiode)
				{
					formulaire0.submit();
				}
			}
		}

		$('#f_aff_classe').change
		(
			function()
			{
				modifier_periodes();
			}
		);

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire0 = $('#form0');

		// Ajout d'une méthode pour valider les dates de la forme jj/mm/aaaa (trouvé dans le zip du plugin, corrige en plus un bug avec Safari)
		// méthode dateITA déjà ajoutée

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation0 = formulaire0.validate
		(
			{
				rules :
				{
					f_aff_classe : { required:true },
					f_date_debut : { required:function(){return (TYPE=='selection') || $("#f_aff_periode").val()==0;} , dateITA:true },
					f_date_fin   : { required:function(){return (TYPE=='selection') || $("#f_aff_periode").val()==0;} , dateITA:true }
				},
				messages :
				{
					f_aff_classe : { required:"classe / groupe manquant" },
					f_date_debut : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" },
					f_date_fin   : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element)
				{
					if(element.is("select")) {element.after(error);}
					else if(element.attr("type")=="text") {element.next().after(error);}
				}
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions0 =
		{
			url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg0",
			beforeSubmit : test_form_avant_envoi0,
			error : retour_form_erreur0,
			success : retour_form_valide0
		};

		// Envoi du formulaire (avec jquery.form.js)
		formulaire0.submit
		(
			function()
			{
				$(this).ajaxSubmit(ajaxOptions0);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi0(formData, jqForm, options)
		{
			$('#ajax_msg0').removeAttr("class").html("&nbsp;");
			var readytogo = validation0.form();
			if(readytogo)
			{
				$('#ajax_msg0').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur0(jqXHR, textStatus, errorThrown)
		{
			$('#ajax_msg0').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide0(responseHTML)
		{
			initialiser_compteur();
			if( (responseHTML.substring(0,4)!='<tr>') && (responseHTML!='<SCRIPT>') )
			{
				$('#ajax_msg0').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#ajax_msg0').removeAttr("class").addClass("valide").html("Demande réalisée !").fadeOut(3000,function(){$(this).removeAttr("class").html("").show();});
				var position_script = responseHTML.lastIndexOf('<SCRIPT>');
				$('table.form tbody').html( responseHTML.substring(0,position_script) );
				eval( responseHTML.substring(position_script+8) );
				trier_tableau();
				afficher_masquer_images_action('show');
				infobulle();
				if( reception_todo )
				{
					$('q.ajouter').click();
				}
			}
		}

		// N'afficher les formulaire qu'une fois le js bien chargé...
		$('#form0 , #form1').show('fast');

		// Et charger par défaut les dernières évaluations du prof.
		$('#form0').submit();

	}
);

