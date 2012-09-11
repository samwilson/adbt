$(document).ready(function(){
    $("input[name='username']").focus();

    /**
    * Database, Edit
    */
    $('.edit-action .related-tables h3').click(function() {
        $(this).next().toggle('slow');
        return false;
    }).next().hide();
});
