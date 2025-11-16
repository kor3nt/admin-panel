$(document).ready(function () {
    // Support for clicks on all delete buttons
    $(document).on("click", ".delete-btn, #delete-item-detail", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const url = $(this).attr("href");
        deleteAlert(url);
    });

});

// Delete alert feature
function deleteAlert(url) {
    if (confirm("Are you sure you want to delete? This action cannot be undone.")) {
        window.location.href = url;
    }
}
