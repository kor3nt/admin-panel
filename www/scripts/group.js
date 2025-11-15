$(document).ready(function () {
    // Adding event for table users - direct to group page 
    $("#group-table tbody tr").on("click", function (e) {
        if ($(e.target).is("a")) return;

        const userId = $(this).data("id");
        if (userId) {
            window.location.href = "/group/" + userId;
        }
    });
});
