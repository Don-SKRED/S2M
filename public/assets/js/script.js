$(document).ready(function($) {

    $('.table_data').DataTable({

            "language": {
                "lengthMenu": "Afficher _MENU_ données par pages",
                "zeroRecords": "Nous n'avons rien trouvé - désolé",
                "info": " page _PAGE_ sur _PAGES_",
                "infoEmpty": "Aucun enregistrement disponibles",
                "infoFiltered": "(Filtrer à partir de _MAX_ enregistrement)",
                "search": "Rechercher:",
                "paginate": {
                    "first": "Premier",
                    "last": "Dernier",
                    "next": "Suivant",
                    "previous": "Précedent"
                },
            },
            //spécifier pour la table courrier
        //    "order": [[0, 'desc'], [1, 'desc'], [2, 'desc'], [3, 'desc'], [4, 'desc'], [5, 'desc']]
            /* "columnDefs": [
                 {
                     "orderSequence" : [ "asc", "desc" ],
                     "targets" : "_all"
                 }
             ]*/
        }
    );
    $('.table_data2').DataTable({

        "language": {
            "lengthMenu": "Afficher _MENU_ données par pages",
            "zeroRecords": "Nous n'avons rien trouvé - désolé",
            "info": " page _PAGE_ sur _PAGES_",
            "infoEmpty": "Aucun enregistrement disponibles",
            "infoFiltered": "(Filtrer à partir de _MAX_ enregistrement)",
            "search": "Rechercher:",
            "paginate": {
                "first": "Premier",
                "last": "Dernier",
                "next": "Suivant",
                "previous": "Précedent"
            },
        },

    });
});

