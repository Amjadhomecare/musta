import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table } from "../reuseable/display_table";

$(document).ready(function () {
display_table("/ajax-arrival-list", "#pending-arrival-datatable", [
  {
    data: "created_at",
    render: data => (data ? new Date(data).toLocaleDateString("en-CA") : ""),
  },
  {
    data: "maid_id",
    render: (data, type, row) => {
      return `<a href="/maid-report/${row.maid_info.name}" target="_blank">${row.maid_info.name}</a>`;
    },
  },
  { data: "maid_status" },
  { data: "nationality" },
  { data: "agent" },
  { data: "note" },
  {
    data: "id",
    render: (data, type, row) => {
      return `
        <a 
          class="open-modal-btn btn-block mb-3"
          data-bs-toggle="modal"
          data-bs-target="#approving-form-modal"
          data-maid-name="${row.maid_info.name}"
          data-maid-id="${row.maid_id}"
          data-agent="${row.agent}"
        >Approving</a>`;
    },
  },
]);

   handleFormPostSubmission(
        "approve_maid",
        "/ajax-approve-maid",
        "#pending-arrival-datatable",
        "#approving-form-modal"
    );


$(document).on("click", ".open-modal-btn", function () {
  const maidName = $(this).data("maid-name");
  const maidId   = $(this).data("maid-id");
  const agent    = $(this).data("agent");

  $("#maidNameInput").val(maidName);
  $("#maidIdInput").val(maidId);          
  $("#agentAccountSelect").val(agent).trigger('change');

  $("#agentAccountSelect").select2({
    dropdownParent: $("#approving-form-modal"),
    width: "100%",
  });

  $("#approving-form-modal").modal("show");
});

});