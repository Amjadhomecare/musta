
import { display_table } from "../reuseable/display_table";

document.addEventListener("DOMContentLoaded", () => {
    const customerName = document.getElementById("customer-name").dataset.name;


    display_table(`/all-p4/${customerName}`, "#allp4dataTable", [
        { data: "created_at" },
        { data: "date" },
         {data:"returned_date"},

         {data:"date_difference"},
      
        {data:"reason"},
        { data: "maid" },
        { data: "Contract_ref" },
        { data: "contract_status", name: "contract_status" },
        { data: "created_by" },
   

    ]);
})