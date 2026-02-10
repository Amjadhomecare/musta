import {ajaxSelector} from "../ajax_select2";

// Initialize Select2 for a given selector
export function initializeSelect2(selector, url, placeholder, dropdownParent) {
    const element = document.querySelector(selector);
    if (element && document.querySelector(dropdownParent)) {
        ajaxSelector(selector, url, placeholder, dropdownParent);
    } else {
        console.log(`Select2 initialization skipped: Element ${selector} or ${dropdownParent} missing.`);
    }
}

// Initialize Select2 for modal
export function initializeModalSelect2(modalSelector, selectSelector, url, placeholder) {
    const modalElement = document.querySelector(modalSelector);
    const selectElement = document.querySelector(selectSelector);
    
    if (modalElement && selectElement) {
        $(selectElement).select2({
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            },
            placeholder: placeholder,
            minimumInputLength: 1,
            dropdownParent: $(modalElement),
            allowClear: true,         // Allows the user to clear the selected value
            width: '100%'             // Set the width of the dropdown to 100% of its parent element
        });
    }
}
