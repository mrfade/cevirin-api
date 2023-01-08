<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <th>Code</th>
            <th>Description</th>
        </thead>
        <tbody>
            @foreach ($codes as $code => $description)
            <tr>
                <td>{{ $code }}</td>
                <td>{{ $description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
