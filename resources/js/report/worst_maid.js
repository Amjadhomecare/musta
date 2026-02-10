import { display_table } from "../reuseable/display_table";

$(document).ready(function () {


    display_table("/table-wrost-p4", "#worst_maid_datatable", [
        { data: "name" },
        { data: "nationality" },
        { data: "record_count" }
    
    ]);
});
