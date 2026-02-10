import { display_table } from "../reuseable/display_table";

document.addEventListener("DOMContentLoaded", () => {
    const customerName = document.getElementById("customer-name").dataset.name;

    display_table(`/customer/attach/${customerName}`, "#customer_atthach", [
        { data: "customer_name", searchable:false },
        {data:"note"},
        {data:"file_path"},
        {data:"created_at"},
        {data:"created_by"},
        
    ]);
});
