
$(function() {
    setTimeout(function(){
        $('.toast').toast('show');
    }, 500);

});


let requestUsers = function(url, classSelector){
    $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        timeout: '7500',
        success: function(data){
            $('.' + classSelector).text('');
            data.forEach((pseudonym) => {
                $('.' + classSelector).append('<span><a href="127.0.0.1:8000/profil/' + pseudonym.pseudonym +'"> ' + pseudonym.pseudonym + '</span></a>');
            });
        },
    })
}
let requestStats = function(url, classSelector) {
    $.ajax({
        type: 'GET',
        url: url,
        dataType: 'json',
        timeout: '7500',
        success: function(data){
            $('.' + classSelector).text('');
            $('.' + classSelector).append('<span>' + data[0][1] + '</span></a>');
        },
    })
}


let requestApis = function () {


    // Requête ajax pour récuperer les utilisateurs connectés
    requestUsers('http://localhost:8000/api/liste-utilisateurs-connectes', 'users-connected');

// Requête pour récupérer les admins connectés
    requestUsers('http://localhost:8000/api/liste-admins-connectes', 'admins-connected');


// Requête pour récupérer le nombre de Forums
    requestStats('http://localhost:8000/api/nombre-forums', 'forums-number');

// Requête pour récupérer le nombre de messages
    requestStats('http://localhost:8000/api/nombre-messages', 'comments-number');

// Requête pour récupérer le nombre de messages
    requestStats('http://localhost:8000/api/nombre-utlisateurs', 'users-number');

    // Délai de 30sec avant de rafraîchir les infos
    setTimeout(requestApis, 30000);

}

requestApis();


