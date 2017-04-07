function ringbell(typeCall){
    $.ajax({
        method: "POST",
        url: "../fusionbells/api.php",
        context: document.body,
        data: { "call" : "manual", "tone" : typeCall}
    }).done(function(msg){
        console.log(msg);
        console.log(typeCall + " sent.");
    });
}