/*$('document').ready(function(){

    document.querySelector('#dateDepuis').min = new Date().toJSON().slice(0,10);
    document.querySelector('#dateUntil').min = new Date().toJSON().slice(0,10);
});*/


function checkDate(type)
{
    var dateDebut,dateFin;
    dateDebut = document.getElementById("dateDepuis");
    dateFin = document.getElementById("dateUntil");

    if(dateDebut.value == "" && type == "fin")
    {
        dateDebut.max = dateFin.value;
        dateDebut.value = dateFin.value;
    }
    else if(dateDebut.value != "" && type == "fin")
    {
        dateDebut.max = dateFin.value;
    }

    if(dateFin.value == "" && type == "debut")
    {
        dateFin.min = dateDebut.value;
        dateFin.value = dateDebut.value;
    }
    else if(dateFin.value != "" && type == "debut")
    {
        dateFin.min = dateDebut.value;
    }
}