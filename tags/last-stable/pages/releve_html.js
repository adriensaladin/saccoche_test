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

    /**
     * Ce fichier est associé à releve_html.php (affichage d'un relevé HTML enregistré temporairement).
     * Ces relevés peuvent en effet comporter des éléments dynamiques : cases à cocher et formulaire à soumettre 
     * afin de créer une évaluation ou constituer un groupe de besoin.
     */

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Initialisation
// 
// Pour "Synthèse de maîtrise du socle" il y a un formulaire simplifié
//  (et pour "Recherche ciblée" c'est affiché directement sans passer par releve_html.php)
// Une bonne partie de ce code ne concerne donc que "Grille d'items d'un référentiel" et "Relevé d'items [...]"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    if($('#span_submit').length)
    {
      $('#form_synthese input[type=checkbox]').css('display','none'); // bcp plus rapide que hide() qd il y a bcp d'éléments
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Afficher / masquer checkbox au changement d'un choix d'action
// 
// .css('display','...') utilisé en remplacement de hide() et show() car plus rapide quand il y a bcp d'éléments.
// Ici, utiliser les fonctions de jQuery ralentissaient Firefox jusqu'à obtenir un boîte d'avertissement.
// 'inline-block' permet d'avoir le checkbox sur la même ligne, mais 'block' semble plus adapté pour gagner en largeur
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_action').change
    (
      function()
      {
        $('#form_synthese input[type=checkbox]').css('display','none');
        $('#check_msg').removeAttr('class').html('');
        var action = $('#f_action option:selected').val();
        if(action=='evaluer_items_perso')
        {
          $('#form_synthese input[name=id_req\\[\\]]').css('display','block'); 
          $('#span_submit').show(0);
        }
        else if(action=='evaluer_items_commun')
        {
          $('#form_synthese input[name=id_user\\[\\]]').css('display','block');
          $('#form_synthese input[name=id_item\\[\\]]').css('display','block');
          $('#span_submit').show(0);
          
        }
        else if(action=='constituer_groupe_besoin')
        {
          $('#form_synthese input[name=id_user\\[\\]]').css('display','block');
          $('#span_submit').show(0);
        }
        else
        {
          $('#span_submit').hide(0);
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Valider "Grille d'items d'un référentiel" et "Relevé d'items [...]"
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#f_submit').click
    (
      function()
      {
        var objet = $('#f_action option:selected').val();
        var action = false;
        // evaluer_items_perso
        if(objet=='evaluer_items_perso')
        {
          if( !$('#form_synthese input[name=id_req\\[\\]]:checked').length )
          {
            $('#check_msg').removeAttr('class').addClass('alerte').html('Aucune case cochée !');
            return false;
          }
          else
          {
            $('#form_synthese input[name=id_user\\[\\]]').remove();
            $('#form_synthese input[name=id_item\\[\\]]').remove();
            action = 'evaluation_gestion';
          }
        }
        // evaluer_items_commun
        else if(objet=='evaluer_items_commun')
        {
          if( !$('#form_synthese input[name=id_user\\[\\]]:checked').length )
          {
            $('#check_msg').removeAttr('class').addClass('alerte').html('Aucun élève coché !');
            return false;
          }
          else if( !$('#form_synthese input[name=id_item\\[\\]]:checked').length )
          {
            $('#check_msg').removeAttr('class').addClass('alerte').html('Aucun item coché !');
            return false;
          }
          else
          {
            $('#form_synthese input[name=id_req\\[\\]]').remove();
            action = 'evaluation_gestion';
          }
        }
        // constituer_groupe_besoin
        else if(objet=='constituer_groupe_besoin')
        {
          if( !$('#form_synthese input[name=id_user\\[\\]]:checked').length )
          {
            $('#check_msg').removeAttr('class').addClass('alerte').html('Aucun élève coché !');
            return false;
          }
          else
          {
            $('#form_synthese input[name=id_req\\[\\]]').remove();
            $('#form_synthese input[name=id_item\\[\\]]').remove();
            action = 'professeur_groupe_besoin';
          }
        }
        // si ok
        if(action)
        {
          $('#check_msg').removeAttr('class').html('');
          $('#form_synthese').attr( 'action' , './index.php?page='+action );
          $('#form_synthese').submit();
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Valider "Synthèse de maîtrise du socle" (formulaire simplifié)
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_synthese button.ajouter').click
    (
      function()
      {
        if( $('#form_synthese input[name=id_user\\[\\]]:checked').length )
        {
          $('#check_msg').removeAttr('class').html('');
          $('#form_synthese').attr( 'action' , './index.php?page='+$(this).attr('name') );
          $('#form_synthese').submit();
        }
        else
        {
          $('#check_msg').removeAttr('class').addClass('alerte').html('Aucun élève coché !');
          return false;
        }
      }
    );








    /** $releve_HTML_synthese .= ($affichage_checkbox) ? '<p>
       <label class="tab">Action <img alt="" src="./_img/bulle_aide.png" title="Cocher auparavant les cases adéquates." /> :</label>
       <button type="button" class="ajouter" onclick="var form=document.getElementById(\'form_synthese\');form.action=\'./index.php?page=evaluation_gestion\';form.submit();">Préparer une évaluation.</button>
       <button type="button" class="ajouter" onclick="var form=document.getElementById(\'form_synthese\');form.action=\'./index.php?page=professeur_groupe_besoin\';form.submit();">Constituer un groupe de besoin.</button>
       </p></form>' : '';
        '<option val="evaluation_gestion_selection_perso">Évaluer des élèves sur des items personnalisés</option>'.
        '<option val="evaluation_gestion_selection_commun">Évaluer des élèves sur des items communs</option>'.
        '<option val="professeur_groupe_besoin">Constituer un groupe de besoin</option>'.
       */

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Traitement du premier formulaire pour afficher le tableau avec les états de validations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire0 = $('#zone_choix');

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation0 = formulaire0.validate
    (
      {
        rules :
        {
          'f_pilier[]' : { required:true },
          f_groupe     : { required:true },
          'f_eleve[]'  : { required:true }
        },
        messages :
        {
          'f_pilier[]' : { required:"compétence(s) manquante(s)" },
          f_groupe     : { required:"classe / groupe manquant" },
          'f_eleve[]'  : { required:"élève(s) manquant(s)" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.is("select")) {element.after(error);}
          else if(element.attr("type")=="checkbox") {element.parent().parent().next().after(error);}
        }
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions0 =
    {
      url : 'ajax.php?page='+PAGE,
      type : 'POST',
      dataType : "html",
      clearForm : false,
      resetForm : false,
      target : "#ajax_msg_choix",
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
      $('#ajax_msg_choix').removeAttr("class").html("&nbsp;");
      var readytogo = validation0.form();
      if(readytogo)
      {
        $("#Afficher_validation").prop('disabled',true);
        $('#ajax_msg_choix').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur0(jqXHR, textStatus, errorThrown)
    {
      $("#Afficher_validation").prop('disabled',false);
      $('#ajax_msg_choix').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide0(responseHTML)
    {
      initialiser_compteur();
      $("#Afficher_validation").prop('disabled',false);
      if(responseHTML.substring(0,7)!='<thead>')
      {
        $('#ajax_msg_choix').removeAttr("class").addClass("alerte").html(responseHTML);
      }
      else
      {
        responseHTML = responseHTML.replace( '@PALIER@' , $("#f_palier option:selected").text() );
        $('#tableau_validation').html(responseHTML);
        $('#zone_validation').show('fast');
        $('#ajax_msg_choix').removeAttr("class").html('');
        $('#zone_choix').hide('fast');
        $('#zone_information').show('fast');
        $("body").oneTime("1s", function() {window.scrollTo(0,1000);} );
      }
    }

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur une cellule du tableau => Modifier visuellement des états de validation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#tableau_validation').on
    (
      'click',
      'tbody th',
      function()
      {
        var classe = $(this).attr('class');
        if(classe=='nu')
        {
          // Intitulé du socle
          return false;
        }
        if(modification==false)
        {
          $('#ajax_msg_validation').removeAttr("class").addClass("alerte").html('Penser à valider les modifications !');
          $('#fermer_zone_validation').removeAttr("class").addClass("annuler").html('Annuler / Retour');
          modification = true;
        }
        var classe_debut = classe.substring(0,4);
        var classe_fin   = classe.charAt(4);
        var new_classe_th = classe_debut + tab_class_next[classe_fin] ;
        var new_classe_td = 'v' + classe_fin ;
        if(classe_debut=='left')
        {
          // Appliquer un état pour un pilier pour tous les élèves
          $(this).removeAttr("class").addClass(new_classe_th).parent().children('td').removeAttr("class").addClass(new_classe_td);
          return false;
        }
        if(classe_debut=='down')
        {
          // Appliquer un état pour tout le palier pour un élève
          var id = $(this).attr('id') + 'C';
          $(this).removeAttr("class").addClass(new_classe_th).parent().parent().find('td[id^='+id+']').removeAttr("class").addClass(new_classe_td);
          return false;
        }
        if(classe_debut=='diag')
        {
          // Appliquer un état pour tous les piliers pour tous les élèves
          $(this).removeAttr("class").addClass(new_classe_th).parent().parent().find('td').removeAttr("class").addClass(new_classe_td);
          return false;
        }
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour fermer la zone de validation
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function fermer_zone_validation()
    {
      $('#zone_choix').show('fast');
      $('#zone_validation').hide('fast');
      $('#tableau_validation').html('<tbody><tr><td></td></tr></tbody>');
      // Vider aussi la zone d'informations
      $('#zone_information').hide('fast');
      $('#identite').html('');
      $('#pilier').html('');
      $('#stats').html('');
      $('#items').html('');
      $('#ajax_msg_information').removeAttr("class").html('');
      modification = false;
      return(false);
    }

    $('#tableau_validation').on
    (
      'click',
      '#fermer_zone_validation',
      function()
      {
        if(!modification)
        {
          fermer_zone_validation();
        }
        else
        {
          $.fancybox( { 'href':'#zone_confirmer_fermer_validation' , onStart:function(){$('#zone_confirmer_fermer_validation').css("display","block");} , onClosed:function(){$('#zone_confirmer_fermer_validation').css("display","none");} , 'modal':true , 'centerOnScroll':true } );
          return(false);
        }
      }
    );

    $('#confirmer_fermer_zone_validation').click
    (
      function()
      {
        $.fancybox.close();
        fermer_zone_validation();
      }
    );

    $('#annuler_fermer_zone_validation').click
    (
      function()
      {
        $.fancybox.close();
      }
    );

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Clic sur le bouton pour envoyer les validations
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#tableau_validation').on
    (
      'click',
      '#Enregistrer_validation',
      function()
      {
        $("button").prop('disabled',true);
        $('#ajax_msg_validation').removeAttr("class").addClass("loader").html("En cours&hellip;");
        // Récupérer les infos
        var tab_valid = new Array();
        $("#tableau_validation tbody td").each
        (
          function()
          {
            tab_valid.push( $(this).attr('id') + $(this).attr('class').toUpperCase() );
          }
        );
        // Les envoyer en ajax
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page='+PAGE,
            data : 'f_action=Enregistrer_validation'+'&f_valid='+tab_valid,
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $("button").prop('disabled',false);
              $('#ajax_msg_validation').removeAttr("class").addClass("alerte").html('Échec de la connexion !');
              return false;
            },
            success : function(responseHTML)
            {
              modification = false; // Mis ici pour le cas "aucune modification détectée"
              initialiser_compteur();
              $("button").prop('disabled',false);
              if(responseHTML.substring(0,2)!='OK')
              {
                $('#ajax_msg_validation').removeAttr("class").addClass("alerte").html(responseHTML);
              }
              else
              {
                $('td.v1').attr('lang','lock').html('');
                $('#ajax_msg_validation').removeAttr("class").addClass("valide").html("Validations enregistrées !");
                $('#fermer_zone_validation').removeAttr("class").addClass("retourner").html('Retour');
              }
            }
          }
        );
      }
    );

  }
);

