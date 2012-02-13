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
		var sorting = [[1,0],[0,0]];
		$('#form_partage table.form').tablesorter({ headers:{2:{sorter:false}} });
		$('#form_perso   table.form').tablesorter({ headers:{2:{sorter:false}} });
		function trier_tableau()
		{
			if($('#form_partage table.form tbody tr').length)
			{
				$('#form_partage table.form').trigger('update');
				$('#form_partage table.form').trigger('sorton',[sorting]);
			}
			if($('#form_perso table.form tbody tr').length)
			{
				$('#form_perso table.form').trigger('update');
				$('#form_perso table.form').trigger('sorton',[sorting]);
			}
		}
		trier_tableau();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Fonctions utilisées
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		/**
		 * Ajouter une matière partagée : affichage du formulaire
		 * @return void
		 */
		var ajouter_partage = function()
		{
			mode = 'ajouter_partage';
			$('#ajax_msg_recherche').removeAttr("class").html("&nbsp;");
			$('#form_partage, #form_perso, #form_move').hide();
			$('#zone_ajout_form').show();
			return false;
		};

		/**
		 * Ajouter une matière spécifique : mise en place du formulaire
		 * @return void
		 */
		var ajouter_perso = function()
		{
			mode = 'ajouter_perso';
			// Fabriquer la ligne avec les éléments de formulaires
			afficher_masquer_images_action('hide');
			new_tr  = '<tr>';
			new_tr += '<td><input id="f_ref" name="f_ref" size="5" type="text" value="" /></td>';
			new_tr += '<td><input id="f_nom" name="f_nom" size="30" type="text" value="" /></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><q class="valider" title="Valider l\'ajout de cette matière."></q><q class="annuler" title="Annuler l\'ajout de cette matière."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Ajouter cette nouvelle ligne
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_ref').focus();
		};

		/**
		 * Modifier une matière : mise en place du formulaire
		 * @return void
		 */
		var modifier = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			// Récupérer les informations de la ligne concernée
			id  = $(this).parent().parent().attr('id').substring(3);
			ref = $(this).parent().prev().prev().html();
			nom = $(this).parent().prev().html();
			// Fabriquer la ligne avec les éléments de formulaires
			new_tr  = '<tr>';
			new_tr += '<td><input id="f_ref" name="f_ref" size="5" type="text" value="'+ref+'" /></td>';
			new_tr += '<td><input id="f_nom" name="f_nom" size="'+Math.max(nom.length,10)+'" type="text" value="'+escapeQuote(nom)+'" /></td>';
			new_tr += '<td class="nu"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_id" name="f_id" type="hidden" value="'+id+'" /><q class="valider" title="Valider les modifications de cette matière."></q><q class="annuler" title="Annuler les modifications de cette matière."></q> <label id="ajax_msg">&nbsp;</label></td>';
			new_tr += '</tr>';
			// Cacher la ligne en cours et ajouter la nouvelle
			$(this).parent().parent().hide();
			$(this).parent().parent().after(new_tr);
			infobulle();
			$('#f_ref').focus();
		};

		/**
		 * Supprimer une matière : mise en place du formulaire
		 * @return void
		 */
		var supprimer = function()
		{
			mode = $(this).attr('class');
			afficher_masquer_images_action('hide');
			id = $(this).parent().parent().attr('id').substring(3);
			texte = (id<id_matiere_transversale) ? "Les référentiels et les résultats associés ne seront plus accessibles !" : "Les référentiels et les résultats associés seront perdus !" ;
			new_span  = '<span class="danger"><input id="f_action" name="f_action" type="hidden" value="'+mode+'" /><input id="f_id" name="f_id" type="hidden" value="'+id+'" />'+texte+'<q class="valider" title="Confirmer la suppression de cette matière."></q><q class="annuler" title="Annuler la suppression de cette matière."></q> <label id="ajax_msg">&nbsp;</label></span>';
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
				case 'ajouter_perso':
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

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur un bouton pour confirmer le retrait d'une matière partagée
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var retirer_partage = function()
		{
			$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée...");
			$('#ajax_msg').parent().children('q').hide();
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : 'f_action=supprimer&f_id='+$('#f_id').val(),
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg').parent().children('q').show();
						$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
						return false;
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						$('#ajax_msg').parent().children('q').show();
						var tab_infos = responseHTML.split(']¤[');
						if(tab_infos[0]!='')
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html(tab_infos[0]);
						}
						else
						{
							var matiere_id = tab_infos[1];
							$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
							$('q.valider').parent().parent().parent().remove();
							afficher_masquer_images_action('show');
							if(matiere_id!=id_matiere_transversale)
							{
								$('#f_matiere_avant option[value='+matiere_id+']').remove();
								$('#f_matiere_apres option[value='+matiere_id+']').remove();
							}
						}
					}
				}
			);
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
		 * Intercepter la touche entrée ou escape pour valider ou annuler les modifications
		 * @return void
		 */
		function intercepter_motclef(e)
		{
			if(e.which==13)	// touche entrée
			{
				$('#rechercher_motclef').click();
				return false;
			}
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#form_partage q.ajouter').click( ajouter_partage );
		$('#form_perso   q.ajouter').click( ajouter_perso );
		$('q.modifier').live(  'click' , modifier );
		$('q.supprimer').live( 'click' , supprimer );
		$('q.annuler').live(   'click' , annuler );
		$('#form_partage q.valider').live( 'click' , retirer_partage );
		$('#form_perso   q.valider').live( 'click' , function(){formulaire.submit();} );
		$('#form_perso input').live( 'keyup' , function(e){intercepter(e);} );
		$('#f_motclef').live(        'keyup' , function(e){intercepter_motclef(e);} );

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour fermer le cadre de recherche d'une matière partagée à ajouter
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#ajout_annuler').click
		(
			function()
			{
				$('#zone_ajout_form').hide();
				$('#form_partage, #form_perso, #form_move').show();
				return(false);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Choix du mode de recherche d'une matière partagée
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#f_recherche_mode input').click
		(
			function()
			{
				mode = $(this).val();
				$("#f_recherche_resultat").html('<li></li>').hide();
				$('#ajax_msg_recherche').removeAttr("class").html("&nbsp;");
				if(mode=='famille')
				{
					$('#f_famille option[value=0]').prop('selected',true);
					$("#f_recherche_motclef").hide();
					$("#f_recherche_famille").show();
				}
				else if(mode=='motclef')
				{
					$("#f_recherche_famille").hide();
					$("#f_recherche_motclef").show();
					$("#f_motclef").focus();
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Actualisation du résultat de la recherche des matières par famille ou mot clef
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function maj_resultat_recherche(data_action,data_parametre)
		{
			$('#ajax_msg_recherche').removeAttr("class").addClass("loader").html("Recherche en cours...");
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page='+PAGE,
					data : data_action+'&'+data_parametre,
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg_recherche').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,3)=='<li')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_msg_recherche').removeAttr("class").html("&nbsp;");
							$('#f_recherche_resultat').html(responseHTML).show();
							infobulle();
						}
						else
						{
							$('#ajax_msg_recherche').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Changement du select f_famille => actualisation du résultat de la recherche
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#f_famille").change
		(
			function()
			{
				$("#f_recherche_resultat").html('<li></li>').hide();
				var famille_id = parseInt( $("#f_famille option:selected").val() ,10);
				if(famille_id)
				{
					maj_resultat_recherche( 'f_action=recherche_matiere_famille' , 'f_famille='+famille_id )
				}
				else
				{
					$('#ajax_msg_recherche').removeAttr("class").html("&nbsp;");
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur bouton rechercher_motclef => actualisation du résultat de la recherche
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#rechercher_motclef').click
		(
			function()
			{
				$("#f_recherche_resultat").html('<li></li>').hide();
				var motclef = $("#f_motclef").val();
				if(motclef!='')
				{
					maj_resultat_recherche( 'f_action=recherche_matiere_motclef' , 'f_motclef='+encodeURIComponent(motclef) )
				}
				else
				{
					$('#ajax_msg_recherche').removeAttr("class").addClass("danger").html("Indiquer des mots clefs !");
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur un bouton pour ajouter une matière partagée trouvée suite à une recherche
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#f_recherche_resultat q.ajouter').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				// afficher_masquer_images_action('hide');
				var matiere_id = $(this).attr('id').substr(4); // add_
				$('#ajax_msg_recherche').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action=ajouter_partage&f_matiere='+matiere_id,
						dataType : "html",
						error : function(msg,string)
						{
							afficher_masquer_images_action('show');
							$('#ajax_msg_recherche').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							afficher_masquer_images_action('show');
							if(responseHTML=='ok')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								$('#ajax_msg_recherche').removeAttr("class").addClass("valide").html("Matière ajoutée.");
								var texte = $('#add_'+matiere_id).parent().text();
								var pos_separe  = (texte.indexOf('|')==-1) ? 0 : texte.lastIndexOf('|')+2 ;
								var pos_par_ouv = texte.lastIndexOf('(');
								var pos_par_fer = texte.lastIndexOf(')');
								var matiere_nom = texte.substring(pos_separe,pos_par_ouv-1);
								var matiere_ref = texte.substring(pos_par_ouv+1,pos_par_fer);
								$('#form_partage table.form tbody').append('<tr id="id_'+matiere_id+'"><td>'+matiere_ref+'</td><td>'+matiere_nom+'</td><td class="nu"><q class="supprimer" title="Supprimer cette matière."></q></td></tr>');
								$('#add_'+matiere_id).removeAttr("class").addClass("ajouter_non").attr('title',"Matière déjà choisie.");
								infobulle();
								trier_tableau();
								$('#f_matiere_avant').append('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
								$('#f_matiere_apres').append('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
							}
							else
							{
								$('#ajax_msg_recherche').removeAttr("class").addClass("alerte").html(responseHTML);
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Clic sur le bouton pour déplacer les référentiels d'une matière vers une autre
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#deplacer_referentiels').click
		(
			function()
			{
				var matiere_id_avant = parseInt( $("#f_matiere_avant option:selected").val() ,10);
				var matiere_id_apres = parseInt( $("#f_matiere_apres option:selected").val() ,10);
				if(!matiere_id_avant)
				{
					$('#ajax_msg_move').removeAttr("class").addClass("erreur").html("Sélectionner une ancienne matière !");
					$("#f_matiere_avant").focus();
					return false;
				}
				if(!matiere_id_apres)
				{
					$('#ajax_msg_move').removeAttr("class").addClass("erreur").html("Sélectionner une nouvelle matière !");
					$("#f_matiere_apres").focus();
					return false;
				}
				if(matiere_id_avant==matiere_id_apres)
				{
					$('#ajax_msg_move').removeAttr("class").addClass("erreur").html("Sélectionner des matières différentes !");
					return false;
				}
				$('button').prop('disabled',true);
				$('#ajax_msg_move').removeAttr("class").addClass("loader").html("Demande envoyée...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action=deplacer_referentiels&f_id_avant='+matiere_id_avant+'&f_id_apres='+matiere_id_apres,
						dataType : "html",
						error : function(msg,string)
						{
							$('button').prop('disabled',false);
							$('#ajax_msg_move').removeAttr("class").addClass("alerte").html("Echec de la connexion !");
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('button').prop('disabled',false);
							if(responseHTML=='ok')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								if(matiere_id_avant!=id_matiere_transversale)
								{
									$('#f_matiere_avant option[value='+matiere_id_avant+']').remove();
									$('#f_matiere_apres option[value='+matiere_id_avant+']').remove();
								}
								$('#id_'+matiere_id_avant).remove();
								$('#ajax_msg_move').removeAttr("class").addClass("valide").html("Transfert effectué.");
							}
							else
							{
								$('#ajax_msg_move').removeAttr("class").addClass("alerte").html(responseHTML);
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Traitement du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		// Le formulaire qui va être analysé et traité en AJAX
		var formulaire = $('#form_perso');

		// Vérifier la validité du formulaire (avec jquery.validate.js)
		var validation = formulaire.validate
		(
			{
				rules :
				{
					f_ref : { required:true , maxlength:5 },
					f_nom : { required:true , maxlength:50 }
				},
				messages :
				{
					f_ref : { required:"référence manquante" , maxlength:"5 caractères maximum" },
					f_nom : { required:"nom manquant" , maxlength:"50 caractères maximum" }
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
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Demande envoyée...");
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
			var tab_infos = responseHTML.split(']¤[');
			if(tab_infos[0]!='')
			{
				$('#ajax_msg').removeAttr("class").addClass("alerte").html(tab_infos[0]);
			}
			else
			{
				$('#ajax_msg').removeAttr("class").addClass("valide").html("Demande réalisée !");
				action = $('#f_action').val();
				switch (action)
				{
					case 'ajouter_perso':
						var matiere_id  = tab_infos[1];
						var matiere_ref = tab_infos[2];
						var matiere_nom = tab_infos[3];
						new_tr = '<tr id="id_'+matiere_id+'" class="new"><td>'+matiere_ref+'</td><td>'+matiere_nom+'</td><td class="nu"><q class="modifier" title="Modifier cette matière."></q><q class="supprimer" title="Supprimer cette matière."></q></td></tr>';
						$('#form_perso table.form tbody').append(new_tr);
						$('q.valider').parent().parent().remove();
						$('#f_matiere_avant').append('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
						$('#f_matiere_apres').append('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
						break;
					case 'modifier':
						var matiere_id  = tab_infos[1];
						var matiere_ref = tab_infos[2];
						var matiere_nom = tab_infos[3];
						new_td = '<td>'+matiere_ref+'</td><td>'+matiere_nom+'</td><td class="nu"><q class="modifier" title="Modifier cette matière."></q><q class="supprimer" title="Supprimer cette matière."></q></td>';
						$('q.valider').parent().parent().prev().addClass("new").html(new_td).show();
						$('q.valider').parent().parent().remove();
						$('#f_matiere_avant option[value='+matiere_id+']').replaceWith('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
						$('#f_matiere_apres option[value='+matiere_id+']').replaceWith('<option value="'+matiere_id+'">'+matiere_nom+' ('+matiere_ref+')</option>');
						break;
					case 'supprimer':
						var matiere_id = tab_infos[1];
						$('q.valider').parent().parent().parent().remove();
						$('#f_matiere_avant option[value='+matiere_id+']').remove();
						$('#f_matiere_apres option[value='+matiere_id+']').remove();
						break;
				}
				trier_tableau();
				afficher_masquer_images_action('show');
				infobulle();
			}
		} 

	}
);
