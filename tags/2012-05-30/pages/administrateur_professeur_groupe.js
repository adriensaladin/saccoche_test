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
		//	Ajouter / Retirer une affectation à un groupe
		//	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-	-

		$('#autocheckbox input[type=checkbox]').click
		(
			function()
			{
				var obj_bouton = $(this);
				var action     = (obj_bouton.is(':checked')) ? 'ajouter' : 'retirer' ;
				var user_id    = obj_bouton.val();
				var groupe_id  = obj_bouton.parent().parent().attr('id').substring(3);
				var check_old  = (action=='ajouter') ? false : true ;
				var class_old  = (action=='ajouter') ? 'off' : 'on' ;
				var class_new  = (action=='ajouter') ? 'on' : 'off' ;
				obj_bouton.hide(0).parent().removeAttr('class').addClass('load');
				$.ajax
				(
					{
						type : 'POST',
						url  : 'ajax.php?page='+PAGE,
						data : 'action='+action+'&user_id='+user_id+'&groupe_id='+groupe_id,
						dataType : "html",
						error : function(msg,string)
						{
							obj_bouton.prop('checked',check_old).show(0).parent().removeAttr('class').addClass(class_old);
							$.fancybox( '<label class="alerte">'+'Echec de la connexion !\nVeuillez recommencer.'+'</label>' , {'centerOnScroll':true} );
							return false;
						},
						success : function(responseHTML)
						{
							if(responseHTML!='ok')
							{
								$.fancybox( '<label class="alerte">'+responseHTML+'</label>' , {'centerOnScroll':true} );
								obj_bouton.prop('checked',check_old).show(0).parent().removeAttr('class').addClass(class_old);
							}
							else
							{
								obj_bouton.show(0).parent().removeAttr('class').addClass(class_new);
								// MAJ tableaux bilans : lignes
								if(action=='ajouter')
								{
									var prof_nom   = $('#th_'+user_id).children('img').attr('alt');
									var groupe_nom = $('#tr_'+groupe_id+' th').html();
									$('#gpb_'+groupe_id).append('<div id="gp_'+groupe_id+'_'+user_id+'">'+prof_nom+'</div>');
									$('#pgb_'+user_id).append('<div id="pg_'+user_id+'_'+groupe_id+'">'+groupe_nom+'</div>');
								}
								else if(action=='retirer')
								{
									$('#gp_'+groupe_id+'_'+user_id).remove();
									$('#pg_'+user_id+'_'+groupe_id).remove();
								}
								// MAJ tableaux bilans : totaux
								var nb_profs = $('#gpb_'+groupe_id+' div').length;
								var nb_groupes = $('#pgb_'+user_id+' div').length;
								var s_profs = (nb_profs>1) ? 's' : '' ;
								var s_groupes = (nb_groupes>1) ? 's' : '' ;
								$('#gpf_'+groupe_id).html(nb_profs+' professeur'+s_profs);
								$('#pgf_'+user_id).html(nb_groupes+' groupe'+s_groupes);
							}
						}
					}
				);
			}
		);

	}
);
