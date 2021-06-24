var showText = function (target, message, index, interval) {    
    if (index < message.length) { 
      $(target).append(message[index++]); 
      setTimeout(function () { showText(target, message, index, interval); }, interval); 
    } 
  }
  $(function () { 
    showText("h1", "Erreur 404 : Game Over! Page non trouvée.", 0, 80);    
  }); 