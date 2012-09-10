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

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Afficher masquer des éléments du formulaire
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#f_bulletin_moyenne_scores').click
		(
			function()
			{
				if($('#f_bulletin_moyenne_scores').is(':checked'))
				{
					$('#span_moyennes').show();
				}
				else
				{
					$('#span_moyennes').hide();
				}
			}
		);

		$('#f_bulletin_appreciation_generale').change
		(
			function()
			{
				if(parseInt($('#f_bulletin_appreciation_generale').val(),10)>0)
				{
					$('#span_moyenne_generale').show();
				}
				else
				{
					$('#span_moyenne_generale').hide();
				}
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		//	Alerter sur la nécessité de valider
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$("#form_releve input , #form_releve select").change
		(
			function()
			{
				$('#ajax_msg_releve').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
			}
		);

		$("#form_bulletin input , #form_bulletin select").change
		(
			function()
			{
				$('#ajax_msg_bulletin').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
			}
		);

		$("#form_socle input , #form_socle select").change
		(
			function()
			{
				$('#ajax_msg_socle').removeAttr("class").addClass("alerte").html("Enregistrer pour confirmer.");
			}
		);

		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*
		// Traitement du formulaire "Relevé d'évaluations"
		// Traitement du formulaire "Bulletin scolaire"
		// Traitement du formulaire "État de maîtrise du socle"
		//	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*	*

		$('#bouton_valider_releve , #bouton_valider_bulletin , #bouton_valider_socle').click
		(
			function()
			{
				var objet = $(this).attr('id').substring(15);
				if( (objet=='socle') && (!$('#f_socle_pourcentage_acquis').is(':checked')) && (!$('#f_socle_etat_validation').is(':checked')) )
				{
					$('#ajax_msg_'+objet).removeAttr("class").addClass("erreur").html("Cocher au moins une indication à faire figurer sur le bilan !");
					return false;
				}
				$('#bouton_valider_'+objet).prop('disabled',true);
				$('#ajax_msg_'+objet).removeAttr("class").addClass("loader").html("Connexion au serveur&hellip;");
				$.ajax
				(
					{
						type : 'POST',
						url : 'ajax.php?page='+PAGE,
						data : 'objet='+objet+'&'+$('#form_'+objet).serialize(),
						dataType : "html",
						error : function(jqXHR, textStatus, errorThrown)
						{
							$('#bouton_valider_'+objet).prop('disabled',false);
							$('#ajax_msg_'+objet).removeAttr("class").addClass("alerte").html("Echec de la connexion !");
							return false;
						},
						success : function(responseHTML)
						{
							initialiser_compteur();
							$('#bouton_valider_'+objet).prop('disabled',false);
							if(responseHTML!='ok')
							{
								$('#ajax_msg_'+objet).removeAttr("class").addClass("alerte").html(responseHTML);
							}
							else
							{
								$('#ajax_msg_'+objet).removeAttr("class").addClass("valide").html("Données enregistrées !");
							}
							return false;
						}
					}
				);
			}
		);

	}
);
