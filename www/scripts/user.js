$(document).ready(function () {
    $("#user-table tbody tr").on("click", function (e) {
        if ($(e.target).is("a")) return;

        const userId = $(this).data("id");
        if (userId) {
            window.location.href = "/user/" + userId;
        }
    });
});