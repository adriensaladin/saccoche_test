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
		var sorting = [[6,0],[7,0]];
		$('table.form').tablesorter({ headers:{0:{sorter:false},9:{sorter:false},11:{sorter:false}} });
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
// Recharger la page en restreignant l'affichage en fonction des choix préalables
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function reload()
		{
			$('#ajax_msg0').addClass("loader").html("Connexion au serveur&hellip;");
			$('#form1').remove();
			$('#form0').submit();
		}

		$('#form0 select').change
		(
			function()
			{
				reload();
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
//	Clic pour tout cocher ou tout décocher
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#all_check').click
		(
			function()
			{
				$('#form1 td.nu input[type=checkbox]').prop('checked',true);
				return false;
			}
		);
		$('#all_uncheck').click
		(
			function()
			{
				$('#form1 td.nu input[type=checkbox]').prop('checked',false);
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le checkbox pour affecter ou non un nouveau mot de passe
//	Clic sur le checkbox pour choisir ou non une date de sortie
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		$('#box_password , #box_date').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				if($(this).is(':checked'))
				{
					$(this).next().show(0).next().hide(0);
				}
				else
				{
					$(this).next().hide(0).next().show(0);
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Fonctions utilisées
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		/**
		 * Ajouter un élève : mise en place du formulaire
		 * @return void
		 */
		var ajouter = function()
		{
			mode = $(this).attr('class');
			// Fabriquer la ligne avec les éléments de formulaires
			afficher_masquer_images_action('hide');
			new_tr  = '<tr>';
			new_tr += '<td class="nu"></td>';
			new_tr += '<td><input id="f_id_ent" name="f_id_ent" size="10" type="text" value="" /><img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d\'identification via un ENT." /></td>';
			new_tr += '<td><input id="f_id_gepi" name="f_id_gepi" size="10" type="text" value="" /><img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d\'utilisation du logiciel GEPI." /></td>';
			new_tr += '<td><input id="f_sconet_id" name="f_sconet_id" size="5" type="text" value="" /><img alt="" src="./_img/bulle_aide.png" title="Champ de Sconet ELEVE.ELEVE_ID (laisser vide si inconnu)." /></td>';
			new_tr += '<td><input id="f_sconet_num" name="f_sconet_num" size="5" type="text" value="" /><img alt="" src="./_img/bulle_aide.png" title="Champ de Sconet ELEVE.ELENOET (laisser vide si inconnu)." /></td>';
			new_tr += '<td><input id="f_reference" name="f_reference" size="10" type="text" value="" /><img alt="" src="./_img/bulle_aide.png" title="Sconet : champ ELEVE.ID_NATIONAL (laisser vide si inconnu).<br />Tableur : référence dans l\'établissement." /></td>';
			new_tr += '<td><input id="f_nom" name="f_nom" size="15" type="text" value="" /></td>';
			new_tr += '<td><input id="f_prenom" name="f_prenom" size="15" type="text" value="" /></td>';
			new_tr += '<td class="i">forme "'+select_login+'"</td>';
			new_tr += '<td><input id="f_password" name="f_password" size="8" type="text" value="" /></td>';
			new_tr += '<td><input id="box_date" name="box_date" value="1" type="checkbox" checked style="vertical-align:-3px" /> <span style="vertical-align:-2px">sans objet</span><span class="hide"><input id="f_sortie_date" name="f_sortie_date" size="8" type="text" value="'+input_date+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></span></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_groupe" name="f_groupe" type="hidden" value="'+$('#f_groupes option:selected').val()+'" /><q class="valider" title="Valider l\'ajout de cet élève."></q><q class="annuler" title="Annuler l\'ajout de cet élève."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Ajouter cette nouvelle ligne
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_nom').focus();
		};

		/**
		 * Modifier un élève : mise en place du formulaire
		 * @return void
		 */
		var modifier = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			// Récupérer les informations de la ligne concernée
			var id         = $(this).parent().parent().attr('id').substring(3);
			var id_ent     = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().prev().prev().html();
			var id_gepi    = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().prev().html();
			var sconet_id  = $(this).parent().prev().prev().prev().prev().prev().prev().prev().prev().html();
			var sconet_num = $(this).parent().prev().prev().prev().prev().prev().prev().prev().html();
			var reference  = $(this).parent().prev().prev().prev().prev().prev().prev().html();
			var nom        = $(this).parent().prev().prev().prev().prev().prev().html();
			var prenom     = $(this).parent().prev().prev().prev().prev().html();
			var login      = $(this).parent().prev().prev().prev().html();
			var date       = $(this).parent().prev().html();
			var date_mysql = date.substring(3,13); // garder la date mysql
			var date_fr    = date.substring(17,date.length); // garder la date française
			if(date_fr=='-') { var date_checked = ' checked'; var date_classe1 = ''; var date_classe2 = ' class="hide"'; date_sortie = input_date; }
			else             { var date_checked = '';         var date_classe2 = ''; var date_classe1 = ' class="hide"'; date_sortie = date_fr; }
			// Retirer une éventuelle balise image présente
			position_image = login.indexOf('<');
			if (position_image!=-1)
			{
				login = login.substring(0,position_image-1);
			}
			// Fabriquer la ligne avec les éléments de formulaires
			new_tr  = '<tr>';
			new_tr += '<td class="nu"></td>';
			new_tr += '<td><input id="f_id_ent" name="f_id_ent" size="'+Math.max(id_ent.length,10)+'" type="text" value="'+escapeQuote(id_ent)+'" /><img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d\'identification via un ENT." /></td>';
			new_tr += '<td><input id="f_id_gepi" name="f_id_gepi" size="'+Math.max(id_gepi.length,10)+'" type="text" value="'+escapeQuote(id_gepi)+'" /><img alt="" src="./_img/bulle_aide.png" title="Uniquement en cas d\'utilisation du logiciel GEPI." /></td>';
			new_tr += '<td><input id="f_sconet_id" name="f_sconet_id" size="5" type="text" value="'+sconet_id+'" /><img alt="" src="./_img/bulle_aide.png" title="Champ de Sconet ELEVE.ELEVE_ID (laisser à 0 si inconnu)." /></td>';
			new_tr += '<td><input id="f_sconet_num" name="f_sconet_num" size="5" type="text" value="'+sconet_num+'" /><img alt="" src="./_img/bulle_aide.png" title="Champ de Sconet ELEVE.ELENOET (laisser à 0 si inconnu)." /></td>';
			new_tr += '<td><input id="f_reference" name="f_reference" size="10" type="text" value="'+escapeQuote(reference)+'" /><img alt="" src="./_img/bulle_aide.png" title="Sconet : champ ELEVE.ID_NATIONAL (laisser vide si inconnu).<br />Tableur : référence dans l\'établissement." /></td>';
			new_tr += '<td><input id="f_nom" name="f_nom" size="'+Math.max(nom.length,5)+'" type="text" value="'+escapeQuote(nom)+'" /></td>';
			new_tr += '<td><input id="f_prenom" name="f_prenom" size="'+Math.max(prenom.length,5)+'" type="text" value="'+escapeQuote(prenom)+'" /></td>';
			new_tr += '<td><input id="f_login" name="f_login" size="'+Math.max(login.length,10)+'" type="text" value="'+login+'" /></td>';
			new_tr += '<td><input id="box_password" name="box_password" value="1" type="checkbox" checked style="vertical-align:-3px" /> <span style="vertical-align:-2px">inchangé</span><span class="hide"><input id="f_password" name="f_password" size="6" type="text" value="" /></span></td>';
			new_tr += '<td><input id="box_date" name="box_date" value="1" type="checkbox"'+date_checked+' style="vertical-align:-3px" /> <span'+date_classe1+' style="vertical-align:-2px">sans objet</span><span'+date_classe2+'><input id="f_sortie_date" name="f_sortie_date" size="8" type="text" value="'+date_sortie+'" /><q class="date_calendrier" title="Cliquez sur cette image pour importer une date depuis un calendrier !"></q></span></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_id" name="f_id" type="hidden" value="'+id+'" /><q class="valider" title="Valider les modifications de cet élève."></q><q class="annuler" title="Annuler les modifications de cet élève."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Cacher la ligne en cours et ajouter la nouvelle
			$(this).parent().parent().hide();
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_nom').focus();
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('q.ajouter').click( ajouter );
		$('q.modifier').live(   'click' , modifier );
		$('q.annuler').live(    'click' , annuler );
		$('q.valider').live(    'click' , function(){formulaire.submit();} );
		$('table.form input , table.form select').live( 'keyup' , function(e){intercepter(e);} );

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form1');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_id_ent      : { required:false , maxlength:63 },
					f_id_gepi     : { required:false , maxlength:63 },
					f_sconet_id   : { required:false , digits:true , max:16777215 },
					f_sconet_num  : { required:false , digits:true , max:65535 },
					f_reference   : { required:false , maxlength:11 },
					f_nom         : { required:true , maxlength:25 },
					f_prenom      : { required:true , maxlength:25 },
					f_login       : { required:true , maxlength:20 },
					f_password    : { required:function(){return !$('#box_password').is(':checked');} , minlength:mdp_longueur_mini , maxlength:20 },
					f_sortie_date : { required:function(){return !$('#box_date').is(':checked');} , dateITA:true }
				},
				messages :
				{
					f_id_ent      : { maxlength:"identifiant ENT de 63 caractères maximum" },
					f_id_gepi     : { maxlength:"identifiant Gepi de 63 caractères maximum" },
					f_sconet_id   : { digits:"Id Sconet : nombre entier inférieur à 2^24" },
					f_sconet_num  : { digits:"N° Sconet : nombre entier inférieur à 2^16" },
					f_reference   : { maxlength:"référence de 11 caractères maximum" },
					f_nom         : { required:"nom manquant"    , maxlength:"25 caractères maximum" },
					f_prenom      : { required:"prénom manquant" , maxlength:"25 caractères maximum" },
					f_login       : { required:"login manquant"  , maxlength:"20 caractères maximum" },
					f_password    : { required:"mot de passe manquant" , minlength:mdp_longueur_mini+" caractères minimum" , maxlength:"20 caractères maximum" },
					f_sortie_date : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" }
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
			// Décocher les checkbox sans rapport avec ce formulaire
			$('input[name=f_ids]:checked').each
			(
				function()
				{
					$(this).prop('checked',false);
				}
			);
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
		function retour_form_erreur(msg,string)
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
						$('table.form tbody').append(responseHTML);
						$('q.valider').parent().parent().remove();
						break;
					case 'modifier':
						$('q.valider').parent().parent().prev().addClass("new").html(responseHTML).show();
						$('q.valider').parent().parent().remove();
						break;
				}
				trier_tableau();
				afficher_masquer_images_action('show');
				infobulle();
			}
		} 

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur un bouton pour effectuer une action sur les utilisateurs cochés
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#zone_actions button').click
		(
			function()
			{
				var listing_id = new Array(); $("input[name=f_ids]:checked").each(function(){listing_id.push($(this).val());});
				if(!listing_id.length)
				{
					$('#ajax_msg1').removeAttr("class").addClass("erreur").html("Aucun utilisateur coché !");
					return false;
				}
				var f_action = $(this).attr('id');
				// On demande confirmation pour la suppression
				if(f_action=='supprimer')
				{
					continuer = (confirm("Attention : les informations associées seront perdues !\nConfirmez-vous la suppression des comptes sélectionnés ?")) ? true : false ;
				}
				else
				{
					continuer = true ;
				}
				if(continuer)
				{
					$('#ajax_msg1').removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
					$('#zone_actions button').prop('disabled',true);
					$.ajax
					(
						{
							type : 'POST',
							url : 'ajax.php?page='+'administrateur_comptes',
							data : 'f_action='+f_action+'&f_listing_id='+listing_id,
							dataType : "html",
							error : function(msg,string)
							{
								$('#ajax_msg1').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
								$('#zone_actions button').prop('disabled',false);
							},
							success : function(responseHTML)
							{
								initialiser_compteur();
								tab_response = responseHTML.split(',');
								if(tab_response[0]!='ok')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
								{
									$('#ajax_msg1').removeAttr("class").addClass("alerte").html(responseHTML);
								}
								else
								{
									$('#ajax_msg1').removeAttr("class").addClass("valide").html("Demande réalisée.");
									for ( i=1 ; i<tab_response.length ; i++ )
									{
										switch (f_action)
										{
											case 'retirer':
												$('#id_'+tab_response[i]).children("td:last").prev().html('<i>'+date_mysql+'</i>'+input_date);
												break;
											case 'reintegrer':
												$('#id_'+tab_response[i]).children("td:last").prev().html('<i>9999-12-31</i>-');
												break;
											case 'supprimer':
												$('#id_'+tab_response[i]).remove();
												break;
										}
									}
								}
								$('#zone_actions button').prop('disabled',false);
							}
						}
					);
				}
			}
		);

	}
);
