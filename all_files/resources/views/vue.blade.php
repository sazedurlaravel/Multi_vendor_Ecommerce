<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>This is my first vue page !</title>
</head>
<body>
    @foreach ($featuredproducts as $item)
        {{$item->vender}}
        {{$item->store}}
        {{$item->subvariants}}
    @endforeach
</body>
</html>