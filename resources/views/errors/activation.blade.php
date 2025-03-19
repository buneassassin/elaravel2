<!DOCTYPE html>
<html>
<head>
    <title>Error de activación</title>
</head>
<body>
    <h1>{{ $message }}</h1>
    @if(isset($errors))
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
</body>
</html>
