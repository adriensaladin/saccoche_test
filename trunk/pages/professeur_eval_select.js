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
//	Initialisation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		var mode = false;
		var modification = false;
		var memo_pilotage = 'clavier';
		var memo_input_id = false;
		var nb_colonnes = 1;
		var nb_lignes   = 1;
		// tri du tableau (avec jquery.tablesorter.js).
		var sorting = [[0,1],[3,0]];
		$('table.form').tablesorter({ headers:{1:{sorter:false},2:{sorter:false},4:{sorter:false},5:{sorter:false},6:{sorter:false}} });
		function trier_tableau()
		{
			if($('table.form tbody tr td').length>1)
			{
				$('table.form').trigger('update');
				$('table.form').trigger('sorton',[sorting]);
			}
		}
		trier_tableau();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Fonctions utilisées
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		/**
		 * Ajouter une évaluation : mise en place du formulaire
		 * @return void
		 */
		var ajouter = function()
		{
			mode = $(this).attr('class');
			// Fabriquer la ligne avec les éléments de formulaires
			afficher_masquer_images_action('hide');
			$('#form0').css('visibility','hidden');
			var new_tr = '';
			new_tr += '<tr>';
			new_tr += '<td><input id="f_date" name="f_date" size="9" type="text" value="'+input_date+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></td>';
			new_tr += '<td><input id="box_date" type="checkbox" checked style="vertical-align:-3px" /> <span style="vertical-align:-2px">identique</span><span class="hide"><input id="f_date_visible" name="f_date_visible" size="9" type="text" value="'+input_date+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></span></td>';
			new_tr += '<td><input id="f_eleve_nombre" name="f_eleve_nombre" size="10" type="text" value="0 élève" readonly /><input id="f_eleve_liste" name="f_eleve_liste" type="hidden" value="" /><q class="choisir_eleve" title="Voir ou choisir les élèves."></q></td>';
			new_tr += '<td><input id="f_info" name="f_info" size="20" type="text" value="" /></td>';
			new_tr += '<td><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="0 item" readonly /><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="" /><q class="choisir_compet" title="Voir ou choisir les items."></q></td>';
			new_tr += '<td><input id="f_prof_nombre" name="f_prof_nombre" size="10" type="text" value="vous seul" readonly /><input id="f_prof_liste" name="f_prof_liste" type="hidden" value="" /><q class="choisir_prof" title="Voir ou choisir les collègues."></q></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><q class="valider" title="Valider l\'ajout de cette évaluation."></q><q class="annuler" title="Annuler l\'ajout de cette évaluation."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Ajouter cette nouvelle ligne
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_date').focus();
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
			$('#p_alerte').show();
			// Récupérer les informations de la ligne concernée
			var ref           = $(this).parent().attr('lang');
			var date          = $(this).parent().prev().prev().prev().prev().prev().prev().html();
			var date_visible  = $(this).parent().prev().prev().prev().prev().prev().html();
			var eleve_nombre  = $(this).parent().prev().prev().prev().prev().html();
			var eleve_liste   = $(this).parent().prev().prev().prev().prev().attr('lang');
			var info          = $(this).parent().prev().prev().prev().html();
			var compet_nombre = $(this).parent().prev().prev().html();
			var compet_liste  = $(this).parent().prev().prev().attr('lang');
			var prof_nombre   = $(this).parent().prev().html();
			var prof_liste    = $(this).parent().prev().attr('lang');
			date = date.substring(17,date.length); // enlever la date mysql cachée
			if(date_visible=='identique')
			{
				var checked = ' checked';
				var classe1 = '';
				var classe2 = ' class="hide"';
				date_visible = date;
			}
			else
			{
				var checked = '';
				var classe1 = ' class="hide"';
				var classe2 = '';
			}
			// Fabriquer la ligne avec les éléments de formulaires
			var new_tr = '';
			new_tr += '<tr>';
			new_tr += '<td><input id="f_date" name="f_date" size="9" type="text" value="'+date+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></td>';
			new_tr += '<td><input id="box_date" type="checkbox"'+checked+' style="vertical-align:-3px" /> <span'+classe1+' style="vertical-align:-2px">identique</span><span'+classe2+'><input id="f_date_visible" name="f_date_visible" size="9" type="text" value="'+date_visible+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></span></td>';
			new_tr += '<td><input id="f_eleve_nombre" name="f_eleve_nombre" size="10" type="text" value="'+eleve_nombre+'" readonly /><input id="f_eleve_liste" name="f_eleve_liste" type="hidden" value="'+eleve_liste+'" /><q class="choisir_eleve" title="Voir ou choisir les élèves."></q></td>';
			new_tr += '<td><input id="f_info" name="f_info" size="'+Math.max(info.length,20)+'" type="text" value="'+info+'" /></td>';
			new_tr += '<td><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="'+compet_nombre+'" readonly /><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="'+compet_liste+'" /><q class="choisir_compet" title="Voir ou choisir les items."></q></td>';
			new_tr += '<td><input id="f_prof_nombre" name="f_prof_nombre" size="10" type="text" value="'+prof_nombre+'" readonly /><input id="f_prof_liste" name="f_prof_liste" type="hidden" value="'+prof_liste+'" /><q class="choisir_prof" title="Voir ou choisir les collègues."></q></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_ref" name="f_ref" type="hidden" value="'+ref+'" /><q class="valider" title="Valider les modifications de cette évaluation."></q><q class="annuler" title="Annuler les modifications de cette évaluation."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Cacher la ligne en cours et ajouter la nouvelle
			$(this).parent().parent().hide();
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_info').focus();
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
			var ref           = $(this).parent().attr('lang');
			var date          = $(this).parent().prev().prev().prev().prev().prev().prev().html();
			var date_visible  = $(this).parent().prev().prev().prev().prev().prev().html();
			var eleve_nombre  = $(this).parent().prev().prev().prev().prev().html();
			var eleve_liste   = $(this).parent().prev().prev().prev().prev().attr('lang');
			var info          = $(this).parent().prev().prev().prev().html();
			var compet_nombre = $(this).parent().prev().prev().html();
			var compet_liste  = $(this).parent().prev().prev().attr('lang');
			var prof_nombre   = $(this).parent().prev().html();
			var prof_liste    = $(this).parent().prev().attr('lang');
			date = date.substring(17,date.length); // enlever la date mysql cachée
			if(date_visible=='identique')
			{
				var checked = ' checked';
				var classe1 = '';
				var classe2 = ' class="hide"';
				date_visible = date;
			}
			else
			{
				var checked = '';
				var classe1 = ' class="hide"';
				var classe2 = '';
			}
			// Fabriquer la ligne avec les éléments de formulaires
			var new_tr = '';
			new_tr += '<tr>';
			new_tr += '<td><input id="f_date" name="f_date" size="9" type="text" value="'+date+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></td>';
			new_tr += '<td><input id="box_date" type="checkbox"'+checked+' style="vertical-align:-3px" /> <span'+classe1+' style="vertical-align:-2px">identique</span><span'+classe2+'><input id="f_date_visible" name="f_date_visible" size="9" type="text" value="'+date_visible+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></span></td>';
			new_tr += '<td><input id="f_eleve_nombre" name="f_eleve_nombre" size="10" type="text" value="'+eleve_nombre+'" readonly /><input id="f_eleve_liste" name="f_eleve_liste" type="hidden" value="'+eleve_liste+'" /><q class="choisir_eleve" title="Voir ou choisir les élèves."></q></td>';
			new_tr += '<td><input id="f_info" name="f_info" size="'+Math.max(info.length,20)+'" type="text" value="'+info+'" /></td>';
			new_tr += '<td><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="'+compet_nombre+'" readonly /><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="'+compet_liste+'" /><q class="choisir_compet" title="Voir ou choisir les items."></q></td>';
			new_tr += '<td><input id="f_prof_nombre" name="f_prof_nombre" size="10" type="text" value="'+prof_nombre+'" readonly /><input id="f_prof_liste" name="f_prof_liste" type="hidden" value="'+prof_liste+'" /><q class="choisir_prof" title="Voir ou choisir les collègues."></q></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_ref" name="f_ref" type="hidden" value="'+ref+'" /><q class="valider" title="Valider l\'ajout de cette évaluation."></q><q class="annuler" title="Annuler l\'ajout de cette évaluation."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Ajouter cette nouvelle ligne
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_groupe').focus();
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
			var ref = $(this).parent().attr('lang');
			var new_span = '<span class="danger"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_ref" name="f_ref" type="hidden" value="'+ref+'" />Toutes les saisies associées seront perdues !<q class="valider" title="Confirmer la suppression de cette évaluation."></q><q class="annuler" title="Annuler la suppression de cette évaluation."></q> <label id="ajax_msg">&nbsp;</label></span>';
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
			var ref    = $(this).parent().attr('lang');
			var date   = $(this).parent().prev().prev().prev().prev().prev().html();
			var groupe = $(this).parent().prev().prev().prev().html();
			var info   = $(this).parent().prev().prev().html();
			    date   = date.substring(17,date.length); // garder la date française
			// Masquer le tableau et Afficher la zone associée
			$('#form0 , #form1').hide('fast');
			$('#zone_imprimer').css("display","block");
			$('#titre_imprimer').html('Imprimer le cartouche d\'une évaluation | '+groupe+' | '+info+'<input id="f_ref" name="f_ref" type="hidden" value="'+ref+'" /><input id="f_date" name="f_date" type="hidden" value="'+date+'" /><input id="f_info" name="f_info" type="hidden" value="'+info+'" />');
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
					$('#p_alerte').hide();
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
			var ref          = $(this).parent().attr('lang');
			var date         = $(this).parent().prev().prev().prev().prev().prev().prev().html();
			var date_visible = $(this).parent().prev().prev().prev().prev().prev().html();
			var info         = $(this).parent().prev().prev().prev().html();
			date1 = date.substring(3,13); // garder la date mysql
			date2 = date.substring(17,date.length); // garder la date française
			// Masquer le tableau ; Afficher la zone associée et charger son contenu
			$('#form0 , #form1').hide('fast');
			$('#msg_import').removeAttr("class").html('&nbsp;');
			$('#zone_saisir').css("display","block");
			$('#titre_saisir').html('Saisir les acquisitions d\'une évaluation | '+date2+' | '+info);
			$('#msg_saisir').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action='+mode+'&f_ref='+ref+'&f_date='+date1+'&f_info='+info+'&f_date_visible='+date_visible+'&f_descriptif='+'Élèves sélectionnés'+':::'+info+':::'+date2,
					dataType : "html",
					error : function(msg,string)
					{
						$('#msg_saisir').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer. <button id="fermer_zone_saisir" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>');
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,1)!='<')
						{
							$('#msg_saisir').removeAttr("class").addClass("alerte").html(responseHTML+' <button id="fermer_zone_saisir" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>');
						}
						else
						{
							modification = false;
							$('#msg_saisir').removeAttr("class").html('&nbsp;');
							$('#table_saisir').html(responseHTML);
							$('#table_saisir tbody tr th img').hide(0);
							$('img[title]').tooltip({showURL:false});
							$('#export_file1').attr("href", $("#filename").val()+ref+'.zip' );
							$('#export_file4').attr("href", $("#filename").val()+ref+'_sans_notes.pdf' );
							colorer_cellules();
							format_liens('#table_saisir');
							infobulle();
							$('#radio_'+memo_pilotage).click();
							if(memo_pilotage=='clavier')
							{
								$('#C1L1').focus();
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
			var ref  = $(this).parent().attr('lang');
			var date = $(this).parent().prev().prev().prev().prev().prev().prev().html();
			var info = $(this).parent().prev().prev().prev().html();
			    date = date.substring(17,date.length); // garder la date française
			// Masquer le tableau ; Afficher la zone associée et charger son contenu
			$('#form0 , #form1').hide('fast');
			$('#zone_voir').css("display","block");
			$('#titre_voir').html('Voir les acquisitions d\'une évaluation | '+date+' | '+info);
			$('#msg_voir').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action='+mode+'&f_ref='+ref+'&f_date='+date+'&f_descriptif='+'Élèves sélectionnés'+':::'+info+':::'+date,
					dataType : "html",
					error : function(msg,string)
					{
						$('#msg_voir').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer. <button id="fermer_zone_voir" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>');
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,1)!='<')
						{
							$('#msg_voir').removeAttr("class").addClass("alerte").html(responseHTML+' <button id="fermer_zone_voir" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>');
						}
						else
						{
							$('#msg_voir').removeAttr("class").html('&nbsp;');
							$('#table_voir').html(responseHTML);
							$('#table_voir tbody tr th img').hide(0);
							$('#export_file2').attr("href", $("#filename").val()+ref+'.zip' );
							$('#export_file3').attr("href", $("#filename").val()+ref+'_sans_notes.pdf' );
							$('#export_file5').attr("href", $("#filename").val()+ref+'_avec_notes.pdf' );
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
			var ref  = $(this).parent().attr('lang');
			var date = $(this).parent().prev().prev().prev().prev().prev().prev().html();
			var info = $(this).parent().prev().prev().prev().html();
			    date = date.substring(17,date.length); // garder la date française
			// Masquer le tableau ; Afficher la zone associée et charger son contenu
			$('#form0 , #form1').hide('fast');
			$('#zone_voir_repart').css("display","block");
			$('#titre_voir_repart').html('Voir les répartitions des élèves à une évaluation | '+date+' | '+info);
			$('#msg_voir_repart').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action='+mode+'&f_ref='+ref+'&f_date='+date+'&f_descriptif='+'Élèves sélectionnés'+':::'+info+':::'+date,
					dataType : "html",
					error : function(msg,string)
					{
						$('#msg_voir_repart').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer. <button id="fermer_zone_voir_repart" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>');
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						tab_response = responseHTML.split('<SEP>');
						if( tab_response.length!=2 )
						{
							$('#msg_voir_repart').removeAttr("class").addClass("alerte").html(responseHTML+' <button id="fermer_zone_voir_repart" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>');
						}
						else
						{
							$('#msg_voir_repart').removeAttr("class").html('<button id="fermer_zone_voir_repart" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>');
							$('#table_voir_repart1').html(tab_response[0]);
							$('#table_voir_repart2').html(tab_response[1]);
							$('#export_file6').attr("href", $("#filename").val()+ref+'_repartition_quantitative.pdf' );
							$('#export_file7').attr("href", $("#filename").val()+ref+'_repartition_nominative.pdf' );
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
			$('#form0 , #form1').hide('fast');
			$('#zone_compet ul').css("display","none");
			$('#zone_compet').css("display","block");
			$('#zone_compet ul.ul_m1').css("display","block");
			var liste = $('#f_compet_liste').val();
			// Décocher tout
			$("#zone_compet input[type=checkbox]").each
			(
				function()
				{
					this.checked = false;
				}
			);
			// Cocher ce qui doit l'être (initialisation)
			if(liste.length)
			{
				var tab_id = liste.split('_');
				for(i in tab_id)
				{
					var id = 'id_'+tab_id[i];
					if($('#'+id).length)
					{
						$('#'+id).prop('checked',true);
						$('#'+id).parent().parent().css("display","block");	// les items
						$('#'+id).parent().parent().parent().parent().css("display","block");	// le thème
						$('#'+id).parent().parent().parent().parent().parent().parent().css("display","block");	// le domaine
						$('#'+id).parent().parent().parent().parent().parent().parent().parent().parent().css("display","block");	// le niveau
					}
				}
			}
		};

		/**
		 * Choisir les élèves associés à une évaluation : mise en place du formulaire
		 * @return void
		 */
		var choisir_eleve = function()
		{
			// Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
			$('#form0 , #form1').hide('fast');
			$('#zone_eleve ul').css("display","none");
			$('#zone_eleve').css("display","block");
			$('#zone_eleve ul.ul_m1').css("display","block");
			var liste = $('#f_eleve_liste').val();
			// Décocher tout
			$("#zone_eleve input[type=checkbox]").each
			(
				function()
				{
					this.checked = false;
				}
			);
			// Cocher ce qui doit l'être (initialisation)
			if(liste.length)
			{
				var tab_id = liste.split('_');
				for(i in tab_id)
				{
					var id_debut = 'id_'+tab_id[i]+'_';
					if($('input[id^='+id_debut+']').length)
					{
						$('input[id^='+id_debut+']').prop('checked',true);
						$('input[id^='+id_debut+']').parent().parent().css("display","block");	// le regroupement
					}
				}
			}
		};

		/**
		 * Réordonner les items associés à une évaluation : mise en place du formulaire
		 * @return void
		 */
		var ordonner = function()
		{
			mode = $(this).attr('class');
			// Récupérer les informations de la ligne concernée
			var ref    = $(this).parent().attr('lang');
			var groupe = $(this).parent().prev().prev().prev().prev().html();
			var info   = $(this).parent().prev().prev().prev().html();
			// Masquer le tableau ; Afficher la zone associée et charger son contenu
			$('#form0 , #form1').hide('fast');
			$('#zone_ordonner').css("display","block");
			$('#titre_ordonner').html('Réordonner les items d\'une évaluation | '+groupe+' | '+info);
			$('#msg_ordonner').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action='+mode+'&f_ref='+ref,
					dataType : "html",
					error : function(msg,string)
					{
						$('#msg_ordonner').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer. <button id="fermer_zone_ordonner" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>');
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,10)!='<div id="i')
						{
							$('#msg_ordonner').removeAttr("class").addClass("alerte").html(responseHTML+' <button id="fermer_zone_ordonner" type="button"><img alt="" src="./_img/bouton/retourner.png" /> Retour</button>');
						}
						else
						{
							modification = false;
							$('#msg_ordonner').removeAttr("class").html('&nbsp;');
							$('#div_ordonner').html(responseHTML);
						}
					}
				}
			);
		};

		/**
		 * Choisir les professeurs associés à une évaluation : mise en place du formulaire
		 * @return void
		 */
		var choisir_prof = function()
		{
			// Récupérer les informations de la ligne concernée
			var prof_liste = $('#f_prof_liste').val();
			// Masquer le tableau
			$('#form0 , #form1').hide('fast');
			// Décocher tout
			$("#zone_profs input[type=checkbox]").each
			(
				function()
				{
					if(this.disabled == false)
					{
						this.checked = false;
					}
				}
			);
			// Cocher des cases des profs
			if(prof_liste.length)
			{
				var tab_id = prof_liste.split('_');
				for(i in tab_id)
				{
					var id = 'p_'+tab_id[i];
					if($('#'+id).length)
					{
						$('#'+id).prop('checked',true);
					}
				}
			}
			// Afficher la zone
			$('#zone_profs').css("display","block");
		};

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le checkbox pour choisir ou non une date visible différente de la date du devoir
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Reporter la date visible si modif date du devoir et demande dates identiques
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer le cadre des items associés à une évaluation (annuler / retour)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#annuler_compet').click
		(
			function()
			{
				$('#zone_compet').css("display","none");
				$('#form0 , #form1').show('fast');
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer le cadre des élèves associés à une évaluation (annuler / retour)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#annuler_eleve').click
		(
			function()
			{
				$('#zone_eleve').css("display","none");
				$('#form0 , #form1').show('fast');
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer le cadre des professeurs associés à une évaluation (annuler / retour)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#annuler_profs').click
		(
			function()
			{
				$('#zone_profs').css("display","none");
				$('#form0 , #form1').show('fast');
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer le formulaire servant à saisir les acquisitions des élèves à une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer le formulaire servant à réordonner les items d'une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer le bloc pour voir les acquisitions des élèves à une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer le bloc pour voir les répartitions des élèves à une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer le formulaire servant à imprimer le cartouche d'une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour valider le choix des items associés à une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#valider_compet').click
		(
			function()
			{
				var liste = '';
				var nombre = 0;
				$("#zone_compet input[type=checkbox]:checked").each
				(
					function()
					{
						liste += $(this).val()+'_';
						nombre++;
					}
				);
				liste = liste.substring(0,liste.length-1);
				s = (nombre>1) ? 's' : '';
				$('#f_compet_liste').val(liste);
				$('#f_compet_nombre').val(nombre+' item'+s);
				$('#annuler_compet').click();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour valider le choix des élèves associés à une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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
						var eleve_id = $(this).attr("lang");
						if(typeof(test_doublon[eleve_id])=='undefined')
						{
							test_doublon[eleve_id] = true;
							liste += eleve_id+'_';
							nombre++;
						}
					}
				);
				liste = liste.substring(0,liste.length-1);
				var s = (nombre>1) ? 's' : '';
				$('#f_eleve_liste').val(liste);
				$('#f_eleve_nombre').val(nombre+' élève'+s);
				$('#annuler_eleve').click();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour valider le choix des profs associés à une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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
				nombre = (nombre==1) ? 'vous seul' : nombre+' profs' ;
				$('#f_prof_liste').val(liste);
				$('#f_prof_nombre').val(nombre);
				$('#annuler_profs').click();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Fonction pour colorer les cases du tableau de saisie des items déjà enregistrés
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Validation de la demande de génération d'un cartouche pour une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#f_submit_imprimer').click
		(
			function()
			{
				$('button').prop('disabled',true);
				$('#msg_imprimer').removeAttr("class").addClass("loader").html("Génération en cours... Veuillez patienter.");
				$('#zone_imprimer_retour').html("&nbsp;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action=Imprimer_cartouche&'+$("#zone_imprimer").serialize(),
						dataType : "html",
						error : function(msg,string)
						{
							$('button').prop('disabled',false);
							$('#msg_imprimer').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Choix du mode de pilotage pour la saisie des résultats
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#table_saisir thead tr td input[type="radio"]').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				memo_pilotage = $(this).val();
				if(memo_pilotage=='clavier')
				{
					$("#C1L1").focus();
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Choix de rétrécir ou pas les colonnes sur #table_saisir
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Choix de rétrécir ou pas les colonnes sur #table_voir
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Choix de rétrécir ou pas les lignes sur #table_saisir ou #table_voir
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#check_hauteur').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var table_id = $(this).parent().parent().parent().parent().parent().attr('id');
				if($(this).is(':checked'))
				{
					$("#"+table_id+" tbody tr th div").hide(0);
					$("#"+table_id+" tbody tr th img").show(0);
				}
				else
				{
					$("#"+table_id+" tbody tr th img").hide(0);
					$("#"+table_id+" tbody tr th div").show(0);
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Gérer la saisie des acquisitions au clavier
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#table_saisir tbody td input').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('keydown',	// keydown au lieu de keyup permet de laisser appuyer sur la touche pour répéter une action
			function(e)
			{
				if(memo_pilotage=='clavier')
				{
					var id = $(this).attr("id");
					var colonne = parseInt(id.substring(1,id.indexOf('L')));
					var ligne   = parseInt(id.substring(id.indexOf('L')+1));
					var findme = '.'+e.which+'.';
					if('.8.46.49.50.51.52.65.68.78.97.98.99.100.'.indexOf(findme)!=-1)
					{
						// Une touche d'item a été pressée
						switch (e.which)
						{
							case   8: $(this).val('X').removeAttr("class").addClass('X'); break;				// backspace
							case  46: $(this).val('X').removeAttr("class").addClass('X'); break;				// suppr
							case  49: $(this).val('RR').removeAttr("class").addClass('RR'); break;			// 1
							case  97: $(this).val('RR').removeAttr("class").addClass('RR'); break;			// 1
							case  50: $(this).val('R').removeAttr("class").addClass('R'); break;				// 2
							case  98: $(this).val('R').removeAttr("class").addClass('R'); break;				// 2
							case  51: $(this).val('V').removeAttr("class").addClass('V'); break;				// 3
							case  99: $(this).val('V').removeAttr("class").addClass('V'); break;				// 3
							case  52: $(this).val('VV').removeAttr("class").addClass('VV'); break;			// 4
							case 100: $(this).val('VV').removeAttr("class").addClass('VV'); break;			// 4
							case  65: $(this).val('ABS').removeAttr("class").addClass('ABS'); break;		// A
							case  78: $(this).val('NN').removeAttr("class").addClass('NN'); break; 			// N
							case  68: $(this).val('DISP').removeAttr("class").addClass('DISP'); break;	// D
						}
						$(this).parent().css("background-color","#F6D").focus();
						if(modification==false)
						{
							$('#fermer_zone_saisir').html('<img alt="" src="./_img/bouton/annuler.png" /> Annuler / Retour');
							modification = true;
						}
						$('#msg_saisir').removeAttr("class").html("&nbsp;");
						// Passer à la case suivante
						if(ligne<nb_lignes)
						{
							ligne++;
						}
						else
						{
							ligne = 1;
							if(colonne<nb_colonnes)
							{
								colonne++;
							}
							else
							{
								colonne = 1;
							}
						}
						var new_id = 'C'+colonne+'L'+ligne;
						$('#'+new_id).focus();
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
					return false; // Permet notamment qu'IE fasse "page précédente" si on appuie sur backspace.
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Gérer la saisie des acquisitions à la souris
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Remplacer la cellule par les images de choix
		$("#table_saisir tbody td.td_clavier").live
		('mouseover',
			function(e)
			{
				if(memo_pilotage=='souris')
				{
					// Test si un précédent td n'a pas été remis en place (js a du mal à suivre le mouseleave sinon)
					if(memo_input_id)
					{
						$("#table_saisir tbody td[lang="+memo_input_id+"]").removeAttr("class").addClass("td_clavier").children("div").remove();
						$("input#"+memo_input_id).show();
						memo_input_id = false;
					}
					else
					{
						// Récupérer les infos associées
						// adresse = $(this).attr("lang");
						memo_input_id = $(this).children("input").attr("id");
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
						$("#table_saisir tbody td[lang="+memo_input_id+"]").removeAttr("class").addClass("td_clavier").children("div").remove();
						$("input#"+memo_input_id).show();
						memo_input_id = false;
					}
				}
			}
		);

		// Renvoyer l'information dans la cellule
		$("div.td_souris img").live
		('click',
			function()
			{
				var valeur = $(this).attr("alt");
				$("input#"+memo_input_id).val(valeur).removeAttr("class").addClass(valeur);
				$(this).parent().children("img").removeAttr("class");
				$(this).addClass("on").parent().parent().css("background-color","#F6D");
				if(modification==false)
				{
					$('#fermer_zone_saisir').html('<img alt="" src="./_img/bouton/annuler.png" /> Annuler / Retour');
					modification = true;
				}
				$('#msg_saisir').removeAttr("class").html("&nbsp;");
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Reporter une note dans toutes les cellules sans saisie
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#report_note').click
		(
			function()
			{
				var note = $("input[name=f_defaut]:checked").val();
				var findme = '.'+note+'.';
				if( (typeof(note)=='undefined') || ('.VV.V.R.RR.ABS.NN.DISP.'.indexOf(findme)==-1) )
				{
					$('#msg_report').removeAttr("class").addClass("alerte").html('Aucun code coché !');
					return false;
				}
				else
				{
					var compteur = 0;
					$("#table_saisir tbody td input").each
					(
						function ()
						{
							if($(this).val()=='X')
							{
								$(this).val(note).removeAttr("class").addClass(note);
								$(this).parent().css("background-color","#F6D");
								compteur++;
							}
						}
					);
					if(!compteur)
					{
						$('#msg_report').removeAttr("class").addClass("alerte").html('Aucune cellule vide trouvée !');
					}
					else
					{
						var s = (compteur>1) ? 's' : '' ;
						$('#msg_report').removeAttr("class").addClass("valide").html(compteur+' report'+s+' effectué'+s+'.');
						if(modification==false)
						{
							$('#fermer_zone_saisir').html('<img alt="" src="./_img/bouton/annuler.png" /> Annuler / Retour');
							modification = true;
						}
						$('#msg_saisir').removeAttr("class").html("&nbsp;");
					}
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur une image pour modifier l'ordre des items d'une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#div_ordonner input[type=image]').live
		('click',
			function()
			{
				para_clic = $(this).parent();
				para_prev = para_clic.prev('div');
				para_next = para_clic.next('div');
				para_clic.before(para_next);
				para_clic.after(para_prev);
				if(modification==false)
				{
					$('#fermer_zone_ordonner').html('<img alt="" src="./_img/bouton/annuler.png" /> Annuler / Retour');
					modification = true;
					$('#ajax_msg').removeAttr("class").html("&nbsp;");
				}
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le lien pour mettre à jour l'ordre des items d'une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

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
					$('#div_ordonner').children('div').each
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
					$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?page='+PAGE,
							data : 'f_action=Enregistrer_ordre&f_ref='+$('#Enregistrer_ordre').val()+'&tab_id='+tab_id,
							dataType : "html",
							error : function(msg,string)
							{
								$('button').prop('disabled',false);
								$('#ajax_msg').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
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
									$('#fermer_zone_ordonner').html('<img alt="" src="./_img/bouton/retourner.png" /> Retour');
								}
							}
						}
					);
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le lien pour mettre à jour les acquisitions des élèves à une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

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
					$('#msg_saisir').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
					// Grouper les saisies dans une variable unique afin d'éviter tout problème dûe à une limitation du module "suhosin" (voir par exemple http://xuxu.fr/2008/12/04/nombre-de-variables-post-limite-ou-tronque).
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
							data : 'f_action=Enregistrer_saisie'+'&f_ref='+$("#f_ref").val()+'&f_date='+$("#f_date").val()+'&f_date_visible='+$("#f_date_visible").val()+'&f_notes='+f_notes+'&f_info='+$("#f_info").val(),
							dataType : "html",
							error : function(msg,string)
							{
								$('button').prop('disabled',false);
								$('#msg_saisir').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
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
									$('#fermer_zone_saisir').html('<img alt="" src="./_img/bouton/retourner.png" /> Retour');
									colorer_cellules();
								}
							}
						}
					);
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire principal
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form1');

		// Ajout d'une méthode pour valider les dates de la forme jj/mm/aaaa (trouvé dans le zip du plugin, corrige en plus un bug avec Safari)
		jQuery.validator.addMethod
		(
			"dateITA",
			function(value, element)
			{
				var check = false;
				var re = /^\d{1,2}\/\d{1,2}\/\d{4}$/ ;
				if( re.test(value))
				{
					var adata = value.split('/');
					var gg = parseInt(adata[0],10);
					var mm = parseInt(adata[1],10);
					var aaaa = parseInt(adata[2],10);
					var xdata = new Date(aaaa,mm-1,gg);
					if ( ( xdata.getFullYear() == aaaa ) && ( xdata.getMonth () == mm - 1 ) && ( xdata.getDate() == gg ) )
						check = true;
					else
						check = false;
				}
				else
					check = false;
				return this.optional(element) || check;
			}, 
			"Veuillez entrer une date correcte."
		);

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_date         : { required:true , dateITA:true },
					f_date_visible : { required:function(){return !$('#box_date').is(':checked');} , dateITA:true },
					f_eleve_liste  : { required:true },
					f_info         : { required:false , maxlength:60 },
					f_prof_liste   : { required:false },
					f_compet_liste : { required:true }
				},
				messages :
				{
					f_date         : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_date_visible : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_eleve_liste  : { required:"élève(s) manquant(s)" },
					f_info         : { maxlength:"60 caractères maximum" },
					f_prof_liste   : { },
					f_compet_liste : { required:"item(s) manquant(s)" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { $('#ajax_msg').after(error); }
			}
		);

		// Options d'envoi du formulaire (avec jquery.form.js)
		var ajaxOptions =
		{
			url : 'ajax.php?page='+PAGE,
			type : 'POST',
			dataType : "html",
			clearForm : false,
			resetForm : false,
			target : "#ajax_msg",
			beforeSubmit : test_form_avant_envoi,
			error : retour_form_erreur,
			success : retour_form_valide
		};

		// Envoi du formulaire (avec jquery.form.js)
		formulaire.submit
		(
			function()
			{
				if($('#box_date').is(':checked'))
				{
					// Obligé rajouter le test à ce niveau car si la date a été changé depuis le calendrier, l'événement change() n'a pas été déclenché (et dans test_form_avant_envoi() c'est trop tard).
					$('#f_date_visible').val($('#f_date').val());
				}
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

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation.form();
			if(readytogo)
			{
				please_wait = true;
				$('#ajax_msg').parent().children('q').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			please_wait = false;
			$('#ajax_msg').parent().children('q').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
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
						var new_tr = '<tr class="new">'+responseHTML+'</tr>';
						$('table.form tbody').append(new_tr);
						$('q.valider').parent().parent().remove();
						break;
					case 'modifier':
						$('q.valider').parent().parent().prev().addClass("new").html(responseHTML).show();
						$('q.valider').parent().parent().remove();
						$('#p_alerte').hide();
						break;
					case 'supprimer':
						$('q.valider').parent().parent().parent().remove();
						break;
				}
				trier_tableau();
				afficher_masquer_images_action('show');
				$('#form0').css('visibility','visible');
				infobulle();
			}
		} 

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du premier formulaire pour afficher le tableau avec la liste des évaluations
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

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
					f_date_debut : { required:true , dateITA:true },
					f_date_fin   : { required:true , dateITA:true }
				},
				messages :
				{
					f_date_debut : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" },
					f_date_fin   : { required:"date manquante" , dateITA:"date JJ/MM/AAAA incorrecte" }
				},
				errorElement : "label",
				errorClass : "erreur",
				errorPlacement : function(error,element) { element.next().after(error); }
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
				$('#ajax_msg0').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur0(msg,string)
		{
			$('#ajax_msg0').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide0(responseHTML)
		{
			initialiser_compteur();
			if( (responseHTML.substring(0,4)!='<tr>') && (responseHTML!='') )
			{
				$('#ajax_msg0').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#ajax_msg0').removeAttr("class").addClass("valide").html("Demande réalisée !").fadeOut(3000,function(){$(this).removeAttr("class").html("").show();});
				$('table.form tbody').html(responseHTML);
				trier_tableau();
				afficher_masquer_images_action('show');
				infobulle();
			}
		}
		$('#form0').submit();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Traitement du clic sur le bouton pour envoyer un import csv (saisie déportée)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Envoi du fichier avec jquery.ajaxupload.js
		new AjaxUpload
		('#import_file',
			{
				action: 'ajax.php?page='+PAGE+'&f_action=importer_saisie_csv',
				name: 'userfile',
				data : '',
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
				$('#msg_import').removeAttr("class").addClass("loader").html('Fichier envoyé... Veuillez patienter.');
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

		// N'afficher les formulaire qu'une fois le js bien chargé...
		$('#form0 , #form1').show('fast');

	}
);

