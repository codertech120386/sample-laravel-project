<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if($status)
    <title>true</title>
    @else
    <title>false</title>
    @endif
</head>

<body>
    @if($status)
    <h1>Your transaction was successful</h1>
    @else
    <h1>Oops something went wrong</h1>
    @endif
</body>

</html>