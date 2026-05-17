<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Parkir App</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body { background:#f4f6f9; }

        .sidebar {
            width:240px;
            height:100vh;
            position:fixed;
            background: linear-gradient(180deg, #3a7bd5, #00d2ff);
            color:white;
        }
        
        .sidebar a {
            display:block;
            padding:12px;
            color:white;
            text-decoration:none;
        }

        .sidebar a:hover {
            background: rgba(255,255,255,0.2);
        }
                .content {
            margin-left:250px;
            padding:20px;
        }

        .card {
            border-radius:12px;
            box-shadow:8 4px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>