/* ------------------------------------------------------------------
   Generic debounce helper
------------------------------------------------------------------ */
/* ------------------------------------------------------------------
   Reusable DataTables builder: Keen Bootstrap 5 dark-mode flavour
------------------------------------------------------------------ */
export const display_table = (url, table_name, columns) => {
  // Restore persisted date filters from URL
  const params = new URLSearchParams(window.location.search);
  const minDate = params.get("min_date") || "";
  const maxDate = params.get("max_date") || "";
  $("#min-date").val(minDate);
  $("#max-date").val(maxDate);

  const dataTable = $(table_name).DataTable({
    responsive: true,
    processing: true,
    serverSide: true,
    searchDelay: 700,

    ajax: {
      url: url,
      type: "GET",
      data(d) {
        d.min_date = $("#min-date").val() || null;
        d.max_date = $("#max-date").val() || null;

        const p = new URLSearchParams(window.location.search);
        d.min_date ? p.set("min_date", d.min_date) : p.delete("min_date");
        d.max_date ? p.set("max_date", d.max_date) : p.delete("max_date");
        // (Optional) avoid polluting history with every keystroke
        window.history.replaceState({}, "", "?" + p.toString());
      },
      error(xhr, err) {
        console.error("AJAX error:", err);
        console.error(xhr.responseText);
      },
    },

    columns,
    order: [[0, "desc"]],
    dom:
      "<'row mb-3 align-items-center'<'col-md-6 d-flex justify-content-start'f>" +
      "<'col-md-6 d-flex justify-content-end align-items-center gap-2' B l>>" +
      "<'table-responsive 'tr>" +
      "<'row mt-3'<'col-md-5'i><'col-md-7 d-flex justify-content-end'p>>",

    pageLength: 10,
    lengthMenu: [
      [10, 50, 100, 300, 1000, 2000, 10000],
      [10, 50, 100, 300, 1000, 2000, 10000],
    ],
  });

  // One-time style block
  if (!$("#dt-keen-common").length) {
    $("<style id='dt-keen-common'>\
      .dataTables_wrapper .dt-buttons{margin:0!important}\
      .dataTables_wrapper .dt-buttons .btn{margin-bottom:0!important}\
      .dataTables_wrapper .dataTables_filter label{display:flex;align-items:center;margin:0!important}\
    </style>").appendTo("head");
  }

  const clean = table_name.replace("#", "");

  dataTable.on("init", () => {
    // ---------- SEARCH BAR TWEAKS ----------
    const input = $(`input[aria-controls='${clean}']`);
    const wrapper = input.closest("div");

    wrapper.find("label").contents().filter((_, n) => n.nodeType === 3).remove();

    // Keen styling (wrapping won't break the default keyup listener)
    if (!wrapper.find(".keen-search-btn").length) {
      wrapper.addClass("ms-2 d-inline-block position-relative");
      input.wrap("<button type='button' \
                    class='btn btn-primary keen-search-btn d-flex align-items-center p-4'></button>");
      wrapper.find(".keen-search-btn").prepend(
        '<i class="ki-duotone ki-magnifier fs-3 me-2">\
           <span class="path1"></span><span class="path2"></span>\
         </i>'
      );
    }

    const phClass = `dt-ph-${clean}`;
    input.attr("placeholder", "Type to search…")
      .addClass(`border-0 bg-transparent ps-2 text-white ${phClass}`)
      .css({ width: "150px", outline: "none" });

    if (!$(`head style#ph-${clean}`).length) {
      $(`<style id='ph-${clean}'>\
          .${phClass}::placeholder{color:#fff!important;opacity:.65}\
        </style>`).appendTo("head");
    }

    // ---------- PER-PAGE SELECT ----------
    const select = $(`select[name='${clean}_length'][aria-controls='${clean}']`);
    const btnH = $(`${table_name}_wrapper .dt-buttons .btn`).first().outerHeight() || 38;

    select.siblings("label").contents().filter((_, n) => n.nodeType === 3).remove();

    select.removeClass()
      .addClass("form-select form-select-sm keen-length-select")
      .css({
        height: btnH + "px",
        lineHeight: btnH + "px",
        paddingTop: "0",
        paddingBottom: "0",
        minWidth: "4.5rem",
        borderRadius: ".475rem",
      })
      .parent().addClass("d-flex align-items-center");

  });

  $("#min-date, #max-date").on("change", () => dataTable.draw());

  return dataTable;
};


export const generateDataAttributesClintServer = (row, fields) => {
  return fields.reduce((acc, field) => {
    return `${acc} data-${field}="${row[field]}" `;
  }, "");
};

export const populateModalFieldsClintServer = (modalSelector, fields) => {
  return function () {
    let data = {};
    fields.forEach((field) => {
      data[field] = $(this).data(field);
    });

    $(modalSelector).modal("show");

    fields.forEach((field) => {
      $(`#${field}Input`).val(data[field]);
    });
  };
};

export const updateTotals = (
  entrySelector,
  totalSelector,
  amountPrefix,
  qtnPrefix,
  totalPrefix
) => {
  let totalAmount = 0;
  document.querySelectorAll(entrySelector).forEach((entryRow) => {
    const uniqueId = entryRow.dataset.uniqueId;
    const amountInput = document.getElementById(
      `${amountPrefix}${uniqueId}`
    );
    const qtnInput = document.getElementById(`${qtnPrefix}${uniqueId}`);
    const totalInput = document.getElementById(`${totalPrefix}${uniqueId}`);

    const amount = parseFloat(amountInput.value) || 0;
    const quantity = parseInt(qtnInput.value) || 0;
    const entryTotal = amount * quantity;

    totalInput.value = entryTotal;
    totalAmount += entryTotal;
  });

  const total = document.getElementById(totalSelector);
  total.value = totalAmount;
};
