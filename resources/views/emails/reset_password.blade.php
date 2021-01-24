<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset Password</title>
</head>
<body>
    <h1>Hai {{ $user->name }}</h1>
    <p>Kamu sudah melakukan permintaan reset password, berikut token reset password </p>
    <p><h3><pre>{{ $user->remember_token }}</pre></h3></p>

</body>
</html>