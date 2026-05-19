<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Helpdesk API Docs</title>
    <script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference"></script>
    <style>
        html,
        body {
            margin: 0;
            min-height: 100%;
        }
    </style>
</head>
<body>
    <div id="app"></div>

    <script>
        Scalar.createApiReference('#app', {
            url: '/docs/api.json',
        })
    </script>
</body>
</html>
