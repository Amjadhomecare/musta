import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";

document.addEventListener("DOMContentLoaded", () => {
    handleFormPostSubmission(
        "maidForm",
        "/add-maid",
        "#cv_datatable",
        "#add-cv-modal"
    );
    handleFormPostSubmission(
        "maidFormEdit",
        "/update-maid-cv",
        "#cv_datatable",
        "#edit-cv-modal"
    );
    handleFormPostSubmission(
        "maidFormBooked",
        "/book/maid",
        "#cv_datatable",
        "#booked-cv-modal"
    );
    handleFormPostSubmission(
        "maidStatus",
        "/update/status",
        "#cv_datatable",
        "#changing-status-cv-modal"
    );
    handleFormPostSubmission(
        "maidVideoLink",
        "/update/video",
        "#cv_datatable",
        "#video-link-cv-modal"
    );
    handleFormPostSubmission(
        "maidDoc",
        "/attach/maid/doc-expiry",
        "#cv_datatable",
        "#expiry-cv-modal"
    );
    handleFormPostSubmission(
        "maidFilter",
        "/filter-update",
        "#cv_datatable",
        "#maidFilterModal"
    );

    const columns = [
        { data: "action", orderable: false, searchable: false },
        { data: "created_at" },
        { data: "img", orderable: false, searchable: false },
        { data: "name" },
        { data: "salary" },
        { data: "maid_status" },
        { data: "nationality" },
        { data: "agency" },
        { data: "maid_type" },
        { data: "maid_booked" },
        { data: "payment" },
        { data: "visa_status" },
        { data: "filters" },
        { data: "passport_exp_date" },
        { data: "visit_visa_expired" },
        { data: "passport_number" , title : "Passport" },
        { data: "created_by" },
        { data: "updated_by" },
        { data: "created_at" },
        {
            data: "updated_at",
            render: function (data, type, row) {
                return data ? moment(data).format("DD-MM-YYYY HH:mm") : "";
            },
        },
    ];

    display_table("/all-maids", "#cv_datatable", columns);

    $('#includeNullBook').change(function () {
        let includeNullBook = $(this).is(':checked') ? 'true' : 'false';
        $('#cv_datatable').DataTable().ajax.url('/all-maids?includeNullBook=' + includeNullBook).load();
    });
    



    function applyFilters() {
        let firstFilter = $("#filterNationality").val();
        let secondFilter = $("#filterAgent").val();
        let statusFilter = $("#filterStatus").val();
        let filterPackage = $("#filterPackage").val();
        let filter_book = $("#filter_book").val();
        let filter_visa = $("#visa_filter").val();
    
        let params = new URLSearchParams();
    
        if (statusFilter) params.set("status", statusFilter);
        if (firstFilter) params.set("nationality", firstFilter);
        if (secondFilter) params.set("agent", secondFilter);
        if (filterPackage) params.set("package", filterPackage);
        if (filter_book) params.set("book", filter_book);
        if (filter_visa) params.set("visa", filter_visa);
    
        let newUrl = window.location.pathname + "?" + params.toString();
        history.replaceState(null, "", newUrl);

        $("#cv_datatable")
            .DataTable()
            .column(5)
            .search(statusFilter)
            .column(6)
            .search(firstFilter)
            .column(7)
            .search(secondFilter)
            .column(8)
            .search(filterPackage)
            .column(9)
            .search(filter_book)
            .column(11)
            .search(filter_visa)
            .draw();
    }
    

    $("#filterNationality,#filterAgent,#filterStatus,#filterPackage,#filter_book,#visa_filter").on("change", applyFilters);
    

    $(document).ready(function () {
        let params = new URLSearchParams(window.location.search);
    
        $("#filterStatus").val(params.get("status") || "");
        $("#filterNationality").val(params.get("nationality") || "");
        $("#filterAgent").val(params.get("agent") || "");
        $("#filterPackage").val(params.get("package") || "");
        $("#filter_book").val(params.get("book") || "");
        $("#visa_filter").val(params.get("visa") || "");
    
        if ([...params].length > 0) {
            applyFilters();
        }
    });
    
});


$("#filterVisaExpiry").on("click", function () {
    let visaExpiryValue = $("#visa-expiry-input").val();
    $("#visa-expiry-filter").val(visaExpiryValue);
    $("#cv_datatable").DataTable().draw();
});

$(document).on("click", ".edit-modal-btn", function () {
    let maidId = $(this).data("id");

    $.ajax({
        url: "/maid/" + maidId,
        type: "GET",
        success: function (data) {
            $("#edit-cv-modal").modal("show");
            $("#maidNameInput").val(data.name);
            $("#maidNationalityInput").val(data.nationality);
            $("#maidAgencyInput").val(data.agency);
            $("#maidSalaryInput").val(data.salary);
            $("#maidIdInput").val(data.id);
            $("#maidImgInput").attr("src", data.img);
            $("#edit_maid_type").val(data.maid_type);
            $("#maidImg2Input").attr("src", data.img2);
            $("#edit_english").val(data.lang_english);
            $("#edit_arabic").val(data.lang_arabic);
            $("#edit_note").val(data.note);
            $("#edit_exp_country").val(data.exp_country);
            $("#edit_period_country").val(data.period_country);
            $("#edit_book").val(data.maid_booked);
            $("#edit_visit_visa_expired").val(data.visit_visa_expired);
            $("#edit_passport_expired").val(data.passport_exp_date);
            $("#edit_visa_status").val(data.visa_status);
            $("#edit_cooking_level").val(data.cooking);
            $("#edit_religion").val(data.religion);
            $("#edit_marital_status").val(data.marital_status);
            $("#edit_education").val(data.education);
            $("#edit_height").val(data.height);
            $("#edit_weight").val(data.weight);
            $("#edit_passport_number").val(data.passport_number);
            $("#edit_nationality").val(data.nationality);
            $("#edit_payment").val(data.payment);
            $("#edit_dob").val(data.dob);
            $("#edit_agent_ref").val(data.agent_ref);
            $("#edit_phone").val(data.phone_maid);
            $("#edit_child").val(data.child);
            $("#edit_age").val(data.age);
            $("#start_as_p4").val(data?.start_as_p4);
            $("#edit_uae_id").val(data?.uae_id_maid);
            $("#current_book")
            .val(data.maid_booked)
            .text(data.maid_booked)
        
            $("#edit_book").val(data.maid_booked);

            $("#thebook").text(data?.maid_booked );

            $("#edit_moi").val(data?.moi);
            $("#edit_branch").val(data?.branch);

            $("#edit_pob").val(data?.meta?.pob || '');
            $("#uid").val(data?.uid );

            console.log( data?.uid );
    
        },
        error: function (xhr) {
            alert("Error: " + xhr.responseJSON.message);
        },
    });
});

$(document).on("click", ".book-modal-btn", function () {
    let maidId = $(this).data("id");

    $.ajax({
        url: "/maid/" + maidId,
        type: "GET",
        success: function (data) {
            $("#booked-cv-modal").modal("show");
            $("#booked_name").val(data.name);
            $("#booked_id").val(data.id);
            $("#note_book").val(data?.maid_booked);
        },
        error: function (xhr) {
            alert("Error: " + xhr.responseJSON.message);
        },
    });
});

$(document).on("click", ".video-btn", function () {
    let maidId = $(this).data("id-video");

    $.ajax({
        url: "/maid/" + maidId,
        type: "GET",
        success: function (data) {
            $("#video-link-cv-modal").modal("show");
            $("#video_link").val(data.video_link);
            $("#video_name").val(data.name);
            $("#video_id").val(data.id);
        },
        error: function (xhr) {
            alert("Error: " + xhr.responseJSON.message);
        },
    });
});

$(document).on("click", ".status-btn", function () {
    let maidId = $(this).data("id-status");
    $.ajax({
        url: "/maid/" + maidId,
        type: "GET",
        success: function (data) {
            $("#changing-status-cv-modal").modal("show");
            $("#status_maid_name").val(data.name);
            $("#status_id").val(data.id);
            $("#current_status").val(data.maid_status);
        },
        error: function (xhr) {
            alert("Error: " + xhr.responseJSON.message);
        },
    });
});

$(document).on("click", ".expire-modal-btn", function () {
    let maidId = $(this).data("id");
    $.ajax({
        url: "/doc-expire/" + maidId,
        type: "GET",
        success: function (data) {
            console.log(data);

            $("#maid_id").val(data.id);
            $("#labor_card_expiry").val(data.maid_doc_expiry.labor_card_expiry);
            $("#passport_expiry").val(data.maid_doc_expiry.passport_expiry);
            $("#visa_expiry").val(data.maid_doc_expiry.visa_expiry);
            $("#eid_expiry").val(data.maid_doc_expiry.eid_expiry);
        },
        error: function (xhr) {
            alert("Error: " + xhr.responseJSON.message);
        },
    });
});

$(document).on("click", ".filter-modal-btn", function () {
    let maidId = $(this).data("id");
    $("#filter_maid_id").val(maidId);

    $.ajax({
        url: "/maid-filter/" + maidId,
        type: "GET",
        success: function (data) {
            $("#has_dog").prop("checked", data.maidFilter?.has_dog || false);
            $("#has_cat").prop("checked", data.maidFilter?.has_cat || false);
            $("#working_days_off").prop(
                "checked",
                data.maidFilter?.working_days_off || false
            );
            $("#babysitting").val(data.maidFilter?.babysitting || "");
            $("#private_room").prop(
                "checked",
                data.maidFilter?.private_room || false
            );
            $("#elderly_care").prop(
                "checked",
                data.maidFilter?.elderly_care || false
            );
            $("#special_needs_care").prop(
                "checked",
                data.maidFilter?.special_needs_care || false
            );
            $("#knows_syrian_lebanese").prop(
                "checked",
                data.maidFilter?.knows_syrian_lebanese || false
            );
            $("#can_assist_and_cook").prop(
                "checked",
                data.maidFilter?.can_assist_and_cook || false
            );
            $("#knows_gulf_food").prop(
                "checked",
                data.maidFilter?.knows_gulf_food || false
            );
            $("#international_cooking").prop(
                "checked",
                data.maidFilter?.international_cooking || false
            );

            $("#baby_0_to_6").prop(
                "checked",
                data.maidFilter?.baby_0_to_6 || false
            );

            $("#baby_6_to_12").prop(
                "checked",
                data.maidFilter?.baby_6_to_12 || false
            );

            $("#baby_1_to_2").prop(
                "checked",
                data.maidFilter?.baby_1_to_2 || false
            );

            $("#baby_2_to_6").prop(
                "checked",
                data.maidFilter?.baby_2_to_6 || false
            );

            $("#live_out").prop("checked", data.maidFilter?.live_out || false);

            $("#maidFilterModal").modal("show");
        },
        error: function (xhr) {
            alert(
                "Error fetching maid filter data: " + xhr.responseJSON.message
            );
        },
    });
});


document.addEventListener("keydown", function(event) {
      
    if (event.key.toLowerCase() === "m" && !event.target.matches("input, textarea, select")) {
        event.preventDefault(); 
        document.querySelector(".open-modal-btn").click(); 
    }
});


document.addEventListener("DOMContentLoaded", function() {
    let modal = document.getElementById("add-cv-modal");

    modal.addEventListener("shown.bs.modal", function () {
        document.getElementById("name").focus();
    });
});