import { display_table } from "../reuseable/display_table";

$(document).ready(function () {
    display_table("/table-log", "#book_datatable", [
        { data: "maid_name" },
        { data: "user_name" },
        { data: "changes" },
        { 
            data: "created_at",
            render: function (data, type, row) {
                if (type === "display" || type === "filter") {
                    let date = new Date(data);
                    let formattedDate = date.toISOString().split("T")[0]; // Extract YYYY-MM-DD
                    let formattedTime = date.toTimeString().split(" ")[0]; // Extract HH:MM:SS
                    return `${formattedDate} ${formattedTime}`;
                }
                return data;
            }
        }
    ]);
});
