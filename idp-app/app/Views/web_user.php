<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link rel="stylesheet" href="/css/style.css">
    <script>
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '/';
        }

        let editingUserId = null;

        function logout() {
            localStorage.removeItem('token');
            window.location.href = '/';
        }

        async function fetchUsers() {
            const res = await fetch('/users', {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            });

            if (res.status === 401) {
                alert('Session expired. Please log in again.');
                localStorage.removeItem('token');
                window.location.href = '/';
                return;
            }

            const users = await res.json();
            const table = document.getElementById('user-list');
            table.innerHTML = '';

            users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.name}</td>
                    <td>${user.role}</td>
                    <td>
                        <button onclick="editUser(${user.id}, '${user.name}', '${user.role}')">Edit</button>
                        <button onclick="deleteUser(${user.id})">Delete</button>
                    </td>
                `;
                table.appendChild(row);
            });
        }

        function editUser(id, name, role) {
            editingUserId = id;
            document.getElementById('name').value = name;
            document.getElementById('role').value = role;
            document.getElementById('password').value = '';
            document.getElementById('submit-button').textContent = 'Update User';
        }

        async function deleteUser(id) {
            if (!confirm('Are you sure you want to delete this user?')) return;

            await fetch(`/users/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            });
            fetchUsers();
        }

        async function handleSubmit(event) {
            event.preventDefault();

            const name = document.getElementById('name').value;
            const role = document.getElementById('role').value;
            const password = document.getElementById('password').value;

            const payload = { name, role };
            if (password) payload.password = password;

            const method = editingUserId ? 'PUT' : 'POST';
            const url = editingUserId ? `/users/${editingUserId}` : '/users';

            await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(payload)
            });

            editingUserId = null;
            document.getElementById('user-form').reset();
            document.getElementById('submit-button').textContent = 'Create User';
            fetchUsers();
        }

        window.onload = () => {
            document.getElementById('user-form').addEventListener('submit', handleSubmit);
            document.getElementById('reset-button').addEventListener('click', () => {
                editingUserId = null;
                document.getElementById('user-form').reset();
                document.getElementById('submit-button').textContent = 'Create User';
            });

            fetchUsers();
        };
    </script>
</head>
<body>
    <?= view('partials/header', ['showLogout' => true]) ?>

    <div class="centered-container">
        <div class="card">
            <h2>User Management</h2>
            <form id="user-form">
                <label>Name:
                    <input type="text" id="name" required>
                </label>
                <label>Role:
                    <select id="role">
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                </label>
                <label>Password:
                    <input type="password" id="password">
                </label>
                <button id="submit-button" type="submit">Create User</button>
                <button type="button" id="reset-button">Reset</button>
            </form>

            <h3 style="margin-top: 2rem;">Users</h3>
            <table border="1" width="100%">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Role</th><th>Actions</th></tr>
                </thead>
                <tbody id="user-list"></tbody>
            </table>
        </div>
    </div>
</body>
</html>
