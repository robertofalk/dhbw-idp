<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="/css/style.css">
    <script>
        async function handleLogin(event) {
            event.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            const res = await fetch('/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            });

            if (res.ok) {
                const data = await res.json();
                localStorage.setItem('token', data.token);
                window.location.href = '/list-users';
            } else {
                alert('Login failed');
            }
        }

        window.onload = () => {
            document.getElementById('login-form').addEventListener('submit', handleLogin);
        };
    </script>
</head>
<body>
    <?= view('partials/header', ['showLogout' => false]) ?>

    <div class="centered-container">
        <div class="card">
            <h2>Login</h2>
            <form id="login-form">
                <label>Username:
                    <input type="text" id="username" required>
                </label>
                <label>Password:
                    <input type="password" id="password" required>
                </label>
                <button type="submit">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
