/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2010-2014
 * 
 * ****************************************************************************************************
 * SACoche <http://sacoche.sesamath.net> - Suivi d'Acquisitions de Compétences
 * © Thomas Crespin pour Sésamath <http://www.sesamath.net> - Tous droits réservés.
 * Logiciel placé sous la licence libre Affero GPL 3 <https://www.gnu.org/licenses/agpl-3.0.html>.
 * ****************************************************************************************************
 * 
 * Ce fichier est une partie de SACoche.
 * 
 * SACoche est un logiciel libre ; vous pouvez le redistribuer ou le modifier suivant les termes 
 * de la “GNU Affero General Public License” telle que publiée par la Free Software Foundation :
 * soit la version 3 de cette licence, soit (à votre gré) toute version ultérieure.
 * 
 * SACoche est distribué dans l’espoir qu’il vous sera utile, mais SANS AUCUNE GARANTIE :
 * sans même la garantie implicite de COMMERCIALISABILITÉ ni d’ADÉQUATION À UN OBJECTIF PARTICULIER.
 * Consultez la Licence Publique Générale GNU Affero pour plus de détails.
 * 
 * Vous devriez avoir reçu une copie de la Licence Publique Générale GNU Affero avec SACoche ;
 * si ce n’est pas le cas, consultez : <http://www.gnu.org/licenses/>.
 * 
 */

// jQuery !
$(document).ready
(
  function()
  {

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Variables globales
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var modification = false;
    var serie_ref    = '';
    var epreuve_code = '';

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Choix ordonné du/des référentiel(s) matière(s)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Choix ordonné du/des référentiel(s) matière(s) : mise en place du formulaire
     * @return void
     */
    $('form').on
    (
      'click',
      'q.modifier',
      function()
      {
        // Récupérer les informations
        var tab_id = $(this).next('input').attr('id').split('_');
        serie_ref    = tab_id[1];
        epreuve_code = tab_id[2];
        var tab_matieres_oui = $('#f_'+serie_ref+'_'+epreuve_code+'_matieres_id').val().split(',');
        // Fabriquer les deux listes
        var li_oui = '';
        for(i in tab_matieres_oui)
        {
          var matiere_id = tab_matieres_oui[i];
          if(matiere_id)
          {
            li_oui += '<li id="m_'+matiere_id+'">'+tab_matiere[matiere_id]+'</li>';
          }
        }
        var li_non = '';
        for(matiere_id in tab_matiere)
        {
          if(tab_matieres_oui.indexOf(matiere_id)==-1)
          {
            li_non += '<li id="m_'+matiere_id+'">'+tab_matiere[matiere_id]+'</li>';
          }
        }
        $('#sortable_oui').html(li_oui);
        $('#sortable_non').html(li_non);
        // Afficher la zone associée après avoir chargé son contenu
        $('#titre_ordonner').html( $('#h2_'+serie_ref).html() + ' | ' + $('#h3_'+serie_ref+'_'+epreuve_code).html() );
        $('#fermer_zone_ordonner').removeAttr("class").addClass("retourner").html('Retour');
        modification = false;
        $('#sortable_oui , #sortable_non').sortable( { connectWith:'.connectedSortable' , cursor:'n-resize' , update:function(event,ui){modif_ordre();} } );
        // Afficher la zone
        $.fancybox( { 'href':'#zone_ordonner' , onStart:function(){$('#zone_ordonner').css("display","block");} , onClosed:function(){$('#zone_ordonner').css("display","none");} , 'modal':true , 'minWidth':500 , 'centerOnScroll':true } );
      }
    );

    function modif_ordre()
    {
      if(modification==false)
      {
        $('#fermer_zone_ordonner').removeAttr("class").addClass("annuler").html('Annuler / Retour');
        modification = true;
      }
    }

    /**
     * Choix ordonné du/des référentiel(s) matière(s) : fermeture de la zone
     * @return void
     */
    $('#zone_ordonner').on
    (
      'click',
      '#fermer_zone_ordonner',
      function()
      {
        $.fancybox.close();
      }
    );

    /**
     * Choix ordonné du/des référentiel(s) matière(s) : maj du choix
     * @return void
     */
    $('#zone_ordonner').on
    (
      'click',
      '#valider_ordre',
      function()
      {
        // On récupère la liste des matières choisies
        var tab_matieres_id   = new Array();
        var tab_matieres_text = new Array();
        $('#sortable_oui').children('li').each
        (
          function()
          {
            var matiere_id = $(this).attr('id');
            if(typeof(matiere_id)!='undefined')
            {
              matiere_id = matiere_id.substring(2); // m_
              tab_matieres_id.push(matiere_id);
              tab_matieres_text.push(tab_matiere[matiere_id]);
            }
          }
        );
        $('#f_'+serie_ref+'_'+epreuve_code+'_matieres_id').val(tab_matieres_id.join(','));
        $('#f_'+serie_ref+'_'+epreuve_code+'_matieres_text').val(tab_matieres_text.join(' ; '));
        $('#ajax_msg_'+serie_ref).removeAttr("class").addClass("alerte").html("Pensez à valider vos modifications !");
        $.fancybox.close();
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Alerter au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('form').on
    (
      'change',
      'select',
      function()
      {
        serie_ref = $(this).parent().parent().attr('id').substring(5); // form_
        $('#ajax_msg_'+serie_ref).removeAttr("class").addClass("alerte").html("Pensez à valider vos modifications !");
        modification = true;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Soumission d'un formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('button[id^=bouton_valider_]').click
    (
      function()
      {
        serie_ref = $(this).attr('id').substring(15); // bouton_valider_
        var nb_erreurs = 0;
        $('#form_'+serie_ref).find('input[type=hidden].required').each
        (
          function()
          {
            if(!$(this).val())
            {
              nb_erreurs++;
            }
          }
        );
        if(nb_erreurs)
        {
          var s = (nb_erreurs>1) ? 's' : '' ;
          $('#ajax_msg_'+serie_ref).removeAttr("class").addClass("erreur").html("Référentiel manquant pour "+nb_erreurs+" épreuve"+s+" obligatoire"+s+" !");
          return false;
        }
        else
        {
          $('#bouton_valider_'+serie_ref).prop('disabled',true);
          $('#ajax_msg_'+serie_ref).removeAttr("class").addClass("loader").html("En cours&hellip;");
          $.ajax
          (
            {
              type : 'POST',
              url : 'ajax.php?page='+PAGE,
              data : 'csrf='+CSRF+'&f_serie='+serie_ref+'&'+$('#form_'+serie_ref).serialize(),
              dataType : "html",
              error : function(jqXHR, textStatus, errorThrown)
              {
                $('#ajax_msg_'+serie_ref).removeAttr("class").addClass("alerte").html(responseHTML);
                $('#bouton_valider_'+serie_ref).prop('disabled',false);
                return false;
              },
              success : function(responseHTML)
              {
                initialiser_compteur();
                if(responseHTML!='ok')
                {
                  $('#ajax_msg_'+serie_ref).removeAttr("class").addClass("alerte").html(responseHTML);
                }
                else
                {
                  $('#ajax_msg_'+serie_ref).removeAttr("class").addClass("valide").html("Paramètres enregistrés.");
                }
                $('#bouton_valider_'+serie_ref).prop('disabled',false);
              }
            }
          );
        }
      }
    );

  }
);
