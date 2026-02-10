import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";

document.addEventListener("DOMContentLoaded", (event) => {
    handleFormPostSubmission(
        "customerForm",
        "/save-customer",
        "#customers-datatable",
        "#customer-form-modal"
    );
    handleFormPostSubmission(
        "customerFormEdit",
        "/update_customer",
        "#customers-datatable",
        "#edit_customer_modal"
    );
});

display_table("/ajax-customers-list", "#customers-datatable", [
    {
        data: "name",
        render: function (data, type, row) {
            return `<a href="customer/report/${row.name}" target="_blank"> ${row.name}</a>`;
        },
    },
    {
        data: "phone",
        render: function (data, type, row) {
            return `<span > ${row.phone}</span>`;
        },
    },
    {
        data: "secondaryPhone",
        render: function (data, type, row) {
            return `<span > ${row.secondaryPhone}</span>`;
        },
    },
    {
        data: "idNumber",
        render: function (data, type, row) {
            return `<span > ${row.idNumber}</span>`;
        },
    },
    {
        data: "idType",
        render: function (data, type, row) {
            return `<span > ${row.idType}</span>`;
        },
    },
    {
        data: "cusomerType",
        render: function (data, type, row) {
            return `<span > ${row.cusomerType}</span>`;
        },
    },
    {
        data: "related",
        render: function (data, type, row) {
            return `<span > ${row.related}</span>`;
        },
    },
    {
        data: "email",
        render: function (data, type, row) {
            return `<span > ${row.email}</span>`;
        },
    },
    {
        data: "nationality",
        render: function (data, type, row) {
            return `<span > ${row.nationality}</span>`;
        },
    },
    {
        data: "address",
        render: function (data, type, row) {
            return `<span > ${row.address}</span>`;
        },
    },
    {
        data: "idImg",
        render: function (data, type, row) {
            return `<a href="${row.idImg}" target="_blank"><img src="${row.idImg}" alt="No image" style="width: 50px; height: auto;"></a>`;
        },
    },
    {
        data: "note",
        render: function (data, type, row) {
            return `<span > ${row.note}</span>`;
        },
    },
    {
        data: "created_by",
        render: function (data, type, row) {
            return `<span > ${row.created_by}</span>`;
        },
    },
    {
        data: "created_at",
        render: function (data, type, row) {
            return `<span > ${row.created_at}</span>`;
        },
    },
    {
        data: "id",
        render: function (data, type, row) {
            return `<div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton${data}" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-menu-fill"></i>  <!-- For Remix Icon -->
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton${data}">
                            <li>
                                <a class="dropdown-item sm edit_customer" href="#" title="Edit"
                                    data-id="${row.id}"
                                    data-name="${row.name}"
                                    data-related="${row.related}"
                                    data-note="${row.note}"
                                    data-phone="${row.phone}"
                                    data-secondary-phone="${row.secondaryPhone}"
                                    data-id-type="${row.idType}"
                                    data-id-number="${row.idNumber}"
                                    data-nationality="${row.nationality}"
                                    data-cusomer-type="${row.cusomerType}"
                                    data-email="${row.email}"
                                    data-address="${row.address}"
                                    data-id-img="${row.idImg}"
                                    data-passport-img="${row.passportImg}">

                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item sm delete_maid" href="#"
                                    data-id="${row.id}">
                                    <i class="ri-delete-bin-6-fill"></i> Delete
                                </a>
                            </li>
                        </ul>
                    </div>`;
        },
    },
]);

$(document).on("click", ".edit_customer", function () {
    let row = $(this).data();
    $("#edit_cus_id").val($(this).attr("data-id"));
    $("#edit_cus_name").val($(this).attr("data-name"));
    $("#edit_cus_related").val($(this).attr("data-related"));
    $("#edit_cus_note").val($(this).attr("data-note"));
    $("#edit_cus_phone").val($(this).attr("data-phone"));
    $("#edit_cus_sPhone").val($(this).attr("data-secondary-phone"));
    $("#edit_cus_ID_type").val($(this).attr("data-id-type"));
    $("#edit_cus_ID_num").val($(this).attr("data-id-number"));
    $("#edit_cus_nationality").val($(this).attr("data-nationality"));
    $("#edit_cus_type").val($(this).attr("data-cusomer-type"));
    $("#edit_cus_email").val($(this).attr("data-email"));
    $("#edit_cus_address").val($(this).attr("data-address"));
    $("#edit_cus_ID_img").val($(this).attr("data-idImg"));
    $("#edit_cus_pass_img").val($(this).attr("data-passportImg"));

    if (row.idImg) {
        $("#current_cus_ID_img").attr("src", row.idImg);
    }
    if (row.passportImg) {
        $("#current_cus_pass_img").attr("src", row.passportImg);
    }

    $("#edit_customer_modal").modal("show");
});

document.addEventListener("keydown", function(event) {
      
    if (event.key.toLowerCase() === "m" && !event.target.matches("input, textarea, select")) {
        event.preventDefault(); 
        document.querySelector(".open-modal-btn").click(); 
    }
});


document.addEventListener("DOMContentLoaded", function() {
    let modal = document.getElementById("customer-form-modal");

    modal.addEventListener("shown.bs.modal", function () {
        document.getElementById("name").focus();
    });
});