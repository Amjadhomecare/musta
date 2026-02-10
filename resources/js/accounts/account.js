import { selectServerSideSearch } from "../reuseable/server_side_search";

$(document).ready(function () {
    let table = $("#account_statement").DataTable({
        order: [[0, "desc"]],
        dom: "Blfrtip",
        buttons: ["excel", "pdf", "print"],

        pageLength: 10,
        lengthMenu: [
            [10, 50, 100, 300, 1000, 2000],
            [10, 50, 100, 300, 1000, 2000],
        ],
    });

selectServerSideSearch('#selected_ledger','/add/new/ledger','#gg')

});


document.addEventListener('DOMContentLoaded', () => {
    const RATE = 3.675;          
    let inUSD = false;

    const format = v => v.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    document.getElementById('toggleCurrency')
            .addEventListener('click', function () {

        document.querySelectorAll('.convertible').forEach(el => {
            const aed = parseFloat(el.dataset.aed);         
            const val = inUSD ? aed : aed / RATE;
            el.textContent = format(val);
        });

        inUSD = !inUSD;
        this.textContent = inUSD ? 'USD Display' : 'In AED Display';
    });
});



