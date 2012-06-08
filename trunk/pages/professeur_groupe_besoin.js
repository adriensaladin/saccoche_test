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

		// tri du tableau (avec jquery.tablesorter.js).
		var sorting = [[0,0],[1,0]]; 
		$('table.form').tablesorter({ headers:{2:{sorter:false},3:{sorter:false},4:{sorter:false}} });
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
		 * Ajouter un groupe de besoin : mise en place du formulaire
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
				reception_users_liste  = '';
			}
			// Fabriquer la ligne avec les éléments de formulaires
			afficher_masquer_images_action('hide');
			new_tr  = '<tr>';
			new_tr += '<td><select id="f_niveau" name="f_niveau">'+select_niveau+'</select></td>';
			new_tr += '<td><input id="f_nom" name="f_nom" size="20" type="text" value="" /></td>';
			new_tr += '<td><input id="f_eleve_nombre" name="f_eleve_nombre" size="10" type="text" value="'+reception_users_texte+'" readonly /><input id="f_eleve_liste" name="f_eleve_liste" type="hidden" value="'+reception_users_liste+'" /><q class="choisir_eleve" title="Voir ou choisir les élèves."></q></td>';
			new_tr += '<td><input id="f_prof_nombre" name="f_prof_nombre" size="10" type="text" value="moi seul" readonly /><input id="f_prof_liste" name="f_prof_liste" type="hidden" value="" /><q class="choisir_prof" title="Voir ou choisir les collègues."></q></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><q class="valider" title="Valider l\'ajout de ce groupe de besoin."></q><q class="annuler" title="Annuler l\'ajout de ce groupe de besoin."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Ajouter cette nouvelle ligne
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_nom').focus();
		};

		/**
		 * Modifier un groupe de besoin : mise en place du formulaire
		 * @return void
		 */
		var modifier = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			// Récupérer les informations de la ligne concernée
			var id            = $(this).parent().parent().attr('id').substring(3);
			var niveau_nom    = $(this).parent().prev().prev().prev().prev().html();
			var nom           = $(this).parent().prev().prev().prev().html();
			var eleve_nombre  = $(this).parent().prev().prev().html();
			var eleve_liste   = tab_eleves[id];
			var prof_nombre   = $(this).parent().prev().html();
			var prof_liste    = tab_profs[id];
			// enlever l'ordre du niveau caché
			niveau_nom = niveau_nom.substring(9,niveau_nom.length);
			// Fabriquer la ligne avec les éléments de formulaires
			new_tr  = '<tr>';
			new_tr += '<td><select id="f_niveau" name="f_niveau">'+select_niveau.replace('>'+niveau_nom,' selected>'+niveau_nom)+'</select></td>';
			new_tr += '<td><input id="f_nom" name="f_nom" size="'+Math.max(nom.length,5)+'" type="text" value="'+escapeQuote(nom)+'" /></td>';
			new_tr += '<td><input id="f_eleve_nombre" name="f_eleve_nombre" size="10" type="text" value="'+eleve_nombre+'" readonly /><input id="f_eleve_liste" name="f_eleve_liste" type="hidden" value="'+eleve_liste+'" /><q class="choisir_eleve" title="Voir ou choisir les élèves."></q></td>';
			new_tr += '<td><input id="f_prof_nombre" name="f_prof_nombre" size="10" type="text" value="'+prof_nombre+'" readonly /><input id="f_prof_liste" name="f_prof_liste" type="hidden" value="'+prof_liste+'" /><q class="choisir_prof" title="Voir ou choisir les collègues."></q></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_id" name="f_id" type="hidden" value="'+id+'" /><q class="valider" title="Valider les modifications de ce groupe de besoin."></q><q class="annuler" title="Annuler les modifications de ce groupe de besoin."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Cacher la ligne en cours et ajouter la nouvelle
			$(this).parent().parent().hide();
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_nom').focus();
		};

		/**
		 * Supprimer un groupe de besoin : mise en place du formulaire
		 * @return void
		 */
		var supprimer = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			id = $(this).parent().parent().attr('id').substring(3);
			new_span  = '<span class="danger"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_id" name="f_id" type="hidden" value="'+id+'" />Les associations des élèves, des professeurs, et les évaluations seront perdues !<q class="valider" title="Confirmer la suppression de ce groupe de besoin."></q><q class="annuler" title="Annuler la suppression de ce groupe de besoin."></q> <label id="ajax_msg">&nbsp;</label></span>';
			$(this).after(new_span);
			infobulle();
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
			mode = false;
		};

		/**
		 * Intercepter la touche entrée ou escape pour valider ou annuler les modifications
		 * @return void
		 */
		function intercepter(e)
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

		/**
		 * Choisir les élèves associés à un groupe : mise en place du formulaire
		 * @return void
		 */
		var choisir_eleve = function()
		{
			// Ne pas changer ici la valeur de "mode" (qui est à "ajouter" ou "modifier" ou "dupliquer").
			$('#zone_eleve q.date_calendrier').show();
			$('#zone_eleve li.li_m1 span.gradient_pourcent').html('');
			cocher_eleves( $('#f_eleve_liste').val() );
			// Afficher la zone
			$.fancybox( { 'href':'#zone_eleve' , onStart:function(){$('#zone_eleve').css("display","block");} , onClosed:function(){$('#zone_eleve').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
		};

		/**
		 * Choisir les professeurs associés à un groupe : mise en place du formulaire
		 * @return void
		 */
		var choisir_prof = function()
		{
			cocher_profs( $('#f_prof_liste').val() );
			// Afficher la zone
			$.fancybox( { 'href':'#zone_profs' , onStart:function(){$('#zone_profs').css("display","block");} , onClosed:function(){$('#zone_profs').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
		};

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('q.ajouter').click( ajouter );
		$('q.modifier').live(  'click' , modifier );
		$('q.supprimer').live( 'click' , supprimer );
		$('q.annuler').live(   'click' , annuler );
		$('q.valider').live(   'click' , function(){formulaire.submit();} );
		$('table.form input , table.form select').live( 'keyup' , function(e){intercepter(e);} );

		$('q.choisir_eleve').live(  'click' , choisir_eleve );
		$('q.choisir_prof').live(   'click' , choisir_prof );

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Cocher / décocher par lot des individus
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer le cadre des élèves associés à un groupe (annuler / retour)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#annuler_eleve').click
		(
			function()
			{
				$.fancybox.close();
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer le cadre des professeurs associés à un groupe (annuler / retour)
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#annuler_profs').click
		(
			function()
			{
				$.fancybox.close();
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour valider le choix des élèves associés à un groupe
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour valider le choix des profs associés à un groupe
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
				nombre = (nombre==1) ? 'moi seul' : nombre+' profs' ;
				$('#f_prof_liste').val(liste);
				$('#f_prof_nombre').val(nombre);
				$.fancybox.close();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('form');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_niveau       : { required:true },
					f_nom          : { required:true , maxlength:20 },
					f_eleve_nombre : { accept:'élève|élèves' },
					f_prof_nombre  : { required:false }
				},
				messages :
				{
					f_niveau       : { required:"niveau manquant" },
					f_nom          : { required:"nom manquant" , maxlength:"20 caractères maximum" },
					f_eleve_nombre : { accept:"élève(s) manquant(s)" },
					f_prof_nombre  : { }
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
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
			}
			return readytogo;
		}

		// Fonction suivant l'envoi du formulaire (avec jquery.form.js)
		function retour_form_erreur(jqXHR, textStatus, errorThrown)
		{
			please_wait = false;
			$('#ajax_msg').parent().children('q').show();
			$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
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
				action = $('#f_action').val();
				switch (action)
				{
					case 'ajouter':
						$('table.form tbody tr td[colspan]').parent().remove(); // En cas de tableau avec une ligne vide pour la conformité XHTML
						var position_script = responseHTML.lastIndexOf('<SCRIPT>');
						var niveau_nom = $('#f_niveau option:selected').text();
						var new_tr = responseHTML.substring(0,position_script).replace('<td>{{NIVEAU_NOM}}</td>','<td>'+'<i>'+tab_niveau_ordre[niveau_nom]+'</i>'+niveau_nom+'</td>');
						$('table.form tbody').append(new_tr);
						$('q.valider').parent().parent().remove();
						eval( responseHTML.substring(position_script+8) );
						break;
					case 'modifier':
						var position_script = responseHTML.lastIndexOf('<SCRIPT>');
						var niveau_nom = $('#f_niveau option:selected').text();
						var new_tds = responseHTML.substring(0,position_script).replace('<td>{{NIVEAU_NOM}}</td>','<td>'+'<i>'+tab_niveau_ordre[niveau_nom]+'</i>'+niveau_nom+'</td>');
						$('q.valider').parent().parent().prev().addClass("new").html(new_tds).show();
						$('q.valider').parent().parent().remove();
						eval( responseHTML.substring(position_script+8) );
						break;
					case 'supprimer':
						$('q.valider').closest('tr').remove();
						break;
				}
				trier_tableau();
				afficher_masquer_images_action('show');
				infobulle();
			}
		} 

		// Initialiser l'affichage au démarrage
		if( reception_todo )
		{
			$('q.ajouter').click();
		}

	}
);
