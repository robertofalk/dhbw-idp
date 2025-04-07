<!DOCTYPE html>
<html>
<head>
    <title>Hello Page</title>
    <script>
        async function fetchHello() {
            const res = await fetch('/hello');
            const data = await res.json();
            document.getElementById('output').textContent = data.message;
        }

        window.onload = fetchHello;
    </script>
</head>
<body>
    <h1>CodeIgniter Web Page</h1>
    <p id="output">Loading...</p>
</body>
</html>
