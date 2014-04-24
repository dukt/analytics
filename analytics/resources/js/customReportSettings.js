$(document).ready(function() {
    $('.analytics-add-filter').click(function() {
        $id = $('.analytics-filters tr').length - 1;

        $clone = $('.analytics-filters .prototype').clone();
        $clone.removeClass('prototype');
        $clone.removeClass('hidden');
        $clone.appendTo('.analytics-filters tbody');

        $inputs = $('select, input', $clone);

        $.each($inputs, function(k, v) {
            $name = $(v).attr('name');
            $name = $name.replace('{index}', $id);
            $(v).attr('name', $name);
        });


        return false;
    });

    $(document).on('click', '.analytics-remove-filter', function() {
        $(this).parents('tr').remove();

        return false;
    });

    $('.analytics-filters').parents('form').submit(function() {
        $('.analytics-filters .prototype').remove();
    });
});