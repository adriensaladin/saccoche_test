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
//	Variables devant être accessible
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		var memo_td_html = '';
		var eleve_id = 0;

		// Initialisation

		$("#select_eleve").hide();

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Charger le select f_eleve en ajax
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function maj_eleve(groupe_id,groupe_type)
		{
			$.ajax
			(
				{
					type : 'POST',
					url : 'ajax.php?page=_maj_select_eleves',
					data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_statut=1'+'&f_multiple=0',
					dataType : "html",
					error : function(msg,string)
					{
						$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
					},
					success : function(responseHTML)
					{
						initialiser_compteur();
						if(responseHTML.substring(0,7)=='<option')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
						{
							$('#ajax_msg').removeAttr("class").addClass("valide").html("");
							$('#select_eleve').html(responseHTML).show();
						}
						else
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
						}
					}
				}
			);
		}
		function changer_groupe()
		{
			$("#select_eleve").html('<option value=""></option>').hide();
			var groupe_val = $("#f_groupe").val();
			if(groupe_val)
			{
				// type = $("#f_groupe option:selected").parent().attr('label');
				groupe_type = groupe_val.substring(0,1);
				groupe_id   = groupe_val.substring(1);
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
				maj_eleve(groupe_id,groupe_type);
			}
			else
			{
				$('#ajax_msg').removeAttr("class").html("&nbsp;");
			}
		}
		$("#f_groupe").change
		(
			function()
			{
				changer_groupe();
			}
		);

		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
		//	Charger la liste des parents d'un élève
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#select_eleve").change
		(
			function()
			{
				$("#fieldset_parents").html('');
				$("#p_valider").hide();
				$('#ajax_msg2').removeAttr("class").html("&nbsp;").parent().hide();
				eleve_id = $("#select_eleve").val();
				if(!eleve_id)
				{
					$('#ajax_msg').removeAttr("class").html("&nbsp;");
					return false;
				}
				$('#ajax_msg').removeAttr("class").addClass("loader").html("Actualisation en cours... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action='+'afficher_parents'+'&f_eleve_id='+eleve_id,
						dataType : "html",
						error : function(msg,string)
						{
							$('#ajax_msg').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML.substring(0,6)=='<table')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								$('#ajax_msg').removeAttr("class").html("");
								$('#fieldset_parents').html(responseHTML).show();
								infobulle();
							}
							else
							{
								$('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
							}
						}
					}
				);
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	ORDONNER => Clic sur une image pour échanger deux contacts / responsables
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#fieldset_parents input[type=image]').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				para_clic = $(this).parent();
				table_prev = para_clic.prev('table');
				table_next = para_clic.next('table');
				titre_prev = table_prev.find('th.vu').html();
				titre_next = table_next.find('th.vu').html();
				table_prev.find('th.vu').html(titre_next);
				table_next.find('th.vu').html(titre_prev);
				para_clic.before(table_next);
				para_clic.after(table_prev);
				$('#ajax_msg2').removeAttr("class").addClass("alerte").html("Modification(s) non enregistrée(s) !").parent().show();
				return false;
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	SUPPRIMER => Clic sur une image pour retirer un contact / responsable
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('q.supprimer').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$(this).parent().html('<q class="ajouter" title="Ajouter un responsable / contact."></q>').prev('td').html('---').parent().parent().parent().removeAttr('id');
				infobulle();
				$('#ajax_msg2').removeAttr("class").addClass("alerte").html("Modification(s) non enregistrée(s) !").parent().show();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	AJOUTER => Clic sur une image pour ajouter un contact / responsable
//	MODIFIER => Clic sur une image pour modifier un contact / responsable
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('q.ajouter , q.modifier').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				memo_td_html = $(this).parent().prev('td').html();
				afficher_masquer_images_action('hide');
				$(this).parent().prev('td').html('<select id="f_parent" name="f_parent">'+select_parent+'</select><q class="valider" title="Choisir ce responsable / contact."></q><q class="annuler" title="Annuler."></q><br /><label id="ajax_msg_select">&nbsp;</label>');
				infobulle();
				$('#f_parent').focus();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	VALIDER => Clic sur une image pour valider l'ajout / la modification d'un contact / responsable
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('q.valider').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var parent_id  = $('#f_parent option:selected').val();
				var parent_nom = $('#f_parent option:selected').text();
				if(!parent_id)
				{
					$('#ajax_msg_select').removeAttr("class").addClass("alerte").html("Aucun responsable / contact choisi !");
					return false;
				}
				if($('#parent_'+parent_id).length)
				{
					$('#ajax_msg_select').removeAttr("class").addClass("alerte").html("Ce responsable / contact est déjà associé à l'élève !");
					return false;
				}
				$(this).parent().html('<em>'+parent_nom+'</em><hr /><div class="astuce">Pensez à enregistrer pour confirmer ce changement.</div>').next('th').html('<q class="modifier" title="Changer ce responsable / contact."></q><q class="supprimer" title="Retirer ce responsable / contact."></q>').parent().parent().parent().attr('id','parent_'+parent_id);
				infobulle();
				afficher_masquer_images_action('show');
				$('#ajax_msg2').removeAttr("class").addClass("alerte").html("Modification(s) non enregistrée(s) !").parent().show();
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	ANNULER => Clic sur une image pour annuler l'ajout / la modification d'un contact / responsable
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('q.annuler').live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				$(this).parent().html(memo_td_html);
				afficher_masquer_images_action('show');
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	ENVOYER les modifications apportées
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#Enregistrer").live // live est utilisé pour prendre en compte les nouveaux éléments créés
		('click',
			function()
			{
				var tab_parents_id = new Array();
				// Récupérer les identifiants des parents
				$('#fieldset_parents table').each
				(
					function()
					{
						var id = (typeof($(this).attr('id'))=='undefined') ? 0 : $(this).attr('id').substring(7) ;
						tab_parents_id.push(id);
					}
				);
				// Pas de resp 2 si pas de resp1 ; pas de contact2 si pas de contact1
				if(!tab_parents_id[0] && tab_parents_id[1])
				{
					$('#ajax_msg2').removeAttr("class").addClass("alerte").html("Déplacez le resp. légal n°2 en n°1 !");
					return false;
				}
				if(!tab_parents_id[2] && tab_parents_id[3])
				{
					$('#ajax_msg2').removeAttr("class").addClass("alerte").html("Déplacez le contact n°2 en n°1 !");
					return false;
				}
				// Zy va : envoi ajax
				$('button').prop('disabled',true);
				afficher_masquer_images_action('hide');
				$('#ajax_msg2').removeAttr("class").addClass("loader").html("Enregistrement en cours... Veuillez patienter.");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_action='+'enregistrer_parents'+'&f_eleve_id='+eleve_id+'&f_parents_id='+tab_parents_id,
						dataType : "html",
						error : function(msg,string)
						{
							$('button').prop('disabled',false);
							afficher_masquer_images_action('show');
							$('#ajax_msg2').removeAttr("class").addClass("alerte").html("Echec de la connexion ! Veuillez essayer de nouveau.");
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							if(responseHTML.substring(0,6)=='<table')	// Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
							{
								$('button').prop('disabled',false);
								$('#ajax_msg2').removeAttr("class").html("").parent().hide();
								$('#fieldset_parents').html(responseHTML).show();
								infobulle();
							}
							else
							{
								$('button').prop('disabled',false);
								afficher_masquer_images_action('show');
								$('#ajax_msg2').removeAttr("class").addClass("alerte").html(responseHTML);
							}
						}
					}
				);
			}
		);

	}
);
