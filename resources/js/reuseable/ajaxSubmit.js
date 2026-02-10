//For reusable ajax functions

export const handleFormPostSubmission = async (
    formId,
    url,
    tableSelector = null,
    modalSelector = null
) => {
    const form = document.getElementById(formId);

    if (!form) {
        console.error(`Form with id "${formId}" not found.`);
        return;
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const submitButton = form.querySelector("button[type='submit']");
        submitButton.disabled = true;
        submitButton.innerHTML = "Submitting...";

        const formData = new FormData(form);

        try {
            const response = await fetch(url, {
                method: "POST",
                body: formData,
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            });

            const result = await response.json();

            if (response.ok) {
                submitButton.disabled = false;
                submitButton.innerHTML = "submit";
                Swal.fire({
                    title: "Sucsess",
                    text: result.message ?? "Added Sucsessfully",
                    icon: "success",
                    confirmButtonText: "Close",
                });

                if (tableSelector)
                    $(tableSelector).DataTable().ajax.reload(null, false);
                if (modalSelector) $(modalSelector).modal("hide");
                form.reset();
            } else {
                submitButton.disabled = false;
                submitButton.innerHTML = "submit";
                console.error("Error:", result.message);
                Swal.fire({
                    title: "Error",
                    text: result.message,
                    icon: "error",
                    confirmButtonText: "Close",
                });
            }
        } catch (error) {
            console.error("Error:", error);
        }
    });
};



export const handleAjaxFormSubmit = async (
    url,
    formSelector,
    modalSelector,
    tableSelector
) => {
    const form = document.querySelector(formSelector);

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            fetch(url, {
                method: "POST",
                body: formData,
                headers: {
                    "X-CSRF-TOKEN": " {{ csrf_token() }}",
                    Accept: url,
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === "success") {
                        Swal.fire({
                            title: "Success!",
                            text: "saved successfully",
                            icon: "success",
                        }).then((result) => {
                            if (result.isConfirmed || result.isDismissed) {
                                $(modalSelector).modal("hide");
                                $(tableSelector)
                                    .DataTable()
                                    .ajax.reload(null, false);
                                form.reset();
                            }
                        });
                    } else {
                        let errorMessage = "Something went wrong:\n";
                        if (data.errors) {
                            for (const key in data.errors) {
                                errorMessage += `${key}: ${data.errors[
                                    key
                                ].join(", ")}\n`;
                            }
                        }
                        Swal.fire({
                            title: "Error!",
                            text: errorMessage,
                            icon: "error",
                            confirmButtonText: "Close",
                        });
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    Swal.fire({
                        title: "Unexpected Error",
                        text: "An unexpected error occurred",
                        icon: "error",
                        confirmButtonText: "Close",
                    });
                });
        } catch (error) {
            console.error("Error:", error);
        }
    });
};

export const handleAjaxReportDisplay = async (
    formId = null,
    url,
    html,
    method,
    fromIdDisplay
) => {
    const form = document.getElementById(formId);

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch(url, {
                method: method,
                body: formData,
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            });

            const result = await response.json();

            if (response.ok) {
                document.getElementById(fromIdDisplay).innerHTML = html;
            } else {
                console.error("Error:", result.message);
                Swal.fire({
                    title: "Error",
                    text: result.message,
                    icon: "error",
                    confirmButtonText: "Close",
                });
            }
        } catch (error) {
            console.error("Error:", error);
            alert("Network Error or Server Issue");
        }
    });
};

export const displayDynamicReport = async (url, method, callBacksuccess) => {
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                Accept: "application/json",
            },
        });

        if (!response.ok) {
            throw new Error("Network response was not ok");
        }

        const result = await response.json();

        if (result.message === "success") {
            //the result should have data in the response
            callBacksuccess(result.data);
        } else {
            console.error("Unexpected response:", result);
        }
    } catch (error) {
        console.error("Error fetching report data:", error);
    }
};
