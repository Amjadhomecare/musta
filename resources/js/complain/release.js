import { display_table } from "../reuseable/display_table";

$(document).ready(function () {
    const columns = [
        { data: "maid_name", name: "maid_name" },     // from join
        { data: "nationality", name: "releases.nationality" },
        { data: "agent", name: "releases.agent" },
        { data: "note", name: "releases.note" },
        { data: "new_status", name: "releases.new_status" },
        { data: "status", name: "releases.status" },  // server returns label
        { data: "maid_type", name: "maids_d_b_s.maid_type" },
        { data: "created_by", name: "releases.created_by" },
        { data: "updated_by", name: "releases.updated_by" },
        { data: "created_at", name: "releases.created_at" },
        {
            data: "actions",
            name: "actions",
            orderable: false,
            searchable: false,
        },
    ];

    display_table("/list-release", "#release_table", columns);

    // Delete
    $(document).on("click", ".delete", async function () {
        const id = $(this).data("id");
        if (!confirm("Are you sure you want to delete this release?")) return;

        try {
            const response = await fetch(`/delete-release/${id}`, {
                method: "DELETE",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            });

            const data = await response.json();
            if (response.ok) {
                $("#release_table").DataTable().ajax.reload(null, false);
                alert(data.message || "Deleted");
            } else {
                alert("Failed to delete release: " + (data.message || ""));
            }
        } catch (error) {
            console.error("Delete error: ", error);
            alert("Failed to delete release. Please try again.");
        }
    });
});

$(document).ready(function () {
  $("#name")
    .select2({
      placeholder: "Search for a maid",
      minimumInputLength: 1,
      ajax: {
        url: "/all/maids",
        dataType: "json",
        delay: 250,
        data: function (params) {
          return { search: params.term, page: params.page || 1 };
        },
        processResults: function (data) {
          // items contain { id: <name>, text, system_id: <numeric id> }
          return {
            results: data.items,
            pagination: { more: data.total_count > data.items.length },
          };
        },
        cache: true,
      },
      allowClear: true,
    })
    .on("select2:select", function (e) {
      const selected = e.params.data;
      const maidNumericId = selected.system_id;

      // ✅ set hidden maid_id for the form submit
      $("#maid_id").val(maidNumericId || "");

      // fetch details by numeric id (no controller change)
      if (!maidNumericId) return;
      fetch("/ajax-maid/" + maidNumericId)
        .then((r) => {
          if (!r.ok) throw new Error("Maid not found");
          return r.json();
        })
        .then((data) => {
          $("#nationality").val(data.nationality || "");
          $("#agent").val(data.agent || "");
        })
        .catch((err) => {
          console.error(err);
          alert("Failed to load maid info.");
        });
    })
    .on("select2:clear", function () {
      // clear hidden when user clears the selection
      $("#maid_id").val("");
      $("#nationality").val("");
      $("#agent").val("");
    });

  // Optional: guard submit if maid_id is missing
  $('form[action$="storeMaidRelease"]').on("submit", function (e) {
    if (!$("#maid_id").val()) {
      e.preventDefault();
      alert("Please select a maid from the list.");
    }
  });
});
