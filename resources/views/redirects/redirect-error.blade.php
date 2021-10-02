<html lang="en">
<head>
    <title>Link Redirect Failed</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet">
    <style>
        body {
            background: rgb(250, 250, 250);
            color: rgba(0, 0, 0, 0.87);
            padding: 80px 30px 30px;
            font-family: Roboto,'Helvetica Neue',sans-serif;
        }
        .logo {
            text-align: center;
            margin-bottom: 25px;
        }
        img {
            max-width: 200px;
        }
        .panel {
            background: rgb(255, 255, 255);
            margin: auto;
            border: 1px solid rgba(0, 0, 0, 0.12);
            padding: 25px;
            border-radius: 4px;
            max-width: 800px;
            text-align: center;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 22px;
            font-weight: 400;
        }
        p {
            font-size: 16px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
<div class="logo">
    <img class="img-responsive" src="client/assets/images/logo-dark.png" alt="logo">
</div>
<div class="panel">
    <h1>Could not redirect to destination url</h1>
    <p>{{$message}}</p>
    <p class="secondary">(This error page is only visible to link owner)</p>
</body>
</html>
