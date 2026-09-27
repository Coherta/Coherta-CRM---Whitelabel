<script src="https://analytics.ahrefs.com/analytics.js" data-key="6DjQ+kFGmftpqbE9M2Ht1g" async></script>
<script>
    let users = [];
    function publishCompanyUsers() {
        window.users = users;
        document.dispatchEvent(new CustomEvent("coherta:users-ready", { detail: { users } }));
    }
    (async function () {
        const key = "company_users";
        if (sessionStorage.getItem(key)) {
            users = JSON.parse(sessionStorage.getItem("company_users") || "[]");
            publishCompanyUsers();
            return;
        }
        try {

            const bearerToken = document.body.getAttribute('session_id');
            const res = await fetch("https://api.coherta.com/users/me", {
                method: "GET",
                headers: { "Accept": "application/json", "Authorization": "Bearer " + bearerToken }
            });
            if (!res.ok) {
                console.warn("Users request failed:", res.status);
                return;
            }
            const data = await res.json();
            if (data && data.error) {
                console.warn("Users API error:", data.message);
                return;
            }
            sessionStorage.setItem(key, JSON.stringify(data.colleagues));
            users = JSON.parse(sessionStorage.getItem("company_users") || "[]");
            publishCompanyUsers();
        } catch (err) {
            console.warn("Users fetch exception:", err);
        }
    })();
</script>
