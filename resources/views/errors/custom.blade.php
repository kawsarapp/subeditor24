<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorry - Something Went Wrong</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { text-align: center; padding: 40px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="error-card">
        <h1 class="display-1 fw-bold text-danger">⚠️</h1>
        <h2 class="mb-3">Sorry, Something Went Wrong!</h2>
        <p class="text-muted">Our technical team is investigating. Please try again shortly.</p>
        <a href="{{ url('/') }}" class="btn btn-primary px-4">Back to Homepage</a>
    </div>
</body>
</html>