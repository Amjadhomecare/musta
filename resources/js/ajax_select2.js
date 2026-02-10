//js/ajax_select2.js

export function ajaxSelector(
    selector,
    ajaxUrl,
    placeholder,
    dropdownParent,
    context = null
) {
    let selectElement = $(selector);
    if (selectElement.length > 0) {
        selectElement.select2({
            dropdownParent: $(dropdownParent),
            ajax: {
                url: ajaxUrl,
                dataType: "json",
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                        page: params.page,
                        context: context,
                    };
                },
                processResults: function (data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: params.page * 30 < data.total_count,
                        },
                    };
                },
                cache: true,
            },
            placeholder: placeholder,
            minimumInputLength: 1, 
            allowClear: true, 
            width: "100%", 

            
        });

               $(dropdownParent).on("shown.bs.modal", function () {
                selectElement.select2("open"); 
            });

            selectElement.on("select2:open", function () {
                setTimeout(() => {
                    let searchField = document.querySelector(".select2-container--open .select2-search__field");
                    if (searchField) {
                        searchField.focus();
                    }
                }, 300); 
            });
    
            selectElement.on("click", function () {
                $(this).select2("open");
            });
            
    } else {
        console.log("Element does not exist"); 
    }
}
