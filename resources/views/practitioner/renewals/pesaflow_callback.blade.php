<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Payment Complete</title>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial; margin:0; padding:20px; }
        .card { max-width:720px; margin:40px auto; padding:20px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; box-shadow:0 6px 18px rgba(0,0,0,0.04); text-align:center }
        .muted { color:#6b7280 }
    </style>
</head>
<body>
    <div class="card">
        <h2>Payment complete</h2>
        <p class="muted">{{ $message ?? 'Thank you. Redirecting...' }}</p>
        <p class="muted">If you are not redirected, <a id="openInvoices" href="{{ $redirect }}" target="_top">click here</a>.</p>
    </div>

    <script>
        (function(){
            var redirectUrl = <?php echo json_encode($redirect); ?>;
            try {
                // If inside an iframe, navigate the top window to the invoices page
                if (window.top && window.top !== window.self) {
                    window.top.location = redirectUrl;
                } else {
                    window.location = redirectUrl;
                }
            } catch(e) {
                // Fallback: set the current location
                window.location = redirectUrl;
            }
        })();
    </script>
</body>
</html>