import { handleFormPostSubmission } from "../reuseable/ajaxSubmit";
import { display_table, generateDataAttributesClintServer, populateModalFieldsClintServer } from "../reuseable/display_table";

const users_data = ['id', 'name', 'group'  , 'active']

const userColumns = [
    { data: 'created_at' },
    {
        data: 'name',
        render: function (data, type, row) {
            return `<p>${row.name}</p>`;
        }
    },
    {
        data: 'group',
        render: function (data, type, row) {
            return `<p>${row.group}</p>`;
        },
    },
        {
            data: 'active',
            render: function (data, type, row) {
                if (row.active == 1) {
                    return `<span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Active</span>`;
                } else {
                    return `<span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Inactive</span>`;
                }
            },
        },


    {
        data: 'id',
        render: function (data, type, row) {
            const dataAttributes = generateDataAttributesClintServer(row, users_data);
            return `<div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton${data}" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ri-menu-fill"></i>  <!-- For Remix Icon -->
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton${data}">
                            <li>
                                <a class="dropdown-item sm e user-btn" href="#" ${dataAttributes} title="Edit" data-bs-toggle="modal" data-bs-target="#edit-users">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </li>
                        </ul>
                    </div>`;
        }
    }
];

$(document).on('click', '.user-btn', populateModalFieldsClintServer('#edit-users', users_data));

display_table("/fetch/users", '#users-datatable', userColumns);

document.addEventListener('DOMContentLoaded', function () {
    handleFormPostSubmission('addUserForm', '/post/user', '#users-datatable');
    handleFormPostSubmission('updateuserForm', '/update/user', '#users-datatable', '#edit-users');

    function applyFilters() {
        let firstFilter = $('#filterUserGrpup').val();
        let userFilter = $('#userName').val();

        $('#users-datatable').DataTable()
                             .column(2).search(firstFilter)
                             .column(1).search(userFilter)
                             .draw();
    }

    $('#filterUserGrpup, #userName').on('change', applyFilters);
});
