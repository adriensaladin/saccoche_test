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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    var mode = false;

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Fonctions utilisées
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function afficher_form_gestion( mode , id , ref , nom )
    {
      $('#f_action').val(mode);
      $('#f_niveau').val(id);
      // pour finir
      $('#gestion_delete_identite').html(ref+" "+nom);
      $('#ajax_msg_gestion').removeAttr('class').html("");
      $('#form_gestion label[generated=true]').removeAttr('class').html("");
      $.fancybox( { 'href':'#form_gestion' , onStart:function(){$('#form_gestion').css("display","block");} , onClosed:function(){$('#form_gestion').css("display","none");} , 'modal':true , 'minWidth':600 , 'centerOnScroll':true } );
    }

    /**
     * Ajouter un niveau : affichage du formulaire
     * @return void
     */
    var ajouter = function()
    {
      mode = $(this).attr('class');
      $('#ajax_msg_recherche').removeAttr("class").html("&nbsp;");
      $('#zone_niveaux').hide();
      $('#zone_ajout_form').show();
      return false;
    };

    /**
     * Supprimer un niveau : mise en place du formulaire
     * @return void
     */
    var supprimer = function()
    {
      mode = $(this).attr('class');
      var objet_tr   = $(this).parent().parent();
      var objet_tds  = objet_tr.find('td');
      // Récupérer les informations de la ligne concernée
      var id         = objet_tr.attr('id').substring(3);
      var ref        = objet_tds.eq(0).html();
      var nom        = objet_tds.eq(1).html();
      // Afficher le formulaire
      afficher_form_gestion( mode , id , unescapeHtml(ref) , unescapeHtml(nom) );
    };

    /**
     * Annuler une action
     * @return void
     */
    var annuler = function()
    {
      $.fancybox.close();
      mode = false;
    };

    /**
     * Retirer un niveau
     * @return void
     */
    var retirer = function()
    {
      $('#form_gestion button').prop('disabled',true);
      $('#ajax_msg_gestion').removeAttr("class").addClass("loader").html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=supprimer'+'&f_niveau='+$('#f_niveau').val(),
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#form_gestion button').prop('disabled',false);
            $('#ajax_msg_gestion').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
            return false;
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            $('#form_gestion button').prop('disabled',false);
            if(responseHTML=='ok')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_msg_gestion').removeAttr("class").addClass("valide").html("Demande réalisée !");
              $('#id_'+$('#f_niveau').val()).remove();
              $.fancybox.close();
              mode = false;
            }
            else
            {
              $('#ajax_msg_gestion').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    };

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Appel des fonctions en fonction des événements ; live est utilisé pour prendre en compte les nouveaux éléments créés
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#zone_niveaux q.ajouter').click( ajouter );
    $('q.supprimer').live( 'click' , supprimer );
    $('#bouton_annuler').click( annuler );
    $('#bouton_valider').click( retirer );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer le cadre de recherche d'un niveau à ajouter
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#ajout_annuler').click
    (
      function()
      {
        $('#zone_ajout_form').hide();
        $('#zone_niveaux').show();
        return(false);
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Actualisation du résultat de la recherche des niveaux
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_resultat_recherche(famille_id)
    {
      $('#ajax_msg_recherche').removeAttr("class").addClass("loader").html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page='+PAGE,
          data : 'csrf='+CSRF+'&f_action=recherche_niveau_famille'+'&f_famille='+famille_id,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_msg_recherche').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML.substring(0,3)=='<li')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
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

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement du select f_famille => actualisation du résultat de la recherche
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $("#f_famille").change
    (
      function()
      {
        $("#f_recherche_resultat").html('<li></li>').hide();
        var famille_id = parseInt( $("#f_famille option:selected").val() ,10);
        if(famille_id)
        {
          maj_resultat_recherche(famille_id)
        }
        else
        {
          $('#ajax_msg_recherche').removeAttr("class").html("&nbsp;");
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur un bouton pour ajouter un niveau trouvé suite à une recherche
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_recherche_resultat q.ajouter').live // live est utilisé pour prendre en compte les nouveaux éléments créés
    ('click',
      function()
      {
        // afficher_masquer_images_action('hide');
        var niveau_id = $(this).attr('id').substr(4); // add_
        $('#ajax_msg_recherche').removeAttr("class").addClass("loader").html("En cours&hellip;");
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'csrf='+CSRF+'&f_action=ajouter'+'&f_niveau='+niveau_id,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              afficher_masquer_images_action('show');
              $('#ajax_msg_recherche').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
              return false;
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              afficher_masquer_images_action('show');
              if(responseHTML=='ok')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
              {
                $('#ajax_msg_recherche').removeAttr("class").addClass("valide").html("Niveau ajouté.");
                var texte = $('#add_'+niveau_id).parent().text();
                var pos_separe  = (texte.indexOf('|')==-1) ? 0 : texte.lastIndexOf('|')+2 ;
                var pos_par_ouv = texte.lastIndexOf('(');
                var pos_par_fer = texte.lastIndexOf(')');
                var niveau_nom  = texte.substring(pos_separe,pos_par_ouv-1);
                var niveau_ref  = texte.substring(pos_par_ouv+1,pos_par_fer);
                $('#zone_niveaux table.form tbody').append('<tr id="id_'+niveau_id+'"><td>'+niveau_ref+'</td><td>'+niveau_nom+'</td><td class="nu"><q class="supprimer" title="Supprimer ce niveau."></q></td></tr>');
                $('#add_'+niveau_id).removeAttr("class").addClass("ajouter_non").attr('title',"Niveau déjà choisi.");
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
    );

  }
);
