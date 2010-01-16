/**
 * @version $Id: eval_groupe.js 8 2009-10-30 20:56:02Z thomas $
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Initialisation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		var mode = false;
		var modification = false;
		var pilotage = 'clavier';
		var memo_input_id = false;
		var memo_td_html = '';
		// tri du tableau (avec jquery.tablesorter.js).
		var sorting = [[0,1],[1,0]];
		$('table.form').tablesorter({ headers:{3:{sorter:false},4:{sorter:false}} });
		function trier_tableau()
		{
			if($('table.form tbody tr').length)
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
			new_tr  = '<tr>';
			new_tr += '<td><input id="f_date" name="f_date" size="9" type="text" value="'+input_date+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></td>';
			new_tr += '<td><select id="f_groupe" name="f_groupe">'+select_groupe+'</select></td>';
			new_tr += '<td><input id="f_info" name="f_info" size="20" type="text" value="" /></td>';
			new_tr += '<td><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="0 item" readonly="readonly" /><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="" /><q class="choisir_compet" title="Voir ou choisir les items."></q></td>';
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
			// Récupérer les informations de la ligne concernée
			ref    = $(this).parent().attr('lang');
			date   = $(this).parent().prev().prev().prev().prev().html();
			groupe = $(this).parent().prev().prev().prev().html();
			info   = $(this).parent().prev().prev().html();
			nombre = $(this).parent().prev().html();
			liste  = $(this).parent().prev().attr('lang');
			date   = date.substring(17,date.length); // enlever la date mysql cachée
			// Fabriquer la ligne avec les éléments de formulaires
			new_tr  = '<tr>';
			new_tr += '<td><input id="f_date" name="f_date" size="9" type="text" value="'+date+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></td>';
			new_tr += '<td><select id="f_groupe" name="f_groupe">'+select_groupe.replace('>'+groupe+'<',' selected="selected">'+groupe+'<')+'</select></td>';
			new_tr += '<td><input id="f_info" name="f_info" size="'+Math.max(info.length,20)+'" type="text" value="'+info+'" /></td>';
			new_tr += '<td><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="'+nombre+'" readonly="readonly" /><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="'+liste+'" /><q class="choisir_compet" title="Voir ou choisir les items."></q></td>';
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
			date   = $(this).parent().prev().prev().prev().prev().html();
			info   = $(this).parent().prev().prev().html();
			nombre = $(this).parent().prev().html();
			liste  = $(this).parent().prev().attr('lang');
			date   = date.substring(17,date.length); // enlever la date mysql cachée
			// Fabriquer la ligne avec les éléments de formulaires
			new_tr  = '<tr>';
			new_tr += '<td><input id="f_date" name="f_date" size="9" type="text" value="'+date+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></td>';
			new_tr += '<td><select id="f_groupe" name="f_groupe">'+select_groupe+'</select></td>';
			new_tr += '<td><input id="f_info" name="f_info" size="'+Math.max(info.length,20)+'" type="text" value="'+info+'" /></td>';
			new_tr += '<td><input id="f_compet_nombre" name="f_compet_nombre" size="10" type="text" value="'+nombre+'" readonly="readonly" /><input id="f_compet_liste" name="f_compet_liste" type="hidden" value="'+liste+'" /><q class="choisir_compet" title="Voir ou choisir les items."></q></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><q class="valider" title="Valider l\'ajout de cette évaluation."></q><q class="annuler" title="Annuler l\'ajout de cette évaluation."></q> <label id="ajax_msg">&nbsp;</label></td>';
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
			ref = $(this).parent().attr('lang');
			new_span  = '<span class="danger"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_ref" name="f_ref" type="hidden" value="'+ref+'" />Toutes les saisies associées seront perdues !<q class="valider" title="Confirmer la suppression de cette évaluation."></q><q class="annuler" title="Annuler la suppression de cette évaluation."></q> <label id="ajax_msg">&nbsp;</label></span>';
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
			ref    = $(this).parent().attr('lang');
			date   = $(this).parent().prev().prev().prev().prev().html();
			groupe = $(this).parent().prev().prev().prev().html();
			info   = $(this).parent().prev().prev().html();
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
			ref    = $(this).parent().attr('lang');
			date   = $(this).parent().prev().prev().prev().prev().html();
			groupe = $(this).parent().prev().prev().prev().html();
			info   = $(this).parent().prev().prev().html();
			date   = date.substring(3,13); // garder la date mysql
			// Masquer le tableau ; Afficher la zone associée et charger son contenu
			$('#form0 , #form1').hide('fast');
			$('#msg_import').removeAttr("class").html('&nbsp;');
			$('#zone_saisir').css("display","block");
			$('#titre_saisir').html('Saisir les acquisitions d\'une évaluation | '+groupe+' | '+info);
			$('#msg_saisir').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
					data : 'f_action='+mode+'&f_ref='+ref+'&f_date='+date,
					dataType : "html",
					error : function(msg,string)
					{
						$('#msg_saisir').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer. <a class="fermer_zone_saisir" href="#"><img alt="Retourner" src="./_img/action/action_retourner.png" /> retour</a>');
						return false;
					},
					success : function(responseHTML)
					{
						maj_clock(1);
						if(responseHTML.substring(0,1)!='<')
						{
							$('#msg_saisir').removeAttr("class").addClass("alerte").html(responseHTML+' <a class="fermer_zone_saisir" href="#"><img alt="Retourner" src="./_img/action/action_retourner.png" /> retour</a>');
						}
						else
						{
							modification = false;
							$('#msg_saisir').removeAttr("class").html('&nbsp;');
							$('#table_saisir').html(responseHTML);
							$('img[title]').tooltip({showURL:false});
							$('#export_file').attr("href", $("#filename").val()+ref+'.zip' );
							colorer_cellules();
							format_liens();
							infobulle();
							$('#radio_'+pilotage).click();
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
			ref    = $(this).parent().attr('lang');
			groupe = $(this).parent().prev().prev().prev().html();
			info   = $(this).parent().prev().prev().html();
			// Masquer le tableau ; Afficher la zone associée et charger son contenu
			$('#form0 , #form1').hide('fast');
			$('#zone_voir').css("display","block");
			$('#titre_voir').html('Voir les acquisitions d\'une évaluation | '+groupe+' | '+info);
			$('#msg_voir').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
					data : 'f_action='+mode+'&f_ref='+ref,
					dataType : "html",
					error : function(msg,string)
					{
						$('#msg_voir').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer. <a class="fermer_zone_voir" href="#"><img alt="Retourner" src="./_img/action/action_retourner.png" /> retour</a>');
						return false;
					},
					success : function(responseHTML)
					{
						maj_clock(1);
						if(responseHTML.substring(0,1)!='<')
						{
							$('#msg_voir').removeAttr("class").addClass("alerte").html(responseHTML+' <a class="fermer_zone_voir" href="#"><img alt="Retourner" src="./_img/action/action_retourner.png" /> retour</a>');
						}
						else
						{
							$('#msg_voir').removeAttr("class").html('&nbsp;');
							$('#table_voir').html(responseHTML);
							$('#table_voir tbody td').css({"background-color":"#DDF","text-align":"center","vertical-align":"middle","font-size":"110%"});
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
			liste = $('#f_compet_liste').val();
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
					id = 'id_'+tab_id[i];
					if($('#'+id).length)
					{
						$('#'+id).attr('checked','true');
						$('#'+id).parent().parent().css("display","block");	// les items
						$('#'+id).parent().parent().parent().parent().css("display","block");	// le thème
						$('#'+id).parent().parent().parent().parent().parent().parent().css("display","block");	// le domaine
						$('#'+id).parent().parent().parent().parent().parent().parent().parent().parent().css("display","block");	// le niveau
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
			ref    = $(this).parent().attr('lang');
			groupe = $(this).parent().prev().prev().prev().html();
			info   = $(this).parent().prev().prev().html();
			// Masquer le tableau ; Afficher la zone associée et charger son contenu
			$('#form0 , #form1').hide('fast');
			$('#zone_ordonner').css("display","block");
			$('#titre_ordonner').html('Réordonner les items d\'une évaluation | '+groupe+' | '+info);
			$('#msg_ordonner').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
					data : 'f_action='+mode+'&f_ref='+ref,
					dataType : "html",
					error : function(msg,string)
					{
						$('#msg_ordonner').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer. <a class="fermer_zone_ordonner" href="#"><img alt="Retourner" src="./_img/action/action_retourner.png" /> retour</a>');
						return false;
					},
					success : function(responseHTML)
					{
						maj_clock(1);
						if(responseHTML.substring(0,16)!='<div class="hc">')
						{
							$('#msg_ordonner').removeAttr("class").addClass("alerte").html(responseHTML+' <a class="fermer_zone_ordonner" href="#"><img alt="Retourner" src="./_img/action/action_retourner.png" /> retour</a>');
						}
						else
						{
							modification = false;
							$('#msg_ordonner').removeAttr("class").html('&nbsp;');
							$('#div_ordonner').html(responseHTML);
							$('img[title]').tooltip({showURL:false});
							format_liens();
							infobulle();
						}
					}
				}
			);
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

		$('q.ordonner').live(        'click' , ordonner );
		$('q.imprimer').live(        'click' , imprimer );
		$('q.saisir').live(          'click' , saisir );
		$('q.voir').live(            'click' , voir );
		$('q.choisir_compet').live(  'click' , choisir_compet );

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le lien pour fermer le cadre des items associés à une évaluation (annuler / retour)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('a.annuler_compet').click
		(
			function()
			{
				$('#zone_compet').css("display","none");
				$('#form0 , #form1').show('fast');
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le lien pour fermer le formulaire servant à saisir les acquisitions des élèves à une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('a.fermer_zone_saisir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
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
//	Clic sur le lien pour fermer le formulaire servant à réordonner les items d'une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('a.fermer_zone_ordonner').live // live est utilisé pour prendre en compte les nouveaux éléments créés
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
//	Clic sur le lien pour fermer le formulaire servant à voir les acquisitions des élèves à une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('a.fermer_zone_voir').live // live est utilisé pour prendre en compte les nouveaux éléments créés
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
//	Clic sur le lien pour fermer le formulaire servant à imprimer le cartouche d'une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('a.fermer_zone_imprimer').click
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
//	Clic sur le lien pour valider le choix des items associés à une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('a.valider_compet').click
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
				$('a.annuler_compet').click();
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
					if( $(this).val()!='X' )
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
				$('#msg_imprimer').removeAttr("class").addClass("loader").html("Génération en cours... Veuillez patienter.");
				$('#zone_imprimer_retour').html("&nbsp;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
						data : 'f_action=Imprimer_cartouche&'+$("#zone_imprimer").serialize(),
						dataType : "html",
						error : function(msg,string)
						{
							$('#msg_imprimer').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
							return false;
						},
						success : function(responseHTML)
						{
							maj_clock(1);
							if(responseHTML.substring(0,6)!='<hr />')
							{
								$('#msg_imprimer').removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#msg_imprimer').removeAttr("class").addClass("valide").html("Cartouches générés !");
								$('#zone_imprimer_retour').html(responseHTML);
								format_liens();
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Choix du mode de pilotage pour la saisie des résultats
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#table_saisir thead tr td input').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				pilotage = $(this).val();
				if(pilotage=='clavier')
				{
					$("#1o1").focus();
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Gérer la saisie des acquisitions au clavier
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#table_saisir tbody td input').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('keyup',
			function(e)
			{
				if(pilotage=='clavier')
				{
					id = $(this).attr("id");
					colonne = parseInt(id.substring(0,id.indexOf('o')));
					ligne   = parseInt(id.substring(id.indexOf('o')+1));
					findme = '.'+e.which+'.';
					if('.8.46.49.50.51.52.65.68.78.97.98.99.100.'.indexOf(findme)!=-1)
					{
						// Une touche d'item a été pressée
						switch (e.which)
						{
							case 8: $(this).val('X').removeAttr("class").addClass('X'); break;				// backspace
							case 46: $(this).val('X').removeAttr("class").addClass('X'); break;				// suppr
							case 49: $(this).val('RR').removeAttr("class").addClass('RR'); break;			// 1
							case 97: $(this).val('RR').removeAttr("class").addClass('RR'); break;			// 1
							case 50: $(this).val('R').removeAttr("class").addClass('R'); break;				// 2
							case 98: $(this).val('R').removeAttr("class").addClass('R'); break;				// 2
							case 51: $(this).val('V').removeAttr("class").addClass('V'); break;				// 3
							case 99: $(this).val('V').removeAttr("class").addClass('V'); break;				// 3
							case 52: $(this).val('VV').removeAttr("class").addClass('VV'); break;			// 4
							case 100: $(this).val('VV').removeAttr("class").addClass('VV'); break;		// 4
							case 65: $(this).val('ABS').removeAttr("class").addClass('ABS'); break;		// A
							case 78: $(this).val('NN').removeAttr("class").addClass('NN'); break; 		// N
							case 68: $(this).val('DISP').removeAttr("class").addClass('DISP'); break;	// D
						}
						$(this).parent().css("background-color","#F6D").focus();
						modification = true;
						// Passer à la case suivante
						ligne++;
						new_id = colonne+'o'+ligne;
						if($('#'+new_id).length)
						{
							$('#'+new_id).focus();
						}
						else
						{
							ligne = 1;
							colonne++;
							new_id = colonne+'o'+ligne;
							if($('#'+new_id).length)
							{
								$('#'+new_id).focus();
							}
						}
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
						new_id = colonne+'o'+ligne;
						if($('#'+new_id).length)
						{
							$('#'+new_id).focus();
						}
					}
					else if(e.which==13)	// touche entrée
					{
						// La touche entrée a été pressée
						$('a.Enregistrer_saisie').click();
					}
					else if(e.which==27)
					{
						// La touche escape a été pressée
						$('a.fermer_zone_saisir').click();
					}
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
				if(pilotage=='souris')
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
						valeur = $(this).children("input").val();
						$(this).children("input").hide();
						$(this).removeAttr("class").addClass("td_souris").append( $("#td_souris_container").html() ).find("img[alt="+valeur+"]").addClass("on");
					}
				}
			}
		);

		// Revenir à la cellule initiale ; mouseout ne fonctionne pas à cause des éléments contenus dans le div ; mouseleave est mieux, mais pb qd même avec les select du calendrier
		$("#table_saisir tbody td").livequery
		('mouseleave',
			function()
			{
				if(pilotage=='souris')
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
				valeur = $(this).attr("alt");
				$("input#"+memo_input_id).val(valeur).removeAttr("class").addClass(valeur);
				$(this).parent().children("img").removeAttr("class");
				$(this).addClass("on").parent().parent().css("background-color","#F6D");
				modification = true;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur une image pour modifier l'ordre des items d'une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('input[type=image]').live
		('click',
			function()
			{
				para_clic = $(this).parent();
				para_prev = para_clic.prev('div');
				para_next = para_clic.next('div');
				para_clic.before(para_next);
				para_clic.after(para_prev);
				modification = true;
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le lien pour mettre à jour l'ordre des items d'une évaluation
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('a.Enregistrer_ordre').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if(!modification)
				{
					$('#msg_ordonner').removeAttr("class").addClass("alerte").html("Aucune modification effectuée !");
				}
				else
				{
					// On récupère la liste des items dans l'ordre de la page
					tab_id = new Array();
					$('#div_ordonner').children('div').each
					(
						function()
						{
							test_id = $(this).attr('id').substring(1);
							if(test_id)
							{
								tab_id.push(test_id);
							}
						}
					);
					$('#msg_ordonner').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
							data : 'f_action=Enregistrer_ordre&f_ref='+$('#f_ref').val()+'&tab_id='+tab_id,
							dataType : "html",
							error : function(msg,string)
							{
								$('#msg_ordonner').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
								return false;
							},
							success : function(responseHTML)
							{
								maj_clock(1);
								if(responseHTML.substring(0,1)!='<')
								{
									$('#msg_ordonner').removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{
									modification = false;
									$('#msg_ordonner').removeAttr("class").addClass("valide").html("Ordre enregistré !");
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

		$('a.Enregistrer_saisie').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if(!modification)
				{
					$('#msg_saisir').removeAttr("class").addClass("alerte").html("Aucune modification effectuée !");
				}
				else
				{
					$('#msg_saisir').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
							data : 'f_action=Enregistrer_saisie&'+$("#zone_saisir").serialize(),
							dataType : "html",
							error : function(msg,string)
							{
								$('#msg_saisir').removeAttr("class").addClass("alerte").html('Echec de la connexion ! Veuillez recommencer.');
								return false;
							},
							success : function(responseHTML)
							{
								maj_clock(1);
								if(responseHTML.substring(0,1)!='<')
								{
									$('#msg_saisir').removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{
									modification = false;
									$('#msg_saisir').removeAttr("class").addClass("valide").html("Saisies enregistrées !");
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
					f_groupe       : { required:true },
					f_info         : { required:false , maxlength:60 },
					f_compet_liste : { required:true }
				},
				messages :
				{
					f_date         : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
					f_groupe       : { required:"groupe manquant" },
					f_info         : { maxlength:"60 caractères maximum" },
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
			url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
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
				$(this).ajaxSubmit(ajaxOptions);
				return false;
			}
		); 

		// Fonction précédent l'envoi du formulaire (avec jquery.form.js)
		function test_form_avant_envoi(formData, jqForm, options)
		{
			$('#ajax_msg').removeAttr("class").html("&nbsp;");
			var readytogo = validation.form();
			if(readytogo)
			{
				$('#ajax_msg').parent().children('q').hide();
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée... Veuillez patienter.");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(msg,string)
		{
			$('#ajax_msg').parent().children('q').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez recommencer.");
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_valide(responseHTML)
		{
			maj_clock(1);
			$('#ajax_msg').parent().children('q').show();
			if(responseHTML.substring(0,1)!='<')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
				action = $('#f_action').val();
				switch (action)
				{
					case 'ajouter':
					case 'dupliquer':
						groupe_id = $("#f_groupe option:selected").val();
						new_td  = responseHTML.replace('<td>{{GROUPE_NOM}}</td>','<td>'+tab_groupes[groupe_id]+'</td>');
						new_tr  = '<tr class="new">'+new_td+'</tr>';
						$('table.form tbody').append(new_tr);
						$('q.valider').parent().parent().remove();
						break;
					case 'modifier':
						groupe_id = $("#f_groupe option:selected").val();
						new_td  = responseHTML.replace('<td>{{GROUPE_NOM}}</td>','<td>'+tab_groupes[groupe_id]+'</td>');
						$('q.valider').parent().parent().prev().addClass("new").html(new_td).show();
						$('q.valider').parent().parent().remove();
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

		//	Afficher masquer des options de la grille

		$('#f_aff_periode').change
		(
			function()
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
					f_date_debut : { required:function(){return $("#f_aff_periode").val()==0;} , dateITA:true },
					f_date_fin   : { required:function(){return $("#f_aff_periode").val()==0;} , dateITA:true }
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
			type : 'POST',
			url : 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER,
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
			maj_clock(1);
			if( (responseHTML.substring(0,4)!='<tr>') && (responseHTML!='') )
			{
				$('#ajax_msg0').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				$('#ajax_msg0').removeAttr("class").addClass("valide").html("Demande réalisée !");
				$('table.form tbody').html(responseHTML);
				trier_tableau();
				afficher_masquer_images_action('show');
				infobulle();
			}
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Traitement du clic sur le bouton pour envoyer un import csv (saisie déportée)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Envoi du fichier avec jquery.ajaxupload.js
		new AjaxUpload
		('#import_file',
			{
				action: 'ajax.php?dossier='+DOSSIER+'&fichier='+FICHIER+'&f_action=importer_saisie_csv',
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
			else if (fichier_extension!='csv' && fichier_extension!='txt')
			{
				$('#msg_import').removeAttr("class").addClass("erreur").html('Le fichier "'+fichier_nom+'" n\'a pas l\'extension "csv" ou "txt".');
				return false;
			}
			else
			{
				$('#msg_import').removeAttr("class").addClass("loader").html('Fichier envoyé... Veuillez patienter.');
				return true;
			}
		}

		function retourner_fichier(fichier_nom,responseHTML)
		{
			if(responseHTML.substring(0,1)!='|')
			{
				$('#msg_import').removeAttr("class").addClass("alerte").html(responseHTML);
			}
			else
			{
				maj_clock(1);
				if(responseHTML.length>2)
				{
					responseHTML = responseHTML.substring(1);
					tab_resultat = responseHTML.split('|');
					for (i=0 ; i<tab_resultat.length ; i++)
					{
						tab_valeur = tab_resultat[i].split('.');
						if(tab_valeur.length==3)
						{
							eleve_id = tab_valeur[0];
							item_id  = tab_valeur[1];
							score    = tab_valeur[2];
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

	}
);

