$('document').ready(function(){

    document.querySelector('#dateDepuis').min = new Date().toJSON().slice(0,10);
    document.querySelector('#dateUntil').min = new Date().toJSON().slice(0,10);
});