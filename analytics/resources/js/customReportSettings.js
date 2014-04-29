$(document).ready(function() {

    // form

    $('#content form').submit(function() {
        $('.analytics-chart-type.hidden').remove();
        $('.geo-dimension.hidden').remove();
    });

    // toggles
    $('.analytics-toggle select').change(function() {
        console.log('change', $(this).val());

        $('.geo-dimension').addClass('hidden');
        $('.geo-dimension[rel="'+$(this).val()+'"]').removeClass('hidden');
    });

    $('.analytics-toggle select').trigger('change');

    // filters

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