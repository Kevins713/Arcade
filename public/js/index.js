// Initilisation des messages flashes
$(function() {
    setTimeout(function(){
        $('.toast').toast('show');
    }, 500);

});


// Fonction pour récuperer une liste d'utilisateur et les inserer avec un foreach
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

// Fonction pour récuperer des stats du site
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


    // Requête ajax pour récuperer les utilisateurs connectés et les insérer dans l'aside
    requestUsers( url + 'api/liste-utilisateurs-connectes', 'users-connected');

// Requête pour récupérer les admins connectés et les insérer dans l'aside
    requestUsers(url + 'api/liste-admins-connectes', 'admins-connected');

// Requête pour récupérer le nombre de Forums et les insérer dans l'aside
    requestStats(url + 'api/nombre-forums', 'forums-number');

// Requête pour récupérer le nombre de messages et les insérer dans l'aside
    requestStats(url + 'api/nombre-messages', 'comments-number');

// Requête pour récupérer le nombre de messages et les insérer dans l'aside
    requestStats(url + 'api/nombre-utlisateurs', 'users-number');

    // Requête pour récupérer les 5 derniers forums
    $.ajax({
        type: 'GET',
        url:  + 'api/derniers-messages',
        dataType: 'json',
        timeout: '7500',
        error: function(){
            console.log('error');
        },
        success: function(data){
            $('.aside-content').text('');
            data.forEach((forum) => {
                console.log(forum);
                // $('.' + classSelector).append('<span><a href="127.0.0.1:8000/profil/' + pseudonym.pseudonym +'"> ' + pseudonym.pseudonym + '</span></a>');
                // $('.' + classSelector).append(`
                //     <div class="row">
                //         <div class="col-3 aside avatar d-flex align-items-center">
                //             <img src="` + asset + '/images/usersAvatar/' + forum. + `) }}" alt="">
                //         </div>
                //         <div class="col-9 aside">
                //             <h6>Aled</h6>
                //             <p>Je sais pas quoi faire pour faire...</p>
                //         </div>
                //     </div>
                // `)
            });
        },
    })

    // Délai de 30sec avant de rafraîchir les infos
    setTimeout(requestApis, 30000);

}

requestApis();


