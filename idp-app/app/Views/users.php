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
                    <td>${user.username}</td>
                    <td>${user.role}</td>
                    <td>
                        <button onclick="editUser(${user.id}, '${user.username}', '${user.role}')">Edit</button>
                        <button onclick="deleteUser(${user.id})">Delete</button>
                    </td>
                `;
                table.appendChild(row);
            });
        }

        function editUser(id, username, role) {
            editingUserId = id;
            document.getElementById('username').value = username;
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

            const username = document.getElementById('username').value;
            const role = document.getElementById('role').value;
            const password = document.getElementById('password').value;

            const payload = { username, role };
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

        function toggleChat() {
            const chatBox = document.getElementById('chat-box');
            chatBox.style.display = chatBox.style.display === 'flex' ? 'none' : 'flex';
        }

        async function handleChat(event) {
            event.preventDefault();
            const input = document.getElementById('chat-input');
            const messages = document.getElementById('chat-messages');

            const userText = input.value.trim();
            if (userText === '') return;

            // Show user message
            const userMsg = document.createElement('div');
            userMsg.textContent = "You: " + userText;
            messages.appendChild(userMsg);

            input.value = '';
            messages.scrollTop = messages.scrollHeight;

            const token = localStorage.getItem('token');
            const res = await fetch('/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify({ message: userText })
            });

            const data = await res.json();

            // Show bot reply
            const botMsg = document.createElement('div');
            botMsg.textContent = "Bot: " + data.reply;
            messages.appendChild(botMsg);

            messages.scrollTop = messages.scrollHeight;
            if (data.action === 'refresh') {
                fetchUsers();
            }
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
                <label>Username:
                    <input type="text" id="username" required>
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
                    <tr><th>ID</th><th>Username</th><th>Role</th><th>Actions</th></tr>
                </thead>
                <tbody id="user-list"></tbody>
            </table>
        </div>
    </div>
    <!-- Chat button -->
    <div id="chat-toggle" onclick="toggleChat()" title="Chat">
        💬
    </div>

    <!-- Chat box -->
    <div id="chat-box">
        <div id="chat-header">ChatBot <span onclick="toggleChat()" style="cursor:pointer; float:right;">&times;</span></div>
        <div id="chat-messages"></div>
        <form id="chat-form" onsubmit="handleChat(event)">
            <input type="text" id="chat-input" placeholder="Type a message..." autocomplete="off" />
            <button type="submit">Send</button>
        </form>
    </div>

</body>
</html>
