<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="SwaggerUI"/>
    <title>{{ config('app.name') }} | Frontend APIs Swagger</title>
    <link href="{{asset('vendor/swagger/main.css')}}" rel="stylesheet">
</head>
<body>
<div id="swagger-ui"></div>
<script src="{{asset('vendor/swagger/main.js')}}"></script>
<script>
    window.onload = () => {
        window.ui = SwaggerUIBundle({
            url: "{{ $url }}",
            dom_id: '#swagger-ui',
        });
    };
</script>
</body>
</html>
