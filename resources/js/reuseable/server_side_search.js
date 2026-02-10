export const selectServerSideSearch = (
    selectElement,
    url,
    dropdownParent,
    placeholder = "Search"
) => {
    $(selectElement).select2({
        dropdownParent: $(dropdownParent),
        placeholder: placeholder,
        minimumInputLength: 1,
        ajax: {
            url: url,
            dataType: "json",
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                    page: params.page || 1,
                };
            },
            processResults: function (data) {
                return {
                    results: data.items,
                    pagination: {
                        more: data.total_count > data.page * 30,
                    },
                };
            },
            cache: true,
        },
    });
};
