import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";
import { selectServerSideSearch } from "../reuseable/server_side_search";

$(document).ready(function () {

    const customerName = document.getElementById("customer-name").dataset.name;

  display_table(`/stripe-links/${customerName}`,'#url_table',[{data:'maid_name'} ,
    {data:'url'},
    {data:'amount'},
    {data:'created_by'},
    {data:'created_at'},

  ]);

    selectServerSideSearch(
        "#maidName",
        "/all/maids",
        "#addStripe",
        "search maid"
    );

    handleFormPostSubmission(
        "stripeForm",
        "/store-stripe-link",
        "#url_table",
        "#addStripe"
    );

});