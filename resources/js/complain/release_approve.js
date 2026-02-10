import { display_table } from "../reuseable/display_table";
import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";

$(document).ready(function () {
  const columns = [
    {
      data: "created_at",
      name: "releases.created_at",
      render: (d) => (d ? new Date(d).toLocaleDateString("en-CA") : ""),
    },
    {
      data: "maid_name",
      name: "maids_d_b_s.name",
      render: function (data, type, row) {
        // Link by maid_id (stable)
        return `<a href="/payroll-note/${row.maid_name}" target="_blank">${row.maid_name ?? ""}</a>`;
      },
    },
    { data: "maid_status", name: "maids_d_b_s.maid_status" },
    { data: "nationality", name: "releases.nationality" },
    { data: "maid_type", name: "maids_d_b_s.maid_type" },
    { data: "agent", name: "releases.agent" },
    { data: "new_status", name: "releases.new_status" },
    { data: "note", name: "releases.note" },
    {
      data: "id", // releases.id (not used in render, but present)
      orderable: false,
      searchable: false,
      render: function (data, type, row) {
        // IMPORTANT: include maid_id (numeric) for the approve flow
        return `
          <a
            class="open-modal-btn btn btn-primary btn-sm mb-3"
            href="#"
            data-id="${row.maid_id}"
            data-maid="${row.maid_name}"
            data-agent="${row.agent ?? ""}"
            data-status="${row.new_status ?? ""}"
            data-bs-toggle="modal"
            data-bs-target="#approving-form-modal"
          >
            Approving
          </a>
        `;
      },
    },
    { data: "created_by", name: "releases.created_by" },
  ];

  handleFormPostSubmission(
    "approve_maid",
    "/ajax-release-maid",
    "#pending-relesed-datatable",
    "#approving-form-modal"
  );

  // Server must return: maid_id, maid_name, maid_status, nationality, agent, new_status, note, created_by, created_at, id
  display_table("/ajax-release-list", "#pending-relesed-datatable", columns);

  $(document).on("click", ".open-modal-btn", function (e) {
    e.preventDefault();

    const maidId = $(this).data("id");      // maid_id (numeric)
    const maidName = $(this).data("maid");    // maid_name (display)
    const agent = $(this).data("agent");
    const status = $(this).data("status");

    // Fill modal fields
    $("#maidNameInput").val(maidName || "");
    $("#maidIDInput").val(maidId || "");      // hidden maid_id to submit

    // Initialize/select agent account inside modal
    $("#agentAccountSelect")
      .select2({
        dropdownParent: $("#approving-form-modal"),
        width: "100%",
      })
      .val(agent || "")
      .trigger("change");

    // Reset and set status
    $("#new_status")
      .empty()
      .append(`<option value="${status ?? ""}" selected>${status ?? ""}</option>`);

    $("#approving-form-modal").modal("show");
  });
});
