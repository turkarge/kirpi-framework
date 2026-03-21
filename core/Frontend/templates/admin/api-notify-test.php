<article class="panel">
    <h2>API Response -> Notify Testi</h2>
    <p>
        Asagidaki butonlar API endpointine istek atar. Donen JSON yapisina gore toast otomatik olusur.
    </p>
    <div class="inline">
        <button class="btn btn-primary" type="button" data-case="success">Success Response</button>
        <button class="btn btn-ghost" type="button" data-case="info">Info Response</button>
        <button class="btn btn-ghost" type="button" data-case="warning">Validation Response</button>
        <button class="btn btn-ghost" type="button" data-case="error">Error Response</button>
        <button class="btn btn-ghost" type="button" data-case="custom">Custom Notify Payload</button>
    </div>
    <pre id="apiNotifyOutput" style="margin-top:12px; overflow:auto; max-height:240px;">Henuz istek gonderilmedi.</pre>
</article>
<script>
    (() => {
        const output = document.getElementById('apiNotifyOutput');
        let bound = false;
        if (!output) return;

        function bindButtons() {
            if (bound) return;
            if (!window.kirpiApi) return;
            bound = true;

            document.querySelectorAll('[data-case]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const testCase = String(button.getAttribute('data-case') || '');
                    try {
                        const result = await window.kirpiApi.request('/kirpi/api-notify-sample?case=' + encodeURIComponent(testCase), {
                            method: 'GET',
                            notifyOnSuccess: true,
                        });

                        output.textContent = JSON.stringify(result, null, 2);
                    } catch (error) {
                        output.textContent = JSON.stringify({
                            ok: false,
                            status: 0,
                            payload: {
                                error: error instanceof Error ? error.message : String(error),
                            },
                        }, null, 2);
                    }
                });
            });
        }

        bindButtons();
        window.addEventListener('kirpi:notify-ready', bindButtons, {once: true});
    })();
</script>
