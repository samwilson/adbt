$(document).ready(function(){
    $("input[name='username']").focus();

    /**
    * Database, Edit
    */
    $('.edit-action .related-tables h3').click(function() {
        $(this).next().toggle('slow');
        return false;
    }).next().hide();

    $('input.datepicker').datepicker({
        dateFormat: 'yy-mm-dd',
        showOn: 'button'
    });
    $('input.datepicker + button')
        .addClass('ui-icon')
        .addClass('ui-icon-calculator')
        .width(20).height(20)
        .css('width', '20px')
        .css('height', '20px')
        .css('border-width', 'thin')
        .attr('title', 'Pop-up calendar widget');

    $('div.tableview h4.debug').click(function(){
        $('dl.debug').dialog("open");
    });
    $("div.tableview").find('dl.debug').dialog({
        width: 600,
        autoOpen: false,
    });

});
