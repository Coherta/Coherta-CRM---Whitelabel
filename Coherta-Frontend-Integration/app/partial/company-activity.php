<div class="container-fluid company-activity" id="company-activity" aria-busy="true">
    <a class="ca-back" href="/app/my-account.php">&larr; My account</a>
    <section class="card">
        <div class="card-header ca-heading">
            <div>
                <h1>Company activity</h1>
                <p>Review recorded activity across your company account, newest first.</p>
                <span class="ca-account" id="ca-account">Loading your account&hellip;</span>
            </div>
            <button type="button" class="btn btn-outline-primary" id="ca-refresh" disabled>Refresh</button>
        </div>
        <div class="card-body">
            <form id="ca-filters" class="ca-filters">
                <div><label for="ca-category">Activity type</label>
                    <select id="ca-category" name="category" class="form-select">
                        <option value="all">All activity</option>
                        <option value="campaigns">Campaign activity</option>
                        <option value="leads">Leads added</option>
                        <option value="crm">Notes and pipeline changes</option>
                        <option value="tasks">Tasks</option>
                        <option value="workflows">Workflow changes</option>
                    </select>
                </div>
                <div><label for="ca-from">From</label><input type="date" class="form-control" id="ca-from" name="from"></div>
                <div><label for="ca-to">To</label><input type="date" class="form-control" id="ca-to" name="to"></div>
                <button type="submit" class="btn btn-primary" id="ca-apply">Apply filters</button>
                <button type="button" class="btn btn-light" id="ca-reset">Reset</button>
            </form>
            <p class="ca-coverage">Includes completed campaign actions, added leads, notes, pipeline changes, tasks and workflow edits. History reflects the records retained in your account. Times are shown as recorded.</p>
            <div class="alert alert-danger" id="ca-error" role="alert" hidden></div>
            <div class="ca-state" id="ca-state" role="status" aria-live="polite">Loading company activity&hellip;</div>
            <div class="ca-table-wrap" id="ca-results" hidden>
                <table class="table ca-table">
                    <caption class="visually-hidden">Company activity history, newest first</caption>
                    <thead><tr><th scope="col">Time</th><th scope="col">Activity</th><th scope="col">Contact / company</th><th scope="col">User</th><th scope="col">Record</th></tr></thead>
                    <tbody id="ca-rows"></tbody>
                </table>
            </div>
            <div class="ca-pagination"><span id="ca-count" aria-live="polite"></span><button type="button" class="btn btn-outline-primary" id="ca-more" hidden>Load older activity</button></div>
            <noscript><p>Enable JavaScript to load your company activity.</p></noscript>
        </div>
    </section>
</div>
