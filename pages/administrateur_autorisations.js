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
// Afficher ou masquer des éléments de formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		function view_bilans()
		{
			var opacite_parent = ( $('#form_autorisations input[name="droit_bilan_moyenne_score"][value="parent"]').is(':checked') || $('#form_autorisations input[name="droit_bilan_pourcentage_acquis"][value="parent"]').is(':checked') ) ? 1 : 0 ;
			var opacite_eleve  = ( $('#form_autorisations input[name="droit_bilan_moyenne_score"][value="eleve"]' ).is(':checked') || $('#form_autorisations input[name="droit_bilan_pourcentage_acquis"][value="eleve"]' ).is(':checked') ) ? 1 : 0 ;
			var opacite_ligne  = ( opacite_parent || opacite_eleve ) ? 1 : 0 ;
			$('#form_autorisations input[name="droit_bilan_note_sur_vingt"][value="parent"]').parent().fadeTo(0,opacite_parent);
			$('#form_autorisations input[name="droit_bilan_note_sur_vingt"][value="eleve"]' ).parent().fadeTo(0,opacite_eleve);
			$('#tr_droit_bilan_note_sur_vingt').fadeTo(0,opacite_ligne);
		}
		view_bilans();

		function view_socle()
		{
			var opacite_parent = $('#form_autorisations input[name="droit_socle_acces"][value="parent"]').is(':checked') ? 1 : 0 ;
			var opacite_eleve  = $('#form_autorisations input[name="droit_socle_acces"][value="eleve"]' ).is(':checked') ? 1 : 0 ;
			var opacite_ligne  = ( opacite_parent || opacite_eleve ) ? 1 : 0 ;
			$('#form_autorisations input[name="droit_socle_pourcentage_acquis"][value="parent"]').parent().fadeTo(0,opacite_parent);
			$('#form_autorisations input[name="droit_socle_pourcentage_acquis"][value="eleve"]' ).parent().fadeTo(0,opacite_eleve);
			$('#form_autorisations input[name="droit_socle_etat_validation"][value="parent"]').parent().fadeTo(0,opacite_parent);
			$('#form_autorisations input[name="droit_socle_etat_validation"][value="eleve"]' ).parent().fadeTo(0,opacite_eleve);
			$('#tr_droit_socle_pourcentage_acquis').fadeTo(0,opacite_ligne);
			$('#tr_droit_socle_etat_validation').fadeTo(0,opacite_ligne);
		}
		view_socle();

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Alerter sur la nécessité de valider
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$("#form_autorisations input").change
		(
			function()
			{
				var objet = $(this).attr('name');
				$('#ajax_msg_'+objet).removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
				if( (objet=='droit_bilan_moyenne_score') || (objet=='droit_bilan_pourcentage_acquis') )
				{
					view_bilans();
				}
				if(objet=='droit_socle_acces')
				{
					view_socle();
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
// Initialiser un formulaire avec les valeurs par défaut
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#form_autorisations button[name=initialiser]').click
		(
			function()
			{
				var objet = $(this).parent().parent().attr('id').substring(3);
				for(var value in tab_init[objet]) // Parcourir un tableau associatif...
				{
					$('#form_autorisations input[name="'+objet+'"][value="'+value+'"]').prop('checked',tab_init[objet][value]);
				}
				$('#ajax_msg_'+objet).removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
				if( (objet=='droit_bilan_moyenne_score') || (objet=='droit_bilan_pourcentage_acquis') )
				{
					view_bilans();
				}
				if(objet=='droit_socle_acces')
				{
					view_socle();
				}
			}
		);

//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-
//	Soumission du formulaire
//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#form_autorisations button[name=valider]').click
		(
			function()
			{
				var objet = $(this).parent().parent().attr('id').substring(3);
				var tab_check = new Array(); $('#form_autorisations input[name='+objet+']:checked').each(function(){tab_check.push($(this).val());});
				$("button").prop('disabled',true);
				$('#ajax_msg_'+objet).removeAttr("class").addClass("loader").html("Transmission en cours...");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'f_objet='+objet+'&f_profils='+tab_check,
						dataType : "html",
						error : function(msg,string)
						{
							$("button").prop('disabled',false);
							$('#ajax_msg_'+objet).removeAttr("class").addClass("alerte").html("Echec ! Veuillez recommencer.");
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$("button").prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_'+objet).removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_'+objet).removeAttr("class").addClass("valide").html("Droits enregistrés !");
							}
						}
					}
				);
			}
		);

	}
);
