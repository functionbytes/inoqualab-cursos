$(function () {
    $(document).on('click', '.btn-view-issues', function () {
        var issues = $(this).data('issues');
        var html = '';

        if (!issues || issues.length === 0) {
            html = '<p class="text-muted">No hay issues registrados.</p>';
        } else {
            issues.forEach(function (issue) {
                var badgeClass = issue.status === 'error' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning';
                html += '<div class="border rounded p-3 mb-2">';
                html += '<div class="d-flex align-items-start gap-2">';
                html += '<span class="badge ' + badgeClass + ' mt-1">' + issue.status + '</span>';
                html += '<div>';
                html += '<p class="mb-1 fw-semibold">' + $('<div>').text(issue.message).html() + '</p>';
                if (issue.recommendation) {
                    html += '<p class="text-muted">' + $('<div>').text(issue.recommendation).html() + '</p>';
                }
                html += '</div></div></div>';
            });
        }

        $('#issuesModalBody').html(html);
        $('#issuesModal').modal('show');
    });
});
