/**
 * @version $Id$
 * @author Thomas Crespin <thomas.crespin@sesamath.net>
 * @copyright Thomas Crespin 2009-2015
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
    // Initialisation
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var matiere_id   = 0;
    var groupe_id    = 0;
    var groupe_type  = $("#f_groupe option:selected").parent().attr('label'); // Il faut indiquer une valeur initiale au moins pour le profil élève
    var eleves_ordre = '';

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Enlever le message ajax et le résultat précédent au changement d'un élément de formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    $('#form_select').on
    (
      'change',
      'select, input',
      function()
      {
        $('#ajax_msg').removeAttr("class").html("&nbsp;");
        $('#bilan').html("&nbsp;");
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Afficher masquer des éléments du formulaire
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function view_periode()
    {
      // On détermine
      groupe_type = $("#f_groupe option:selected").parent().attr('label');
      if(typeof(groupe_type)=='undefined')
      {
        periode_requise = false;
      }
      else if($('#f_type_generique').is(':checked'))
      {
        periode_requise = false;
      }
      else if($('#f_type_synthese').is(':checked'))
      {
        periode_requise = true;
      }
      else if($('#f_type_individuel').is(':checked'))
      {
        if( ($('#f_remplissage option:selected').val()=='plein') || ($('#f_colonne_bilan option:selected').val()=='oui') )
        {
          periode_requise = true;
        }
        else
        {
          periode_requise = false;
        }
      }
      else
      {
        periode_requise = false;
      }
      // On affiche / masque
      if(periode_requise)
      {
        $('#zone_periodes').show();
      }
      else
      {
        $('#zone_periodes').hide();
      }
    }

    $('#f_type_generique').click
    (
      function()
      {
        $('#generique_non_1 , #generique_non_2 , #generique_non_3').toggle();
        view_periode();
      }
    );

    $('#f_type_individuel').click
    (
      function()
      {
        $('#options_individuel').toggle();
        view_periode();
      }
    );

    $('#f_type_synthese').click
    (
      function()
      {
        $('#options_synthese').toggle();
        view_periode();
      }
    );

    $('#f_remplissage , #f_colonne_bilan').change
    (
      function()
      {
        view_periode();
      }
    );

    var autoperiode = true; // Tant qu'on ne modifie pas manuellement le choix des périodes, modification automatique du formulaire

    function view_dates_perso()
    {
      var periode_val = $("#f_periode").val();
      if(periode_val!=0)
      {
        $("#dates_perso").attr("class","hide");
      }
      else
      {
        $("#dates_perso").attr("class","show");
      }
    }

    $('#f_periode').change
    (
      function()
      {
        view_dates_perso();
        autoperiode = false;
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select f_niveau en ajax (au changement de f_matiere et au départ)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_niveau()
    {
      $("#f_niveau").html('<option value="">&nbsp;</option>').hide();
      var matiere_val = $("#f_matiere").val();
      if(!matiere_val)
      {
        $('#ajax_maj_matiere').removeAttr("class").html("&nbsp;");
        return false;
      }
      $('#ajax_maj_matiere').removeAttr("class").addClass("loader").html("En cours&hellip;");
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_niveaux',
          data : 'f_matiere='+matiere_val,
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj_matiere').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(responseHTML.substring(0,7)=='<option')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_maj_matiere').removeAttr("class").html("&nbsp;");
              $('#f_niveau').html(responseHTML).show();
            }
          else
            {
              $('#ajax_maj_matiere').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }
    $("#f_matiere").change
    (
      function()
      {
        maj_niveau();
      }
    );
    maj_niveau();

// ////////////////////////////////////////////////////////////////////////////////////////////////////
// Changement de groupe
// -> desactiver les périodes prédéfinies en cas de groupe de besoin (prof uniquement)
// -> choisir automatiquement la meilleure période si un changement manuel de période n'a jamais été effectué
// -> afficher ou non le formulaire de périodes
// ////////////////////////////////////////////////////////////////////////////////////////////////////

    function selectionner_periode_adaptee()
    {
      var id_groupe = $('#f_groupe option:selected').val();
      if(typeof(tab_groupe_periode[id_groupe])!='undefined')
      {
        for(var id_periode in tab_groupe_periode[id_groupe]) // Parcourir un tableau associatif...
        {
          var tab_split = tab_groupe_periode[id_groupe][id_periode].split('_');
          if( (date_mysql>=tab_split[0]) && (date_mysql<=tab_split[1]) )
          {
            $("#f_periode option[value="+id_periode+"]").prop('selected',true);
            view_dates_perso();
            break;
          }
        }
      }
    }

    $('#f_groupe').change
    (
      function()
      {
        groupe_type = $("#f_groupe option:selected").parent().attr('label');
        $("#f_periode option").each
        (
          function()
          {
            periode_id = $(this).val();
            // La période personnalisée est tout le temps accessible
            if(periode_id!=0)
            {
              // classe ou groupe classique -> toutes périodes accessibles
              if(groupe_type!='Besoins')
              {
                $(this).prop('disabled',false);
              }
              // groupe de besoin -> desactiver les périodes prédéfinies
              else
              {
                $(this).prop('disabled',true);
              }
            }
          }
        );
        // Sélectionner si besoin la période personnalisée
        if(groupe_type=='Besoins')
        {
          $("#f_periode option[value=0]").prop('selected',true);
          $("#dates_perso").attr("class","show");
        }
        // Modification automatique du formulaire : périodes
        if(autoperiode)
        {
          if( (typeof(groupe_type)!='undefined') && (groupe_type!='Besoins') )
          {
            // Rechercher automatiquement la meilleure période
            selectionner_periode_adaptee();
          }
          // Afficher / masquer la zone de choix des périodes
          view_periode();
        }
      }
    );

    // Rechercher automatiquement la meilleure période au chargement de la page (uniquement pour un élève, seul cas où la classe est préselectionnée)
    if( $('#form_select').length ) // Indéfini si pas de droit d'accès à cette fonctionnalité.
    {
      selectionner_periode_adaptee();
    }

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger le select f_eleve en ajax (au changement de f_groupe)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    function maj_eleve(groupe_id,groupe_type,eleves_ordre)
    {
      $.ajax
      (
        {
          type : 'POST',
          url : 'ajax.php?page=_maj_select_eleves',
          data : 'f_groupe_id='+groupe_id+'&f_groupe_type='+groupe_type+'&f_eleves_ordre='+eleves_ordre+'&f_statut=1'+'&f_multiple='+is_multiple+'&f_selection=1',
          dataType : "html",
          error : function(jqXHR, textStatus, errorThrown)
          {
            $('#ajax_maj_groupe').removeAttr("class").addClass("alerte").html("Échec de la connexion !");
          },
          success : function(responseHTML)
          {
            initialiser_compteur();
            if(groupe_type=='Classes')
            {
              $("#bloc_ordre").hide();
            }
            else
            {
              $("#bloc_ordre").show();
            }
            if( ( is_multiple && (responseHTML.substring(0,6)=='<label') ) || ( !is_multiple && (responseHTML.substring(0,7)=='<option') ) ) // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
            {
              $('#ajax_maj_groupe').removeAttr("class").html("&nbsp;");
              $('#f_eleve').html(responseHTML).parent().show();
            }
          else
            {
              $('#ajax_maj_groupe').removeAttr("class").addClass("alerte").html(responseHTML);
            }
          }
        }
      );
    }
    $("#f_groupe").change
    (
      function()
      {
        $("#f_eleve").html('<option value="">&nbsp;</option>').parent().hide();
        groupe_id = $("#f_groupe option:selected").val();
        if(groupe_id)
        {
          groupe_type  = $("#f_groupe option:selected").parent().attr('label');
          eleves_ordre = $("#f_eleves_ordre option:selected").val();
          $('#ajax_maj_groupe').removeAttr("class").addClass("loader").html("En cours&hellip;");
          maj_eleve(groupe_id,groupe_type,eleves_ordre);
        }
        else
        {
          $("#bloc_ordre").hide();
          $('#ajax_maj_groupe').removeAttr("class").html("&nbsp;");
        }
      }
    );

    $("#f_eleves_ordre").change
    (
      function()
      {
        groupe_id    = $("#f_groupe option:selected").val();
        groupe_type  = $("#f_groupe option:selected").parent().attr('label');
        eleves_ordre = $("#f_eleves_ordre option:selected").val();
        $("#f_eleve").html('<option value="">&nbsp;</option>').parent().hide();
        $('#ajax_maj_groupe').removeAttr("class").addClass("loader").html("En cours&hellip;");
        maj_eleve(groupe_id,groupe_type,eleves_ordre);
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Charger toutes les matières ou seulement les matières affectées (pour un prof)
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    var modifier_action = 'ajouter';
    $("#modifier_matiere").click
    (
      function()
      {
        $('button').prop('disabled',true);
        matiere_id = $("#f_matiere option:selected").val();
        $.ajax
        (
          {
            type : 'POST',
            url : 'ajax.php?page=_maj_select_matieres_prof',
            data : 'f_matiere='+matiere_id+'&f_action='+modifier_action+'&f_multiple=0',
            dataType : "html",
            error : function(jqXHR, textStatus, errorThrown)
            {
              $('button').prop('disabled',false);
            },
            success : function(responseHTML)
            {
              initialiser_compteur();
              if(responseHTML.substring(0,7)=='<option')  // Attention aux caractères accentués : l'utf-8 pose des pbs pour ce test
              {
                modifier_action = (modifier_action=='ajouter') ? 'retirer' : 'ajouter' ;
                $('#modifier_matiere').removeAttr("class").addClass("form_"+modifier_action);
                $('#f_matiere').html(responseHTML);
              }
              $('button').prop('disabled',false);
            }
          }
        );
      }
    );

    // ////////////////////////////////////////////////////////////////////////////////////////////////////
    // Soumettre le formulaire principal
    // ////////////////////////////////////////////////////////////////////////////////////////////////////

    // Le formulaire qui va être analysé et traité en AJAX
    var formulaire = $("#form_select");

    // Vérifier la validité du formulaire (avec jquery.validate.js)
    var validation = formulaire.validate
    (
      {
        rules :
        {
          'f_type[]'       : { required:true },
          f_remplissage    : { required:true },
          f_colonne_bilan  : { required:true },
          f_colonne_vide   : { required:true },
          f_tri_objet      : { required:true },
          f_tri_mode       : { required:true },
          f_repeter_entete : { required:false },
          f_retroactif     : { required:true },
          f_matiere        : { required:true },
          f_niveau         : { required:true },
          f_groupe         : { required:function(){return !$('#f_type_generique').is(':checked');} },
          'f_eleve[]'      : { required:function(){return $("#f_groupe").val()!=0;} },
          f_eleves_ordre   : { required:function(){return $("#f_groupe").val()!=0;} },
          f_periode        : { required:function(){return periode_requise;} },
          f_date_debut     : { required:function(){return periode_requise && $("#f_periode").val()==0;} , dateITA:true },
          f_date_fin       : { required:function(){return periode_requise && $("#f_periode").val()==0;} , dateITA:true },
          f_restriction    : { required:false },
          f_coef           : { required:false },
          f_socle          : { required:false },
          f_lien           : { required:false },
          f_orientation    : { required:true },
          f_couleur        : { required:true },
          f_fond           : { required:true },
          f_legende        : { required:true },
          f_marge_min      : { required:true },
          f_pages_nb       : { required:true },
          f_cases_nb       : { required:true },
          f_cases_larg     : { required:true }
        },
        messages :
        {
          'f_type[]'       : { required:"type(s) manquant(s)" },
          f_remplissage    : { required:"contenu manquant" },
          f_colonne_bilan  : { required:"contenu manquant" },
          f_colonne_vide   : { required:"contenu manquant" },
          f_tri_objet      : { required:"choix manquant" },
          f_tri_mode       : { required:"choix manquant" },
          f_repeter_entete : { },
          f_retroactif     : { required:"choix manquant" },
          f_matiere        : { required:"matière manquante" },
          f_niveau         : { required:"niveau manquant" },
          f_groupe         : { required:"classe/groupe manquant" },
          'f_eleve[]'      : { required:"élève(s) manquant(s)" },
          f_eleves_ordre   : { required:"ordre manquant" },
          f_periode        : { required:"période manquante" },
          f_date_debut     : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_date_fin       : { required:"date manquante" , dateITA:"format JJ/MM/AAAA non respecté" },
          f_restriction    : { },
          f_coef           : { },
          f_socle          : { },
          f_lien           : { },
          f_orientation    : { required:"orientation manquante" },
          f_couleur        : { required:"couleur manquante" },
          f_fond           : { required:"fond manquant" },
          f_legende        : { required:"légende manquante" },
          f_marge_min      : { required:"marge mini manquante" },
          f_pages_nb       : { required:"choix manquant" },
          f_cases_nb       : { required:"nombre manquant" },
          f_cases_larg     : { required:"largeur manquante" }
        },
        errorElement : "label",
        errorClass : "erreur",
        errorPlacement : function(error,element)
        {
          if(element.attr("id")=='f_matiere') { element.next().after(error); }
          else if(element.attr("type")=="radio") {element.parent().next().next().after(error);}
          else if(element.attr("type")=="checkbox") {
            if(element.parent().parent().hasClass('select_multiple')) {element.parent().parent().next().after(error);}
            else {element.parent().next().after(error);}
          }
          else {element.after(error);}
        }
        // success: function(label) {label.text("ok").removeAttr("class").addClass("valide");} Pas pour des champs soumis à vérification PHP
      }
    );

    // Options d'envoi du formulaire (avec jquery.form.js)
    var ajaxOptions =
    {
      url : 'ajax.php?page='+PAGE+'&csrf='+CSRF,
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
        // récupération d'éléments
        $('#f_matiere_nom').val( $("#f_matiere option:selected").text() );
        $('#f_niveau_nom' ).val( $("#f_niveau option:selected").text() );
        $('#f_groupe_nom' ).val( $("#f_groupe option:selected").text() );
        $('#f_groupe_type').val( groupe_type );
        $(this).ajaxSubmit(ajaxOptions);
        return false;
      }
    ); 

    // Fonction précédent l'envoi du formulaire (avec jquery.form.js)
    function test_form_avant_envoi(formData, jqForm, options)
    {
      $('#ajax_msg').removeAttr("class").html("&nbsp;");
      var readytogo = validation.form();
      if(readytogo)
      {
        $('#bouton_valider').prop('disabled',true);
        $('#bilan').html("&nbsp;");
        $('#ajax_msg').removeAttr("class").addClass("loader").html("En cours&hellip;");
      }
      return readytogo;
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_erreur(jqXHR, textStatus, errorThrown)
    {
      $('#bouton_valider').prop('disabled',false);
      var message = (jqXHR.status!=500) ? 'Échec de la connexion !' : 'Erreur 500&hellip; Mémoire insuffisante ? Sélectionner moins d\'élèves à la fois ou demander à votre hébergeur d\'augmenter la valeur "memory_limit".' ;
      $('#ajax_msg').removeAttr("class").addClass("alerte").html(message);
    }

    // Fonction suivant l'envoi du formulaire (avec jquery.form.js)
    function retour_form_valide(responseHTML)
    {
      initialiser_compteur();
      $('#bouton_valider').prop('disabled',false);
      if(responseHTML.substring(0,6)=='<hr />')
      {
        $('#ajax_msg').removeAttr("class").addClass("valide").html("Résultat ci-dessous.");
        $('#bilan').html(responseHTML);
      }
      else if(responseHTML.substring(0,4)=='<h2>')
      {
        $('#ajax_msg').removeAttr("class").html('');
        // Mis dans le div bilan et pas balancé directement dans le fancybox sinon la mise en forme des liens nécessite un peu plus de largeur que le fancybox ne recalcule pas (et $.fancybox.update(); ne change rien).
        // Malgré tout, pour Chrome par exemple, la largeur est mal clculée et provoque des retours à la ligne, d'où le minWidth ajouté.
        $('#bilan').html('<p class="noprint">Afin de préserver l\'environnement, n\'imprimer que si nécessaire !</p>'+responseHTML);
        $.fancybox( { 'href':'#bilan' , onClosed:function(){$('#bilan').html("");} , 'centerOnScroll':true , 'minWidth':550 } );
      }
      else
      {
        $('#ajax_msg').removeAttr("class").addClass("alerte").html(responseHTML);
      }
    }

  }
);
